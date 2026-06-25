#!/usr/bin/env bash
#
# fetch-contracts.sh — the pull-fresh engine for the SPARXSTAR Contracts Registry.
#
# Runs INSIDE a checkout of this registry that has already been placed on the
# requested contract-ref. Resolves which contracts a consumer is allowed to
# pull (MANIFEST-driven + binding filter), enforces the version policy
# (floor + v3-bug guard), copies the contract tree into an output directory,
# and injects a DO-NOT-EDIT header on every fetched file.
#
# It NEVER writes back to the registry — distribution is read-only. The optional
# drift report-back is a separate step in fetch-contracts.yml using a different
# (write) token.
#
# Required environment:
#   CONTRACT_REF          The ref the registry is checked out at (tag/branch/sha).
#   OUT_DIR               Directory to write fetched contracts into.
# Optional environment:
#   REQUESTED_CONTRACTS   Space/comma list of contract ids from MANIFEST.json.
#                         Empty = every contract that passes the binding filter.
#   CONSUMER              owner/repo of the calling consumer. When set, only
#                         contracts that list this repo in `consumers` are fetched.
#   REGISTRY              owner/repo of this registry (for the header). Optional.

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

: "${CONTRACT_REF:?CONTRACT_REF is required}"
: "${OUT_DIR:?OUT_DIR is required}"
REQUESTED_CONTRACTS="${REQUESTED_CONTRACTS:-}"
CONSUMER="${CONSUMER:-}"
REGISTRY="${REGISTRY:-Starisian-Technologies/sparxstar-platform-contracts}"
MANIFEST="MANIFEST.json"
POLICY="config/version-policy.yml"

command -v jq >/dev/null 2>&1 || { echo "::error::jq is required"; exit 1; }
[[ -f "$MANIFEST" ]] || { echo "::error::$MANIFEST not found at $CONTRACT_REF"; exit 1; }
[[ -f "$POLICY" ]]   || { echo "::error::$POLICY not found at $CONTRACT_REF"; exit 1; }

# ---------------------------------------------------------------------------
# 1. Version policy: floor + recommended + v3-bug guard.
# ---------------------------------------------------------------------------
floor="$(grep -E '^[[:space:]]*floor:' "$POLICY" | head -1 | sed -E 's/^[[:space:]]*floor:[[:space:]]*//; s/[[:space:]]*(#.*)?$//' | tr -d '"'\''')"
[[ -n "$floor" ]] || { echo "::error::no floor in $POLICY"; exit 1; }

# Highest real semver tag in the repo, resolved at runtime, is the recommended pin.
semver_re='^v[0-9]+\.[0-9]+\.[0-9]+$'
recommended="$(git tag --list 'v*' | grep -E "$semver_re" | sort -V | tail -1 || true)"
[[ -n "$recommended" ]] || echo "::warning::no immutable vMAJOR.MINOR.PATCH tag exists yet; recommended pin is undefined"

# The requested ref MUST resolve to a real object. An invalid / non-existent ref
# is a HARD FAIL — never silently degrade to a default branch (the v3 bug).
if ! git rev-parse --verify --quiet "${CONTRACT_REF}^{commit}" >/dev/null; then
  echo "::error::contract-ref '${CONTRACT_REF}' does not resolve to a commit in this registry. Refusing to fetch (v3-bug guard)."
  exit 1
fi

# When the requested ref is itself a version tag, enforce the floor.
ref_tag=""
if git rev-parse --verify --quiet "refs/tags/${CONTRACT_REF}" >/dev/null; then
  ref_tag="$CONTRACT_REF"
fi
if [[ -n "$ref_tag" && "$ref_tag" =~ ^v[0-9] ]]; then
  # Compare ref_tag against floor with version sort; lowest must be the floor.
  lowest="$(printf '%s\n%s\n' "$floor" "$ref_tag" | sort -V | head -1)"
  if [[ "$lowest" != "$floor" && "$ref_tag" != "$floor" ]]; then
    echo "::error::contract-ref '${ref_tag}' is below the version-policy floor '${floor}'."
    exit 1
  fi
