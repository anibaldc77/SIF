---
id: WP-246-I1-REVIEW
title: WP-246 I1 Architecture Review
summary: Revisa la arquitectura inicial de WebAuthn FIDO2 y Passkey Authentication sobre la plataforma de seguridad SIF.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
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
  - architecture-review
depends_on:
  - EG-537
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-246 I1 Architecture Review

## Alcance revisado

Se incorporan WebAuthn credential, registration/authentication contexts, verification result y contratos iniciales de verifier, repository, challenge store y authenticator policy.

## Hallazgos

- WebAuthn complementa password/MFA sin duplicarlos.
- Passkeys se modelan como public-key credentials y no como un tipo de password.
- Registration y authentication ceremonies quedan separadas.
- Challenge, RP ID y origin son inputs de seguridad explícitos.
- Browser APIs, CTAP, criptografía y storage permanecen fuera de Foundation.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
