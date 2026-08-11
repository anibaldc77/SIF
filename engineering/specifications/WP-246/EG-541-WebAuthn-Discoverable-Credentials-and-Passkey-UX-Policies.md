---
id: EG-541
title: WebAuthn Discoverable Credentials and Passkey UX Policies
summary: Define perfiles de credenciales discoverable, mediation y políticas de selección/UX para passkeys sin acoplar Foundation a APIs, UI o comportamiento específico de navegador.
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
  - passkeys
  - discoverable-credentials
  - ux
depends_on:
  - EG-540
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-541 — WebAuthn Discoverable Credentials and Passkey UX Policies

## Objetivo

Modelar requisitos de discoverable credentials y decisiones de UX de passkeys sin incluir UI o Browser API en Foundation.

## Discoverable Credential Profile

`WebAuthnDiscoverableCredentialProfile` expresa discoverable credential requerida, user verification preference, transports preferidos y hints.

## Passkey UX Context

`WebAuthnPasskeyUxContext` representa disponibilidad de conditional mediation, usernameless flow permitido, credential ids y transports disponibles.

## Passkey UX Decision

`WebAuthnPasskeyUxDecision` expresa conditional mediation, usernameless flow, credenciales preferidas y warnings.

## Contratos

- `WebAuthnDiscoverableCredentialPolicyInterface`;
- `WebAuthnPasskeyUxPolicyInterface`;
- `WebAuthnCredentialSelectionPolicyInterface`.

## Seguridad

Las implementaciones productivas deberán evitar account enumeration, no revelar credenciales de otros usuarios, respetar user verification y no degradar controles por conveniencia de UX.

## Neutralidad

Foundation no conoce DOM, HTML, CSS, autofill, `navigator.credentials`, UI toolkit ni browser concreto.

## Criterios de aceptación

Profile/context/decision tipados, policy contracts, neutralidad de UI/browser, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
