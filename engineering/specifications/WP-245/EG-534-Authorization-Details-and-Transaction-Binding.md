---
id: EG-534
title: Authorization Details and Transaction Binding
summary: Define authorization details específicos de credenciales y su binding con client, subject, issuer y transaction sin acoplar Foundation al authorization server, transporte o storage concretos.
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
  - authorization-details
  - transaction-binding
depends_on:
  - EG-533
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-534 — Authorization Details and Transaction Binding

## Objetivo

Modelar authorization details para issuance y vincular su autorización con la transaction concreta.

## Authorization Detail

`CredentialAuthorizationDetail` representa:

- type;
- credential configuration ids;
- locations opcionales;
- metadata opcional.

## Authorization Context

`CredentialAuthorizationContext` vincula:

- credential issuer;
- client id;
- subject id;
- authorization request id opcional.

## Assessment

`CredentialAuthorizationAssessment` separa:

- authorized;
- violations;
- warnings.

## Transaction Binding

`CredentialAuthorizationTransactionBinding` expresa:

- transaction id;
- client id;
- subject id;
- credential configuration id;
- authorization request id opcional.

## Contratos

- `CredentialAuthorizationDetailValidatorInterface`;
- `CredentialAuthorizationTransactionBinderInterface`;
- `CredentialAuthorizationTransactionBindingRepositoryInterface`;
- `CredentialAuthorizationTransactionPolicyInterface`.

## Seguridad

Implementaciones productivas deberán:

- impedir client substitution;
- impedir subject substitution;
- validar configuration id autorizado;
- validar location cuando corresponda;
- impedir reutilización de binding con otra transaction;
- conservar trazabilidad entre authorization request y issuance transaction;
- no almacenar authorization details sensibles innecesariamente.

## Neutralidad

Foundation no conoce OAuth endpoint concreto, Pushed Authorization Request transport, Redis, base de datos o framework HTTP.

## Criterios de aceptación

Detail/context/assessment/binding tipados, binder/repository/policy contracts, OAuth neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
