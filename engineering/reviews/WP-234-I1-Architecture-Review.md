---
id: WP-234-I1-REVIEW
title: WP-234 I1 Architecture Review
summary: Revisa la arquitectura inicial de OpenID Connect Client y autenticación federada.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - federation
  - architecture-review
depends_on:
  - EG-441
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-234 I1 Architecture Review

## Alcance revisado

Se incorpora la base del cliente OpenID Connect:

- provider metadata;
- client registration;
- state;
- nonce;
- contratos de provider y generación.

## Hallazgos

- No se incorpora código específico de Keycloak.
- Discovery permanece detrás de contrato.
- Los secretos no forman parte del registro público.
- State y nonce son tipos distintos.
- JWT/JWKS se reutilizarán desde WP-233.
- La sesión seguirá perteneciendo a WP-226.

## Riesgo evitado

Un acoplamiento temprano a un proveedor concreto produciría una arquitectura federada difícil de reutilizar. La capa inicial se limita al estándar OIDC.

## Decisión

La arquitectura es apta para continuar hacia Authorization Code + PKCE en I2 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
