---
id: EG-420
title: Ciclo de vida y revocación de concesiones de dispositivo confiable
summary: Define emisión, validación contextual, expiración y revocación individual o global de trusted-device grants para WP-231 I4.
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
  - lifecycle
  - revocation
depends_on:
  - EG-419
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-420 — Ciclo de vida y revocación de dispositivos confiables

## Objetivo

Implementar el ciclo de vida independiente de una concesión de dispositivo confiable sin convertirla en una credencial de autenticación ni en una elevación MFA.

## Emisión

Una concesión contiene:

- identificador criptográficamente aleatorio;
- identidad;
- fecha de emisión;
- fecha de expiración;
- estado.

No contiene:

- contraseña;
- validator persistente;
- `AuthenticationLevel`;
- estado de sesión;
- resultado MFA.

## Evaluación

`isTrusted()` sólo responde si existe una concesión:

- activa;
- perteneciente a la identidad esperada;
- no expirada.

El resultado es una señal de política. No autentica a la identidad.

## Revocación

Se soporta:

- revocación individual;
- revocación global por identidad.

La revocación global permite responder a operaciones como:

- cambio de contraseña;
- recuperación de cuenta;
- sospecha de compromiso;
- cierre de todos los dispositivos.

## Seguridad

La representación pública usa fingerprints de identidad e identificador.

Una concesión confiable nunca debe:

- crear `AuthenticatedPrincipal`;
- modificar `SecurityContext`;
- crear sesión;
- elevar `AuthenticationLevel`;
- satisfacer MFA automáticamente.

## Compatibilidad

El modelo continúa siendo independiente de HTTP, Cookie, Session y autenticación persistente.

## Criterios de aceptación

- Expiración determinística.
- Revocación individual.
- Revocación global.
- Separación de identidades.
- Ninguna elevación de autenticación implícita.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
