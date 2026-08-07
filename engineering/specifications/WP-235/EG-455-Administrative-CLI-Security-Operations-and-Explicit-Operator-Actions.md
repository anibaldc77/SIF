---
id: EG-455
title: Operaciones administrativas CLI, inspección y acciones explícitas
summary: Define comandos neutrales para inspeccionar y ejecutar revocaciones federadas con confirmación explícita y sin I/O directo.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - cli
  - administration
depends_on:
  - EG-454
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-455 — Administrative CLI Security Operations

## Objetivo

Exponer inspección y ejecución administrativa de revocaciones federadas sin incorporar parsing de consola ni efectos implícitos dentro de Foundation.

## Inspection

`FederatedRevocationInspectCommand` consulta el journal por operation id y devuelve un resultado estructurado.

La inspección no modifica estado.

## Execute

`FederatedRevocationExecuteCommand` exige confirmación explícita.

Sin confirmación devuelve `confirmation_required` y no ejecuta el coordinator.

## CLI boundary

Los comandos:

- no leen `$argv`;
- no llaman `exit`, `die` o `readline`;
- no realizan transporte HTTP;
- no conocen proveedores concretos.

El host CLI de SIF conserva parsing, UX y confirmación interactiva.

## Seguridad

- acciones destructivas requieren confirmación;
- inspection permanece read-only;
- output no contiene tokens ni secretos;
- idempotency sigue gobernada por operation id;
- no existe ejecución automática.

## Criterios de aceptación

- inspect not-found sin side effects;
- execute sin confirmación no actúa;
- execute confirmado delega al coordinator;
- inspection no expone secretos;
- commands sin argv/exit/readline;
- sin transporte proveedor;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
