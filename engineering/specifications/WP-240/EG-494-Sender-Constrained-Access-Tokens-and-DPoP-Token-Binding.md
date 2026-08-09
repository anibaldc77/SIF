---
id: EG-494
title: Sender-Constrained Access Tokens and DPoP Token Binding
summary: Define la representación y las fronteras de emisión y validación de access tokens vinculados a la identidad criptográfica validada mediante DPoP.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-240
tags:
  - security
  - oauth
  - dpop
  - sender-constrained-tokens
depends_on:
  - EG-493
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-494 — Sender-Constrained Access Tokens and DPoP Token Binding

## Objetivo
Vincular access tokens OAuth a la identidad criptográfica previamente validada mediante DPoP.

## Confirmation
`OAuthTokenConfirmation` representa el thumbprint de clave pública que debe quedar asociado al token.

## Sender-Constrained Token
`OAuthSenderConstrainedAccessToken` conserva token serializado, confirmation y expiración.

## Contratos
- `OAuthSenderConstrainedAccessTokenIssuerInterface`;
- `OAuthSenderConstraintValidatorInterface`;
- `OAuthTokenConfirmationExtractorInterface`.

## Validación
La validación compara el thumbprint asociado al token con el thumbprint de la proof DPoP validada.

## Seguridad
Los adapters productivos deberán asegurar que la confirmation emitida provenga exclusivamente de una proof válida y que la validación del resource server no acepte bearer semantics cuando el token sea sender-constrained.

## Neutralidad
Foundation no prescribe JWT, token opaco, librería JWS, almacenamiento ni transporte HTTP.

## Fuera de alcance
Resource server middleware, HTTP challenge mapping y perfiles de interoperabilidad obligatorios quedan para incrementos posteriores.

## Criterios de aceptación
Confirmation tipada, token sender-constrained tipado, issuance/validation/extraction contracts, conexión con DPoP verification result, neutralidad de infraestructura y quality gates limpios.
