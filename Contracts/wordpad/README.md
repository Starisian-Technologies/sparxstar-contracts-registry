# WordPad — Contracts

No contracts published yet.

This directory is the org-standard path for interfaces WordPad publishes for other
repos to consume (per the platform governance playbook, §3 "Standard Paths"). When
WordPad has an interface other repos need — a TypeScript type, an event payload
shape, etc. — it goes here, with an entry in this README describing what it
returns.

Publishing is automatic: `.github/workflows/sync-contracts.yml` copies this
directory's contents to `Contracts/wordpad/` in
`Starisian-Technologies/sparxstar-contracts-registry` on every push to `main` that
touches `docs/contracts/**`.
