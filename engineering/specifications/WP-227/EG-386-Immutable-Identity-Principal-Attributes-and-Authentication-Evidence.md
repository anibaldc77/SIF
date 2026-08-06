---
id: EG-386
title: Modelo inmutable de identidad, atributos del principal y evidencia de autenticación
summary: Define el modelo de identidad autenticada, atributos tipados y evidencia no sensible para WP-227 I2.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-05
updated: 2026-08-05
work_package: WP-227
tags:
  - security
  - identity
  - principal
  - authentication
  - immutability
depends_on:
  - EG-385
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-386 — Modelo inmutable de identidad, atributos del principal y evidencia de autenticación

## 1. Objetivo

Establecer un modelo inmutable y neutral respecto de transporte, persistencia y proveedor para representar una identidad autenticada, sus atributos y la evidencia mínima que justifica el estado de autenticación.

## 2. Límites

La implementación no autentica credenciales, no consulta repositorios, no conoce BaseModel, no emite cookies, no persiste sesiones y no decide autorizaciones. Esos comportamientos pertenecen a incrementos posteriores.

## 3. Invariantes

- `IdentityId` es opaco, no vacío, libre de caracteres de control y limitado a 255 bytes.
- La identidad se expresa mediante `IdentityInterface`; no se exige una entidad de usuario.
- `PrincipalInterface` permanece mínimo y no incorpora propiedades anulables exclusivas del principal autenticado.
- `AuthenticatedPrincipal` siempre contiene identidad, atributos y evidencia de autenticación.
- Los atributos usan nombres estables en minúsculas y valores escalares o nulos.
- No se permiten atributos duplicados ni valores flotantes no finitos.
- La colección de atributos se ordena canónicamente por nombre.
- La evidencia conserva método, nivel y fecha de autenticación normalizada a UTC.
- La evidencia no contiene secretos, credenciales, tokens ni material reutilizable de autenticación.

## 4. Modelo de nivel de autenticación

`AuthenticationLevel` utiliza un valor entero entre 0 y 100. El rango es deliberadamente neutral: no codifica hoy una taxonomía de proveedor ni un estándar externo, pero permite comparar el nivel alcanzado con el requerido mediante `satisfies()`.

Los adaptadores futuros podrán mapear niveles propios, AAL u otras escalas a este contrato sin modificar el Core.

## 5. Serialización segura

`AuthenticatedPrincipal::toArray()` produce una instantánea determinista y no sensible formada por:

- identificador opaco de identidad;
- atributos ordenados canónicamente;
- método de autenticación;
- nivel;
- instante UTC con precisión de milisegundos.

La instantánea podrá ser utilizada posteriormente por integración de sesión, diagnóstico controlado o transporte interno. No constituye por sí misma una credencial ni autoriza restauración sin validación adicional.

## 6. Compatibilidad y extensibilidad

El diseño permite identidades respaldadas por bases de datos, directorios, servicios remotos o tokens sin que el Core dependa de esos mecanismos. Mantener `PrincipalInterface` mínimo evita obligar al principal anónimo a devolver identidad o atributos nulos y preserva sustitución segura.

## 7. Criterios de aceptación

- Objetos inmutables y compatibles con PHP 8.2.
- Igualdad explícita por valor para `IdentityId`.
- Validación fail-closed de identificadores y atributos.
- Orden canónico y serialización determinista.
- Evidencia temporal normalizada a UTC.
- PHPUnit focalizado y PHPStan sin errores.
- SIF Builder sin diagnósticos.
