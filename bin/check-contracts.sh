#!/usr/bin/env bash

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

mapfile -d '' contract_files < <(find Contracts -type f -name '*.php' -print0 | sort -z)

if [[ ${#contract_files[@]} -eq 0 ]]; then
    echo "No contract PHP files found under Contracts/."
    exit 1
fi

status=0

report_missing() {
    local label="$1"
    shift
    local items=("$@")

    if [[ ${#items[@]} -gt 0 ]]; then
        echo "$label"
        printf ' - %s\n' "${items[@]}"
        status=1
    fi
}

mapfile -t missing_namespace < <(grep -L '^namespace ' "${contract_files[@]}" || true)
mapfile -t missing_strict_types < <(grep -L 'declare(strict_types=1);' "${contract_files[@]}" || true)
# Contracts are interfaces, backed enums, or final value objects — all are valid.
# A file is valid if it contains any of: interface, enum, class.
missing_type_keyword=()
for file in "${contract_files[@]}"; do
    if ! grep -qE '\b(interface|enum|class)\b' "$file"; then
        missing_type_keyword+=("$file")
    fi
done
malformed_contract_filenames=()

for file in "${contract_files[@]}"; do
    basename="$(basename "$file")"
    if [[ ! "$basename" =~ ^[[:alnum:]_-]+\.php$ ]]; then
        malformed_contract_filenames+=("$file")
    fi
done

# Governance rule: "Every interface uses the SPX prefix per the naming
# convention." Any file declaring an interface must be named SPX*.php.
missing_spx_prefix=()
for file in "${contract_files[@]}"; do
    if grep -qE '\binterface\b' "$file" && [[ "$(basename "$file")" != SPX* ]]; then
        missing_spx_prefix+=("$file")
    fi
done

report_missing "Contract files missing a namespace declaration:" "${missing_namespace[@]}"
report_missing "Contract files missing strict types:" "${missing_strict_types[@]}"
report_missing "Contract files missing a type declaration (interface, enum, or class):" "${missing_type_keyword[@]}"
report_missing "Contract files with malformed PHP filenames:" "${malformed_contract_filenames[@]}"
report_missing "Interface files missing the SPX prefix (naming convention):" "${missing_spx_prefix[@]}"

# Only require README.md at the top-level product directories (one level under Contracts/).
# Sub-package directories (e.g. Consent/, Identity/) are synced from source repos and
# are not required to carry their own README here.
missing_readmes=()
while IFS= read -r -d '' directory; do
    if [[ ! -f "$directory/README.md" ]]; then
        missing_readmes+=("$directory")
    fi
done < <(find Contracts -mindepth 1 -maxdepth 2 -type d -print0 | sort -z)

report_missing "Contract directories missing a README.md:" "${missing_readmes[@]}"

# Governance rule: "The canonical namespace is Starisian\Sparxstar\{ProductName}."
# This is advisory, not fatal: known deviations are owned by the upstream source
# repo (synced files must not be edited here) and are tracked in MANIFEST.json
# open_items. Surface them on every run so they are not forgotten.
non_canonical_namespace=()
# Anchored regex (\\ matches one literal backslash) — unambiguous, no glob-escaping pitfalls.
canonical_ns_re='^namespace Starisian\\Sparxstar\\'
for file in "${contract_files[@]}"; do
    ns_line="$(grep -m1 '^namespace ' "$file" || true)"
    if [[ -n "$ns_line" && ! "$ns_line" =~ $canonical_ns_re ]]; then
        non_canonical_namespace+=("$file")
    fi
done

if [[ ${#non_canonical_namespace[@]} -gt 0 ]]; then
    echo "WARNING: files outside the canonical 'Starisian\\Sparxstar\\{Product}' namespace"
    echo "         (fix upstream in the source repo; tracked in MANIFEST.json open_items):"
    printf ' - %s\n' "${non_canonical_namespace[@]}"
fi

if [[ $status -eq 0 ]]; then
    echo "Contract structure checks passed."
fi

exit $status