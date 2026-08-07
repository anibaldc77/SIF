---
id: EG-461
title: Parsing de SAML Assertion, Conditions, Audience y SubjectConfirmation
summary: Define parsing estructural de assertions y validación temporal, audience y bearer SubjectConfirmation sin incorporar aún XML Signature.
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
  - assertion
  - audience
  - subject-confirmation
depends_on:
  - EG-460
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-461 — SAML Assertion Parsing and Validation

## Objetivo

Procesar la assertion SAML y validar las restricciones semánticas necesarias antes de convertirla en identidad autenticada.

## Assertion model

`SamlAssertion` contiene:

- assertion ID;
- IssueInstant;
- Issuer;
- Subject NameID;
- Conditions;
- SubjectConfirmationData.

## Conditions

`SamlAssertionConditions` representa:

- NotBefore;
- NotOnOrAfter;
- AudienceRestriction.

## SubjectConfirmationData

Se modelan:

- Recipient;
- InResponseTo;
- NotOnOrAfter.

## Clock skew

`SamlAssertionValidationContext` recibe un `DateInterval` explícito para tolerancia de reloj.

Foundation no obtiene la hora globalmente.

## Validator

`SamlAssertionValidator` valida:

- issuer;
- ventana temporal de Conditions;
- audience;
- existencia de SubjectConfirmationData;
- recipient;
- InResponseTo;
- expiración de SubjectConfirmation.

## Seguridad

- audience es obligatoria para validación exitosa;
- bearer SubjectConfirmation debe estar correlacionada;
- expiración se evalúa con skew explícito;
- XML parsing usa `LIBXML_NONET`;
- no se crea sesión;
- no se verifica aún XML Signature.

## Fuera de alcance de I5

- XML Signature;
- assertion encryption;
- AttributeStatement mapping;
- AuthnStatement;
- replay store;
- session creation.

## Criterios de aceptación

- assertion parseada;
- NameID extraído;
- Conditions/Audience extraídas;
- SubjectConfirmation extraída;
- validación temporal con skew;
- audience/recipient/correlation mismatch detectados;
- sin firma ni sesión;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
