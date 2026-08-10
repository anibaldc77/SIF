---
id: EG-514
title: Security Event Token Verification Issuer Audience Time and Replay Boundaries
summary: Define validación contextual de Security Event Tokens con issuer, audience, tiempo y replay protection preservando la frontera de verificación inicial.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-243
tags:
  - security
  - shared-signals
  - set
  - replay
depends_on:
  - EG-513
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-514 — Security Event Token Verification, Issuer, Audience, Time and Replay Boundaries

## Objetivo

Fortalecer la recepción de Security Event Tokens mediante validación contextual y fronteras separadas para trust y replay protection.

## Validation Context

`SecurityEventTokenValidationContext` expresa:

- issuer esperado;
- audience esperada;
- instante de evaluación;
- clock skew permitido.

## Validation Result

`SecurityEventTokenValidationResult` agrega:

- token verificado;
- issuer validity;
- audience validity;
- temporal validity;
- replay safety;
- warnings.

## Compatibilidad

`SecurityEventTokenVerifierInterface::verify()` se conserva.

I2 agrega `verifyWithContext()` para ampliar la verificación sin romper I1.

## Contratos

- `SecurityEventTokenIssuerValidatorInterface`;
- `SecurityEventTokenAudienceValidatorInterface`;
- `SecurityEventTokenTimeValidatorInterface`;
- `SecurityEventTokenReplayStoreInterface`.

## Seguridad

Adapters productivos deberán:

- validar firma y algoritmo antes de aceptar claims;
- comprobar issuer y audience;
- validar timestamps con clock skew acotado;
- utilizar issuer + token id como replay key;
- persistir replay evidence durante una ventana apropiada;
- rechazar duplicados;
- evitar registrar el SET completo cuando contenga información sensible.

## Neutralidad

Foundation no conoce JWT/JWS library, Redis, base de datos, cache, HSM ni HTTP transport.

## Criterios de aceptación

Compatibilidad I1 preservada, validation context/result tipados, replay contract, validators separados, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
