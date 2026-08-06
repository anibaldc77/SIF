---
id: EG-398
title: Almacenes de hashes de contraseña y coordinación transaccional de rehash
summary: Define contratos de lectura y escritura de hashes y una coordinación segura de rehash posterior a una verificación exitosa.
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
  - specification
  - security
  - password
  - rehash
  - persistence
depends_on:
  - EG-397
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-398 — Almacenes de hashes y coordinación de rehash

## 1. Objetivo

Completar el ciclo de rehash sin acoplar Foundation a BaseModel, PDO o una tecnología transaccional concreta.

## 2. Decisión arquitectónica

`PasswordHashStoreInterface` extiende el contrato de lectura existente y agrega reemplazo explícito. Los adaptadores pueden implementar atomicidad mediante la infraestructura disponible.

`PasswordRehashCoordinatorInterface` recibe exclusivamente una credencial ya verificada dentro del límite de seguridad. El coordinador genera el nuevo hash y entrega únicamente material derivado al almacén.

## 3. Invariantes

1. El rehash sólo se ejecuta después de una verificación exitosa.
2. La contraseña en texto claro no se entrega al almacén.
3. El almacén recibe únicamente identidad y hash de reemplazo.
4. La ausencia de coordinador conserva el comportamiento anterior mediante implementación nula.
5. Los adaptadores distribuidos deben documentar atomicidad, concurrencia y recuperación ante fallos.

## 4. Compatibilidad

El nuevo coordinador se incorpora como dependencia opcional al final del constructor de `PasswordAuthenticator`. Los consumidores existentes permanecen compatibles.

## 5. Fuera de alcance

No se define esquema SQL, repositorio BaseModel, unidad de trabajo concreta ni política de cambio manual de contraseña.
