---
id: WP-252-I3-REVIEW
title: WP-252 I3 Wallet Attestation Authentication Review
summary: Revisa Wallet Attestation, issuer audience binding, client-id/subject matching y OAuth2 client-authentication boundaries para HAIP.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
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
  - architecture-review
depends_on:
  - EG-587
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-252 I3 Wallet Attestation Authentication Review

## Alcance revisado

I3 incorpora Wallet Attestation, authentication context/assessment, verifier contract y default HAIP authentication policy.

## Hallazgos

- Wallet Attestation se mantiene separada de su verificación criptográfica.
- Audience queda ligado al Issuer.
- `client_id` y attestation subject deben coincidir cuando el client_id está presente.
- La policy exige uso de la attestation en endpoints que soportan client authentication.
- La arquitectura evita identificadores únicos de wallet instance en el modelo de conformance.
- HTTP, JWT/JWS, platform APIs y storage permanecen fuera de Foundation.

## Compatibilidad

I3 no modifica I1/I2 ni subsistemas canónicos existentes.

## Decisión

Apto para I4 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
