---
id: WP-251-I4-REVIEW
title: WP-251 I4 Metadata Policy Resolution Review
summary: Revisa los siete standard metadata policy operators, los modelos de policy y los boundaries de resolution/application.
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
  - metadata-policy
  - architecture-review
depends_on:
  - EG-580
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-251 I4 Metadata Policy Resolution Review

## Alcance revisado

I4 incorpora standard operator enum, parameter policy, metadata policy, resolved policy, application result y contracts separados de resolution/application.

## Hallazgos

- Los siete standard operators son explícitos.
- Policy resolution y policy application permanecen responsabilidades distintas.
- `metadata_policy_crit` queda representable mediante critical operators.
- Un fallo de aplicación queda expresado mediante violations y no produce metadata confiable silenciosamente.
- Trust-chain enforcement continúa perteneciendo a WP-250.

## Compatibilidad

I4 no modifica I1-I3 ni WP-250.

## Decisión

Apto para I5 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
