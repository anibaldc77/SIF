---
id: EG-422
title: Política de dispositivo confiable e interacción con MFA
summary: Define cómo un trusted-device grant puede influir en decisiones de política sin autenticar, elevar nivel ni omitir MFA de forma implícita.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - trusted-device
  - policy
  - mfa
depends_on:
  - EG-421
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-422 — Política de dispositivo confiable e interacción con MFA

## Objetivo

Permitir que una concesión de dispositivo confiable participe en una decisión de política sin convertirse en autenticación ni en bypass MFA automático.

## Principio

La confianza del dispositivo es una señal adicional de contexto.

No constituye:

- prueba primaria de identidad;
- restauración de sesión;
- elevación de `AuthenticationLevel`;
- satisfacción de un desafío MFA.

## Política por defecto

La política incluida por SIF reconoce un dispositivo válido pero nunca autoriza por sí misma omitir MFA.

Si el nivel actual ya satisface el requerido, la decisión informa que no hace falta una elevación adicional por razones independientes del grant.

Si el nivel actual no alcanza el requerido, el grant puede ser reconocido pero la autenticación reforzada continúa siendo necesaria.

## Política personalizada

Una aplicación podrá implementar `TrustedDevicePolicyInterface` y decidir explícitamente si determinados escenarios permiten omitir un nuevo desafío MFA.

Esa decisión será de la aplicación y no del Core.

## Seguridad

Toda evaluación debe validar:

- existencia del grant;
- identidad propietaria;
- estado;
- expiración.

La evaluación no puede mutar:

- principal;
- sesión;
- `SecurityContext`;
- nivel de autenticación.

## Compatibilidad

La política queda desacoplada de HTTP, Cookie y Session.

## Criterios de aceptación

- Política default nunca habilita bypass MFA implícito.
- Identidad distinta es rechazada.
- Grant expirado o inexistente es rechazado.
- El principal nunca se modifica.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
