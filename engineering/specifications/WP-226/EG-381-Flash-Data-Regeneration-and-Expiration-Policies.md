---
id: EG-381
title: Flash Data, Regeneration and Expiration Policies
summary: Specifies request-bound flash transitions, deterministic session regeneration and exact expiration policies for WP-226 I5.
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
  - flash
  - regeneration
  - expiration
  - specification
depends_on:
  - EG-380
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Flash Data, Regeneration and Expiration Policies

WP-226 I5 defines flash data as a separate session concern with deterministic request-to-request transitions. New flash values SHALL become available only after a successful commit. Available values SHALL be removed on the next successful commit unless retained explicitly through `keepFlash()` or `reflash()`.

Session regeneration SHALL preserve normal data and pending flash data, issue a cryptographically new identifier, delete the previous record and occur at most once per commit. Regeneration MAY be requested manually or triggered by an interval policy evaluated through the injected clock.

Absolute and idle expiration SHALL use exact `>=` boundaries. Last activity SHALL be advanced only during successful commit, never during open. Expired records SHALL be deleted before a new request-scoped state is created.
