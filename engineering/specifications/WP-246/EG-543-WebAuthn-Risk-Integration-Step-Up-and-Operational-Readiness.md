---
id: EG-543
title: WebAuthn Risk Integration Step Up and Operational Readiness
summary: Define risk integration, step-up decisions y operational readiness para WebAuthn/passkeys sin acoplar Foundation a motores de riesgo, SIEM, device intelligence o proveedores externos.
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
  - passkeys
  - risk
  - step-up
  - readiness
depends_on:
  - EG-542
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-543 — WebAuthn Risk Integration, Step-Up and Operational Readiness

## Objetivo

Integrar WebAuthn con evaluación de riesgo y step-up, y formalizar readiness operacional sin introducir dependencias de proveedores.

## Modelos

- `WebAuthnRiskContext`;
- `WebAuthnRiskAssessment`;
- `WebAuthnStepUpRequirement`;
- `WebAuthnOperationalReadinessContext`;
- `WebAuthnOperationalReadinessReport`.

## Contratos

- `WebAuthnRiskEvaluatorInterface`;
- `WebAuthnStepUpPolicyInterface`;
- `WebAuthnOperationalReadinessEvaluatorInterface`.

## Seguridad

Las implementaciones productivas deberán impedir que decisiones de UX degraden controles de riesgo, exigir fresh user verification cuando corresponda, integrar step-up sin romper resistencia al phishing y mantener auditabilidad.

## Neutralidad

Foundation no conoce SIEM, device intelligence vendors, telemetry backends, Redis, base de datos o HTTP clients concretos.

## Criterios de aceptación

Risk/context/step-up/readiness tipados, contracts separados, neutralidad, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
