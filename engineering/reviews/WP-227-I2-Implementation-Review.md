---
id: WP-227-I2-REVIEW
title: WP-227 I2 Implementation Review
summary: Revisa el modelo inmutable de identidad, atributos del principal, nivel, método y evidencia de autenticación implementado para WP-227 I2.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-05
updated: 2026-08-05
work_package: WP-227
tags:
  - security
  - identity
  - principal
  - authentication-evidence
  - implementation-review
depends_on:
  - EG-386
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-227 I2 Implementation Review

## Alcance revisado

El incremento incorpora identidad opaca, principal autenticado, atributos escalares ordenados canónicamente y evidencia no sensible de autenticación. No incorpora autenticadores, credenciales, repositorios, middleware, sesión ni autorización.

## Hallazgos

- `PrincipalInterface` permanece deliberadamente mínimo y compatible con el principal anónimo.
- `IdentityInterface` desacopla el concepto de identidad de entidades persistentes o modelos de aplicación.
- Los identificadores rechazan vacío, exceso de longitud y caracteres de control.
- Los atributos rechazan nombres inestables, duplicados y valores flotantes no finitos.
- La colección es inmutable, indexada por nombre y determinista.
- `AuthenticationEvidence` normaliza el instante a UTC y no acepta ni expone secretos.
- `AuthenticationLevel` permite comparación explícita sin fijar todavía una taxonomía de proveedor.
- La instantánea del principal tiene estructura estable y no contiene credenciales reutilizables.

## Riesgos revisados

El método `toArray()` no debe interpretarse en incrementos posteriores como prueba suficiente para restaurar una autenticación. La integración de sesión de I5 deberá proteger integridad, vigencia y versión del estado persistido antes de reconstruir un principal.

## Verificación

La prueba focalizada cubre validación, igualdad, orden canónico, duplicados, normalización temporal, comparación de nivel y serialización determinista. Deben completarse PHPUnit, PHPStan y las validaciones gobernadas antes de aprobar el incremento.

## Decisión

WP-227 I2 es apto para integración cuando la validación focalizada y el quality gate completo finalicen sin errores ni diagnósticos.
