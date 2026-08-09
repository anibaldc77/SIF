---
id: WP-238-I5-REVIEW
title: WP-238 I5 Implementation Review
summary: Revisa reglas SoD, detección de conflictos y evaluación de riesgo, incluyendo exhaustividad estática del nivel de riesgo.
status: Draft for Review
version: 0.1.1
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - sod
  - risk
  - implementation-review
depends_on:
  - EG-477
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-238 I5 Implementation Review

## Alcance revisado

Se incorpora:

- conflict rule id;
- risk level;
- SoD rule;
- conflict model;
- risk assessment;
- evaluator;
- rule provider contract.

## Hallazgos

- Los conflictos son simétricos.
- Risk score es determinista y limitado.
- Sólo assignments efectivos participan.
- El evaluator no realiza remediation.
- No existe dependencia de storage, SCIM o proveedor.

## Corrección de validación estática

PHPStan detectó que `GovernanceRiskLevel::$value` conserva tipo estático `string`, por lo que el `match` de `weight()` no podía considerarse exhaustivo aunque el constructor valide los cuatro valores permitidos.

Se agregó una rama `default` defensiva que lanza `LogicException`. No modifica la semántica pública: cualquier valor inválido continúa siendo rechazado en construcción, y el `default` protege únicamente un estado interno imposible.

## Decisión

El incremento queda apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
