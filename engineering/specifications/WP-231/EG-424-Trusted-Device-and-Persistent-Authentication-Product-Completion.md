---
id: EG-424
title: Cierre de producto de dispositivos confiables y autenticación persistente
summary: Define los criterios integrales de cierre, seguridad, compatibilidad y operación de WP-231 I8.
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
  - persistent-authentication
  - trusted-device
  - product-completion
depends_on:
  - EG-423
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-424 — Cierre de producto de dispositivos confiables y autenticación persistente

## Objetivo

Cerrar WP-231 con validación integral de emisión, transporte, restauración de sesión, rotación, replay, expiración, revocación y política de trusted-device.

## Invariantes finales

- El validator nunca se persiste en claro.
- Toda restauración válida rota el validator.
- El selector permanece estable durante una cadena de rotaciones.
- La expiración absoluta nunca se extiende por uso.
- La reutilización de un validator anterior se clasifica como posible replay y revoca la credencial.
- Una identidad no resoluble provoca revocación fail-closed.
- Una sesión autenticada existente no es reemplazada mediante persistent authentication.
- Trusted-device no autentica por sí mismo.
- Trusted-device no modifica `AuthenticationLevel`.
- La política por defecto nunca omite MFA implícitamente.
- Revocar trusted-device y revocar persistent authentication son operaciones independientes.
- Los snapshots y comandos no exponen material reutilizable.
- Los stores productivos deben implementar rotación atómica.

## Integración HTTP

La aplicación es responsable de emitir la cookie con atributos seguros apropiados:

- Secure;
- HttpOnly;
- SameSite;
- Domain;
- Path;
- Max-Age o Expires.

Después de cada restauración exitosa debe reemplazarse la cookie anterior por el token rotado.

## Operación

Los comandos administrativos son opt-in y deben quedar protegidos por la autorización operativa de la aplicación.

## Compatibilidad

WP-231 es aditivo y no habilita autenticación persistente ni trusted-device automáticamente.

## Fuera de alcance

- OAuth/OIDC;
- refresh tokens OAuth;
- JWT como mecanismo de sesión;
- WebAuthn;
- passkeys;
- Keycloak;
- LDAP;
- device attestation;
- fingerprinting invasivo del navegador.

## Criterios de aceptación

- Prueba end-to-end de restauración y replay.
- Prueba de cadena de rotación con expiración absoluta estable.
- Prueba de trusted-device sin elevación.
- Prueba de independencia de revocación.
- PHPUnit completo sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
