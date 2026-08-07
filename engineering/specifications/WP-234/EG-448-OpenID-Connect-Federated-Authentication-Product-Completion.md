---
id: EG-448
title: Cierre de producto OpenID Connect y autenticación federada
summary: Define invariantes finales y criterios end-to-end para OIDC Client, PKCE, ID Token, account linking, sesión, HTTP y logout federado.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - federation
  - product-completion
depends_on:
  - EG-447
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-448 — OpenID Connect Federated Authentication Product Completion

## Objetivo

Cerrar WP-234 validando el flujo federado completo de SIF como OIDC Client / Relying Party.

## Invariantes finales

- discovery permanece transport-neutral;
- provider metadata no introduce dependencia concreta;
- Authorization Code utiliza PKCE S256;
- state y nonce son independientes;
- authorization code y client secret son secretos protegidos;
- token exchange permanece detrás de contrato;
- ID Token utiliza validación JWT/JWKS existente;
- issuer, audience, tiempos y nonce se validan;
- identidad federada estable = issuer + subject;
- email nunca es link implícito;
- provisioning automático es opt-in;
- principal resultante utiliza identidad local;
- sesión se establece sólo por contrato;
- eventos no exponen secretos;
- HTTP produce instrucciones, no Responses;
- logout permanece provider-neutral.

## Compatibilidad

La arquitectura soporta mediante estándares:

- Keycloak;
- Microsoft Entra ID;
- Auth0;
- Okta;
- otros Identity Providers OIDC compatibles.

No existe dependencia directa de ninguno.

## Fuera de alcance

WP-234 no implementa:

- Authorization Server;
- administración de usuarios de proveedores;
- SCIM;
- SAML;
- MFA del proveedor;
- almacenamiento propio de sesiones;
- account linking implícito por email.

## Criterios de aceptación

- login federado end-to-end produce principal local;
- ID Token inválido falla cerrado;
- cuenta desconocida falla cerrado por defecto;
- sesión sólo tras validación completa;
- logout federado modelado;
- sin dependencia específica de proveedor;
- suite completa sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
