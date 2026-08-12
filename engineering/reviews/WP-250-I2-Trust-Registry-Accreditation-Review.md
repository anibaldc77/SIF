---
id: WP-250-I2-REVIEW
title: WP-250 I2 Trust Registry Accreditation Review
summary: Revisa registry membership, accreditation scope y accreditation validity boundaries para credential trust ecosystems.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - trust-registry
  - accreditation
  - architecture-review
depends_on:
  - EG-570
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-250 I2 Trust Registry Accreditation Review

## Alcance revisado

I2 incorpora registry membership status, registry entry, accreditation scope, accreditation y contratos independientes de resolution/policy.

## Hallazgos

- Membership y accreditation permanecen responsabilidades distintas.
- Suspension, revocation y expiration se modelan explícitamente.
- Accreditation scope permite restringir credential types, jurisdicciones y constraints.
- La vigencia temporal es parte del modelo.
- Registry/federation/PKI concretos permanecen fuera de Foundation.

## Compatibilidad

I2 no modifica los contracts de I1 ni los contratos públicos previos de credential trust.

## Decisión

Apto para I3 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
