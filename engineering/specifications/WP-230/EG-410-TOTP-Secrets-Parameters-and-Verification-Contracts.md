---
id: EG-410
title: Secretos, parámetros y contratos de verificación TOTP
summary: Define el tratamiento seguro de semillas y códigos TOTP, los parámetros criptográficos interoperables y los contratos neutrales de generación y verificación.
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
  - totp
  - secrets
  - contracts
depends_on:
  - EG-409
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-410 — TOTP Secrets, Parameters and Verification Contracts

## Objetivo

Definir objetos y contratos seguros para TOTP antes de incorporar algoritmos concretos, enrolamiento, persistencia o integración HTTP.

## Decisiones normativas

- La semilla TOTP se representa como Base32 sin padding, normalizada a mayúsculas y con longitud acotada.
- Semillas y códigos son objetos sensibles: no exponen getters directos, se redactan, no pueden clonarse ni serializarse.
- El acceso al valor en claro sólo se permite mediante un callback acotado.
- Los códigos aceptan entre seis y ocho dígitos decimales y preservan ceros iniciales.
- Los algoritmos admitidos son SHA-1, SHA-256 y SHA-512 para interoperabilidad RFC 6238.
- Dígitos, período y ventanas toleradas son parámetros explícitos e inmutables.
- La política por defecto representa RFC 6238: SHA-1, seis dígitos y período de treinta segundos.
- El resultado de verificación expone únicamente éxito y contador temporal coincidente; nunca código ni semilla.
- Los contratos de generación y verificación no dependen de BaseModel, PDO, Redis, aplicaciones autenticadoras ni proveedores externos.
- I2 no implementa todavía Base32, HMAC, generación aleatoria ni validación concreta; esos adaptadores corresponden a I3.

## Riesgos controlados

- Exposición accidental de semilla o código mediante logs, dumps o serialización.
- Pérdida de ceros iniciales por tratamiento numérico del código.
- Ambigüedad de algoritmo, cantidad de dígitos, período o tolerancia temporal.
- Acoplamiento prematuro a una aplicación autenticadora o almacenamiento específico.

## Criterios de aceptación I2

- Semilla Base32 sensible y normalizada.
- Código decimal sensible con longitud acotada.
- Algoritmos y parámetros interoperables e inmutables.
- Resultado de verificación sin secretos.
- Contratos neutrales de generación y verificación.
- PHPUnit, PHPStan y Builder sin diagnósticos.
