---
id: EG-443
title: Callback correlation, code exchange y credenciales confidenciales OIDC
summary: Define validación de state, authorization code sensible, client secret sensible y contrato de token exchange transport-neutral.
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
  - callback
  - token-exchange
  - client-credentials
depends_on:
  - EG-442
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-443 — Callback correlation y code exchange

## Objetivo

Modelar el retorno del Authorization Server, validar correlación `state` y definir el token exchange detrás de contratos.

## Authorization Code

`OidcAuthorizationCode` representa material sensible:

- redactado;
- no serializable;
- exposición sólo mediante callback explícito.

## Client Secret

`OidcClientSecret` también se trata como secreto:

- no forma parte de `OidcClientRegistration`;
- se redacta;
- no se serializa;
- se entrega únicamente al request de token exchange cuando corresponde.

## Callback

`OidcAuthorizationCallback` contiene:

- state;
- authorization code.

## Correlación

`OidcAuthorizationCallbackValidator` compara el state recibido con el transaction state usando comparación segura.

Mismatch implica fallo cerrado.

## Token Exchange

`OidcTokenExchangerInterface` abstrae el intercambio de código.

El Core no realiza HTTP ni conoce credenciales de transporte.

`OidcTokenExchangeRequest` incluye:

- authorization code;
- client registration;
- code verifier PKCE original;
- client secret opcional.

## Fuera de alcance de I3

I3 no:

- valida ID Token;
- crea principal;
- crea sesión;
- consulta UserInfo;
- implementa logout;
- realiza HTTP concreto.

## Criterios de aceptación

- State matching correcto.
- Mismatch fail-closed.
- Code y client secret protegidos.
- Verifier original preservado.
- Token exchange transport-neutral.
- Sin ID Token validation/session.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
