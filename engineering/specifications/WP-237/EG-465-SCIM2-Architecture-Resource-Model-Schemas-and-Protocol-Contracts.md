---
id: EG-465
title: Arquitectura SCIM 2.0, modelo de recursos, schemas y contratos de protocolo
summary: Define los value objects y contratos neutrales para recursos User, Group, Meta y operaciones básicas de provisión SCIM 2.0.
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
  - provisioning
  - identity
  - architecture
depends_on:
  - EG-464
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-465 — SCIM 2.0 Architecture, Resource Model, Schemas and Protocol Contracts

## Objetivo
Iniciar WP-237 con una foundation SCIM 2.0 desacoplada de transporte, persistencia y proveedor.

## Alcance
Se definen `ScimResourceId`, `ScimSchemaUri`, `ScimMeta`, `ScimUser`, `ScimGroup`, `ScimGroupMember`, `ScimError` y contratos de provisión de User/Group.

## Neutralidad
No existe transporte HTTP, persistencia concreta ni dependencia de Keycloak, Entra ID, Okta u OneLogin.

## Fuera de alcance de I1
GET/list, filtering, pagination, PATCH, Bulk, discovery endpoints, ETags y authorization.

## Criterios de aceptación
IDs y schema URIs tipados; Meta; User; Group; contracts de provisión; PHPUnit focalizado; PHPStan limpio; Builder sin diagnósticos.
