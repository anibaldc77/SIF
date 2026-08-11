---
id: EG-567
title: High Assurance Credential Status Enforcement
summary: Define fail-closed enforcement of credential status assessments after resolution and freshness validation.
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
  - high-assurance
depends_on:
  - EG-566
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-567 — High Assurance Credential Status Enforcement

## Objective

Define the enforcement boundary applied to a resolved credential status assessment.

## Enforcement order

The freshness policy is evaluated before credential acceptance.

An assessment is rejected when it is:

- revoked;
- suspended;
- invalid;
- associated with policy violations;
- rejected by the configured freshness policy.

## Fail-closed semantics

The enforcement layer never converts an indeterminate or invalid assessment into an accepted credential.

## Separation of responsibilities

The enforcement service consumes `CredentialStatusAssessment`.

It does not perform transport, status-list retrieval, cache persistence or status-list decoding.

Resolution and enforcement therefore remain independently replaceable.