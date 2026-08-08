---
id: EG-470
title: Versionado de recursos SCIM, ETag, precondiciones y control de concurrencia
summary: Define versiones opacas, entity tags, If-Match/If-None-Match y evaluación neutral de precondiciones para evitar actualizaciones perdidas.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-237
tags:
  - security
  - scim
  - etag
  - concurrency
  - versioning
depends_on:
  - EG-469
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-470 — SCIM Resource Versioning, ETag Preconditions and Concurrency Control

## Objetivo

Introducir control optimista de concurrencia para recursos SCIM sin acoplarlo a HTTP concreto ni a persistencia.

## Resource version

`ScimResourceVersion` representa una versión opaca.

Foundation no prescribe hashes, timestamps, rowversion ni secuencias.

## EntityTag

`ScimEntityTag` representa un ETag fuerte o débil.

Puede comparar su opaque tag con una `ScimResourceVersion`.

## Preconditions

`ScimPrecondition` representa:

- If-Match;
- If-None-Match;
- lista de entity tags;
- wildcard `*`.

## Evaluación

`DefaultScimPreconditionEvaluator` aplica semántica determinista:

### If-Match

- `*` exige recurso existente;
- tags explícitos exigen versión existente y al menos un match.

### If-None-Match

- `*` exige recurso inexistente;
- tags explícitos permiten recurso inexistente o versión distinta;
- un match explícito falla.

## Guard

`ScimVersionGuard` convierte un resultado fallido en `ScimPreconditionFailedException`.

La traducción a HTTP 412 pertenece a la capa HTTP.

## Seguridad y concurrencia

La precondición debe evaluarse contra la misma versión que protege la mutación.

Adapters productivos deben combinar evaluación y escritura de forma atómica cuando el storage lo permita.

## Fuera de alcance de I6

- parsing de headers HTTP;
- generación concreta de versiones;
- repository compare-and-swap;
- ETag response headers;
- transacciones;
- HTTP 304.

## Criterios de aceptación

- weak ETag;
- If-Match matching/mismatch;
- If-None-Match wildcard;
- guard de precondición;
- contract storage/transport-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
