#!/usr/bin/env bash
#
# Consumer-side local fetch helper. Pulls your pinned contract(s) into ./.contracts
# for local development (codegen, IDE type-checking) WITHOUT committing them.
#
# CI does NOT use this — CI calls the reusable fetch-contracts.yml workflow. This is
# only for a developer who wants the contract locally. ./.contracts is gitignored
# (see .gitignore-additions): you never hold a stale contract under version control.
#
# Requires: gh (authenticated), unzip.
#
# Usage:
#   CONTRACT_REF=v1.0.0 ./scripts/fetch-contracts.sh
#
# It triggers the registry's fetch on a throwaway ref and downloads the artifact.
# For most consumers the CI workflow is enough; reach for this only for local work.

set -euo pipefail

REGISTRY="${REGISTRY:-Starisian-Technologies/sparxstar-contracts-registry}"
CONTRACT_REF="${CONTRACT_REF:-v1.0.0}"
DEST="${DEST:-.contracts}"

command -v gh >/dev/null 2>&1 || { echo "gh CLI is required and must be authenticated."; exit 1; }

echo "This helper pulls contracts at ${CONTRACT_REF} from ${REGISTRY} into ${DEST}/."
echo "Contracts are pulled fresh and are NOT committed (see .gitignore-additions)."
echo
echo "In CI, prefer calling the reusable workflow instead:"
echo "  uses: ${REGISTRY}/.github/workflows/fetch-contracts.yml@${CONTRACT_REF}"
echo
echo "For local use, download the most recent contract artifact your CI produced:"
echo "  gh run download --repo <your-repo> --name fetched-contracts --dir ${DEST}"
echo
echo "Then point your codegen / static analysis at ${DEST}/."

mkdir -p "$DEST"
