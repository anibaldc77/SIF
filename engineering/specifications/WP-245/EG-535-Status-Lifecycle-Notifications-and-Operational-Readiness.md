---
id: EG-535
title: Status Lifecycle Notifications and Operational Readiness
summary: Define lifecycle posterior a la emisión, notificaciones y readiness operacional de OpenID4VCI sin acoplar Foundation a transporte, colas, storage o proveedores externos concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-245
tags:
  - security
  - verifiable-credentials
  - openid4vci
  - lifecycle
  - notifications
  - readiness
depends_on:
  - EG-534
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-535 — Status Lifecycle, Notifications and Operational Readiness

## Objetivo

Completar la operación del Credential Issuer modelando lifecycle, notificaciones y readiness operacional.

## Lifecycle Status

`CredentialIssuanceLifecycleStatus` representa:

- pending;
- issued;
- delivered;
- accepted;
- failed;
- expired.

## Notification

`CredentialIssuanceNotification` expresa:

- notification id;
- transaction id;
- event;
- metadata opcional.

## Lifecycle Assessment

`CredentialIssuanceLifecycleAssessment` separa:

- valid;
- violations;
- warnings.

## Operational Context

`CredentialIssuanceOperationalContext` representa:

- capabilities disponibles;
- controles activos.

## Operational Readiness

`CredentialIssuanceOperationalReadinessReport` separa:

- ready;
- blocking issues;
- advisories.

## Contratos

- `CredentialIssuanceLifecyclePolicyInterface`;
- `CredentialIssuanceNotificationPublisherInterface`;
- `CredentialIssuanceNotificationHandlerInterface`;
- `CredentialIssuanceOperationalReadinessEvaluatorInterface`.

## Seguridad

Implementaciones productivas deberán:

- impedir transiciones inválidas de lifecycle;
- vincular notificaciones a transaction/client correctos;
- deduplicar notificaciones cuando corresponda;
- evitar replay de eventos;
- auditar cambios de estado;
- no incluir credenciales o secretos completos en notificaciones;
- tratar delivery y processing como estados distintos.

## Neutralidad

Foundation no conoce HTTP callbacks, queue brokers, event buses, Redis, base de datos o scheduler concreto.

## Criterios de aceptación

Lifecycle/notification/readiness tipados, contracts separados, infraestructura neutral, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
