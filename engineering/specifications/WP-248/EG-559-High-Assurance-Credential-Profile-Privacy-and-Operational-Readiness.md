---
id: EG-559
title: High Assurance Credential Profile Privacy and Operational Readiness
summary: Define a high-assurance policy profile, privacy minimization boundary and operational readiness gate for SD-JWT VC and ISO mdoc credential formats.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-248
tags:
  - security
  - verifiable-credentials
  - sd-jwt-vc
  - mdoc
  - high-assurance
  - privacy
  - readiness
depends_on:
  - EG-558
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-559 — High-Assurance Credential Profile, Privacy and Operational Readiness

## Objetivo

Consolidar SD-JWT VC e ISO mdoc bajo un profile de alta garantía, controles de privacidad y readiness operacional previo al cierre de WP-248.

## High-Assurance Profile

`HighAssuranceCredentialProfile` expresa:

- allowed formats;
- required controls;
- required claims;
- holder/device binding requirement;
- status validation requirement.

## Privacy

`HighAssurancePrivacyContext` representa claims solicitados, permitidos y sensibles.

`HighAssurancePrivacyDecision` representa disclosure permitido, claims bloqueados y warnings.

## Operational Readiness

`HighAssuranceOperationalReadinessContext` expresa capabilities y controles activos.

`HighAssuranceOperationalReadinessReport` expresa readiness, blocking issues y warnings.

## Contratos

- `HighAssuranceCredentialProfilePolicyInterface`;
- `HighAssurancePrivacyPolicyInterface`;
- `HighAssuranceOperationalReadinessEvaluatorInterface`.

## Seguridad

Un deployment de alta garantía debe impedir degradación silenciosa de issuer trust, status, holder/device binding, selective disclosure, MSO validation o device authentication.

## Privacidad

El formato no autoriza disclosure por sí mismo. La policy deberá minimizar claims revelados y evitar sobreexposición de atributos sensibles.

## Neutralidad

Foundation no conoce trust store, HSM, telemetry backend, policy engine, HTTP client, persistence ni crypto provider concreto.

## Criterios de aceptación

Profile/privacy/readiness tipados, contracts separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
