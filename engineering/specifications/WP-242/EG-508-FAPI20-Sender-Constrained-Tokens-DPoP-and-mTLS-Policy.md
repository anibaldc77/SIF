---
id: EG-508
title: FAPI 2.0 Sender-Constrained Tokens DPoP and mTLS Policy
summary: Define requisitos y policies FAPI para access tokens sender-constrained mediante DPoP o mTLS reutilizando capacidades existentes de SIF.
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
  - dpop
  - mtls
depends_on:
  - EG-507
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-508 — FAPI 2.0 Sender-Constrained Tokens: DPoP and mTLS Policy

## Objetivo

Definir la capa de conformidad FAPI para access tokens sender-constrained sin reimplementar DPoP ni mTLS.

## Métodos

`FapiSenderConstraintMethod` admite:

- DPoP;
- mTLS.

## Requirements

`FapiSenderConstraintRequirements` expresa:

- obligatoriedad de sender constraint;
- métodos permitidos.

## Context

`FapiSenderConstraintContext` identifica:

- client id;
- resource;
- método aplicado.

## Assessment

`FapiSenderConstraintAssessment` expresa conformidad y violaciones.

## Contratos

- `FapiSenderConstraintPolicyInterface`;
- `FapiSenderConstraintRequirementsProviderInterface`;
- `FapiDPoPPolicyInterface`;
- `FapiMtlsCertificateBindingPolicyInterface`.

## Reutilización

DPoP reutiliza las capacidades de WP-240.

mTLS se expresa mediante policy/binding contractual; certificados, TLS termination y validación concreta permanecen fuera de Foundation.

## Seguridad

Adapters productivos deberán:

- exigir un sender constraint cuando el perfil lo requiera;
- validar proof DPoP o certificate binding;
- impedir downgrade entre métodos;
- vincular el método al client y resource correctos;
- rechazar bearer semantics cuando el token sea sender-constrained.

## Neutralidad

Foundation no conoce OpenSSL directo, TLS termination, reverse proxy, HSM, storage ni HTTP framework.

## Criterios de aceptación

Métodos tipados, requirements explícitos, context/assessment tipados, policies separadas para DPoP/mTLS, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
