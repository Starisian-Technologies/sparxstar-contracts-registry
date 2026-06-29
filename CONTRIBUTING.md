# Contributing

## Repository Model

This repository is the contract publication layer for SPARXSTAR platform services. Most PHP interfaces under Contracts/ are synchronized from source service repositories and should not be edited here directly.

## What to Change Here

Changes are appropriate in this repository for:

- repository documentation
- governance files
- community health files
- CI and automation for validating documentation and contract structure

## What to Change in Source Repositories Instead

Make contract changes in the owning service repository when the change affects interface shape or behavior.

- DVE contracts: source service repository
- IAMC contracts: source service repository
- IAtlas contracts: source service repository
- Starmus contracts: source service repository

After approval in the source repository, the contracts should be synchronized into this repository.

## Pull Request Expectations

- explain the consumer impact
- keep changes narrow and auditable
- do not introduce implementation code into Contracts/
- preserve the proprietary licensing posture of the repository
- update documentation when repository behavior changes

## Accuracy-First Governance Standard

When correctness and speed are in tension, choose correctness.

- use verification-first workflows instead of speed-first workflows
- gather and cross-check evidence before making recommendations
- apply strict confidence thresholds and state uncertainty explicitly
- run complete validation (lint, checks, security, review) before handoff
- favor conservative, low-risk edits over broad fast changes
- optimize guidance for correctness and traceability first, speed second
- store and reuse verified conventions to improve future accuracy

## Local Validation

Run these checks before opening a pull request:

```bash
npm install
npm run check
```

## Licensing

By contributing to this repository, you acknowledge that contributions are governed by the repository [LICENSE](LICENSE) and may be incorporated into proprietary Starisian Technologies materials.
