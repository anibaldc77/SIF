---
id: EG-403
title: Almacenamiento y ciclo de vida atómico de desafíos de recuperación
summary: Define emisión única, consulta, consumo atómico, revocación, invalidación de anteriores y limpieza de desafíos vencidos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - challenge-store
  - lifecycle
depends_on:
  - EG-401
  - EG-402
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-403 — Recovery Challenge Store and Atomic Lifecycle

## Objetivo

Definir el ciclo persistible de un desafío de recuperación o verificación sin acoplarlo a BaseModel, PDO, Redis ni un proveedor de entrega.

## Decisiones normativas

- El almacenamiento conserva un `RecoveryChallengeRecord` compuesto por desafío, digest y estado.
- Los estados son `pending`, `consumed` y `revoked`.
- Sólo un desafío pendiente y no vencido puede consumirse.
- El consumo valida identificador, propósito y token antes de realizar una transición única a `consumed`.
- La reutilización, revocación, expiración, propósito incorrecto y token incorrecto se rechazan de forma cerrada.
- Las implementaciones persistentes deben garantizar atomicidad equivalente a compare-and-swap o transacción con bloqueo apropiado.
- La invalidación por sujeto y propósito permite reemplazar desafíos anteriores sin afectar otros propósitos.
- La limpieza de vencidos es explícita y no altera desafíos todavía vigentes.
- Los snapshots excluyen token, digest y sujeto directo.
- `InMemoryRecoveryChallengeStore` es una referencia para pruebas y procesos individuales, no un almacén distribuido.

## Compatibilidad

La evolución del contrato ocurre dentro de WP-229 antes de su publicación y no modifica APIs consolidadas de WP-226 a WP-228.

## Criterios de aceptación

- Emisión con identificador único.
- Consulta determinista.
- Consumo único y rechazo de replay.
- Validación de propósito y digest.
- Revocación individual y masiva por sujeto/propósito.
- Purga de vencidos.
- PHPUnit, PHPStan y Builder sin diagnósticos.
