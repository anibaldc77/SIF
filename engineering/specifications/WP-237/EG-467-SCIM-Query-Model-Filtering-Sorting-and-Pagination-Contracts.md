---
id: EG-467
title: Modelo de consulta SCIM, filtros, orden y paginación
summary: Define un AST de filtros y contratos neutrales para parseo y ejecución de consultas SCIM con sort, paginación y proyección.
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
  - query
  - filtering
  - pagination
depends_on:
  - EG-466
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-467 — SCIM Query Model, Filtering, Sorting and Pagination

## Objetivo

Definir un modelo de consulta neutral que pueda ser usado por controladores, adapters SQL, APIs remotas u otros backends sin acoplar el core a una implementación concreta.

## Filter AST

Se incorporan:

- `ScimComparisonFilter`;
- `ScimLogicalFilter`;
- `ScimNotFilter`;
- `ScimFilterNodeInterface`.

El AST representa la semántica del filtro pero no ejecuta consultas.

## Sort

`ScimSort` contiene atributo y orden `ascending|descending`.

## Pagination

`ScimPagination` utiliza `startIndex` basado en 1 y `count`.

## Projection

`ScimQuery` permite declarar:

- attributes;
- excludedAttributes.

## Contracts

`ScimFilterParserInterface` abstrae el parser textual.

`ScimQueryExecutorInterface` abstrae la ejecución contra storage o servicios externos.

## Fuera de alcance de I3

- parser concreto de RFC 7644;
- SQL generation;
- filter execution;
- list response envelope;
- PATCH;
- Bulk.

## Criterios de aceptación

- comparison AST;
- logical/not composition;
- sort;
- pagination;
- projection;
- parser/executor neutrales;
- PHPUnit focalizado;
- PHPStan limpio;
- Builder sin diagnósticos.
