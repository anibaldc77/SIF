---
id: EG-447
title: Integración HTTP de login/callback, redirect model y logout federado
summary: Define una capa HTTP-neutral para iniciar login OIDC, procesar callback y modelar logout federado sin emitir Responses ni redirects directos.
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
  - http
  - callback
  - logout
depends_on:
  - EG-446
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-447 — HTTP Integration y Federated Logout

## Objetivo

Exponer el flujo OIDC a aplicaciones HTTP sin acoplar Foundation a un framework web ni emitir respuestas directamente.

## Redirect model

`OidcRedirectInstruction` contiene:

- location;
- query parameters.

No ejecuta redirects.

## Login start

`OidcHttpLoginStartService`:

1. crea authorization transaction;
2. genera redirect instruction al authorization endpoint.

La aplicación decide cómo convertirlo en una respuesta HTTP.

## Callback

`OidcHttpCallbackRequest` encapsula el callback ya parseado.

`OidcHttpCallbackService` delega el procesamiento al `FederatedLoginOrchestrator`.

No crea sesión por sí mismo.

## Logout

`OidcLogoutRequest` modela:

- end-session endpoint;
- id_token_hint opcional;
- post_logout_redirect_uri opcional.

`OidcFederatedLogoutProviderInterface` abstrae la construcción del redirect federado.

`StandardOidcLogoutRedirectProvider` implementa la forma estándar sin código específico de proveedor.

## Seguridad

- ID Token continúa redactado fuera de la frontera explícita de logout;
- la capa HTTP no llama `header()`;
- no instancia Response;
- no ejecuta redirects;
- no crea cookies;
- no inicia sesiones;
- logout permanece provider-neutral.

## Criterios de aceptación

- login start produce redirect instruction;
- redirect model no ejecuta I/O;
- logout incluye id_token_hint cuando corresponde;
- post logout redirect modelado;
- sin código específico de Keycloak;
- sin session/cookie/Response;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
