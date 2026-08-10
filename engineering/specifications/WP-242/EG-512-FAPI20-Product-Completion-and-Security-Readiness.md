---
id: EG-512
title: FAPI 2.0 Product Completion and Security Readiness
summary: Consolida las capacidades FAPI 2.0 implementadas en WP-242 y define product profile y readiness sin confundir readiness interno con certificación externa.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - product-completion
depends_on:
  - EG-511
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-512 — FAPI 2.0 Product Completion and Security Readiness

## Objetivo

Cerrar WP-242 consolidando la superficie FAPI 2.0 implementada en I1-I7 y agregando una frontera explícita de product readiness.

## Capacidades consolidadas

`FapiProductCapabilities` representa:

- Client Profile;
- Authorization Server Profile;
- PAR/PKCE/Issuer/Metadata conformance;
- sender-constrained tokens;
- Resource Server enforcement;
- Message Signing;
- deployment conformance.

## Product Profile

`FapiProductProfile` expresa:

- nombre del perfil;
- capabilities;
- strict conformance requerido;
- deployment readiness requerido;
- message signing requerido.

## Readiness

`FapiProductReadinessReport` representa:

- readiness global;
- blocking issues;
- warnings.

`FapiProductReadinessEvaluatorInterface` define la frontera de evaluación.

## Distinción importante

Readiness interno de SIF no equivale a certificación formal externa.

La certificación de un ecosistema u organismo externo pertenece a adapters/tooling específicos.

## Cobertura acumulada WP-242

1. I1 — architecture y conformance contracts;
2. I2 — Client y Authorization Server Security Profile;
3. I3 — PAR, PKCE, Issuer y Metadata Conformance;
4. I4 — DPoP/mTLS sender-constrained token policy;
5. I5 — Resource Server Enforcement;
6. I6 — JAR/JARM/Signed Introspection boundaries;
7. I7 — Ecosystem Profile, Conformance y Deployment Policy;
8. I8 — Product Completion y Security Readiness.

## Neutralidad

Foundation no prescribe HTTP framework, storage, TLS implementation, JWT/JWS library, certification tooling ni proveedor IAM.

## Criterios de aceptación

WP-242 se considera completo cuando PHPUnit, PHPStan, Composer y SIF Builder finalizan sin errores ni diagnósticos y `git diff --check` queda limpio.
