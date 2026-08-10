---
id: EG-522
title: Presentation Request Response and Nonce Audience Binding
summary: Define modelos y contratos para solicitudes y respuestas de presentación con binding explícito de nonce, audience, state y replay protection.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-244
tags:
  - security
  - verifiable-credentials
  - openid4vp
  - presentation
  - replay
depends_on:
  - EG-521
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-522 — Presentation Request/Response and Nonce/Audience Binding

## Objetivo

Agregar una frontera explícita para solicitar y recibir presentaciones verificables manteniendo binding entre request y response.

## Presentation Request

`PresentationRequest` expresa:

- request id;
- audience;
- nonce;
- credential types requeridos;
- claims requeridos;
- state opcional.

## Presentation Response

`PresentationResponse` expresa:

- request id;
- presentation;
- audience;
- nonce;
- state.

## Binding

`PresentationBindingAssessment` expresa validity y violations.

`PresentationBindingPolicyInterface` compara request y response.

## Replay Protection

`PresentationReplayStoreInterface` desacopla replay protection del storage.

La replay key mínima es `requestId + nonce`.

## Contratos

- `PresentationRequestFactoryInterface`;
- `PresentationBindingPolicyInterface`;
- `PresentationResponseMapperInterface`;
- `PresentationReplayStoreInterface`.

## Seguridad

Implementaciones productivas deberán:

- generar nonce con suficiente entropía;
- comparar audience exactamente;
- validar state cuando corresponda;
- rechazar nonce reuse;
- verificar presentation antes de mapear claims;
- evitar persistir material sensible innecesariamente.

## Neutralidad

Foundation no conoce redirect URIs, wallet schemes, HTTP clients, browser flows, Redis ni base de datos.

## Criterios de aceptación

Request/response/binding tipados, replay contract, transport neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
