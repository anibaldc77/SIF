---
id: EG-542
title: WebAuthn Credential Lifecycle Recovery and Device Migration
summary: Define lifecycle de credenciales, recovery y device migration para WebAuthn/passkeys sin acoplar Foundation a cloud sync, vendors, browser accounts o storage concreto.
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
  - lifecycle
  - recovery
  - migration
depends_on:
  - EG-541
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-542 — WebAuthn Credential Lifecycle, Recovery and Device Migration

## Objetivo

Modelar lifecycle, recuperación y migración de credenciales WebAuthn sin asumir mecanismos de sincronización específicos.

## Lifecycle

`WebAuthnCredentialLifecycleStatus` soporta active, suspended, revoked y retired.

`WebAuthnCredentialLifecycleEvent` registra transición, fecha y razón.

## Recovery

`WebAuthnRecoveryContext` expresa credenciales disponibles, factores de recuperación verificados y nivel de riesgo.

`WebAuthnRecoveryDecision` expresa si recovery está permitido, credenciales a retirar y acciones requeridas.

## Device Migration

`WebAuthnDeviceMigrationContext` expresa credenciales fuente, transports destino y disponibilidad de credencial sincronizada.

## Contratos

- `WebAuthnCredentialLifecyclePolicyInterface`;
- `WebAuthnCredentialLifecycleRepositoryInterface`;
- `WebAuthnRecoveryPolicyInterface`;
- `WebAuthnDeviceMigrationPolicyInterface`.

## Seguridad

Recovery no debe degradar autenticación resistente al phishing. Las implementaciones deberán aplicar step-up cuando corresponda, retirar credenciales comprometidas y auditar cambios de lifecycle.

## Neutralidad

Foundation no conoce iCloud Keychain, Google Password Manager, cuentas Microsoft, vendor sync, navegador, Redis o base de datos concreta.

## Criterios de aceptación

Lifecycle/recovery/migration tipados, contracts separados, neutralidad de vendor/cloud/storage, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
