---
id: EG-562
title: Bitstring Status List Data Model and Bit Resolution
summary: Define the decoded Bitstring Status List model, minimum population boundary, left-to-right bit indexing, multi-bit status values, and deterministic local resolution.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
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
depends_on:
  - EG-561
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-562 — Bitstring Status List Data Model and Bit Resolution

## Objetivo

Incorporar el modelo local de Bitstring Status List y una resolución determinística de status sin acoplar Foundation a transporte, Multibase, Base64URL, GZIP, cache o persistencia.

## Modelo decodificado

`BitstringStatusList` recibe el bitstring ya expandido como bytes. Mantiene explícitos URI de lista, purpose, `statusSize`, longitud y capacidad.

La transformación desde una representación publicada hacia bytes decodificados queda fuera de esta implementación y será responsabilidad de capas/adapters posteriores.

## Tamaño mínimo

La representación exige al menos 131072 bits. Este límite mantiene el mínimo de 16 KB definido para Bitstring Status List v1.0.

## Orden de bits

El índice cero corresponde al bit situado más a la izquierda. Dentro de cada byte la resolución utiliza orden MSB-first. Los índices posteriores avanzan de izquierda a derecha.

## Status size

`statusSize` permite representar entradas de uno o más bits. `entryCapacity` se obtiene dividiendo la longitud total en bits por `statusSize`.

## Resolución

`BitstringStatusResolver`:

- valida bounds antes de acceder al bitstring;
- calcula la posición inicial como `index * statusSize`;
- resuelve bits de izquierda a derecha;
- soporta valores que atraviesan límites de byte;
- devuelve `BitstringStatusValue` sin inferir todavía policy de revocation/suspension.

## Neutralidad

La implementación no realiza HTTP, GZIP, Multibase, Base64URL, almacenamiento ni cache. La autenticidad de la Status List tampoco se presume por la mera disponibilidad de bytes.

## Compatibilidad

I2 no modifica `CredentialStatusResolverInterface` ni otros contratos públicos preexistentes. Extiende únicamente la arquitectura especializada introducida por WP-249.

## Criterios de aceptación

Resolución MSB-first determinística, minimum-size enforcement, bounds validation, multi-bit support, PHPUnit/PHPStan limpios y SIF Builder sin diagnósticos.
