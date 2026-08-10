---
id: EG-532
title: Credential Endpoint Batch and Deferred Issuance
summary: Define emisión inmediata, batch y deferred issuance para OpenID4VCI mediante contratos neutrales respecto de HTTP, colas y persistencia.
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
  - batch
  - deferred
depends_on:
  - EG-531
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-532 — Credential Endpoint, Batch and Deferred Issuance

## Objetivo

Agregar soporte contractual para emisión inmediata, por lotes y diferida.

## Batch Issuance

`BatchCredentialIssuanceRequest` agrupa múltiples solicitudes de emisión.

`BatchCredentialIssuanceResponse` agrupa resultados y permite detectar elementos diferidos.

## Deferred Issuance

`DeferredCredentialIssuanceRequest` representa:

- transaction id;
- client id.

`DeferredCredentialIssuanceResult` representa:

- ready;
- response opcional;
- reason opcional.

## Transaction Binding

`CredentialIssuanceTransaction` vincula:

- transaction id;
- client id;
- subject id;
- credential configuration id;
- created at.

## Contratos

- `BatchCredentialIssuanceServiceInterface`;
- `DeferredCredentialIssuanceServiceInterface`;
- `CredentialIssuanceTransactionRepositoryInterface`;
- `CredentialIssuanceTransactionPolicyInterface`.

## Seguridad

Implementaciones productivas deberán:

- vincular transaction id con client y subject;
- impedir enumeración de transaction ids;
- aplicar expiración;
- impedir reutilización después de completar;
- tratar batch parcialmente fallido de forma explícita;
- no exponer credenciales pendientes en logs;
- mantener idempotencia cuando corresponda.

## Neutralidad

Foundation no conoce HTTP endpoints, queue brokers, workers, Redis, base de datos o scheduler concreto.

## Criterios de aceptación

Batch/deferred/transaction models tipados, transaction policy/repository contracts, infraestructura neutral, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
