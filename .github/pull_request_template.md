## Summary

<!-- One sentence: what does this PR do and why? -->

## Type of Change

- [ ] Contract update (synced from source repository)
- [ ] Governance / documentation
- [ ] CI / workflow
- [ ] Repository hygiene

## Consumer Impact

<!-- Does this change affect any bound consumers? If yes, list them and describe the impact. -->

## Checklist

- [ ] Changes are within the allowed scope (see AGENTS.md and CONTRIBUTING.md)
- [ ] Contract files under `Contracts/` are not directly edited (source-repo changes only)
- [ ] `MANIFEST.json` updated if a contract was added, promoted, or a consumer binding changed
- [ ] `npm run check` passes locally (markdown lint + format check + contract structure)
- [ ] `composer validate --strict` passes
- [ ] No secrets, credentials, or personally identifiable information included
- [ ] Documentation updated if repository behavior changed

## Breaking Changes

<!-- List any breaking changes. If none, write "None." -->

## Migration Notes

<!-- Instructions for consumers adapting to this change, if applicable. -->
