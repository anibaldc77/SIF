---
id: EG-546
title: OpenID4VP Presentation Query and Credential Selection
summary: Define requisitos de presentación, candidatos, selección y evaluación de satisfacción para OpenID4VP sin acoplar Foundation a formatos concretos de credencial o almacenamiento.
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
  - presentation-query
  - credential-selection
depends_on:
  - EG-545
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-546 — OpenID4VP Presentation Query and Credential Selection

## Objetivo

Modelar requisitos de presentación y selección de credenciales sin introducir conocimiento de formatos concretos en Foundation.

## Presentation Requirement

`OpenId4VpPresentationRequirement` expresa:

- id;
- formatos aceptados;
- constraints;
- si el requisito es obligatorio.

## Presentation Query

`OpenId4VpPresentationQuery` agrupa uno o más requisitos bajo un query id.

## Credential Candidate

`OpenId4VpCredentialCandidate` expresa credential id, formatos y atributos de selección.

## Credential Selection

`OpenId4VpCredentialSelection` expresa:

- query id;
- credenciales seleccionadas;
- requisitos satisfechos;
- warnings.

## Query Assessment

`OpenId4VpPresentationQueryAssessment` separa satisfacción, requisitos faltantes y warnings.

## Contratos

- `OpenId4VpCredentialSelectionPolicyInterface`;
- `OpenId4VpPresentationQueryEvaluatorInterface`;
- `OpenId4VpCredentialCandidateProviderInterface`.

## Seguridad y privacidad

La selección deberá minimizar disclosure, evitar credential enumeration, no revelar inventario completo al verifier y respetar restricciones de formato/policy.

## Neutralidad

Foundation no conoce SD-JWT VC, mdoc, wallet database, Redis, SQL ni UI concreta.

## Criterios de aceptación

Requirement/query/candidate/selection/assessment tipados, contratos separados, neutralidad de formato/storage, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
