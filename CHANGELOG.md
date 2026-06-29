# Changelog

All notable changes to this repository are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This repository uses [Semantic Versioning](https://semver.org/).

Each release packages the canonical contracts + MANIFEST.json + version policy + scripts as a GitHub Release artifact. See the [Releases page](https://github.com/Starisian-Technologies/sparxstar-contracts-registry/releases) for download links.

---

## [1.0.0] — 2026-06-28

### Added

- Initial canonical release of the SPARXSTAR Platform Contracts Registry.
- `iamc/helios` contract (`canonical`): `SPXHeliosClientInterface`, `SPXSirusClientInterface`, `SPXHeliosIdentityDataInterface`, `SPXIamcEnvelope`, `SPXConsentTier`, `SPXConsentReference`, `SPXRetentionClass`, `SPXResourceSensitivity`.
- `dve/sky-esu` contract (`canonical`): `SPXEsuInterface`, `SPXJobInterface`, `SPXJobFactoryInterface`, `SPXJobResultInterface`, `SPXTusUploadInterface`, `SPXBehistunClientInterface`, `SPXConsentResolverInterface`, `SPXCorrectionStoreInterface`, `SPXDictionaryClientInterface`, `SPXYahuraClientInterface`.
- `MANIFEST.json` — authoritative contract index with status vocabulary, binding filter, and consumer bindings.
- `config/version-policy.yml` — version floor (`v1.0.0`) and v3-bug guard.
- `scripts/fetch-contracts.sh` — MANIFEST-driven contract distribution script.
- `scripts/contract-conformance.php` — PHP token-based conformance assertion (IMPLEMENT-ALL, NO-FORK, VALID-MEMBER).
- Reusable workflows: `fetch-contracts.yml`, `contract-conformance.yml`, `propose-contract.yml`, `validate-contracts.yml`, `publish-contracts.yml`, `dispatch-contract-change.yml`, `collect-drift.yml`.
- Consumer CI template: `templates/calling-repo/ci-with-contracts.yml`.

### Contracts in Review (not yet binding)

- `iamc/sirus`, `iamc/ouroboros`, `iatlas/dictionary`, `iatlas/nodeengine`, `iatlas/wordpad`, `starmus` — placeholder entries, not fetchable by consumers until promoted to `ratified` or `canonical`.

---

**© 2026 Starisian Technologies. All Rights Reserved.**
