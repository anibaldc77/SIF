---
id: WP-252-I1-REVIEW
title: WP-252 I1 Architecture Review
summary: Revisa la arquitectura inicial de OpenID4VC HAIP 1.0 como perfil de composición sobre los subsistemas de seguridad y credenciales ya disponibles en SIF.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-252
tags:
  - security
  - verifiable-credentials
  - haip
  - high-assurance
  - interoperability
  - architecture-review
depends_on:
  - EG-585
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-252 I1 Architecture Review

## Alcance revisado

I1 introduce HAIP flows, credential formats, product profile, conformance context/assessment y contratos de policy/bridge.

## Hallazgos

- HAIP se modela como profile y no como duplicación de protocolos.
- OpenID4VCI, OpenID4VP, FAPI 2.0, SD-JWT VC e ISO mdoc permanecen en sus subsistemas canónicos.
- El profile puede seleccionar flows y formats soportados.
- El contexto generic high-assurance de WP-244 permanece reutilizable mediante bridge.
- OpenID Federation puede complementar trust/discovery a nivel ecosystem pero no se transforma en dependencia protocol-specific obligatoria.
- Transport, crypto y platform APIs permanecen fuera de Foundation.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
