/**
 * SPARXSTAR 3iAtlas RLC — shared wire contract (TypeScript).
 *
 * ⚠️ This file is the typed mirror of
 *    .github/instructions/SPARXSTAR-3iAtlas-RLC-Contract-v1.0.md
 *    and is COMMITTED IDENTICALLY to both repos:
 *      - sparxstar-3iatlas-rlc-node-engine  (src/contract.ts)
 *      - sparxstar-3iatlas-rlc-ui           (src/contract.ts)
 *    Keep them byte-for-byte identical. No imports, no runtime code — pure
 *    types — so it can be copied verbatim. If you change a shape here, change
 *    the markdown and the other repo in the same commit.
 *
 * The backend's conformance test (tests/contract.smoke.test.ts) hits a running
 * server and asserts real responses/socket payloads match these types; the UI
 * gets compile-time errors the moment a call diverges.
 */

/* ────────────────────────────── Enums / unions ───────────────────────────── */

export type Tier = 'lower_basic' | 'upper_basic' | 'senior_secondary' | 'adult'
export type Mode = 'rwc' | 'rsc'
export type CollectionDepth = 'full' | 'translation_only' | 'basic'
export type SessionStatus = 'open' | 'qc' | 'ceremony' | 'closed' | 'archived'
export type CompletenessSignal = 'basic' | 'partial' | 'complete' | 'verified' | 'promoted'
export type SpellingSignal = 'confirmed' | 'variant' | 'discovery'
export type SaturationSignal = 'continue' | 'saturated'
export type VoteDimension = 'orthography' | 'semantics' | 'audio'
export type StarKind =
  | 'most_words'
  | 'most_sentences'
  | 'best_spelling'
  | 'discovery'
  | 'speed'
  | 'audio'
  | 'teacher'
  | 'teacher_award'

export interface Rights {
  license: string
  ai_training: boolean
  commercial: boolean
}

/** {yes,no} only — voters[] is internal to the backend, never on the wire. */
export interface VoteCount {
  yes: number
  no: number
}
export interface VoteCounts {
  orthography: VoteCount
  semantics: VoteCount
  audio: VoteCount
}

/* ─────────────────────────────── Error envelope ──────────────────────────── */

/** Every error response: { error, ...details }. */
export interface ErrorBody {
  error: string
  [detail: string]: unknown
}
export interface CredentialInvalidBody {
  error: 'credential_invalid'
  remaining_attempts: number
}
export interface AccountLockedBody {
  error: 'account_locked'
  unlock_path: string
}
export interface RateLimitedBody {
  error: 'rate_limited'
  // Present on per-route token-bucket 429s; the global per-IP backstop
  // (express-rate-limit) returns only { error: 'rate_limited' }.
  retry_after_seconds?: number
}
export interface ScreenTimeExceededBody {
  error: 'screen_time_exceeded'
  reset_at: number
}
/** Localization is the UI's job (it holds the i18n keys); the backend never
 *  sends a localized_message, so the wire body is just { error }. */
export interface UnknownScreenNameBody {
  error: 'unknown_screen_name'
}

/* ───────────────────────────────── REST: §3.1 ────────────────────────────── */

export interface SchoolCreateRequest {
  name: string
  country: string
  region?: string
  recording_enabled?: boolean
}
export interface SchoolCreateResponse {
  school_id: string
}

export interface ClassCreateRequest {
  school_id: string
  name: string
  tier: Tier
  teacher_id?: string
}
export interface ClassCreateResponse {
  class_id: string
}

export interface SchoolResponse {
  school_id: string
  name: string
  country: string
  region: string | null
  recording_enabled: boolean
  total_xp: number
  total_gold: number
}
export interface ClassResponse {
  class_id: string
  school_id: string
  name: string
  tier: Tier
  teacher_id: string | null
  total_xp: number
  total_gold: number
  recording_enabled: boolean
}

export interface RecordingToggleRequest {
  enabled: boolean
}
export interface RecordingToggleResponse {
  school_id: string
  recording_enabled: boolean
}

/* ───────────────────────────────── REST: §3.2 ────────────────────────────── */

export interface AccountCreateRequest {
  school_id: string
  class_id: string
  screen_name: string
  tier: Tier
  pin?: string
  password?: string
}
export interface AccountCreateResponse {
  account_id: string
}

