---
id: EG-579
title: Federation Fetch List and Resolve Protocol Messages
summary: Define transport-neutral request and response models for OpenID Federation fetch, subordinate listing and resolve operations.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - fetch
  - list
  - resolve
depends_on:
  - EG-578
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-579 — Federation Fetch, List and Resolve Protocol Messages

## Objetivo

Definir mensajes tipados y transport-neutral para los endpoints fetch, subordinate listing y resolve de OpenID Federation.

## Fetch

`OpenIdFederationFetchRequest` representa issuer y subject.

`OpenIdFederationFetchResponse` contiene el Subordinate Statement recuperado.

La serialización HTTP GET/POST y `application/entity-statement+jwt` pertenecen a adapters de transporte.

## Subordinate Listing

`OpenIdFederationListRequest` representa el superior y filtros opcionales.

`OpenIdFederationListResponse` contiene los Entity Identifiers conocidos de Immediate Subordinates.

## Resolve

`OpenIdFederationResolveRequest` representa:

- subject;
- uno o más trust anchors;
- cero o más entity types.

`OpenIdFederationResolveResponse` representa:

- subject;
- trust anchor usado;
- resolved metadata;
- referencias a statements de la trust chain;
- Trust Marks verificados.

## Contratos

- `OpenIdFederationFetchProtocolInterface`;
- `OpenIdFederationListProtocolInterface`;
- `OpenIdFederationResolveProtocolInterface`.

## Separación de responsabilidades

Los mensajes no realizan HTTP, JWT/JWS, crypto, metadata policy application ni trust-chain enforcement.

Esas responsabilidades permanecen en adapters y en los subsistemas de I1/I2 y WP-250.

## Neutralidad

Foundation no conoce HTTP client, routing framework, Redis, PDO ni crypto provider concreto.

## Compatibilidad

I3 agrega protocol messages sin modificar I1, I2 ni WP-250.
