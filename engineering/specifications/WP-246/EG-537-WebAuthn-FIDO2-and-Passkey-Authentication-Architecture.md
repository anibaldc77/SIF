---
id: EG-537
title: WebAuthn FIDO2 and Passkey Authentication Architecture
summary: Define modelos y contratos neutrales para registro y autenticación WebAuthn/FIDO2, credenciales públicas, challenges, authenticator policy y passkeys sobre la plataforma de seguridad SIF.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-246
tags:
  - security
  - webauthn
  - fido2
  - passkeys
depends_on:
  - EG-536
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-537 — WebAuthn, FIDO2 and Passkey Authentication Architecture

## Objetivo

Introducir autenticación WebAuthn/FIDO2 contract-first, compatible con passkeys y autenticadores de plataforma o roaming.

## WebAuthn Credential

`WebAuthnCredential` representa:

- credential id;
- user handle;
- relying party id;
- public key;
- signature counter;
- transports;
- metadata opcional.

## Registration Context

`WebAuthnRegistrationContext` expresa:

- relying party id;
- origin;
- user id;
- user name;
- challenge.

## Authentication Context

`WebAuthnAuthenticationContext` expresa:

- relying party id;
- origin;
- challenge;
- user handle opcional.

El `userHandle` opcional permite soportar credenciales discoverable.

## Verification Result

`WebAuthnVerificationResult` expresa explícitamente:

- validation general;
- challenge validity;
- origin validity;
- relying party validity;
- user verification;
- violations;
- warnings.

## Contratos

- `WebAuthnRegistrationVerifierInterface`;
- `WebAuthnAuthenticationVerifierInterface`;
- `WebAuthnCredentialRepositoryInterface`;
- `WebAuthnChallengeStoreInterface`;
- `WebAuthnAuthenticatorPolicyInterface`.

## Seguridad

Implementaciones productivas deberán:

- validar challenge y single use;
- validar origin;
- validar RP ID;
- verificar signature;
- aplicar user verification policy;
- manejar signature counters según authenticator capabilities;
- validar attestation cuando el perfil la requiera;
- evitar almacenar material privado;
- permitir lifecycle de credenciales y revocación.

## Neutralidad

Foundation no conoce Browser API, authenticator concreto, CTAP transport, hardware vendor, librería CBOR/COSE, storage o framework HTTP.

## Roadmap I1-I8

1. I1 — architecture y capability contracts;
2. I2 — registration ceremony y creation options;
3. I3 — authentication ceremony y assertion validation;
4. I4 — attestation, authenticator metadata y trust;
5. I5 — discoverable credentials y passkey UX policies;
6. I6 — credential lifecycle, recovery y device migration;
7. I7 — risk integration, step-up y operational readiness;
8. I8 — product completion e integration tests.

## Criterios de aceptación

Modelos inmutables, contratos tipados, neutralidad de navegador/crypto/storage, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
