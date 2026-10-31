---
id: EG-587
title: Wallet Attestation and Wallet Authentication Boundaries
summary: Define HAIP wallet-attestation and wallet-authentication boundaries for OAuth2 endpoints while preserving transport, JWT, cryptographic and ecosystem trust concerns behind specialized adapters and policies.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-252
tags:
  - security
  - verifiable-credentials
  - haip
  - wallet-attestation
  - client-authentication
depends_on:
  - EG-586
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-587 — Wallet Attestation and Wallet Authentication Boundaries

## Objetivo

Definir el boundary HAIP para Wallet Attestation y autenticación de Wallets en endpoints OAuth2 que soportan client authentication.

## Wallet Attestation

`OpenId4VcHaipWalletAttestation` representa una attestation ya parseada y criptográficamente verificada por adapters especializados.

Conserva issuer, subject, audience, timestamps y claims adicionales.

## Privacidad

El subject representa una identidad compartida por las instancias del mismo tipo de wallet implementation y no una identificación única de una instancia individual.

Wallet Attestations no deben reutilizarse entre Issuers.

El audience del modelo identifica al Issuer para el cual la attestation resulta aplicable.

## Client Authentication

`OpenId4VcHaipWalletAuthenticationContext` expresa:

- Wallet Attestation;
- Issuer;
- client_id opcional;
- instante de evaluación;
- uso efectivo en un endpoint que soporta client authentication.

Cuando existe `client_id`, debe coincidir con el subject de la Wallet Attestation.

## Validation

`DefaultOpenId4VcHaipWalletAuthenticationPolicy` verifica vigencia, audience, client-id/subject binding y uso de la attestation para client authentication.

La verificación criptográfica permanece detrás de `OpenId4VcHaipWalletAttestationVerifierInterface`.

## Reutilización

I3 compone HAIP I1/I2, OpenID4VCI, FAPI y credential trust sin duplicarlos.

## Neutralidad

Foundation no implementa JWT/JWS parsing, signature verification, x5c, HTTP, platform attestation APIs, HSM/KMS ni persistence.

## Compatibilidad

I3 agrega Wallet Attestation y authentication policies sin modificar I1/I2 ni subsistemas previos.
