---
id: EG-446
title: Orquestación de login federado, sesión y eventos de seguridad
summary: Define el flujo end-to-end de callback OIDC hasta principal local, establecimiento de sesión por contrato y emisión de eventos de seguridad.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - federation
  - session
  - events
depends_on:
  - EG-445
  - EG-226
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-446 — Federated Login Orchestration, Session y Security Events

## Objetivo

Orquestar el flujo federado completo sin duplicar los subsistemas existentes de sesión, identidad o autorización.

## Flujo

`FederatedLoginOrchestrator` ejecuta:

1. validación de callback/state;
2. construcción de token exchange;
3. token exchange por contrato;
4. validación del ID Token;
5. mapping de identidad federada a identidad local;
6. establecimiento de sesión por contrato;
7. emisión de evento de seguridad.

## Session boundary

`FederatedSessionEstablisherInterface` representa el único punto de integración con el subsistema de sesiones.

WP-234 no crea cookies ni implementa almacenamiento de sesión propio.

## Eventos

`FederatedSecurityEventPublisherInterface` permite publicar:

- `oidc.login.succeeded`;
- `oidc.login.id_token_rejected`;
- `oidc.login.account_unresolved`.

Los eventos no deben contener:

- authorization code;
- ID Token;
- access token;
- client secret.

## Fail-closed

No se establece sesión cuando:

- el callback no valida;
- el ID Token es inválido;
- no existe vínculo local y provisioning no está permitido.

## Autorización

La orquestación no produce decisiones de autorización.

El principal resultante continúa usando WP-227/WP-232.

## Criterios de aceptación

- login válido establece sesión una sola vez;
- ID Token inválido no crea sesión;
- cuenta no resuelta no crea sesión;
- eventos no contienen secretos;
- sin cookies/Response;
- sin decision engine paralelo;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
