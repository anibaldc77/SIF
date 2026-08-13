---
id: EG-577
title: OpenID Federation Entity Statements and Protocol Architecture
summary: Define OpenID Federation 1.0 Entity Statement, Entity Configuration and Subordinate Statement boundaries and bridge them to the generic credential trust infrastructure completed in WP-250.
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
  - entity-statements
  - trust
depends_on:
  - EG-576
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-577 — OpenID Federation Entity Statements and Protocol Architecture

## Objetivo

Introducir la superficie protocol-specific de OpenID Federation 1.0 sobre la infraestructura genérica de trust de WP-250.

WP-251 no redefine trust anchors, trust chains, accreditation, caching, resilience ni high-assurance enforcement.

## Entity Statements

`OpenIdFederationEntityStatement` representa los claims protocol-relevant de un Entity Statement ya decodificado y verificado por adapters especializados.

La capa Foundation diferencia:

- Entity Configuration;
- Subordinate Statement.

## Entity Configuration

Una Entity Configuration es self-issued: issuer y subject representan la misma Federation Entity.

Puede transportar authority hints, metadata, trust-mark references y federation-entity metadata.

## Subordinate Statement

Un Subordinate Statement representa una declaración emitida por una entidad superior acerca de una entidad subordinada.

Issuer y subject son distintos.

## Protocol Contracts

- `OpenIdFederationEntityConfigurationResolverInterface`;
- `OpenIdFederationSubordinateStatementResolverInterface`;
- `OpenIdFederationEntityStatementVerifierInterface`;
- `OpenIdFederationTrustBridgeInterface`.

## WP-250 Bridge

`OpenIdFederationTrustBridgeInterface` permite mapear entidades OpenID Federation a `CredentialTrustEntityReference`.

La trust-chain genérica y su enforcement permanecen en WP-250.

## Neutralidad

Foundation no implementa JWT/JWS concreto, key retrieval, HTTP, persistence ni un crypto provider específico.

## Roadmap WP-251

1. I1 — Entity Statements and protocol architecture;
2. I2 — Entity Configuration and Subordinate Statement validation;
3. I3 — Federation fetch/list/resolve protocol messages;
4. I4 — Metadata Policy resolution and operators;
5. I5 — Trust Marks and accreditation binding;
6. I6 — Trust Chain collection/verification bridge to WP-250;
7. I7 — Federation runtime freshness, resilience and wallet/OIDC interoperability;
8. I8 — Product Completion and release readiness.
