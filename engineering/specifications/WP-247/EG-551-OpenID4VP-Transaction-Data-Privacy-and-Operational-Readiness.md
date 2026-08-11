---
id: EG-551
title: OpenID4VP Transaction Data Privacy and Operational Readiness
summary: Define transaction binding, privacy policy boundaries and operational readiness for OpenID4VP before product completion.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-247
tags:
  - security
  - verifiable-credentials
  - openid4vp
  - transaction-data
  - privacy
  - readiness
depends_on:
  - EG-550
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-551 — OpenID4VP Transaction Data, Privacy and Operational Readiness

## Objetivo

Cerrar los controles transaccionales y de privacidad de OpenID4VP y formalizar readiness operacional previo a Product Completion.

## Transaction Data

`OpenId4VpTransactionData` representa transaction id, type y atributos asociados.

## Privacy

`OpenId4VpPrivacyContext` representa claims solicitados, permitidos y sensibles.

`OpenId4VpPrivacyDecision` expresa disclosure permitido, claims bloqueados y warnings.

## Operational Readiness

`OpenId4VpOperationalReadinessContext` representa capabilities y controles activos.

`OpenId4VpOperationalReadinessReport` expresa readiness, blocking issues y warnings.

## Contratos

- `OpenId4VpTransactionBindingPolicyInterface`;
- `OpenId4VpPrivacyPolicyInterface`;
- `OpenId4VpOperationalReadinessEvaluatorInterface`.

## Seguridad y privacidad

La implementación productiva deberá vincular transaction data al nonce/verifier/response context, aplicar minimización de disclosure, impedir sobreexposición de claims y exigir controles operacionales antes de habilitar el producto.

## Neutralidad

Foundation no conoce storage, telemetry, HTTP, wallet vendor, policy engine o framework concreto.

## Criterios de aceptación

Transaction/privacy/readiness tipados, contracts separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