export interface AdultRegisterRequest {
  screen_name: string
  password: string
  reset_email?: string
  captcha_token?: string
}
export interface AdultRegisterResponse {
  account_id: string
}

export interface SuccessResponse {
  success: true
}

export interface AccountXpResponse {
  account_id: string
  lifetime_xp: number
  lifetime_gold: number
}

/* ───────────────────────────────── REST: §3.3 ────────────────────────────── */

export interface ClassLeaderboardResponse {
  class_id: string
  total_xp: number
  students: Array<{
    account_id: string
    screen_name: string
    lifetime_xp: number
    session_xp: number
  }>
}
export interface SchoolLeaderboardResponse {
  school_id: string
  total_xp: number
  classes: Array<{ class_id: string; name: string; total_xp: number }>
}
export interface NationalLeaderboardResponse {
  country: string
  schools: Array<{ school_id: string; name: string; total_xp: number; rank: number }>
}

/* ───────────────────────────────── REST: §3.4 ────────────────────────────── */

export interface SessionCreateRequest {
  mode: Mode
  language: string
  locale: string
  semantic_domain_id?: string
  duration_minutes: number
  collection_depth: CollectionDepth
  class_id: string
  rights: Rights
}
export interface SessionCreateResponse {
  session_id: string
  join_code: string
  qr_code_url: string
}

/** Lower-Basic step 1 (join_code only). */
export interface JoinRosterResponse {
  requires_screen_name: true
  session_screen_names: string[]
}
export type SessionJoinRequest =
  | { join_code: string } // LB step 1
  | { join_code: string; screen_name: string } // LB commit
  | { join_code: string; screen_name: string; pin: string } // UB
  | { join_code: string; screen_name: string; password: string } // SS / Adult
export interface SessionJoinResponse {
  session_id: string
  participant_id: string
  participant_token: string
  account_id: string
  language: string
  locale: string
  mode: Mode
  collection_depth: CollectionDepth
  session_screen_names?: string[]
}

export interface SessionStatusResponse {
  status: SessionStatus
  participant_count: number
  token_count: number
  time_remaining_seconds: number
  leaderboard: Array<{ participant_id: string; screen_name: string; session_xp: number }>
  class_xp_total: number
  participant_token?: string
}

export interface QcAdvanceResponse {
  success: true
  token_id: string
}

export interface QcToken {
  token_id: string
  text: string
  translation: string
  yahura_transcription: string | null
  yahura_confidence: number | null
  grammar_domain: string
  spelling_signal: SpellingSignal | null
  completeness_signal: CompletenessSignal
  vote_orthography: VoteCount
  vote_semantics: VoteCount
  vote_audio: VoteCount
}
export interface QcWordsResponse {
  qc_words: QcToken[]
}

export interface Star {
  star: StarKind
  participant_ids: string[]
  screen_names: string[]
  xp_awarded: number
}
export interface AwardsResponse {
  stars: Star[]
  leaderboard: Array<{ participant_id: string; screen_name: string; tokens: number; session_xp: number }>
  total_tokens: number
  discovery_count: number
}

export interface TeachersStarRequest {
  participant_id: string
}

/* ───────────────────────────────── REST: §3.5 ────────────────────────────── */

/** Discriminated on collection_mode so the UI gets a compile error when it
 *  forgets the fields that the backend enforces at the /token/save boundary. */
export type TokenSaveRequest =
  | {
      session_id: string
      text: string
      /** Always on the wire in RSC: the enriched/collected translation, or '' in
       *  basic depth. */
      translation: string
      collection_mode: 'rsc'
      /** Required in RSC — authoritative (drives rsc_progress). The backend
       *  derives the canonical grammar_domain name from it; if grammar_domain is
       *  also sent it must match, else the server returns 400
       *  grammar_domain_mismatch. */
      grammar_domain_index: number
      grammar_domain?: string
      focus_detected?: boolean
      rights?: Rights
    }
  | {
      session_id: string
      text: string
      translation?: string
      collection_mode: 'rwc'
      /** Free-form Louw-Nida semantic domain; index unused in RWC. */
      grammar_domain?: string
      grammar_domain_index?: number
      focus_detected?: boolean
      rights?: Rights
    }
export interface TokenSaveResponse {
  token_id: string
  spelling_signal: SpellingSignal
  saturation_signal: SaturationSignal
  spelling_score: number
  completeness_signal: CompletenessSignal
  xp_awarded: number
  account_lifetime_xp: number
  rsc_progress?: { completed: number; total: number }
}

