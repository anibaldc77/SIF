---
id: EG-540
title: WebAuthn Attestation Authenticator Metadata and Trust
summary: Define attestation statements, authenticator metadata y trust policy para WebAuthn sin acoplar Foundation a FIDO MDS, X.509, COSE, CBOR o transporte concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-246
tags:
  - security
  - webauthn
  - fido2
  - attestation
  - metadata
  - trust
depends_on:
  - EG-539
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-540 — WebAuthn Attestation, Authenticator Metadata and Trust

## Objetivo

Separar parsing de attestation, metadata de authenticator y trust evaluation.

## Attestation Statement

`WebAuthnAttestationStatement` expresa formato, statement serializado y atributos.

## Authenticator Metadata

`WebAuthnAuthenticatorMetadata` expresa identificador, descripción, certificaciones, algoritmos, tipos de attestation y atributos.

## Trust Context

`WebAuthnAttestationTrustContext` expresa si attestation es requerida, formatos permitidos, certificaciones requeridas y necesidad de metadata.

## Trust Assessment

`WebAuthnAttestationTrustAssessment` separa trust global, attestation validity y metadata trust.

## Contratos

- `WebAuthnAttestationStatementParserInterface`;
- `WebAuthnAuthenticatorMetadataResolverInterface`;
- `WebAuthnAttestationTrustPolicyInterface`;
- `WebAuthnAttestationVerifierInterface`.

## Seguridad

Las implementaciones productivas deberán validar attestation cryptographically cuando la policy lo requiera, verificar provenance, freshness de metadata y certification policy sin asumir que metadata presente implica trust.

## Neutralidad

Foundation no conoce FIDO MDS, X.509 store, CBOR/COSE library, HTTP client, cache o storage concreto.

## Criterios de aceptación

Attestation/metadata/context/assessment tipados, contratos separados, neutralidad, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
