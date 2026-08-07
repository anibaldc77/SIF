---
id: EG-419
title: Ciclo de vida, rotación y detección de replay de credenciales persistentes
summary: Define emisión, validación, rotación atómica, revocación y detección de replay para WP-231 I3.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - rotation
  - replay
depends_on:
  - EG-418
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-419 — Ciclo de vida, rotación y detección de replay

## Objetivo

Implementar el ciclo de vida de credenciales persistentes antes de permitir cualquier restauración de sesión.

## Emisión

La emisión persiste:

- selector;
- identidad;
- digest del validator;
- fecha de emisión;
- expiración absoluta.

El validator en claro se entrega únicamente al cliente.

## Validación y rotación

Una validación exitosa debe:

1. buscar por selector;
2. verificar estado y expiración;
3. comparar el digest del validator;
4. generar un validator nuevo;
5. reemplazar atómicamente el digest anterior;
6. devolver un token sustituto al cliente.

El selector permanece estable durante la rotación.

## Detección de replay

Si el selector existe pero el validator presentado no coincide con el digest vigente, la credencial se revoca y el resultado se clasifica como `replay_suspected`.

Esto cubre el escenario típico de un token anterior reutilizado después de una rotación exitosa.

## Atomicidad

El store productivo debe implementar `rotate()` como compare-and-swap o transacción equivalente:

- selector;
- digest esperado actual;
- credential replacement.

Dos validaciones concurrentes con el mismo validator no deben poder aceptarse ambas.

## Expiración

La expiración es absoluta y no se extiende durante la rotación.

Una credencial expirada se revoca y no puede rotarse.

## Compatibilidad

Todavía no se restaura sesión ni se emiten cookies HTTP. I3 se limita al dominio y al ciclo de vida seguro.

## Criterios de aceptación

- Rotación preserva selector y expiración absoluta.
- Validator previo es rechazado como replay.
- Replay revoca la credencial.
- Expiración y revocación impiden rotación.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
