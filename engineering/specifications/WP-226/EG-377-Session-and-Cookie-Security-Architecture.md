---
id: EG-377
title: Session and Cookie Security Architecture
summary: Defines the security boundaries, lifecycle, storage neutrality, expiration, regeneration, flash-data and CSRF architecture for WP-226.
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
increment: I1
tags:
  - foundation
  - http
  - session
  - cookie
  - csrf
  - security
  - architecture
depends_on:
  - EG-354
  - EG-355
  - EG-357
  - EG-358
  - EG-359
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# Session and Cookie Security Architecture

## 1. Purpose

This specification defines the governed architecture for cookies, sessions, flash data, expiration, identifier regeneration and CSRF protection in SIF. It establishes boundaries and invariants before executable implementation begins.

WP-226 extends the HTTP foundation without introducing authentication, authorization, identity management or user accounts.

## 2. Architectural boundaries

The subsystem is divided into explicit layers:

1. **Cookie value model** — immutable representation and deterministic `Set-Cookie` serialization.
2. **Session state** — request-scoped mutable logical state with controlled lifecycle transitions.
3. **Session storage** — storage-neutral persistence contract.
4. **Session transport** — extraction and emission of the session identifier through an HTTP cookie.
5. **Lifecycle middleware** — load, expose, persist, rotate and close the session around a request.
6. **Flash data** — values that survive one subsequent request and then expire.
7. **CSRF protection** — token generation, comparison and request validation for unsafe HTTP methods.

No layer may read PHP globals directly except a native transport adapter. No layer may emit headers except the HTTP response transport boundary.

## 3. Cookie model

Cookies are immutable value objects. A cookie definition includes:

- name;
- value;
- path;
- optional domain;
- optional expiration timestamp;
- optional max-age;
- secure flag;
- http-only flag;
- same-site policy;
- deletion intent.

Cookie names and values must reject control characters, separators that would corrupt header serialization, CR, LF and NUL.

### 3.1 Security defaults

Framework-provided session cookies default to:

- `Secure` when HTTPS is required by configuration;
- `HttpOnly` enabled;
- `SameSite=Lax` unless explicitly governed otherwise;
- path `/`;
- no domain attribute unless explicitly configured;
- no persistent expiration unless explicitly configured.

### 3.2 Prefix rules

`__Secure-` cookies require `Secure`.

`__Host-` cookies require:

- `Secure`;
- path `/`;
- no domain attribute.

Invalid combinations fail during value construction, not during emission.

## 4. Session identifier

A session identifier is opaque, unguessable and independent from user identity. It must be generated with a cryptographically secure source and must not encode application data.

The identifier is never logged in plaintext. Diagnostics may include only an irreversible or request-scoped correlation reference when necessary.

## 5. Session state and lifecycle

The lifecycle is:

```text
incoming request
    ↓
extract identifier
    ↓
load record from storage
    ↓
validate expiration and integrity
    ↓
create request-scoped session
    ↓
execute downstream handler
    ↓
apply flash transitions
    ↓
regenerate when requested
    ↓
persist or delete
    ↓
attach Set-Cookie to response
```

A session is request-scoped. Mutable session state must not be stored in a singleton service.

The request receives the session through a governed request attribute or dedicated contract. Existing applications that do not enable session middleware remain unaffected.

## 6. Storage neutrality

Session storage is defined by contracts for:

- reading by identifier;
- writing a complete session record;
- deleting by identifier;
- optional compare-and-swap or locking capability;
- garbage collection where supported.

The core runtime does not depend on PDO, filesystem, Redis, native PHP sessions or any particular database.

A session record contains only serializable data, lifecycle timestamps, version information and metadata required for expiration or concurrency. It must not contain controllers, services, requests, responses, resources or closures.

## 7. Regeneration and fixation resistance

Identifier regeneration is explicit and may be requested:

- after a security boundary changes;
- after authentication in a future subsystem;
- through application code;
- after policy-defined intervals.

Regeneration creates a new identifier, persists the current logical state under it and invalidates the previous identifier according to a governed ordering that avoids losing state.

The controller layer must never accept a session identifier from route, query or body input.

## 8. Expiration policies

Two independent policies are supported:

