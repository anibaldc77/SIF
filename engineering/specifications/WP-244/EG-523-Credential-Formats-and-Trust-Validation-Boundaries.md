---
id: EG-523
title: Credential Formats and Trust Validation Boundaries
summary: Define fronteras extensibles para formatos de credenciales y evaluación de confianza de issuer, tipo, firma y vigencia sin acoplar Foundation a formatos o librerías concretas.
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
  - credential-format
  - trust
depends_on:
  - EG-522
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-523 — Credential Formats and Trust Validation Boundaries

## Objetivo

Separar formato, parsing y trust evaluation para que SIF pueda admitir múltiples familias de credenciales sin condicionar Foundation a una tecnología concreta.

## Credential Format

`CredentialFormat` representa un identificador opaco y extensible.

Ejemplos concretos como SD-JWT VC, mdoc o JSON-LD pertenecen a adapters y no al núcleo.

## Credential Envelope

`CredentialEnvelope` contiene:

- format;
- serialized credential.

## Trust Context

`CredentialTrustContext` expresa:

- instante de evaluación;
- issuers confiables;
- credential types aceptados;
- firma requerida;
- validity window requerida.

## Trust Assessment

`CredentialTrustAssessment` separa:

- trusted;
- violations;
- warnings.

## Contratos

- `CredentialFormatHandlerInterface`;
- `CredentialFormatRegistryInterface`;
- `CredentialTrustPolicyInterface`;
- `CredentialIssuerTrustResolverInterface`.

## Seguridad

Implementaciones productivas deberán:

- validar firmas según el formato;
- resolver issuer trust desde fuente confiable;
- validar tipos y vigencia;
- rechazar algoritmos inseguros;
- evitar format confusion;
- no tratar parsing exitoso como trust exitoso.

## Neutralidad

Foundation no conoce SD-JWT library, mdoc/CBOR library, JSON-LD processor, JWT/JWS library, HSM ni trust store concreto.

## Criterios de aceptación

Formato extensible, envelope/context/assessment tipados, trust y parsing separados, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
