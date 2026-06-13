# Starisian Infrastructure Contract Specification

SICS v0.2 — PARTIAL DRAFT

Sections 1–3 of 10

Starisian Technologies © 2026. All Rights Reserved.

LICENSE: Proprietary

---

## Section 1 — Purpose and Scope

### 1.1 Purpose

This document defines the minimum infrastructure behaviors required for compatibility with the runtime server security layer and the SPARXSTAR DVE platform, independent of implementation stack.

It does not prescribe technology choices. It prescribes observable behaviors, trust boundaries, and operational invariants that any compliant infrastructure implementation MUST satisfy.

### 1.2 Scope

This contract governs:

- Any infrastructure stack on which our runtime security layer is deployed
- Any infrastructure stack on which the SPARXSTAR DVE platform is deployed
- Any third-party implementation or certifying authority claiming contract compatibility
- All Starisian Technologies reference implementations

This contract does not govern:

- Internal application logic of DVE plugins or WordPress components
- The specific edge provider used, provided its trust boundary behavior satisfies the requirements defined herein
- Storage providers, CDN configuration, or DNS topology

### 1.3 Relationship to Implementing Repositories

Repositories implement this contract. They do not define it. No implementing repository may redefine, narrow, or extend contract invariants through implementation choices. Where an implementation conflicts with this contract, the contract is authoritative.

### 1.4 Definitions

**Compliant Infrastructure Implementation**
Any deployment stack that satisfies all mandatory invariants defined in this contract for its declared profile.

**Reference Implementation**
A Starisian Technologies supported implementation provided as an official example of contract compliance. Satisfying a reference implementation is sufficient but not necessary for compliance.

**Trust Boundary**
The point at which externally supplied request metadata becomes eligible for acceptance into trusted internal processing. Headers, IP addresses, and identity claims originating outside this boundary MUST NOT be trusted unless verified by a compliant perimeter layer.

**Validator**
The official Starisian Technologies reference enforcement tool used to test observable contract compliance against a live deployment. Equivalent independently developed validators MAY be used, provided their outputs conform to this contract and do not supersede the authoritative interpretation of SICS.

**Contract Compatibility**
A status indicating that an implementation satisfies all mandatory invariants for its declared profile, regardless of implementation stack.

**Perimeter Layer**
The infrastructure component responsible for enforcing the trust boundary, stripping unauthorized headers, and passing verified request metadata to the application layer.

**Profile**
A named compliance tier defining which invariants are mandatory for a given deployment context. See Section 5.

**Recording Pipeline**
A compliant implementation of audio or media ingestion that attaches a governance token to all recorded data submitted through the designated upload route. The reference recording pipeline is Starmus.

**Silent Rejection (444)**
A connection termination that returns no response body, no status message, and no confirmation of server existence to the caller. Used where revealing rejection reason would provide attack surface. The caller receives no information.

**Uninformative Rejection (404)**
A standard not-found response. Used where the server's existence is known but the requested path does not exist or is not disclosed. Provides misdirection without revealing system structure.

---

## Section 2 — Normative Authority

### 2.1 Constitutional Hierarchy

The following hierarchy governs all conflicts between artifacts:

1. This written contract (SICS)
2. Published amendments to this contract
3. The validator reference implementation
4. Stack-specific reference implementations

No subordinate tier may contradict a superior tier. Where conflict exists, the superior tier is authoritative and the subordinate tier requires correction.

### 2.2 Normative Language

This document uses RFC 2119 normative language throughout:

- **MUST** — absolute requirement
- **MUST NOT** — absolute prohibition
- **SHOULD** — recommended; deviation requires documented justification
- **MAY** — permitted but not required

### 2.3 Versioning

This document is versioned. The current version is SICS v0.2.

All changes require a version increment, a published amendment notice, and a backward compatibility statement. Silent amendment is prohibited.

### 2.4 Validator Relationship

The validator reference implementation tests observable behaviors defined in this contract. Where a validator result conflicts with the written contract, the written contract is authoritative. Validator bugs do not redefine contract requirements.

