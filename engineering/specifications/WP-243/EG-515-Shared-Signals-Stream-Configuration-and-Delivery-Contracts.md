---
id: EG-515
title: Shared Signals Stream Configuration and Delivery Contracts
summary: Define configuración, estado y fronteras de entrega para streams Shared Signals sin acoplar Foundation a Push, Poll o transporte HTTP concreto.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-243
tags:
  - security
  - shared-signals
  - streams
  - delivery
depends_on:
  - EG-514
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-515 — Shared Signals Stream Configuration and Delivery Contracts

## Objetivo

Agregar administración y entrega de streams SSF sin trasladar transporte, almacenamiento o reintentos concretos a Foundation.

## Stream Configuration

`SharedSignalsStreamConfiguration` expresa:

- stream id;
- delivery method;
- event types;
- enabled.

## Delivery Envelope

`SharedSignalsDeliveryEnvelope` identifica stream, delivery id, SET e instante de preparación.

## Delivery Result

`SharedSignalsDeliveryResult` separa:

- accepted;
- retryable;
- reason.

## Operational Status

`SharedSignalsStreamStatus` mantiene evidencia operacional mínima de éxito/fallo.

## Contratos

- `SharedSignalsStreamConfigurationRepositoryInterface`;
- `SharedSignalsDeliveryServiceInterface`;
- `SharedSignalsDeliveryMethodPolicyInterface`;
- `SharedSignalsStreamStatusRepositoryInterface`.

## Push y Poll

Push y Poll se implementan mediante adapters concretos de `SharedSignalsDeliveryServiceInterface`.

Foundation no conoce endpoints HTTP, authentication methods, backoff, queue brokers o persistence concretos.

## Seguridad

Adapters productivos deberán:

- autenticar receptor/emisor según delivery method;
- validar destination;
- aplicar retries acotados;
- evitar replay de deliveries;
- proteger SETs en tránsito;
- no registrar tokens completos en logs;
- diferenciar fallas retryable de rechazos definitivos.

## Criterios de aceptación

Modelos tipados, delivery contract, repositories abstractos, transport-neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
