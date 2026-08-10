---
id: EG-531
title: Proof of Possession c nonce and Replay Protection
summary: Define proof-of-possession, c_nonce lifecycle y replay protection para OpenID4VCI sin acoplar Foundation a JWT, COSE, storage o criptografía concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-245
tags:
  - security
  - verifiable-credentials
  - openid4vci
  - proof-of-possession
  - replay
depends_on:
  - EG-530
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-531 — Proof-of-Possession, c_nonce and Replay Protection

## Objetivo

Formalizar la validación de proof-of-possession y lifecycle de `c_nonce` para emisión segura de credenciales.

## Proof

`CredentialIssuanceProof` representa:

- proof type;
- serialized proof;
- attributes opcionales.

El proof type permanece extensible.

## c_nonce

`CredentialNonce` representa:

- valor;
- issued at;
- expires at opcional.

`CredentialNonceServiceInterface` permite emitir y rotar nonces.

## Validation Context

`CredentialProofValidationContext` vincula:

- credential issuer;
- client id;
- subject id;
- nonce;
- instante de evaluación.

## Validation Result

`CredentialProofValidationResult` expresa:

- validation base;
- nonce validity;
- holder binding;
- replay safety;
- violations;
- warnings.

## Replay Protection

`CredentialProofReplayStoreInterface` mantiene replay evidence usando fingerprint + nonce.

`CredentialProofFingerprintResolverInterface` desacopla la derivación de fingerprint del formato de proof.

## Seguridad

Implementaciones productivas deberán:

- validar proof cryptographically;
- validar audience/issuer cuando el formato lo exija;
- validar `c_nonce`;
- imponer expiración;
- rotar nonce cuando corresponda;
- detectar replay;
- vincular holder/subject/client;
- evitar registrar proof completo.

## Neutralidad

Foundation no conoce JWT, COSE, JWK, HSM, Redis, base de datos ni librerías criptográficas.

## Criterios de aceptación

Proof/nonce/context/result tipados, replay contracts, crypto neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
