---
id: EG-421
title: Restauración de sesión mediante autenticación persistente
summary: Define restauración de principal y sesión a partir de credenciales persistentes rotadas, sin implicar MFA ni confianza de dispositivo.
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
  - session
  - restoration
depends_on:
  - EG-420
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-421 — Restauración de sesión mediante autenticación persistente

## Objetivo

Permitir que una credencial persistente válida restaure una identidad autenticada en una sesión nueva, manteniendo rotación obligatoria y sin convertir autenticación persistente en MFA o trusted-device.

## Flujo

1. No debe existir un principal autenticado previamente en `SecurityContext`.
2. Se localiza la credencial por selector.
3. Se ejecuta validación y rotación mediante el ciclo de vida de I3.
4. Se obtiene la identidad asociada a la credencial.
5. Un factory desacoplado resuelve el principal.
6. Se crea evidencia con método `persistent`.
7. Se persiste el principal mediante `SessionAuthenticationManager`.
8. Se devuelve el token rotado para transporte posterior.

## Principal factory

El dominio no conoce BaseModel, PDO, LDAP, Keycloak ni un proveedor concreto.

`PersistentAuthenticationPrincipalFactoryInterface` permite que cada aplicación resuelva la identidad utilizando el mecanismo apropiado.

## Nivel de autenticación

El nivel restaurado es configuración explícita. El subsistema no asume que una credencial persistente equivale a password, MFA o step-up.

Debe utilizarse un nivel menor que cualquier nivel reservado por la aplicación para autenticación reforzada.

## Fail closed

Si la credencial es válida pero la identidad ya no puede resolverse, la credencial se revoca.

Una sesión autenticada existente nunca se reemplaza mediante persistent authentication.

## Separaciones

Restaurar sesión no:

- concede trusted-device;
- satisface MFA;
- eleva automáticamente autorización;
- extiende la expiración absoluta de la credencial.

## Compatibilidad

La integración reutiliza `SessionAuthenticationManager`, `SecurityContext` y `SessionState` sin modificar sus contratos públicos.

## Criterios de aceptación

- Restauración válida crea sesión y solicita regeneración.
- El token rota en cada restauración.
- Replay no restaura una segunda sesión.
- Identidad inexistente revoca la credencial.
- Un principal existente no es reemplazado.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