### 2.5 Governing Jurisdiction

Unless superseded by an executed commercial agreement, this contract shall be governed under the laws of the State of California, with venue in San Diego County, California.

### 2.6 Compliance States

Implementations SHALL be classified as one of the following:

- **Fully Compliant** — all mandatory invariants for the declared profile are satisfied
- **Conditionally Compliant** — mandatory invariants are satisfied with documented exceptions approved by Starisian Technologies or an authorized certifier
- **Non-Compliant** — one or more mandatory invariants are not satisfied

Formal failure conditions and enforcement consequences are defined in Section 9.

### 2.7 Amendment Authority

Only Starisian Technologies, or its explicitly designated governance authority, may issue official amendments to this contract. Amendments issued by any other party carry no normative weight under this contract.

Starisian Technologies MAY issue emergency security amendments outside normal version cycles where immediate correction is required to preserve contract security integrity. Such amendments MUST be published with retroactive version integration at the next formal release.

---

## Section 3 — Universal Mandatory Invariants

These invariants apply to all profiles unless explicitly noted. They represent the minimum behavioral requirements below which no implementation may be considered contract-compatible regardless of certification tier.

Default rejection behavior: Unless explicitly specified otherwise, trust boundary violations SHALL result in a Silent Rejection (444). Requests to undisclosed or non-existent paths SHALL result in an Uninformative Rejection (404).

### 3.1 Client Identity

**3.1.1** A compliant implementation MUST restore and preserve the original client IP address through the full request path from the trust boundary to the application layer.

**3.1.2** The mechanism of IP restoration is implementation-defined provided the application layer receives the correct original client IP in a consistent, documented header.

**3.1.3** A compliant implementation MUST NOT permit a client to supply or manipulate its own IP representation through request headers. Any request in which IP restoration cannot be verified MUST result in a Silent Rejection (444).

**3.1.4** A compliant implementation MUST apply a Silent Rejection (444) to any request in which identity-bearing headers are malformed, internally inconsistent, or selectively absent in a pattern that would otherwise be present for legitimate requests.

**3.1.5** Where a request presents a claimed relationship, credential, or context that is inconsistent with its verified network origin, the implementation MUST treat this as a trust boundary violation and apply a Silent Rejection (444). The implementation MUST NOT attempt to reconcile the inconsistency within the request path. Reconciliation MAY occur only through a governed exception authorization workflow outside the request path.

**3.1.6** Geographic origin MAY be evaluated as a trust signal where applicable to the active trust model. Where geographic origin is inconsistent with expected identity context, the perimeter layer MUST treat the request as anomalous. Access MUST be denied unless the origin context has been explicitly pre-authorized through a declared trust exception. A compliant implementation MUST generate an internal system alert on anomalous origin detection and MUST make that alert available to the identity governance layer to initiate an exception authorization workflow. The denial MUST NOT reveal its reason to the caller.

### 3.2 Protocol Context

**3.2.1** A compliant implementation MUST forward the original request Host header to the application layer without modification.

**3.2.2** A compliant implementation MUST preserve and forward the original request protocol to the application layer such that the application can make correct protocol-aware decisions.

**3.2.3** A compliant implementation MUST forward the original server port to the application layer.

**3.2.4** A compliant implementation MUST preserve the client IP forwarding chain without permitting client-supplied manipulation. The forwarding chain MUST reflect verified infrastructure hops only.

**3.2.5** A compliant implementation MUST NOT forward headers whose values cannot be verified as infrastructure-originated rather than client-supplied.

### 3.3 Request Tracing

**3.3.1** A compliant implementation MUST assign a unique, cryptographically random request identifier of at minimum UUIDv4 equivalent entropy to every inbound request at the perimeter.

**3.3.2** The request identifier MUST be propagated through the full request path without modification.

**3.3.3** The request identifier MUST be present in all infrastructure log entries for that request, enabling full end-to-end correlation across layers.

