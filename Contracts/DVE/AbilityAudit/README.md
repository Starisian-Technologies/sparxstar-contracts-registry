# Ability Audit

The canonical shape of an ability-attempt audit event, and the redaction rules
that govern what such an event may contain.

## Why this is a contract and not a library

Two WordPress plugins — Sky Hermes and Sky DVE — each register abilities and
each must record attempts against them. They are independently installable and
neither may depend on the other, so each keeps its own recorder, its own table,
and its own delivery loop.

That leaves the _rules_ as the only thing that can be shared, and they are the
part that must not drift. A redaction bug in one plugin is a data-protection
incident regardless of how correct the other one is. So the specification lives
here once, and both implementations are held to it by the same vectors.

Extraction into a shared runtime package is deliberately deferred. Revisit it
only once Composer authentication across the estate is stable, or a third
WordPress consumer exists.

## What an event is, and is not

Events are emitted from `wp_ability_invoked`, which WordPress fires **before**
the permission callback and **before** input validation.

An event therefore records that a caller _asked_. It does not record that the
call was permitted, that its input was valid, or that anything executed. Reading
these rows as an execution log will overstate what happened. This is also why
the input is dangerous: at that point it is entirely attacker-controlled.

## The rules that carry weight

**No raw input, in any form.** The `summary` object is the only representation
of input permitted to leave memory. See
`SPXAbilityAttemptSummarizerInterface.php` for what it may hold.

**Validation is two-phase.** An event is written `pending` and resolved when
`wp_ability_validate_input` reports. Still `pending` at delivery is a real
outcome — the request died before validation — and must not be rewritten.

**Telemetry never changes a verdict.** A recorder observing
`wp_ability_validate_input` must return the incoming verdict untouched. Those
filters assert; an observer that influences acceptance has become part of the
security decision it was only supposed to watch.

**A recorder ignores abilities it does not own.** `wp_ability_invoked` is
global. A recorder that does not filter on its own namespace will write a row
for every other plugin's invocations, and the platform audit will double-count
every call on any site running more than one of them.

**The digest is keyed.** It exists so two attempts can be recognised as
identical without either being readable. An unkeyed hash of a short or
low-entropy input is reversible by brute force, which would defeat the whole
summary.

## Conformance

`redaction-vectors.json` is the conformance suite. Every implementation must
pass every vector unmodified — editing a vector so an implementation passes is
itself the failure.

The vectors are adversarial on purpose. They cover credentials under obvious key
names and under innocuous ones, a credential used as a _key_ rather than a
value, PEM bodies, payloads nested past the depth bound, bare scalars, null, and
key names in African orthographies, which must survive intact: they are
diagnostic content, not credentials, and a summarizer that strips non-ASCII keys
destroys the audit's usefulness for the languages this platform exists to serve.

## Files

| File                                       | What it fixes                                          |
| ------------------------------------------ | ------------------------------------------------------ |
| `SPXAbilityAuditEventInterface.php`        | The event's shape and the meaning of each field        |
| `SPXAbilityValidationOutcome.php`          | The three validation states and why `pending` persists |
| `SPXAbilityAttemptSummarizerInterface.php` | The redaction boundary                                 |
| `ability-audit-event.schema.json`          | Machine-readable event schema                          |
| `redaction-vectors.json`                   | The adversarial conformance suite                      |
