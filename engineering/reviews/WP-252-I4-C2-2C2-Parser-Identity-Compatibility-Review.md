---
id: WP-252-I4-C2-2C2-PARSER-IDENTITY-REVIEW
title: WP-252 I4 Parser Identity Compatibility Review
summary: Registra el impacto de class_alias y las restricciones de compatibilidad de la migracion JOSE/JWT.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-11-01
updated: 2026-11-01
work_package: WP-252
tags:
  - architecture
  - haip
  - compatibility
  - jose
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Parser Identity Compatibility Review

## 1. Objetivo

Definir las restricciones de compatibilidad de la migracion
del parser JOSE/JWT conforme a ADR-0006.

Esta revision no habilita el verificador integral HAIP.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 74dc83f.
- PHPUnit: 2880 tests, 19150 assertions.
- PHPStan: 3121 archivos, sin errores.
- EG-588: Draft for Review.

## 3. Evidencia de compatibilidad

Auditoria ejecutada con PHP 8.2.32.

La prueba aislada de class_alias confirmo:

- class_exists con el nombre historico: YES.
- instanceof historico: YES.
- instanceof adaptador: YES.
- Contrato de evidencia Foundation: YES.
- get_class devuelve el nombre del adaptador.
- ReflectionClass devuelve el nombre del adaptador.
- La serializacion utiliza el nombre del adaptador.

La equivalencia de tipos no preserva toda la identidad
publica observable de la implementacion historica.

## 4. Referencias

La busqueda git grep identifico referencias al parser
historico en documentacion, pruebas y la propia clase.

No se identificaron consumidores de produccion directos
adicionales en la busqueda efectuada.

No se descartan referencias dinamicas ni consumidores externos.

## 5. Decision transitoria propuesta

No sustituir la clase historica mediante class_alias.

Conservar temporalmente:

- El parser historico y su identidad publica en Foundation.
- El parser especializado independiente en Adapters.
- El contrato de evidencia no verificada en Foundation.
- La prueba de paridad exacta entre implementaciones.
- Las pruebas positivas y negativas existentes.

La duplicacion es transitoria, no una arquitectura definitiva.

Foundation no debe incorporar dependencias hacia Adapters.

## 6. Condiciones de consolidacion futura

Antes de retirar la implementacion historica se requiere:

1. Inventario de consumidores propios y externos conocidos.
2. Decision expresa sobre compatibilidad de API publica.
3. Estrategia versionada y documentada de migracion.
4. Evaluacion de identidad, reflexion y serializacion.
5. Revision de referencias dinamicas y autoload.
6. Regresion focalizada y global.
7. PHPStan, Composer y SIF Builder sin errores.
8. Aprobacion arquitectonica del cambio.

No se autoriza una ruptura silenciosa de identidad publica.

## 7. Limites de seguridad

- Ningun parser acredita autenticidad por si solo.
- Ningun claim no verificado constituye evidencia confiable.
- No se activa Key Attestation integral.
- No se modifican las comprobaciones ES256 ni X.509.
- EG-588 permanece Draft for Review.

## 8. Estado

Draft for Review.

Esta revision registra una propuesta sujeta a aprobacion.
No modifica ADR-0006 ni declara finalizada la migracion.
