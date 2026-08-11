---
id: EG-538
title: WebAuthn Registration Ceremony and Creation Options
summary: Define creation options, relying party, user entity, authenticator selection y exclusión de credenciales para la ceremonia de registro WebAuthn sin acoplar Foundation a Browser APIs o transporte.
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
  - registration
depends_on:
  - EG-537
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-538 — WebAuthn Registration Ceremony and Creation Options

## Objetivo

Modelar la ceremonia de creación de credenciales WebAuthn de forma independiente del navegador y del transporte.

## Modelos

Se incorporan:

- `WebAuthnRelyingParty`;
- `WebAuthnUserEntity`;
- `WebAuthnAuthenticatorSelection`;
- `WebAuthnCredentialDescriptor`;
- `WebAuthnCreationOptions`.

## Contratos

- `WebAuthnCreationOptionsFactoryInterface`;
- `WebAuthnCreationOptionsSerializerInterface`;
- `WebAuthnRegistrationPolicyInterface`.

## Seguridad

Las implementaciones productivas deberán generar challenges impredecibles, impedir su reutilización, aplicar algoritmos permitidos, excluir credenciales existentes cuando corresponda y validar RP/origin al procesar el retorno.

## Neutralidad

Foundation no conoce `navigator.credentials.create`, serialización concreta, HTTP framework, session store ni browser transport.

## Criterios de aceptación

Entities/options tipados, factory/serializer/policy contracts, neutralidad de navegador, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
