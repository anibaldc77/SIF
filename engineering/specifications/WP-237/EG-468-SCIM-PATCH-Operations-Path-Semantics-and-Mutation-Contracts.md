---
id: EG-468
title: Operaciones SCIM PATCH, semántica de path y contratos de mutación
summary: Define Add, Replace y Remove, PatchOp request, paths tipados y contratos neutrales de aplicación sin acoplar PATCH al modelo concreto de recursos.
status: Draft for Review
version: 0.1.1
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
  - patch
  - mutation
  - provisioning
depends_on:
  - EG-467
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-468 — SCIM PATCH Operations, Path Semantics and Mutation Contracts

## Objetivo

Introducir el modelo PATCH de SCIM 2.0 como capa protocolaria neutral, sin exigir un tipo concreto de User o Group y sin acoplar la foundation a HTTP o storage.

## PatchOp schema

`ScimPatchRequest::SCHEMA_URI` define el URN protocolario:

`urn:ietf:params:scim:api:messages:2.0:PatchOp`

El request exige dicho schema y al menos una operación.

## Operation type

`ScimPatchOperationType` representa exclusivamente:

- add;
- remove;
- replace.

## Path

`ScimPatchPath` conserva el path protocolario como value object validado.

I4 no interpreta todavía valuePath/filter expressions.

## Operation

`ScimPatchOperation` contiene operation, path y value.

Reglas mínimas:

- remove requiere path;
- add y replace requieren value.

## Applier contract

`ScimPatchApplierInterface` opera sobre una representación SCIM genérica:

`array<string, mixed>`

y retorna una nueva representación del recurso.

Esta decisión evita acoplar PATCH a una clase concreta de User/Group y permite integrarlo posteriormente con los provisioners existentes.

## Compatibilidad

I4 no presupone la existencia de:

- `ScimSchemas`;
- `ScimUserResource`;
- `ScimGroupResource`.

El protocolo PATCH permanece independiente del modelo concreto de recursos definido por implementaciones anteriores.

## Criterios de aceptación

- Add/Replace/Remove explícitos;
- PatchOp schema autocontenido;
- orden preservado;
- validación estructural;
- contracts storage/transport/provider-neutral;
- sin dependencia de tipos de recurso inexistentes;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