export interface VoteRequest {
  dimension: VoteDimension
  vote_yes: boolean
}
export interface VoteResponse {
  success: true
  vote_counts: VoteCounts
  has_voted: boolean
}

export interface TranslateRequest {
  translation: string
}
export interface CorrectRequest {
  corrected_text: string
}

export interface AudioRoutedRequest {
  yahura_transcription: string
  yahura_confidence: number
}
export interface TranslationEnrichedRequest {
  enriched_translation: string
  confidence?: number // optional — backend stores null when omitted
  target_language: string
}
export interface CompletenessRequest {
  completeness_signal: CompletenessSignal
}

export type BatchEventType = 'token.save' | 'token.vote' | 'token.translate' | 'token.correct'
export interface BatchEvent {
  event_id: string
  event_type: BatchEventType
  payload: Record<string, unknown>
}
export interface BatchRequest {
  events: BatchEvent[]
}
export interface BatchResponse {
  accepted: number
  failed: Array<{ event_id: string; reason: string }>
}

/* ───────────────────────────────── REST: §3.6 ────────────────────────────── */

export interface WebhookReplayResponse {
  success: true
  delivered: boolean
}

/* ──────────────── Rewards: ledger wire contract (P1, additive v1.1) ────────
 * The engine is the system of record for game-earned value (points, gold,
 * stars, badges); myCred mirrors earnings one-way and may pull totals for
 * reconciliation. The ledger is append-only and earn-only: entries are never
 * mutated or deleted, and amounts are strictly positive. Star/badge kinds and
 * subject_type 'device' are reserved for later phases.                       */

export type LedgerKind = 'xp' | 'gold' | 'star' | 'badge'
export type LedgerSubjectType = 'account' | 'device'

/** Reward reasons — one namespace shared by the §2.4 webhook event names, the
 *  game-manifest scoring keys, and ledger entries. The set is additive: future
 *  game manifests mint new reasons, so consumers must tolerate unknown
 *  strings; the listed values are the RLC gameType's vocabulary. */
export type RewardReason =
  | 'token.submitted'
  | 'translation.collection'
  | 'audio.routed'
  | 'qc.round.completed'
  | 'qc.translation'
  | 'consensus.reached'
  | 'settlement.retroactive'
  | 'discovery.found'
  | 'rsc.completed'

export interface LedgerEntry {
  /** bigint on the wire as string (JS number precision). */
  entry_id: string
  subject_type: LedgerSubjectType
  subject_id: string
  /** Platform gameType — 'rlc' today (PRODUCT-ROLE-BOUNDARY §4.5). */
  game_type: string
  session_id: string | null
  kind: LedgerKind
  /** Strictly positive — the ledger records earning only, never spending. */
  amount: number
  reason: RewardReason | (string & {})
  /** Unix epoch ms. */
  created_at: number
}

export interface LedgerTotals {
  xp: number
  gold: number
  entry_count: number
  /** Unix epoch ms of the newest entry; null when the ledger is empty. */
  last_entry_at: number | null
}

/** POST /ledger/totals — orchestrator HMAC; reconciliation pull (the engine
 *  never learns about spending; lifetime totals and wallet balances differ by
 *  design). */
export interface LedgerTotalsRequest {
  account_id: string
}
export interface LedgerTotalsResponse extends LedgerTotals {
  account_id: string
}

/** GET /account/:id/ledger?limit=N — participant (owner) auth; wallet/history
 *  view. Entries are newest-first; limit defaults to 50, max 200. */
export interface AccountLedgerResponse {
  account_id: string
  totals: LedgerTotals
  entries: LedgerEntry[]
}

/* ─────────────────────────── WebSocket: §4 events ────────────────────────── */

/** Server → client. Use with io<ServerToClientEvents, ClientToServerEvents>() on
 *  the UI and Server<ClientToServerEvents, ServerToClientEvents> on the backend. */
