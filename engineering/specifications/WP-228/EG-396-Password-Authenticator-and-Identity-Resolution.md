---
id: EG-396
title: Autenticador de contraseña y resolución de identidad
summary: Define la coordinación segura entre credencial compuesta, proveedor de identidad, proveedor de hash, estado de cuenta, verificador y principal autenticado.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - security
  - password
  - authenticator
  - identity-provider
depends_on:
  - EG-393
  - EG-394
  - EG-395
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-396 — Password Authenticator and Identity Resolution

## Objetivo

Integrar un autenticador de contraseña completo sobre los contratos existentes, manteniendo separados identidad, hash almacenado, verificación, estado de cuenta y resultado público de autenticación.

## Decisiones normativas

- La credencial de autenticación combina `IdentityLookupKey` y `PasswordCredential` sin serialización implícita.
- `IdentityProviderInterface` resuelve identidad y estado de cuenta; no expone hashes.
- `PasswordHashProviderInterface` resuelve el hash por identidad mediante un contrato independiente.
- Identidad inexistente, hash ausente y contraseña incorrecta producen `invalid_credentials`.
- El autenticador ejecuta una verificación contra un hash de respaldo cuando la identidad o el hash no existen, reduciendo diferencias observables de ejecución.
- Cuentas deshabilitadas o bloqueadas nunca producen principal autenticado y devuelven rechazo genérico.
- El principal exitoso utiliza método `password`, nivel 20 y el instante UTC de la solicitud.
- La recomendación de rehash se comunica mediante un handler desacoplado y sólo después de autenticación exitosa.
- El handler no recibe la contraseña ni otro secreto reutilizable.
- El autenticador no escribe persistencia, no conoce BaseModel, PDO, HTTP ni sesión.

## Compatibilidad

Los contratos de WP-227 y las implementaciones I1–I3 permanecen sin cambios. La nueva credencial compuesta evita modificar el constructor público de `PasswordCredential`.

## Seguridad

La respuesta pública no distingue entre identidad inexistente, hash ausente o contraseña incorrecta. El hash de respaldo debe usar un formato válido para el verificador configurado y debe administrarse como configuración segura de la aplicación.

## Límites

I4 no incorpora rate limiting, bloqueo progresivo, auditoría de intentos, persistencia de rehash ni endpoints HTTP de login.

## Criterios de aceptación I4

- Una cuenta activa con contraseña válida produce principal autenticado.
- Identidad inexistente y contraseña incorrecta son indistinguibles públicamente.
- Hash ausente falla de manera cerrada.
- Cuentas disabled y locked son rechazadas.
- La señal de rehash no expone el secreto.
- PHPUnit, PHPStan y Builder finalizan sin diagnósticos.
