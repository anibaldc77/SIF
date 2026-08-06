---
id: EG-393
title: Arquitectura de proveedores de identidad y autenticación por contraseña
summary: Define límites, contratos e invariantes para resolver identidades y habilitar autenticación por contraseña sin acoplar Security a persistencia ni protocolos externos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - security
  - identity-provider
  - password-authentication
  - architecture
depends_on:
  - EG-392
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-393 — Identity Provider and Password Authentication Architecture

## Objetivo

Definir la frontera neutral entre el subsistema de autenticación y las fuentes capaces de resolver identidades, preparando autenticación por contraseña sin introducir dependencias sobre BaseModel, PDO, LDAP, OpenID Connect, Keycloak ni una tabla de usuarios concreta.

## Decisiones normativas

- Un proveedor de identidad resuelve una clave de búsqueda y devuelve un resultado explícito `found` o `notFound`.
- La semántica y normalización específica de usuario, correo, matrícula o identificador externo pertenece al adaptador proveedor.
- El Core únicamente elimina espacios exteriores, limita longitud y rechaza caracteres de control.
- Un registro de proveedor contiene una identidad neutral y un estado de cuenta; nunca contiene una contraseña en texto plano.
- Los estados mínimos son `active`, `disabled` y `locked`.
- `notFound`, `disabled` y `locked` deberán poder convertirse en fallos públicos indistinguibles cuando una política de seguridad lo requiera.
- La verificación de contraseña, hashing, rehashing y mitigación temporal se incorporarán en implementaciones posteriores del WP.
- Los proveedores concretos dependerán de estos contratos; Security no dependerá de Persistence, BaseModel ni protocolos externos.

## Impacto futuro

La frontera permite adaptadores para repositorios propios, BaseModel, LDAP o servicios remotos. OpenID Connect y Keycloak deberán integrarse mediante autenticadores federados y no mediante el contrato de contraseña local.

## Criterios de aceptación I1

- Identificadores de proveedor estables e inmutables.
- Claves de búsqueda acotadas y libres de caracteres de control.
- Resultados explícitos sin contratos anulables.
- Estados de cuenta mínimos y transport-neutral.
- PHPUnit, PHPStan y Builder sin diagnósticos.