- **absolute expiration** — maximum lifetime since creation;
- **idle expiration** — maximum inactivity since last accepted activity.

Expiration is evaluated using an injected clock. Tests must not depend on wall-clock time.

Expired sessions are treated as unavailable and scheduled for deletion. They are not revived by an incoming stale cookie.

## 9. Flash data

Flash values have explicit lifecycle states:

- new — created during the current request;
- available — readable during the next request;
- consumed — removed at the end of the request unless retained.

Flash transitions occur exactly once per successful lifecycle boundary. Error handling must define whether persistence is attempted after downstream failure; the default is to preserve deterministic cleanup while avoiding partial state transitions.

## 10. Concurrency

The architecture must not silently overwrite concurrent session updates when storage provides versioning or locking capabilities.

A later increment may define optimistic version checks. The storage-neutral contract must allow conflict reporting rather than assuming last-write-wins.

## 11. CSRF architecture

CSRF protection applies to browser-originated, cookie-authenticated state-changing requests. It is independent from authentication implementation.

Protected methods are initially:

```text
POST
PUT
PATCH
DELETE
```

Safe methods are not rejected by CSRF middleware:

```text
GET
HEAD
OPTIONS
```

### 11.1 Token model

A CSRF token is random, opaque and bound to the session state. Comparison uses constant-time semantics.

Tokens may be transported through a configured header or request body field. Query-string tokens are not enabled by default.

The raw token is never logged.

### 11.2 Validation sequence

```text
request method
    ↓
session availability
    ↓
submitted token extraction
    ↓
constant-time validation
    ↓
downstream handler or safe rejection response
```

CSRF middleware does not parse arbitrary body formats itself. It relies on explicit, media-type-aware input already produced by governed request parsing.

### 11.3 Failure response

CSRF failure returns a safe structured response, normally `403 Forbidden`, without revealing the expected token, submitted token or session identifier.

## 12. Response integration

Cookies are attached by creating a new immutable response with one or more `Set-Cookie` header values. Existing `ResponseInterface` and `HeaderBagInterface` semantics are preserved.

The session middleware does not call `header()`, `setcookie()`, `echo` or `exit()`.

## 13. Logging and observability

Permitted metadata includes:

- lifecycle outcome;
- created, resumed, expired, regenerated or destroyed state;
- CSRF validation outcome;
- failure identifier;
- request correlation identifier.

Forbidden data includes:

- cookie values;
- session identifiers;
- session contents;
- CSRF tokens;
- authorization credentials;
- personal or secret application values.

## 14. Configuration

Configuration is immutable after bootstrap and includes:

- cookie name and attributes;
- absolute and idle lifetime;
- regeneration policy;
- storage service identifier;
- CSRF header and field names;
- protected methods;
- secure-cookie requirements.

Invalid security combinations fail during bootstrap or configuration construction.

## 15. Compatibility

The subsystem is optional.

Without registration of the session runtime and middleware:

- existing requests and responses behave unchanged;
- existing handlers and controllers remain valid;
- no cookie is emitted;
- no session storage is accessed;
- no CSRF validation occurs.

## 16. Explicit exclusions

WP-226 does not implement:

- users or identities;
- login or logout workflows;
- password hashing;
- authorization;
- roles or permissions;
- OAuth, OpenID Connect or JWT;
- remember-me credentials;
- distributed cache-specific adapters;
- browser-side JavaScript helpers.

## 17. Increment plan

```text
I1 — Session and Cookie Security Architecture
I2 — Immutable Cookie Model and Set-Cookie Serialization
I3 — Session Contracts, State and Storage-Neutral Runtime
I4 — Native Session Transport and Lifecycle Middleware
I5 — Flash Data, Regeneration and Expiration Policies
I6 — CSRF Token Generation, Validation and Middleware
I7 — CLI, Skeleton Integration and Web Example
I8 — Compatibility, Migration and Product Completion
```

## 18. Acceptance criteria

I1 is accepted when:

- boundaries are explicit;
- cookie security invariants are defined;
- session storage remains neutral;
- lifecycle and expiration semantics are deterministic;
- regeneration and fixation resistance are governed;
- flash-data transitions are specified;
- CSRF scope and failure behavior are explicit;
- sensitive data logging is prohibited;
- compatibility and exclusions are documented.
