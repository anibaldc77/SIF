---
id: EG-582
title: Trust Chain Collection and Verification Bridge
summary: Define OpenID Federation trust-chain collection and semantic verification boundaries and bridge protocol-specific chains to the generic credential trust-chain infrastructure completed in WP-250.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - trust-chain
  - credential-trust
depends_on:
  - EG-581
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-582 — Trust Chain Collection and Verification Bridge

## Objetivo

Definir la representación y validación protocol-specific de Trust Chains de OpenID Federation y su bridge hacia la infraestructura genérica de WP-250.

## Federation Trust Chain

`OpenIdFederationTrustChain` conserva leaf Entity Configuration, ordered Subordinate Statements y Trust Anchor Entity Configuration.

## Collection

`OpenIdFederationTrustChainCollectorInterface` abstrae la construcción de una cadena para un subject y uno o más Trust Anchors candidatos.

## Validation

`DefaultOpenIdFederationTrustChainValidator` valida expected leaf, continuidad subject/superior, vigencia temporal, ciclos y terminación en el Trust Anchor.

La validación criptográfica de cada Entity Statement continúa siendo responsabilidad de `OpenIdFederationEntityStatementVerifierInterface`.

## Bridge WP-250

`OpenIdFederationCredentialTrustChainBridgeInterface` mapea la cadena protocol-specific a `CredentialTrustChain`.

Después del bridge, trust policy, anchor policy, caching, resilience y high-assurance enforcement permanecen en WP-250.

## Seguridad

Una federation chain inválida no debe producir una `CredentialTrustChain` confiable. El bridge no implica trust acceptance.

## Neutralidad

Foundation no implementa HTTP, JWT/JWS library, Redis, PDO, storage ni crypto provider concreto.

## Compatibilidad

I6 agrega collection/validation/bridge sin modificar I1-I5 ni WP-250.
