---
id: EG-409
title: Arquitectura de autenticación multifactor
summary: Define límites, vocabulario e invariantes para desafíos MFA, factores adicionales y elevación de autenticación sin acoplar Security a TOTP, persistencia ni proveedores externos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - multi-factor-authentication
  - step-up-authentication
  - architecture
depends_on:
  - EG-408
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-409 — Multi-Factor Authentication Security Architecture

## Objetivo

Definir una arquitectura neutral para factores adicionales, desafíos transitorios y elevación del nivel de autenticación, preservando expiración, separación de propósitos, no exposición de identidad y extensibilidad hacia TOTP, códigos de recuperación y proveedores futuros.

## Decisiones normativas

- Cada desafío posee identificador opaco, identidad objetivo, tipo de factor, propósito, nivel requerido, estado, emisión y expiración.
- Los propósitos `authentication_continuation` y `step_up` no son intercambiables.
- Los tipos de factor son valores extensibles y no un enum cerrado, para evitar cambios incompatibles al incorporar WebAuthn, hardware tokens o proveedores externos.
- El desafío no contiene secretos, códigos, semillas TOTP ni material criptográfico reutilizable.
- Los snapshots exponen únicamente una huella SHA-256 de la identidad.
- La expiración se evalúa en UTC y es inclusiva: `now >= expiresAt` implica expiración.
- `AuthenticationLevel` de WP-227 permanece como lenguaje común para expresar elevación y suficiencia.
- El almacenamiento se gobierna mediante contrato y no depende de BaseModel, PDO, Redis, TOTP ni Keycloak.
- La transición atómica, consumo, replay protection y verificación concreta se incorporarán en implementaciones posteriores.

## Límites de confianza

Las credenciales primarias ya verificadas no deben volver a circular dentro del desafío MFA. Cada adaptador de factor será responsable de su material secreto, mientras el Core coordina estados, propósito, vigencia y elevación del nivel de autenticación.

## Criterios de aceptación I1

- Tipos de factor extensibles y estables.
- Propósitos explícitos y no intercambiables.
- Identificadores transport-safe.
- Nivel requerido basado en `AuthenticationLevel`.
- Expiración determinista en UTC.
- Snapshot sin identidad directa, códigos ni secretos.
- Contrato de almacenamiento neutral.
- PHPUnit, PHPStan y Builder sin diagnósticos.