**3.3.4** A compliant implementation MUST NOT permit a client to supply or override the request identifier. Any client-supplied request identifier header MUST be overwritten at the perimeter.

### 3.4 Trust Boundary Enforcement

**3.4.1** A compliant implementation MUST define and enforce a trust boundary at the perimeter layer. No request metadata originating outside this boundary may be treated as trusted without perimeter verification.

**3.4.2** All SPARXSTAR platform headers — including but not limited to the `X-SPARXSTAR-*` and `X-SPX-*` header namespaces — MUST be stripped from all inbound requests at the trust boundary. These headers MUST NOT be passed to the application layer unless emitted by a verified authenticated perimeter component after successful trust verification.

**3.4.3** The stripping requirement in 3.4.2 is unconditional. No configuration, operator preference, or deployment profile may exempt a compliant implementation from this requirement.

**3.4.4** A compliant implementation MUST NOT expose its internal header structure, trust verification mechanism, or security overlay behavior to external clients or users. Operators administering the infrastructure have access to this information. End users do not. This separation MUST be enforced at the perimeter and MUST NOT be bridgeable by application-layer configuration.

**3.4.5** Any request carrying a SPARXSTAR platform header that was not emitted by a verified perimeter component MUST result in a Silent Rejection (444). The rejection MUST NOT reveal its reason to the caller.

### 3.5 Recording Pipeline Route

**3.5.1** A compliant implementation supporting a recording pipeline MUST provide a TUS-compatible resumable upload endpoint at the `/files/` path.

**3.5.2** The TUS endpoint MUST accept the following HTTP methods: `OPTIONS`, `HEAD`, `POST`, `PATCH`, `DELETE`.

**3.5.3** Request buffering MUST be disabled for the TUS route. The implementation MUST stream request bodies directly to the TUS backend without accumulating the body in proxy memory.

**3.5.4** Response buffering MUST be disabled for the TUS route.

**3.5.5** The implementation MUST support a minimum upload session timeout of 3600 seconds on the TUS route.

**3.5.6** The TUS backend MUST NOT be publicly routable. It MUST be bound to a private network interface and accessible only through the compliant infrastructure stack.

**3.5.7** The TUS route MUST forward TUS protocol headers to the TUS backend without modification.

**3.5.8** The TUS route MUST NOT apply body size limits.

**3.5.9** Rate limiting MUST NOT be applied to the TUS route in a manner that would interrupt or invalidate an active resumable upload session.

**3.5.10** A compliant recording pipeline MUST attach a valid governance token to all recorded data submitted through this route. The governance token specification is defined in the DVE integration specification.

### 3.6 Application Reachability

**3.6.1** A compliant implementation MUST ensure the application layer receives all of the following on every request: verified client IP, correct host, correct protocol, correct port, unique request identifier, and all trust-verified headers emitted by the perimeter layer.

**3.6.2** A compliant implementation MUST NOT silently drop, modify, or reorder headers between the perimeter layer and the application layer except as explicitly required by trust boundary enforcement rules defined in 3.4.

**3.6.3** The application layer MUST be reachable only through the compliant infrastructure stack. Direct external access to the application layer that bypasses the perimeter MUST be prevented at the network level.

**3.6.4** A compliant implementation MUST provide a health verification endpoint that returns a deterministic success response without invoking application logic. This endpoint MUST be restricted to trusted infrastructure monitoring systems or authenticated operators and MUST NOT be publicly routable.

---

*End of SICS v0.2 partial draft. Sections 4 through 10 pending.*

---

**© 2026 Starisian Technologies. All Rights Reserved.**

**PATENT PENDING**

The technologies, linguistic processing methods, and data structures described in this document are proprietary to **Starisian Technologies** and are the subject of pending patent applications.

This document is furnished for informational purposes only. No license, express or implied, by estoppel or otherwise, to any intellectual property rights is granted by this document. Unauthorized use or reproduction of these technical concepts may result in legal action upon the issuance of related patents.