fi

echo "Version policy: floor=${floor} recommended=${recommended:-<none>} requested=${CONTRACT_REF}"

# ---------------------------------------------------------------------------
# 2. Resolve which contracts to fetch (binding filter + request + consumer).
# ---------------------------------------------------------------------------
mapfile -t allowed_statuses < <(jq -r '.binding_filter[]' "$MANIFEST")
status_filter="$(printf '%s\n' "${allowed_statuses[@]}" | jq -R . | jq -s .)"

# Normalise the requested list (commas -> spaces).
req_norm="$(echo "$REQUESTED_CONTRACTS" | tr ',' ' ')"

mapfile -t selected < <(
  jq -r \
    --argjson statuses "$status_filter" \
    --arg consumer "$CONSUMER" \
    --arg requested "$req_norm" \
    '
    ($requested | split(" ") | map(select(length > 0))) as $req
    | .contracts
    | to_entries[]
    | select(.value.status as $s | $statuses | index($s))
    | select(($req | length) == 0 or (.key as $k | $req | index($k)))
    | select($consumer == "" or (.value.consumers | index($consumer)))
    | "\(.key)\t\(.value.path)\t\(.value.status)"
    ' "$MANIFEST"
)

if [[ ${#selected[@]} -eq 0 ]]; then
  echo "::error::no contracts matched (requested='${REQUESTED_CONTRACTS}' consumer='${CONSUMER}' filter=${allowed_statuses[*]})."
  exit 1
fi

# ---------------------------------------------------------------------------
# 3. Copy + inject DO-NOT-EDIT header.
# ---------------------------------------------------------------------------
mkdir -p "$OUT_DIR"
fetched_json="$OUT_DIR/fetched-manifest.json"
echo '{"registry":"'"$REGISTRY"'","contract_ref":"'"$CONTRACT_REF"'","recommended":"'"${recommended:-}"'","floor":"'"$floor"'","contracts":[]}' > "$fetched_json"

inject_header() {
  local file="$1" id="$2"
  local tmp
  tmp="$(mktemp)"
  case "$file" in
    *.php)
      # Insert the header right after the opening <?php tag if present.
      if head -1 "$file" | grep -q '<?php'; then
        {
          echo '<?php'
          cat <<EOF
/**
 * DO NOT EDIT — fetched from ${REGISTRY}@${CONTRACT_REF} (contract: ${id}).
 * This is a pulled-fresh copy of a canonical/ratified contract. Edits here are
 * meaningless: the next fetch overwrites them. To change the contract, open a
 * PR in the registry via propose-contract.yml.
 */
EOF
          tail -n +2 "$file"
        } > "$tmp"
        mv "$tmp" "$file"
      fi
      ;;
    *.md)
      {
        echo "<!-- DO NOT EDIT — fetched from ${REGISTRY}@${CONTRACT_REF} (contract: ${id}). Propose changes in the registry; do not edit this pulled copy. -->"
        cat "$file"
      } > "$tmp"
      mv "$tmp" "$file"
      ;;
    *)
      rm -f "$tmp"
      ;;
  esac
}

for row in "${selected[@]}"; do
  IFS=$'\t' read -r id path status <<< "$row"
  [[ -d "$path" ]] || { echo "::error::contract '${id}' path '${path}' missing at ${CONTRACT_REF}"; exit 1; }
  dest="$OUT_DIR/$path"
  mkdir -p "$(dirname "$dest")"
  cp -R "$path" "$dest"
  while IFS= read -r -d '' f; do
    inject_header "$f" "$id"
  done < <(find "$dest" -type f \( -name '*.php' -o -name '*.md' \) -print0)
  echo "Fetched ${id} (${status}) -> ${path}"
  tmp="$(mktemp)"
  jq --arg id "$id" --arg path "$path" --arg status "$status" \
    '.contracts += [{"id":$id,"path":$path,"status":$status}]' "$fetched_json" > "$tmp"
  mv "$tmp" "$fetched_json"
done

echo "Wrote $(jq '.contracts | length' "$fetched_json") contract(s) to $OUT_DIR"
