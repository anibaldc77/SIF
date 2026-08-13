---
id: WP-251-I5-REVIEW
title: WP-251 I5 Trust Marks Accreditation Binding Review
summary: Revisa Trust Mark models, semantic validation y el bridge con CredentialAccreditation de WP-250.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - trust-marks
  - accreditation
  - architecture-review
depends_on:
  - EG-581
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-251 I5 Trust Marks Accreditation Binding Review

## Alcance revisado

I5 incorpora Trust Mark, validation context/result, default semantic validator, accreditation binding y contracts separados de resolution/validation/binding policy.

## Hallazgos

- Trust Mark y accreditation permanecen conceptos distintos.
- La accreditation canónica continúa siendo la de WP-250.
- Subject e issuing authority deben coincidir al crear el binding.
- Un Trust Mark no implica aceptación automática.
- Crypto, HTTP y persistence permanecen fuera de Foundation.

## Compatibilidad

I5 no modifica I1-I4 ni WP-250.

## Decisión

Apto para I6 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
