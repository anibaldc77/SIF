---
id: EG-466
title: Discovery SCIM ServiceProviderConfig, ResourceTypes y Schemas
summary: Define modelos y contratos neutrales para publicar capacidades del servidor SCIM, tipos de recursos y definiciones de schemas.
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
  - discovery
  - schemas
  - resource-types
depends_on:
  - EG-465
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-466 — SCIM Discovery

## Objetivo

Representar las superficies de discovery definidas por SCIM 2.0 sin acoplarlas a controladores HTTP.

## ServiceProviderConfig

`ScimServiceProviderConfig` declara soporte para:

- PATCH;
- Bulk;
- Filter;
- ChangePassword;
- Sort;
- ETag.

`ScimFeatureSupport` permite declarar soporte y límites opcionales.

## ResourceTypes

`ScimResourceType` describe:

- id;
- name;
- endpoint;
- schema principal;
- schema extensions.

## Schemas

`ScimSchemaDefinition` y `ScimSchemaAttribute` modelan:

- nombre;
- tipo;
- multiValued;
- required;
- mutability;
- returned;
- uniqueness;
- canonicalValues;
- subAttributes.

## Discovery provider

`ScimDiscoveryProviderInterface` entrega:

- ServiceProviderConfig;
- ResourceTypes;
- Schemas.

La exposición HTTP pertenece a una integración posterior.

## Fuera de alcance de I2

- controllers HTTP;
- filtering;
- pagination;
- PATCH execution;
- Bulk execution;
- persistence;
- authorization.

## Criterios de aceptación

- capacidades del servidor tipadas;
- ResourceTypes;
- Schemas;
- atributos complejos;
- contrato de discovery neutral;
- PHPUnit focalizado;
- PHPStan limpio;
- Builder sin diagnósticos.
