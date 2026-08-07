---
id: EG-459
title: Construcción de AuthnRequest, RelayState y binding HTTP-Redirect SAML
summary: Define solicitudes SP-initiated, correlación por request id y RelayState, serialización XML y encoding HTTP-Redirect sin transporte ni firma.
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
  - authn-request
  - relay-state
  - redirect-binding
depends_on:
  - EG-458
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-459 — SAML AuthnRequest, RelayState y HTTP-Redirect

## Objetivo

Construir el inicio de un login SAML SP-initiated sin realizar transporte ni validar respuestas.

## Request ID

`SamlRequestId` representa el identificador de `AuthnRequest`.

Los generators se abstraen mediante `SamlRequestIdGeneratorInterface`.

## RelayState

`SamlRelayState` mantiene el valor de correlación de browser flow.

Se limita a 80 bytes para mantener compatibilidad con el profile de bindings.

## AuthnRequest

`SamlAuthnRequest` contiene:

- ID;
- IssueInstant;
- Issuer;
- Destination;
- AssertionConsumerServiceURL;
- ForceAuthn.

## XML serializer

`SamlAuthnRequestXmlSerializer` genera el XML protocol mínimo requerido.

No firma el documento.

## HTTP-Redirect binding

`SamlHttpRedirectBindingEncoder`:

1. serializa XML;
2. aplica raw DEFLATE;
3. codifica Base64;
4. produce parámetros `SAMLRequest` y `RelayState`;
5. utiliza RFC3986 para query string.

## Factory

`SamlSpInitiatedLoginRequestFactory` selecciona el primer endpoint SSO del metadata IdP y crea request + RelayState.

## Seguridad

- request ids criptográficamente aleatorios en implementación nativa;
- RelayState nativo generado con random bytes;
- no hay HTTP;
- no hay XML Signature;
- no hay Response parsing;
- no hay proveedor específico.

## Fuera de alcance de I3

- Redirect binding signature;
- HTTP POST binding;
- correlation store;
- Response/InResponseTo validation;
- Assertion validation;
- session creation.

## Criterios de aceptación

- request y relay correlation;
- XML SAML 2.0 válido estructuralmente;
- raw DEFLATE + Base64;
- RFC3986 query;
- RelayState acotado;
- sin transporte/firma;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
