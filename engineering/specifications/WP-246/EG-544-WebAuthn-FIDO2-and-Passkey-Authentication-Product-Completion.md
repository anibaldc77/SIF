---
id: EG-544
title: WebAuthn FIDO2 and Passkey Authentication Product Completion
summary: Consolida registro, autenticación, attestation trust, discoverable credentials, passkey UX, lifecycle, recovery, migration, risk y readiness como superficie coherente de producto.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-246
tags:
  - security
  - webauthn
  - fido2
  - passkeys
  - product-completion
depends_on:
  - EG-543
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-544 — WebAuthn, FIDO2 and Passkey Authentication Product Completion

## Objetivo

Cerrar WP-246 consolidando I1-I7 como superficie coherente de autenticación WebAuthn/FIDO2 y passkeys.

## Capacidades consolidadas

`WebAuthnProductCapabilities` representa registration, authentication, attestation trust, discoverable credentials, passkey UX policies, credential lifecycle, recovery, device migration, risk integration y operational readiness.

## Product Profile

`WebAuthnProductProfile` expresa user verification, challenge replay protection, origin validation, RP ID validation y operational readiness como requisitos de seguridad explícitos.

## Readiness

`WebAuthnProductReadinessReport` representa readiness global, blocking issues y warnings.

`WebAuthnProductReadinessEvaluatorInterface` define la frontera de evaluación.

## Cobertura acumulada WP-246

1. I1 — architecture y capability contracts;
2. I2 — registration ceremony y creation options;
3. I3 — authentication ceremony y assertion validation;
4. I4 — attestation, authenticator metadata y trust;
5. I5 — discoverable credentials y passkey UX policies;
6. I6 — credential lifecycle, recovery y device migration;
7. I7 — risk integration, step-up y operational readiness;
8. I8 — Product Completion.

## Neutralidad

Foundation no prescribe Browser API, CTAP transport, authenticator vendor, FIDO MDS, cloud sync, UI, SIEM, storage ni crypto library concreta.

## Criterios de aceptación

WP-246 se considera completo cuando PHPUnit, PHPStan, Composer y SIF Builder finalizan sin errores ni diagnósticos y `git diff --check` queda limpio.
