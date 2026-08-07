---
id: EG-460
title: Parsing de SAML Response, correlación InResponseTo y validación de Status
summary: Define parsing seguro del envelope Response y validación estructural de issuer, destination, status e InResponseTo antes de procesar assertions.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-236
tags:
  - security
  - saml
  - response
  - correlation
  - validation
depends_on:
  - EG-459
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-460 — SAML Response Parsing, Correlation and Status Validation

## Objetivo

Procesar de forma estructural el envelope `samlp:Response` recibido por el ACS antes de validar firmas o assertions.

## Response model

`SamlResponse` representa:

- response ID;
- IssueInstant;
- Issuer;
- StatusCode;
- InResponseTo opcional;
- Destination opcional.

## Parser

`NativeSamlResponseParser` utiliza DOM + XPath con `LIBXML_NONET`.

El parser exige:

- root `samlp:Response`;
- ID;
- IssueInstant;
- Issuer;
- StatusCode.

## Validation context

`SamlResponseValidationContext` define:

- issuer esperado;
- ACS destination esperada;
- request id esperado.

## Validator

`SamlResponseValidator` detecta:

- status no exitoso;
- issuer mismatch;
- destination mismatch;
- InResponseTo mismatch.

La validación devuelve un resultado tipado y no crea sesión.

## Correlation

Cuando existe request id esperado, `InResponseTo` debe coincidir exactamente.

El soporte IdP-initiated sin `InResponseTo` queda separado para una política posterior.

## Seguridad

- XML network deshabilitada;
- status success obligatorio;
- destination explícita;
- issuer explícito;
- correlation estricta para SP-initiated;
- no hay firma criptográfica todavía.

## Fuera de alcance de I4

- XML Signature validation;
- assertion parsing;
- Conditions;
- AudienceRestriction;
- SubjectConfirmation;
- replay protection;
- session creation.

## Criterios de aceptación

- Response válido parseado;
- issuer/destination/correlation validados;
- status no-success rechazado semánticamente;
- mismatch detectado;
- XML inválido rechazado;
- sin signature verification;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
