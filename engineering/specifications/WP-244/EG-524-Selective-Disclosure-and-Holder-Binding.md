---
id: EG-524
title: Selective Disclosure and Holder Binding
summary: Define requisitos, assessment y contratos para mínima divulgación de claims y binding de una presentación al holder esperado sin acoplar Foundation a SD-JWT, mdoc o criptografía concreta.
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
  - selective-disclosure
  - holder-binding
depends_on:
  - EG-523
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-524 — Selective Disclosure and Holder Binding

## Objetivo

Agregar políticas explícitas para revelar únicamente los claims necesarios y validar que una presentación está vinculada al holder esperado.

## Selective Disclosure Request

`SelectiveDisclosureRequest` separa:

- required claims;
- optional claims.

## Assessment

`SelectiveDisclosureAssessment` expresa:

- valid;
- missing claims;
- excess claims;
- warnings.

El exceso de claims puede considerarse una violación de data minimization.

## Holder Binding

`HolderBindingContext` expresa:

- holder esperado;
- audience;
- nonce.

`HolderBindingAssessment` expresa validity y violations.

## Contratos

- `SelectiveDisclosurePolicyInterface`;
- `HolderBindingVerifierInterface`;
- `DisclosedClaimsExtractorInterface`.

## Compatibilidad

El método `SelectiveDisclosurePolicyInterface::validate()` introducido en I1 se conserva.

I4 agrega `assess()` para evaluación rica.

## Seguridad

Implementaciones productivas deberán:

- requerir únicamente claims necesarios;
- detectar over-disclosure;
- verificar holder binding;
- validar audience y nonce;
- impedir reutilización de proof;
- no asumir que possession equivale a identity assurance.

## Neutralidad

Foundation no conoce SD-JWT, KB-JWT, mdoc, CBOR, wallet SDK, HSM o librería criptográfica concreta.

## Criterios de aceptación

Request/assessment/context tipados, compatibilidad I1 preservada, holder binding contract, data minimization explícita, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
