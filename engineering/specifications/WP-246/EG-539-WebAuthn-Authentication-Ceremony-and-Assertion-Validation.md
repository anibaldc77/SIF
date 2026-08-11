---
id: EG-539
title: WebAuthn Authentication Ceremony and Assertion Validation
summary: Define request options, assertion payload, validation y signature counter policy para autenticación WebAuthn sin acoplar Foundation a Browser APIs, COSE o criptografía concreta.
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
  - authentication
depends_on:
  - EG-538
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-539 — WebAuthn Authentication Ceremony and Assertion Validation

## Objetivo

Modelar la ceremonia de autenticación y validación de assertions WebAuthn manteniendo Browser API y criptografía fuera de Foundation.

## Request Options

`WebAuthnRequestOptions` expresa challenge, RP ID, timeout, allow credentials, user verification y hints.

## Assertion

`WebAuthnAssertion` representa credential id, client data JSON, authenticator data, signature y user handle opcional.

## Validation Result

`WebAuthnAssertionValidationResult` separa challenge, origin, RP, signature, user verification y signature counter.

## Contratos

- `WebAuthnRequestOptionsFactoryInterface`;
- `WebAuthnRequestOptionsSerializerInterface`;
- `WebAuthnAssertionValidatorInterface`;
- `WebAuthnSignatureCounterPolicyInterface`.

## Seguridad

Las implementaciones productivas deberán validar challenge single-use, origin, RP ID, signature, user verification y política de counter según capacidades del authenticator.

## Neutralidad

Foundation no conoce `navigator.credentials.get`, COSE implementation, crypto library, storage o framework HTTP.

## Criterios de aceptación

Request/assertion/result tipados, contratos separados, neutralidad, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
