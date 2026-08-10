---
id: EG-526
title: Credential Status Revocation and Freshness
summary: Define estado, revocación, suspensión y freshness de credenciales verificables mediante contratos neutrales respecto de Status List, OCSP, CRL, HTTP o storage concretos.
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
  - status
  - revocation
  - freshness
depends_on:
  - EG-525
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-526 — Credential Status, Revocation and Freshness

## Objetivo

Separar la evaluación de status de una credencial de su parsing, formato y trust evaluation.

## Credential Status

`CredentialStatus` representa:

- valid;
- suspended;
- revoked;
- unknown.

## Status Context

`CredentialStatusContext` expresa:

- instante de evaluación;
- status check requerido;
- freshness requerida;
- máxima antigüedad permitida.

## Status Evidence

`CredentialStatusEvidence` expresa:

- estado;
- instante del check;
- fuente;
- metadata opcional.

## Assessment

`CredentialStatusAssessment` separa:

- acceptable;
- evidence;
- violations;
- warnings.

## Contratos

- `CredentialStatusResolverInterface`;
- `CredentialStatusPolicyInterface`;
- `CredentialFreshnessPolicyInterface`.

## Seguridad

Implementaciones productivas deberán:

- validar revocación/suspensión cuando el perfil lo requiera;
- aplicar freshness explícita;
- tratar `unknown` según policy;
- distinguir falla de transporte de estado revocado;
- evitar caching más allá de la ventana permitida;
- no asumir que una credencial firmada continúa vigente.

## Neutralidad

Foundation no conoce Status List concreta, OCSP, CRL, HTTP client, Redis, base de datos o issuer endpoint.

## Criterios de aceptación

Status/context/evidence/assessment tipados, resolver/policy/freshness contracts, neutralidad, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
