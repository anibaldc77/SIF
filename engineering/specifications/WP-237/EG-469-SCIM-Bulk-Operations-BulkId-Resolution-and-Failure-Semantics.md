---
id: EG-469
title: Operaciones SCIM Bulk, resolución bulkId y semántica de fallos
summary: Define BulkRequest, operaciones masivas, bulkId, failOnErrors, resultados y contratos neutrales de ejecución y validación.
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
  - bulk
  - provisioning
  - identity
depends_on:
  - EG-468
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-469 — SCIM Bulk Operations, bulkId Resolution and Failure Semantics

## Objetivo

Introducir el modelo Bulk de SCIM 2.0 sin acoplar el core a HTTP, storage o proveedores concretos.

## Bulk request

`ScimBulkRequest` exige:

- schema `BulkRequest`;
- al menos una operación;
- `failOnErrors` opcional mayor que cero;
- `bulkId` únicos.

## Bulk operations

`ScimBulkOperation` representa:

- método POST, PUT, PATCH o DELETE;
- path;
- bulkId opcional;
- version opcional;
- data opcional.

Reglas mínimas:

- POST requiere bulkId;
- POST/PUT/PATCH requieren data;
- DELETE no requiere data.

## bulkId

`ScimBulkId` representa identificadores temporales de referencias dentro del request.

`ScimBulkIdMap` permite registrar la location final y resolver referencias `bulkId:<id>`.

I5 no ejecuta sustituciones recursivas dentro de payloads arbitrarios.

## Bulk response

`ScimBulkResponse` contiene resultados ordenados.

`ScimBulkOperationResult` representa status, location, version, bulkId y response opcional.

## Contracts

- `ScimBulkValidatorInterface`
- `ScimBulkExecutorInterface`

Ambos permanecen neutrales respecto de persistencia y transporte.

## failOnErrors

La foundation modela el umbral pero no prescribe una transacción global.

El executor concreto debe detener operaciones posteriores cuando el número de errores alcance el umbral configurado.

## Seguridad

- bulkId únicos;
- métodos explícitos;
- paths obligatorios;
- write operations requieren payload;
- sin SQL;
- sin HTTP clients;
- sin dependencia de proveedor.

## Fuera de alcance de I5

- ejecución concreta;
- transacciones;
- resolución recursiva de bulkId dentro de estructuras complejas;
- límites globales configurables de operaciones/payload;
- HTTP controller;
- persistencia.

## Criterios de aceptación

- orden preservado;
- failOnErrors modelado;
- POST requiere bulkId;
- write operations requieren data;
- bulkId uniqueness;
- resolution map;
- response tipada;
- contracts infrastructure-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
