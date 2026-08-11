---
id: WP-249-I2-REVIEW
title: WP-249 I2 Bitstring Status List Review
summary: Revisa el modelo decodificado y la resolución determinística de Bitstring Status List incorporados en WP-249 I2.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - credential-status
  - bitstring-status-list
  - architecture-review
depends_on:
  - EG-562
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-249 I2 Bitstring Status List Review

## Alcance revisado

Se incorpora el modelo de bitstring expandido, tamaño mínimo, capacidad, status size y resolución local MSB-first.

## Hallazgos

- El índice cero corresponde al bit más a la izquierda.
- La resolución soporta entradas multi-bit y cruces entre bytes.
- Los índices fuera de capacidad fallan antes del acceso a memoria.
- Foundation continúa neutral respecto de HTTP, compresión, codificación, cache y persistencia.
- Los contratos públicos previos de credential status no son redefinidos.

## Decisión

Apto para I3 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
