---
id: EG-525
title: Identity Assurance Verified Claims and Evidence
summary: Define niveles, perfiles, contexto y assessment de identity assurance sobre verified claims y evidence sin acoplar Foundation a proveedores KYC o registros externos concretos.
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
  - identity-assurance
  - verified-claims
depends_on:
  - EG-524
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-525 — Identity Assurance, Verified Claims and Evidence

## Objetivo

Formalizar requisitos de identity assurance sobre claims ya verificados y evidencia asociada.

## Assurance Level

`IdentityAssuranceLevel` es opaco y extensible.

No impone una taxonomía global única en Foundation.

## Assurance Context

`IdentityAssuranceContext` expresa:

- assurance level requerido;
- evidence types requeridos;
- verification methods requeridos;
- verified claims requeridos.

## Assessment

`IdentityAssuranceAssessment` separa:

- satisfied;
- missing evidence;
- violations;
- warnings.

## Profile

`IdentityAssuranceProfile` expresa:

- nombre;
- nivel;
- evidence types aceptados;
- methods aceptados.

## Contratos

- `IdentityAssurancePolicyInterface`;
- `IdentityAssuranceProfileProviderInterface`;
- `IdentityAssuranceEvidenceValidatorInterface`.

## Reutilización

Se reutilizan `VerifiedIdentityClaims` e `IdentityAssuranceEvidence` de I1.

## Seguridad

Implementaciones productivas deberán:

- verificar que evidence corresponde al subject;
- validar method y fuente;
- distinguir identity proofing de authentication;
- aplicar freshness cuando corresponda;
- no tratar presencia de evidencia como garantía suficiente sin policy.

## Neutralidad

Foundation no conoce KYC vendors, registro civil, bureau, HTTP client, storage o proveedor documental concreto.

## Criterios de aceptación

Level/context/assessment/profile tipados, evidence validator, provider neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
