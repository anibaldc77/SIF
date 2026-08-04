---
id: EG-382
title: CSRF Token Generation, Validation and Middleware
summary: Specifies opaque session-bound CSRF tokens, deterministic request extraction and safe HTTP middleware enforcement for WP-226 I6.
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
  - csrf
  - session
  - middleware
  - security
  - specification
depends_on:
  - EG-381
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# CSRF Token Generation, Validation and Middleware

WP-226 I6 defines CSRF tokens as opaque Base64URL values generated from cryptographically secure randomness and stored only inside request-scoped session state. Tokens SHALL be compared with `hash_equals` and SHALL NOT appear in logs, diagnostics or error responses.

The default protected methods are POST, PUT, PATCH and DELETE. GET, HEAD and OPTIONS SHALL bypass validation. Submitted tokens MAY be read from the configured header or body field, with the header taking precedence. Query strings, route parameters and independent cookies SHALL NOT be accepted.

Protected requests without a valid session-bound token SHALL receive a generic `403 application/problem+json` response. Internal failure reasons MAY be represented structurally but SHALL NOT alter the public error detail. Handler exceptions after successful validation SHALL propagate unchanged.