export interface ServerToClientEvents {
  'session:joined': (p: { participant_id: string; screen_name: string; tier: Tier }) => void
  'session:left': (p: { participant_id: string; screen_name: string }) => void
  'session:status': (p: { status: SessionStatus }) => void
  'token:submitted': (p: {
    participant_id: string
    completeness_signal: CompletenessSignal
    account_lifetime_xp: number
  }) => void
  'saturation:signal': (p: { token_id: string; signal: 'saturated' }) => void
  'qc:token': (p: {
    token_id: string
    text: string
    yahura_transcription: string | null
    yahura_confidence: number | null
    grammar_domain: string
    vote_orthography: VoteCount
    vote_semantics: VoteCount
    vote_audio: VoteCount
  }) => void
  'qc:audio-ready': (p: { token_id: string }) => void
  'qc:vote': (p: { token_id: string; dimension: VoteDimension; vote_counts: VoteCounts }) => void
  'qc:translation': (p: { token_id: string }) => void
  'qc:correction': (p: { token_id: string; correction_needed?: true; corrected?: true }) => void
  'screentime:limit-reached': (p: { participant_id: string | null; reset_at: number | null }) => void
  'ceremony:star': (p: Star) => void
  'ceremony:end': (p: { session_id: string; total_tokens: number; discovery_count: number }) => void
}

/** Client → server. */
export interface ClientToServerEvents {
  heartbeat: () => void
  'qc:vote': (p: { token_id: string; dimension: VoteDimension; vote_yes: boolean }) => void
  'qc:translation': (p: { token_id: string; translation: string }) => void
  'qc:correction': (p: { token_id: string; corrected_text: string }) => void
}

/** Handshake auth payloads (socket.io `auth`). */
export type StudentHandshakeAuth = { token: string }
/** sessionId is optional — a teacher socket may connect without being placed in
 *  a session's rooms (it just won't receive that session's room events). */
export type TeacherHandshakeAuth = { role: 'teacher'; token: string; sessionId?: string }

/* ──── Helios contracts (TS twin of sparxstar-helios-contracts; v0 staging) ──
 * SparxStar\Helios\* PHP is the source of truth (Helios repo,
 * packages/sparxstar-helios-contracts/ — a Composer package, not npm). These
 * TypeScript shapes mirror the package's public types for engine-side
 * consumption; the engine maintains no parallel definitions and does not
 * extend or improve these shapes locally.
 * Drift is fixed at the Helios source. NODE-ADR-005 records the citation.
 *
 * Engine-relevant path: POST /helios/v1/consent/resolve returns
 * ConsentReference-shaped JSON (per `ConsentReference::to_array()` in PHP).
 * Used by P4 when the engine submits evidence through the IngestManifest seam
 * with consent envelopes; unused today.
 *
 * Timestamp note: Helios's PHP `time()` returns Unix EPOCH SECONDS.
 * Engine-internal storage is epoch ms (per repo convention) — convert at
 * the Helios-client boundary, not in these shapes.                          */

/** SparxStar\Helios\Consent\RetentionClass — the ADR-013 carrier
 *  RetentionClass. Engine maps Path A → `ephemeral`, Path B → `vault`. */
export type HeliosRetentionClass = 'vault' | 'ephemeral'

/** SparxStar\Helios\Consent\ConsentTier — gates external-consent flow. */
export type HeliosConsentTier = 'adult' | 'minor' | 'institutional'

/** SparxStar\Helios\Consent\ConsentReference — JSON shape returned by
 *  POST /helios/v1/consent/resolve (mirrors `ConsentReference::to_array()`). */
export interface HeliosConsentReference {
  consent_id: string
  technical_consent: boolean
  /** Per-purpose grants — keys are {@link HeliosConsentPurpose} values. */
  purpose_consent: Partial<Record<HeliosConsentPurpose, boolean>>
  retention_class: HeliosRetentionClass
  /** Epoch SECONDS (PHP time()); convert to ms at the boundary if storing. */
  resolved_at: number
}

/** Purpose vocabulary from `ConsentReference::PURPOSE_*` in PHP — pure type
 *  union so contract.ts stays runtime-code-free (see file header). */
export type HeliosConsentPurpose = 'storage' | 'training' | 'research' | 'publication'

/** SparxStar\Helios\Identity\HeliosIdentityData — resolved identity Helios
 *  issues. The engine's JWT claims are a transport for this; alignment of
 *  `src/middleware/auth.ts` lands when contributor_ref starts flowing (P4). */
export interface HeliosIdentityData {
  contributor_ref: string
  correlation_id: string
  roles: string[]
  version: string
  /** Epoch SECONDS (PHP time()). */
  issued_at: number
  is_anonymous: boolean
}
