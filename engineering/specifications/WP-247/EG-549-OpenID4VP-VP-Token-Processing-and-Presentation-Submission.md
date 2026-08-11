---
id: EG-549
title: OpenID4VP VP Token Processing and Presentation Submission
summary: Define VP Token processing, resolved presentations, presentation submission and binding policies without duplicating credential verification.
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
  - vp-token
  - presentation-submission
depends_on:
  - EG-548
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-549 — OpenID4VP VP Token Processing and Presentation Submission

## Objetivo

Procesar VP Tokens y presentation submission manteniendo la verificación criptográfica de credenciales en la capa WP-244.

## VP Token Envelope

`OpenId4VpVpTokenEnvelope` representa uno o más tokens y metadata contextual.

## Resolved Presentation

`OpenId4VpResolvedPresentation` representa presentaciones ya resueltas y credential ids asociados.

## Presentation Submission

`OpenId4VpPresentationSubmission` representa id, definition id y descriptor map.

## Assessment

`OpenId4VpPresentationSubmissionAssessment` separa validez, descriptors faltantes, violations y warnings.

## Contratos

- `OpenId4VpVpTokenProcessorInterface`;
- `OpenId4VpPresentationSubmissionValidatorInterface`;
- `OpenId4VpPresentationBindingPolicyInterface`.

## Seguridad

Las implementaciones deberán validar el binding entre presentation, query, verifier, nonce y transaction context. El processor no debe asumir que resolver un token equivale a verificar criptográficamente su contenido.

## Neutralidad

Foundation no conoce SD-JWT VC, mdoc, JOSE/COSE library, wallet storage ni HTTP client concreto.

## Criterios de aceptación

Envelope/resolved presentation/submission/assessment tipados, contratos separados, reutilización de WP-244, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
