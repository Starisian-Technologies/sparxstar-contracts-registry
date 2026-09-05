#!/usr/bin/env node
// Reference verifier for the Identity <-> Key Vault grant contract.
//
// This is not a test of any implementation. It encodes the contract's rules
// once, runs every vector through them, and asserts each case lands on the
// verdict it claims. It exists so the suite cannot ship self-contradicting:
// a vector marked `accept` that the contract's own rules reject is a defect in
// the vector, and without this it is only found when someone tries to conform.
//
// Both sides may also use it as the reference reading of the rules.
//
//   node check-vectors.mjs           # from this directory
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const here = dirname(fileURLToPath(import.meta.url))
const suite = JSON.parse(readFileSync(join(here, 'vectors.json'), 'utf8'))
const NOW = suite.reference_time
const stored = suite.stored_state

const OPS = new Set([
  'manifest-read', 'manifest-create', 'manifest-rotate',
  'manifest-provisioning-replace', 'manifest-reset-read',
  'manifest-generation-recover', 'device-wrap-create', 'device-wrap-remove',
])
const DESTRUCTIVE = new Set([
  'manifest-rotate', 'manifest-provisioning-replace',
  'device-wrap-create', 'device-wrap-remove',
])
const MAX_DESTRUCTIVE_TTL = 300

/** Returns [] when the grant is acceptable, else the reasons it is refused. */
function verify(claims, route, consumed) {
  const f = []
  // audience — scalar only, exact
  if (!('aud' in claims)) f.push('aud missing')
  else if (Array.isArray(claims.aud)) f.push('aud is an array')
  else if (claims.aud !== 'key-vault') f.push(`aud is ${claims.aud}, not key-vault`)
  // subject / path
  if (claims.sub !== route.accountRef) f.push('sub does not match {accountRef}')
  // operation
  if (!('op' in claims)) f.push('op missing')
  else if (!OPS.has(claims.op)) f.push(`op ${claims.op} not in the enumerated set`)
  else if (claims.op !== route.op) f.push('op does not match the route')
  // time — iat/nbf/exp all required and valid (INV-017)
  for (const c of ['iat', 'nbf', 'exp']) if (!(c in claims)) f.push(`${c} missing`)
  if (claims.exp !== undefined && claims.exp <= NOW) f.push('expired')
  if (claims.nbf !== undefined && claims.nbf > NOW) f.push('not yet valid')
  // five-minute ceiling on destructive operations
  if (DESTRUCTIVE.has(claims.op) && claims.iat !== undefined && claims.exp !== undefined) {
    if (claims.exp - claims.iat > MAX_DESTRUCTIVE_TTL) f.push('destructive grant longer than 300s')
  }
  // conditional bindings
  if (claims.op === 'manifest-rotate') {
    if (claims.expectedVersion === undefined) f.push('manifest-rotate without expectedVersion')
    if (claims.txId === undefined) f.push('manifest-rotate without txId')
  }
  if (claims.op === 'manifest-provisioning-replace') {
    if (claims.expectedVersion === undefined) f.push('provisioning-replace without expectedVersion')
    if (claims.generation === undefined) f.push('provisioning-replace without generation')
  }
  if (claims.op === 'manifest-generation-recover' && claims.generation === undefined) {
    f.push('generation-recover without generation')
  }
  // version / generation preconditions against stored state
  if (claims.expectedVersion !== undefined && claims.expectedVersion !== stored.manifestVersion) {
    f.push(`expectedVersion ${claims.expectedVersion} != stored ${stored.manifestVersion}`)
  }
  if (claims.generation !== undefined && !stored.generations.includes(claims.generation)) {
    f.push(`generation ${claims.generation} not in stored generations`)
  }
  // replay — destructive grants are single-use; read grants are not
  if (DESTRUCTIVE.has(claims.op) && consumed.includes(claims.jti)) f.push('jti already consumed')
  return f
}

let failures = 0
for (const c of suite.cases) {
  const reasons = verify(c.claims, c.route, c.consumed_jti ?? [])
  const verdict = reasons.length === 0 ? 'accept' : 'reject'
  if (verdict !== c.expect) {
    failures += 1
    console.error(`MISMATCH  ${c.id}`)
    console.error(`  vector says: ${c.expect}`)
    console.error(`  rules say:   ${verdict}${reasons.length ? ` (${reasons.join('; ')})` : ''}`)
  }
}

const accept = suite.cases.filter((c) => c.expect === 'accept').length
console.log(
  `${suite.cases.length} vectors (${accept} accept / ${suite.cases.length - accept} reject) — ` +
    (failures === 0 ? 'all land on their stated verdict.' : `${failures} MISMATCHED.`),
)
process.exit(failures === 0 ? 0 : 1)
