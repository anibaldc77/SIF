---
id: EG-395
title: Adaptadores nativos de hashing y verificación de contraseñas
summary: Define política inmutable y adaptadores sobre las primitivas nativas de PHP con rehash explícito y compatibilidad controlada de algoritmos.
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
  - hashing
  - native-adapter
depends_on:
  - EG-394
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-395 — Native Password Hashing and Verification Adapters

## Objetivo

Incorporar adaptadores seguros sobre `password_hash`, `password_verify`, `password_get_info` y `password_needs_rehash`, preservando los contratos neutrales establecidos en I2.

## Decisiones normativas

- La generación y la verificación se implementan mediante componentes separados detrás de `PasswordHasherInterface` y `PasswordVerifierInterface`.
- Ambos componentes reciben una `PasswordHashPolicy` inmutable.
- La política define algoritmo nativo y opciones, pero no contiene secretos ni hashes.
- La política por defecto utiliza `PASSWORD_DEFAULT` para seguir la recomendación del runtime PHP.
- Bcrypt valida costos entre 4 y 31 antes de invocar funciones nativas.
- Argon2id sólo puede configurarse cuando el runtime expone `PASSWORD_ARGON2ID`.
- El hash generado se inspecciona con `password_get_info`; el algoritmo y las opciones efectivas se conservan como metadata del `StoredPasswordHash`.
- Una contraseña incorrecta o un hash nativo no reconocido producen rechazo esperado, no excepción técnica.
- La recomendación de rehash sólo puede emitirse después de una verificación exitosa.
- Ningún adaptador conoce repositorios, BaseModel, PDO, HTTP ni sesión.

## Compatibilidad

Los hashes generados son formatos nativos autocontenidos de PHP. La verificación puede aceptar hashes creados con políticas anteriores y señalar rehash cuando la política vigente difiere.

## Límites

I3 no implementa resolución de cuentas, autenticador, bloqueo, rate limiting ni escritura del hash actualizado. La aplicación o un adaptador de persistencia futuro será responsable de aplicar el rehash recomendado.

## Criterios de aceptación I3

- Hash y verificación nativos funcionan con `PASSWORD_DEFAULT`.
- Contraseñas incorrectas son rechazadas.
- El aumento de costo bcrypt genera recomendación de rehash.
- Hashes no reconocidos fallan de manera cerrada.
- PHPStan, PHPUnit y Builder finalizan sin diagnósticos.
