---
id: EG-472
title: Cierre de producto SCIM 2.0 Identity Provisioning
summary: Consolida discovery, provisioning, query, PATCH, Bulk, versioning, concurrency y lifecycle como foundation empresarial neutral.
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
  - product-completion
depends_on:
  - EG-471
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-472 — SCIM 2.0 Identity Provisioning Product Completion

## Objetivo

Cerrar WP-237 como foundation SCIM 2.0 modular, provider-neutral y preparada para adapters empresariales.

## Capacidades consolidadas

- arquitectura de recursos SCIM;
- ServiceProviderConfig;
- ResourceTypes;
- Schemas;
- Users/Groups provisioning contracts;
- filtering/query model;
- sorting;
- pagination;
- PATCH;
- Bulk;
- bulkId resolution;
- failOnErrors;
- versionado opaco;
- ETag/preconditions;
- lifecycle;
- deactivation;
- membership consistency boundary;
- audit/event boundary.

## Orden recomendado de mutación

1. validar recurso/request;
2. evaluar precondiciones;
3. ejecutar mutación mediante contract;
4. mantener consistencia de membresías;
5. producir nueva versión;
6. publicar evento/auditoría;
7. traducir resultado a HTTP fuera de Foundation.

## Neutralidad

Foundation no contiene:

- controllers HTTP concretos;
- acceso SQL;
- Redis concreto;
- LDAP concreto;
- HTTP clients;
- SDKs de Keycloak;
- SDKs de Microsoft Entra;
- SDKs de Okta;
- lógica específica de proveedor.

## Integraciones previstas

Adapters externos pueden implementar SCIM contra:

- Microsoft Entra ID;
- Okta;
- Keycloak;
- Google Workspace;
- Ping Identity;
- directorios internos;
- aplicaciones SIF.

## Seguridad

- versión de recurso opaca;
- precondiciones explícitas;
- PATCH/Bulk no persisten directamente;
- lifecycle no ejecuta side effects;
- deactivation y deletion separadas;
- membership consistency es explícita;
- eventos no conocen storage.

## Riesgos residuales

Los adapters productivos deben implementar:

- parser completo RFC 7644 de filters;
- valuePath completo para PATCH;
- atomicidad de precondición + escritura;
- límites de Bulk;
- autorización de atributos;
- rate limiting;
- protección de credenciales;
- logging seguro;
- adapters HTTP.

## Criterios de aceptación

- componentes I1-I7 interoperan;
- schemas PATCH/Bulk deterministas;
- versioning opaco;
- lifecycle ordenado;
- mutation execution detrás de contracts;
- HTTP/storage/provider-neutral;
- suite completa sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
