---
id: EG-378
title: Immutable Cookie Model and Set-Cookie Serialization
summary: Specifies the immutable cookie value model, security invariants, deterministic Set-Cookie serialization and response-cookie collection delivered by WP-226 I2.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
tags:
  - cookie
  - http
  - security
  - serialization
  - specification
depends_on:
  - EG-377
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Immutable Cookie Model and Set-Cookie Serialization

WP-226 I2 defines a transport-neutral and immutable representation of response cookies. Cookie construction SHALL validate names, values, paths, domains, SameSite policy and security prefixes before serialization.

## Value model

`CookieName`, `CookieValue`, `CookieExpiration`, `CookieSameSite` and `Cookie` SHALL be immutable. A cookie SHALL NOT read request globals, emit headers or mutate a response. `CookieCollection` SHALL preserve deterministic declaration order and SHALL expose each serialized cookie as an independent `Set-Cookie` header value.

Cookie names SHALL use the HTTP token grammar. Cookie values SHALL use RFC 6265 cookie-octets and SHALL reject CR, LF, NUL and other disallowed characters. Paths SHALL be absolute. Domains SHALL be normalized lower-case DNS host names and SHALL NOT include a scheme or port.

## Security invariants

`SameSite=None` SHALL require `Secure`. A `__Secure-` cookie SHALL require `Secure`. A `__Host-` cookie SHALL require `Secure`, `Path=/` and no `Domain` attribute. These rules SHALL be enforced at construction time and SHALL NOT be deferred to the browser.

## Serialization

`CookieSerializer` SHALL emit attributes in a stable order: name/value, `Expires`, `Max-Age`, `Domain`, `Path`, `Secure`, `HttpOnly`, `SameSite`. Expiration dates SHALL be emitted in GMT using the HTTP-date representation. Removal SHALL use an empty value, an epoch expiration and `Max-Age=0`.

Serialization SHALL return strings only. It SHALL NOT call `header()`, read globals or terminate the process. Integration with `ResponseInterface` SHALL remain an explicit later lifecycle concern.

## Product boundary

I2 does not define incoming cookie parsing, session identifiers, storage, lifecycle middleware, flash data or CSRF. Those concerns remain assigned to later WP-226 increments.
