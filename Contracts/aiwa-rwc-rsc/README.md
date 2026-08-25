# AIWA RWC/RSC — Contracts

Published contracts for the **3iAtlas Node engine**: the backend that owns
participation, rewards, game state, capture/QC, and submission routing for
the 3iAtlas suite (RLC games, WordPad, Dictionary, Prosody Reader, future
gameTypes).

This directory is **distributed** to
[`sparxstar-contracts-registry`](https://github.com/Starisian-Technologies/sparxstar-contracts-registry)
at `Contracts/aiwa-rwc-rsc/` on every push to `main` that touches it (or
`src/contract.ts`). The source repo is the law; the registry is a public
mirror.

| File | Role |
| :--- | :--- |
| `README.md` (this file) | API surface + response shapes documentation |

This directory holds documentation only — there is no `contract.ts` here. The
canonical TypeScript wire-shape definitions live at `src/contract.ts` in this
repo; the sync workflow copies that file into the public contracts registry
(`sparxstar-contracts-registry`) on push.

Source of truth for the TypeScript types: `src/contract.ts` in the engine
repo (the file is byte-mirrored to the UI repo for the v1.0 client-facing
portion). The Helios twin section (v0 staging) and the rewards v1.1 section
are engine-scoped — see the file header for the per-section scoping rules.

---

## Identity and product key

| Field | Value |
| :--- | :--- |
| Product key | `aiwa-rwc-rsc` |
| Source repo | `Starisian-Technologies/sparxstar-3iatlas-rlc-node-engine` |
| Spec | `docs/aiwa-rwc-rsc-tech-spec.md` (registry holds canonical) |
| ROLE | `ROLE.md` |
| Base path | `/api/v1` |
| Transports | REST (Express) + WebSocket (`socket.io`) + outbound HMAC webhooks |
| Auth | 3iAtlas Identity token (every account principal — authentication only; roles come from `rlc_authorizations`, NODE-ADR-007) · HMAC participant tokens (classroom students) · HMAC service auth (Yahura/Behistun/ESU/orchestrator) |
| Timestamps | Unix epoch milliseconds (`BIGINT`), unless otherwise specified |

---

## Section index

The TypeScript file is sectioned for readability. Each section is mirrored
below; see `contract.ts` itself for the exact types.

1. **Enums / unions** — shared discriminators (`Tier`, `Mode`, `SessionStatus`,
   `CompletenessSignal`, `SpellingSignal`, `SaturationSignal`, `VoteDimension`,
   `StarKind`, `Rights`, `VoteCount`/`VoteCounts`).
2. **Error envelope** — `ErrorBody` + the typed special-case bodies
   (`CredentialInvalid`, `AccountLocked`, `RateLimited`, `ScreenTimeExceeded`,
   `UnknownScreenName`).
3. **Schools / classes / leaderboards** (REST §3.1, §3.3) — admin and
   leaderboard reads.
4. **Accounts** (REST §3.2) — student create, adult registration, XP read,
   wallet/ledger read.
5. **Sessions** (REST §3.4) — create, join, status, QC selection, awards,
   teacher's-star.
6. **Tokens** (REST §3.5) — save (RWC/RSC), vote, translate, correct.
7. **Service routes** (REST §3.6) — Yahura audio-routed, Behistun
   translation-enriched, ESU completeness, screen-time limit, ledger
   reconciliation, admin webhook replay.
8. **Offline queue** — `POST /events/batch` (the participant offline-flush;
   distinct from the inbound 3iAtlas Event Contract handled at the seam).
9. **WebSocket protocol** (§4) — handshake auth + emitted events.
10. **Rewards (v1.1)** — typed reward ledger, totals, wallet response, the
    one-way myCred push semantics. Engine-side ratification of NODE-ADR-004.
11. **Helios contracts (TS twin, v0 staging)** — `HeliosRetentionClass`,
    `HeliosConsentTier`, `HeliosConsentReference`, `HeliosConsentPurpose`,
    `HeliosIdentityData`. Mirror of the `sparxstar-helios-contracts` Composer
    package per NODE-ADR-005 — engine-server-side only, not for the UI bundle.

---

## Endpoint surface (with response shapes)

Below, request/response types reference exported names from `contract.ts`.
Per-endpoint behavioral spec lives in `docs/API.md` and the canonical engine
spec (`.github/instructions/sparxstar-3iatlas-rlc-spec-v4.0.md`); this README
is the contract-level overview.

**Paths below are relative to the base path `/api/v1`** (e.g. `/school/create`
is `/api/v1/school/create`), **except `/health`**, which is mounted at the
app root and has no `/api/v1` prefix.

### Health

| Method | Path | Auth | Returns |
| :----- | :--- | :--- | :------ |
| GET | `/health` | none | `{ status: 'ok' \| 'degraded', db: boolean }` |

### Schools, classes, leaderboards

| Method | Path | Auth | Request → Response |
| :----- | :--- | :--- | :----------------- |
| POST | `/school/create` | `rlc:school_admin` | `SchoolCreateRequest` → `SchoolCreateResponse` |
| POST | `/school/:id/recording` | `rlc:school_admin` | `RecordingToggleRequest` → `RecordingToggleResponse` |
| POST | `/class/create` | `rlc:school_admin` | `ClassCreateRequest` → `ClassCreateResponse` |
| GET | `/class/:id/leaderboard` | `rlc:teacher` | → `ClassLeaderboardResponse` |
| GET | `/school/:id/leaderboard` | `rlc:school_admin` | → `SchoolLeaderboardResponse` |
| GET | `/leaderboard/national?country=GM` | none | → `NationalLeaderboardResponse` |

### Accounts

| Method | Path | Auth | Request → Response |
| :----- | :--- | :--- | :----------------- |
| POST | `/account/create` | `rlc:school_admin` | `AccountCreateRequest` → `AccountCreateResponse` |
| POST | `/account/adult-register` | none + captcha | `AdultRegisterRequest` → `AdultRegisterResponse` |
| POST | `/account/:id/unlock` | `rlc:teacher` | → `SuccessResponse` |
| GET | `/account/:id/xp` | participant (owner) | → `AccountXpResponse` `{ account_id, lifetime_xp, lifetime_gold }` |
| GET | `/account/:id/ledger?limit=N` | participant (owner) | → `AccountLedgerResponse` (default 50, max 200; newest first) |

### Sessions

| Method | Path | Auth | Request → Response |
| :----- | :--- | :--- | :----------------- |
| POST | `/session/create` | `rlc:teacher` | `SessionCreateRequest` → `SessionCreateResponse` |
| POST | `/session/join` | none | `SessionJoinRequest` → `SessionJoinResponse \| JoinRosterResponse` |
| GET | `/session/:id/status` | optional participant | → `SessionStatusResponse` |
| POST | `/session/:id/close` | `rlc:teacher` | → `SuccessResponse` |
| GET | `/session/:id/qc-words` | none | → `QcWordsResponse` (≤10, priority-ordered, submitter-anonymized) |
| POST | `/session/:id/qc-advance` | `rlc:teacher` | → `QcAdvanceResponse` (emits `qc:token`) |
| GET | `/session/:id/awards` | none | → `AwardsResponse` |
| POST | `/session/:id/teachers-star` | `rlc:teacher` | `TeachersStarRequest` → `SuccessResponse` (409 if already assigned) |
| POST | `/session/:id/ceremony` | `rlc:teacher` | → `AwardsResponse` |

### Tokens

| Method | Path | Auth | Request → Response |
| :----- | :--- | :--- | :----------------- |
| POST | `/token/save` | participant | `TokenSaveRequest` → `TokenSaveResponse` |
| POST | `/token/:id/vote` | participant | `VoteRequest` → `VoteResponse` (409 on duplicate) |
| POST | `/token/:id/translate` | participant | `TranslateRequest` → `SuccessResponse` |
| POST | `/token/:id/correct` | participant (submitter) | `CorrectRequest` → `SuccessResponse` |
| POST | `/token/:id/approve` | `rlc:teacher` | → `SuccessResponse` (requires `verified` → `promoted`) |

### Service routes (HMAC over request body)

| Method | Path | Caller | Request → Response |
| :----- | :--- | :----- | :----------------- |
| POST | `/token/:id/audio-routed` | Yahura | `AudioRoutedRequest` → `SuccessResponse` (one-shot; idempotent re-route is a no-op) |
| POST | `/token/:id/translation-enriched` | Behistun | `TranslationEnrichedRequest` → `SuccessResponse` |
| POST | `/token/:id/completeness` | ESU | `CompletenessRequest` → `SuccessResponse` (monotonic only) |
| POST | `/screentime/limit-reached` | orchestrator | `{ account_id, reset_at? }` → `{ success, ... }` (emits `screentime:limit-reached`) |
| POST | `/ledger/totals` | orchestrator | `LedgerTotalsRequest` → `LedgerTotalsResponse` (404 on unknown account) |
| POST | `/admin/webhooks/replay/:event_id` | `rlc:school_admin` | → `WebhookReplayResponse` |

### Offline queue

| Method | Path | Auth | Request → Response |
| :----- | :--- | :--- | :----------------- |
| POST | `/events/batch` | participant | `BatchRequest` → `BatchResponse` `{ accepted: number, failed: Array<{event_id, reason}> }` |

Queueable types today: `token.save`, `token.vote`, `token.translate`,
`token.correct`. Dedup key is `event_id` (claim → confirm → release lifecycle
on `processed_events`, 5-minute stale window). Batch cap 200; rate 30
batches/s burst + 1/s refill per participant token. Events apply in order
within a batch. See NODE-ADR-003 for the engine's end of the 3iAtlas Event
Contract seam and the v0.2 drift list.

---

## Rewards (v1.1) — engine ledger reference

The engine is the system of record for **game-earned value**: XP, gold,
stars, badges. myCred mirrors earnings via the §6.6 webhooks and may pull
authoritative totals at any time for reconciliation. Lifetime engine totals
and spendable myCred balances are different numbers **by design** — the
engine never learns about spending.

### Wire types

```ts
type LedgerKind        = 'xp' | 'gold' | 'star' | 'badge'
type LedgerSubjectType = 'account' | 'device'
type RewardReason =
  | 'token.submitted'
  | 'translation.collection'
  | 'audio.routed'
  | 'qc.round.completed'
  | 'qc.translation'
  | 'consensus.reached'
  | 'settlement.retroactive'
  | 'discovery.found'
  | 'rsc.completed'

interface LedgerEntry {
  entry_id: string                                  // bigint as string (precision)
  subject_type: LedgerSubjectType                   // 'device' reserved for later phases
  subject_id: string
  game_type: string                                 // 'rlc' today
  session_id: string | null
  kind: LedgerKind                                  // 'star' / 'badge' reserved
  amount: number                                    // strictly positive (earn-only)
  reason: RewardReason | (string & {})              // open set for future games
  created_at: number                                // Unix epoch ms
}

interface LedgerTotals {
  xp: number
  gold: number
  entry_count: number
  last_entry_at: number | null
}
```

### Endpoints

```ts
// POST /api/v1/ledger/totals  (orchestrator HMAC)
interface LedgerTotalsRequest  { account_id: string }
interface LedgerTotalsResponse extends LedgerTotals { account_id: string }
// → 404 unknown_account on missing account (NEVER zeroed totals)

// GET /api/v1/account/:id/ledger?limit=N  (participant, owner-only)
interface AccountLedgerResponse {
  account_id: string
  totals: LedgerTotals
  entries: LedgerEntry[]                            // newest-first; default 50, max 200
}
```

### Stars

`Star` and `StarKind` cover ceremony reveal:

```ts
type StarKind =
  | 'most_words' | 'most_sentences' | 'best_spelling'
  | 'discovery' | 'speed' | 'audio'
  | 'teacher' | 'teacher_award'

interface Star {
  star: StarKind
  participant_ids: string[]
  screen_names: string[]
  xp_awarded: number
}

interface AwardsResponse {
  stars: Star[]
  leaderboard: Array<{ participant_id: string; screen_name: string; tokens: number; session_xp: number }>
  total_tokens: number
  discovery_count: number
}
```

Ceremony order is **manifest-declared**, not hardcoded — see
`src/games/manifests.ts` (PLATFORM-PLAN P2). RWC carries `most_words`; RSC
carries `most_sentences`. All other stars share rule implementations across
modes.

### Invariants

- **Append-only.** No `UPDATE` or `DELETE` on `reward_ledger` rows.
- **Earn-only.** `amount` strictly positive; enforced at the DB (`CHECK`), at
  the model layer (`appendEarns` throws on negative), and at the service
  (`grantXp` throws on negative deltas).
- **Dual-write atomicity.** Every grant writes the ledger row in the same
  transaction as the lifetime/class/school counters — totals never drift.
- **One vocabulary.** `RewardReason` keys are simultaneously the §6.6 webhook
  event names AND the game manifest's `scoring_xp` keys — scoring, hooks,
  and ledger share a single namespace.
- **Reconciliation is pull, not push.** myCred can fetch authoritative totals
  via `POST /ledger/totals`; the engine never asks myCred about spending.
- **Person-reputation scoring is prohibited** (ADR-012). Conferred trust
  lives in `conferred_trust` keyed by language; computed alignment metrics
  attach only to sessions and artifacts.

---

## WebSocket protocol (§4)

```ts
// Student handshake
io(BACKEND_URL, { auth: { token: participantToken } } as StudentHandshakeAuth)

// Teacher handshake
io(BACKEND_URL, {
  auth: { role: 'teacher', token: identityToken, sessionId } as TeacherHandshakeAuth
})
```

Server → client events (typed in `contract.ts` socket section):

| Event | Audience | Payload |
| :---- | :------- | :------ |
| `session:joined` | teachers | `{ participant_id, screen_name, tier }` |
| `session:left` | teachers | `{ participant_id, screen_name }` |
| `session:status` | room | `{ status: SessionStatus }` |
| `token:submitted` | room | `{ participant_id, completeness_signal, account_lifetime_xp }` |
| `saturation:signal` | teachers | `{ token_id, signal: 'saturated' }` |
| `qc:token` | room | `{ token_id, text, yahura_transcription, yahura_confidence, grammar_domain, vote_orthography, vote_semantics, vote_audio }` |
| `qc:audio-ready` | room | `{ token_id }` |
| `qc:vote` | room | `{ token_id, dimension, vote_counts }` |
| `qc:translation` | room | `{ token_id }` |
| `qc:correction` | room | `{ token_id, correction_needed?: true, corrected?: true }` |
| `ceremony:star` | room | `Star` |
| `ceremony:end` | room | `{ session_id, total_tokens, discovery_count }` |
| `screentime:limit-reached` | student + teachers | `{ participant_id: string \| null, reset_at: number \| null }` |

Client → server events: `qc:vote` `{ token_id, dimension, vote_yes }` ·
`qc:translation` `{ token_id, translation }` ·
`qc:correction` `{ token_id, corrected_text }`.

---

## Helios contracts (v0 staging, NODE-ADR-005)

TypeScript twin of the `sparxstar-helios-contracts` Composer package. Used
only on the server (engine → Helios `/helios/v1/consent/resolve`); **not**
intended for the browser bundle. The PHP package is the source of truth;
twins are not edited locally — drift fixes go upstream to Helios.

```ts
type HeliosRetentionClass = 'vault' | 'ephemeral'
type HeliosConsentTier    = 'adult' | 'minor' | 'institutional'
type HeliosConsentPurpose = 'storage' | 'training' | 'research' | 'publication'

interface HeliosConsentReference {
  consent_id: string
  technical_consent: boolean
  purpose_consent: Partial<Record<HeliosConsentPurpose, boolean>>
  retention_class: HeliosRetentionClass
  resolved_at: number     // Unix EPOCH SECONDS (PHP time()) — convert to ms at the client boundary
}

interface HeliosIdentityData {
  contributor_ref: string
  correlation_id: string
  roles: string[]
  version: string
  issued_at: number       // Unix EPOCH SECONDS
  is_anonymous: boolean
}
```

---

## Error envelope

```ts
interface ErrorBody { error: string; message?: string }
```

Plus typed special-case bodies (`CredentialInvalidBody`, `AccountLockedBody`,
`RateLimitedBody`, `ScreenTimeExceededBody`, `UnknownScreenNameBody`) that
clients should narrow on the `error` discriminator before reading additional
fields like `retry_after_seconds` or `unlock_at`.

---

## Versioning

| Section | Status |
| :------ | :----- |
| v1.0 client-facing (sessions, tokens, sockets, awards) | byte-mirrored to the UI repo |
| v1.1 rewards (`LedgerEntry`, `AccountLedgerResponse`, `LedgerTotals*`) | engine + UI consumers (UI repo propagation tracked as a follow-up PR there) |
| Helios twin (`Helios*`) | engine-server-side only — **not** mirrored to UI |
| 3iAtlas Event Contract (inbound `/events/batch` reward face) | v0.1 cited by NODE-ADR-003 with drift list pending v0.2 ratification |
| IngestManifest seam (engine → ESU evidence face) | not yet specced (P4 entry gate) |

---

## Related documents in this repo

| Path | What it is |
| :--- | :--- |
| `src/contract.ts` | The TypeScript wire-shape source of truth |
| `docs/API.md` | Per-endpoint behavioral reference (rate limits, auth detail, side-effects) |
| `docs/PLATFORM-PLAN.md` | Migration charter (P1 → P8); current state and next phases |
| `docs/aiwa-rwc-rsc-tech-spec.md` | This repo's draft of the tech spec (registry holds canonical) |
| `docs/adr/` | NODE-ADR-NNN series (engine-interior decisions; bare `ADR-NNN` belongs to the registry) |
| `.github/instructions/governance/` | Compiled ADRs / invariants / open questions (auto-synced; do not edit) |
| `ROLE.md` | Boundary statement — what this repo owns and does not own |
