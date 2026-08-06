---
id: WP-228-I3-REVIEW
title: Revisión de implementación WP-228 I3
summary: Revisa la política de hashing y los adaptadores nativos de generación, verificación y detección de rehash.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - review
  - security
  - password
  - hashing
depends_on:
  - EG-395
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I3 — Implementation Review

## Resultado

La implementación incorpora hashing y verificación mediante primitivas mantenidas por PHP, sin reproducir algoritmos criptográficos en código propio. La política de hash es inmutable y compartida por adaptadores independientes.

## Evaluación arquitectónica

`NativePasswordHasher` sólo genera hashes y devuelve `StoredPasswordHash`. `NativePasswordVerifier` sólo valida credenciales existentes y recomienda rehash. Esta separación permite sustituir generación o verificación de manera independiente, incluyendo futuros adaptadores remotos o respaldados por hardware.

## Seguridad

Los secretos continúan expuestos únicamente dentro de callbacks acotados. Las funciones nativas reciben parámetros marcados como sensibles. Un hash inválido o desconocido produce rechazo fail-closed y no revela detalles técnicos al flujo de autenticación.

## Compatibilidad y evolución

`PASSWORD_DEFAULT` permite adoptar mejoras futuras del runtime. Las políticas explícitas de bcrypt y Argon2id permiten reproducibilidad cuando la aplicación la necesita. `password_needs_rehash` desacopla la migración de hashes del resultado de autenticación.

## Riesgos controlados

- No se implementan algoritmos criptográficos propios.
- No se fija Argon2id en runtimes que no lo soportan.
- No se actualiza persistencia durante la verificación.
- No se considera un hash desconocido como fallo de infraestructura.

## Próxima implementación

I4 deberá integrar un autenticador de contraseña que coordine proveedor de identidad, estado de cuenta, hash almacenado y verificador, manteniendo respuestas indistinguibles para identidad inexistente y contraseña incorrecta.
