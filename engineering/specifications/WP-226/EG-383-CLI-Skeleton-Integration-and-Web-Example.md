---
id: EG-383
title: CLI, Skeleton Integration and Web Example
summary: Specifies safe configuration inspection commands and deterministic session/CSRF application skeleton artifacts for WP-226 I7.
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
  - session
  - csrf
  - cli
  - skeleton
  - web
  - specification
depends_on:
  - EG-382
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# CLI, Skeleton Integration and Web Example

WP-226 I7 defines inspection-only CLI commands for session and CSRF configuration. The commands SHALL expose policy and transport settings but SHALL NOT expose session identifiers, cookie values, CSRF tokens, secrets or session contents.

The application skeleton SHALL generate user-owned artifacts with fail-on-conflict overwrite policy. Generated configuration SHALL make HTTPS assumptions explicit, use a secure `__Host-` cookie example, and declare CSRF header, form field and protected methods without enabling authentication or authorization.

The web example SHALL demonstrate explicit GET and POST routes, session middleware, CSRF middleware, session-bound token rendering and flash data after a successful submission. It SHALL NOT use controller discovery, global state or direct header emission.
