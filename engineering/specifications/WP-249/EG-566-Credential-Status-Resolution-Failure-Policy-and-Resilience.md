---
id: EG-566
title: Credential Status Resolution Failure Policy and Resilience
summary: Define fail-closed and controlled stale-evidence behavior when credential status resolution is unavailable.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - credential-status
  - verifier
  - resilience
depends_on:
  - EG-565
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-566 — Credential Status Resolution Failure Policy and Resilience

## Objective

Define deterministic verifier behavior when live credential status resolution is unavailable.

## Default behavior

Resolution failure is fail-closed by default.

A resolution failure never creates synthetic valid credential status evidence.

## Controlled stale evidence

Cached evidence may be reused only when:

- the configured failure mode explicitly allows usable stale evidence;
- a cached entry exists;
- the entry remains inside its stale usability window.

When stale evidence is accepted, the resolution decision explicitly identifies the result as cached and stale and recommends refresh.

## Separation of responsibilities

Failure policy does not implement transport, storage, retries or external status resolution.

Those concerns remain outside Foundation or behind dedicated contracts.