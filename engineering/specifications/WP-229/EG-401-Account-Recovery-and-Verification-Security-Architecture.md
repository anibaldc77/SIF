---
id: EG-401
title: Arquitectura de recuperación de cuentas y verificación de identidad
summary: Define límites, vocabulario e invariantes para desafíos transitorios de recuperación y verificación sin acoplar Security a persistencia ni canales de entrega.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - identity-verification
  - architecture
depends_on:
  - EG-400
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-401 — Account Recovery and Verification Security Architecture

## Objetivo

Definir la arquitectura neutral para desafíos transitorios usados en recuperación de contraseña y verificación de identidad, preservando consumo único, expiración, revocación, separación de propósitos y protección contra enumeración.

## Decisiones normativas

- Cada desafío posee un identificador opaco, propósito explícito, sujeto, instante de emisión y expiración.
- Los propósitos `password_reset` e `identity_verification` no son intercambiables.
- Los tokens secretos y sus digest se incorporarán en I2; nunca formarán parte de snapshots, logs o excepciones.
- La clave del sujeto pertenece al adaptador y sólo podrá exponerse mediante una huella no reversible para diagnóstico.
- La expiración se evalúa en UTC y el límite es inclusivo: un desafío expira cuando `now >= expiresAt`.
- El almacenamiento será gobernado por contratos y no dependerá de BaseModel, PDO, Redis ni servicios externos.
- La emisión pública deberá responder de manera indistinguible cuando la identidad no exista.
- Correo, SMS y otros canales serán adaptadores de entrega y permanecerán fuera del Core.
- Consumo único, revocación e invalidación de desafíos previos se incorporarán en implementaciones posteriores.

## Límites de confianza

El token en claro sólo puede existir entre su generación y entrega. El Core persistirá exclusivamente material derivado. Los consumidores deberán fallar cerrados frente a propósito incorrecto, expiración, revocación, reutilización o error técnico.

## Criterios de aceptación I1

- Propósitos explícitos e inmutables.
- Identificadores estables y transport-safe.
- Sujetos acotados y diagnosticables sin exposición directa.
- Expiración determinista en UTC.
- Snapshot sin secretos ni identificador directo del sujeto.
- Contrato de almacenamiento neutral.
- PHPUnit, PHPStan y Builder sin diagnósticos.
