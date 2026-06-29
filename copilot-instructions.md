---
applyTo: "**"
---

# Copilot Instructions for SPARXSTAR Platform Contracts

## Workflow

When writing or updating README files in this repository, follow this guidance:

### Governing Principle

- Prioritize accuracy over efficiency whenever there is a tradeoff.
- Verify claims with evidence from files, workflows, or tool output.
- Call out uncertainty explicitly when confidence is not high.
- Prefer conservative, low-risk edits over broad speculative changes.
- Complete validation steps before finalizing work.
- Optimize recommendations for traceability and correctness first.
- Store and reuse verified repository conventions.

### Tone & Style

- **Commercial** — Professional, trustworthy, business-ready language
- **Consumer-focused** — Explain "what it does and why you need it" before technical details
- **Compliance-aware** — Acknowledge licensing, versioning, and governance requirements
- **Security-first** — Highlight security considerations and best practices

### README Structure

Each contract directory README should follow this pattern:

```markdown
# [Service Name]

## What Is This?

[1-2 sentence pitch: what the service does, who uses it, what problem it solves]

## Why You Need It

[Benefits and use cases for platform integrators]

## At a Glance

[Quick facts: status, audience, key interfaces]

## Core Functionality

[What capabilities does this service provide?]

## How It Works

[Architecture overview; don't duplicate code from interfaces]

## Getting Started

[How to install, basic usage, first integration step]

## Key Concepts

[Important vocabulary, patterns, or mental models]

## Common Patterns

[Typical integration patterns; code examples are OK here]

## Important Notes

[Warnings, compliance requirements, version constraints]

## Related Services

[Links to related contracts and how they interact]

## Support & Questions

[How to report issues, ask for help, or request features]
```

### Rules for This Repo

1. **Do not include code examples from interface files** — link to the interface instead
2. **Do highlight the interface/contract names** — capitalize them correctly (e.g., `SPXHeliosClientInterface`)
3. **Respect the auto-sync nature** — explain that contracts come from source services
4. **Link between services** — use relative markdown links to show relationships
5. **Include governance notices** — mention that changes require approval
6. **Use Starisian branding** — refer to "SPARXSTAR" and "Starisian Technologies"
7. **Emphasize versioning** — remind readers that contracts are versioned and breaking changes are rare

### What NOT to Do

- ❌ Write code that implements the interface
- ❌ Add dependencies or require statements
- ❌ Document internal implementation details
- ❌ Make jokes or use casual language
- ❌ Duplicate content from interface docblocks
- ❌ Assume the reader knows the SPARXSTAR architecture
- ❌ Edit files that are auto-synced (those are read-only)

### Target Audience

- **Primary:** Platform integrators and service developers
- **Secondary:** DevOps engineers and architects
- **Context:** They're choosing whether to adopt this service in their architecture

Write for someone who:

- Understands PHP and modern architecture
- May not know the SPARXSTAR ecosystem yet
- Needs to decide if this service solves their problem
- Will eventually read the interface definitions

### Examples to Reference

When describing a service:

- Explain what the service **controls** (e.g., "Helios controls all identity verification")
- Explain what the service **doesn't do** (e.g., "Helios doesn't store passwords")
- Explain what happens if it's **missing** (e.g., "Without Helios, you can't trust request identity")
- Explain who **depends on it** in the platform

## Implementation Notes

- Keep README files **under 1500 words**
- Use **h2 (##)** as the highest heading after the title
- Provide **clear navigation** between related services
- Update READMEs when **interfaces change significantly**
- Link to **specific interface files** when describing methods

---

**Governed by:** SPARXSTAR Contract Architecture Board
**Last Updated:** 2026-06-16
**Status:** Active
