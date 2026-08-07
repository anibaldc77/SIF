---
id: WP-235-I1-REVIEW
title: WP-235 I1 Architecture Review
summary: Revisa la arquitectura inicial de operaciones de seguridad federada y contratos de revocación.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - architecture-review
depends_on:
  - EG-449
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-235 I1 Architecture Review

## Alcance revisado

Se incorpora la base de operaciones de seguridad federada:

- revocation scope;
- revocation reason;
- revocation request/result;
- session revocation contract;
- provider credential revocation contract;
- identity link revocation contract;
- security operation event contract.

## Hallazgos

- La intención de revocación es explícita.
- Identidad local y federada permanecen diferenciadas.
- No existe acoplamiento a persistencia.
- No existe acoplamiento a Keycloak ni otro proveedor.
- Foundation no ejecuta I/O.
- Los eventos no contienen tokens.

## Riesgo evitado

Revocar únicamente la sesión local puede dejar credenciales externas activas; desvincular únicamente la identidad puede dejar sesiones válidas. La arquitectura separa scopes para permitir una política coordinada posterior.

## Decisión

La arquitectura es apta para continuar con lifecycle y orchestration de revocación en I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
