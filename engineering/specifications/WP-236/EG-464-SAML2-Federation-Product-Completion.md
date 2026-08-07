---
id: EG-464
title: Cierre de producto de federación SAML 2.0
summary: Consolida metadata, AuthnRequest, Response, Assertion, trust, replay protection, identity mapping y establecimiento de sesión.
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
  - federation
  - product-completion
depends_on:
  - EG-463
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-464 — SAML 2.0 Federation Product Completion

## Objetivo

Cerrar WP-236 como foundation SAML 2.0 modular, segura y provider-neutral.

## Capacidades consolidadas

- metadata IdP/SP;
- parsing XML seguro;
- extracción de fingerprints;
- AuthnRequest SP-initiated;
- RelayState;
- HTTP-Redirect encoding;
- Response parsing;
- Status/Issuer/Destination/InResponseTo validation;
- Assertion parsing;
- Conditions;
- AudienceRestriction;
- SubjectConfirmation;
- clock skew explícito;
- trust por issuer/fingerprint;
- signature verification contract;
- signed-document policy;
- replay protection;
- identity mapping;
- session establishment boundary.

## Orden de validación recomendado

1. parse Response;
2. validar envelope;
3. parse Assertion;
4. validar assertion semantics;
5. validar trust y firmas;
6. registrar Response/Assertion IDs en replay store;
7. mapear identidad;
8. establecer sesión.

Una aplicación no debe crear sesión antes de completar las etapas anteriores.

## Neutralidad

Foundation no contiene:

- HTTP clients;
- metadata fetch;
- storage concreto;
- session storage concreto;
- Keycloak SDK;
- OneLogin SDK;
- SimpleSAMLphp;
- Symfony/Laravel adapters.

## Compatibilidad

La arquitectura está diseñada para integrarse con IdP SAML 2.0 como:

- Keycloak;
- Microsoft Entra ID;
- ADFS;
- Okta;
- Shibboleth;
- Ping Identity;
- OneLogin.

Estas integraciones deben implementarse como adapters externos.

## Fuera de alcance

- implementación XMLDSig concreta;
- XML Encryption;
- Single Logout completo;
- IdP role;
- artifact binding;
- SOAP binding;
- persistent replay adapter;
- metadata automatic refresh.

## Criterios de aceptación

- flujo end-to-end de componentes validado;
- trust antes de autenticación;
- replay protection demostrada;
- session boundary explícita;
- provider/storage/transport neutral;
- suite completa sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
