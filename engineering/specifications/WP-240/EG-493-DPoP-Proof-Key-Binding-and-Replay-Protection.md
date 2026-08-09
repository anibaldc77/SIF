---
id: EG-493
title: DPoP Proof Key Binding and Replay Protection
summary: Define el modelo DPoP, contexto de verificación, key binding, nonce y replay boundaries sin acoplar Foundation a criptografía o almacenamiento concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-240
tags:
  - security
  - oauth
  - dpop
depends_on:
  - EG-492
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-493 — DPoP Proof, Key Binding and Replay Protection

## Objetivo
Introducir fronteras DPoP para vincular solicitudes OAuth a una clave pública del cliente.

## Modelo
`OAuthDPoPProof` conserva proof serializada, método, URI, issuedAt, jti, thumbprint, access-token hash y nonce opcionales.

`OAuthDPoPVerificationContext` expresa el target esperado y `OAuthDPoPVerificationResult` la identidad criptográfica validada.

## Contratos
- `OAuthProofOfPossessionVerifierInterface`;
- `OAuthDPoPReplayStoreInterface`;
- `OAuthDPoPNonceServiceInterface`.

## Seguridad
La implementación productiva debe validar firma, algoritmo, clave pública, método, URI, freshness, jti, access-token hash y nonce según política.

## Neutralidad
Foundation no conoce librería JWT/JWS, OpenSSL directo, Redis, PDO ni transporte HTTP concreto.

## Fuera de alcance
Sender-constrained access tokens, claims `cnf`, resource server enforcement y HTTP mapping quedan para incrementos posteriores.

## Criterios de aceptación
Proof/context/result tipados, replay y nonce detrás de contratos, neutralidad criptográfica, PHPUnit y PHPStan limpios y Builder sin diagnósticos.
