---
id: EG-490
title: Pushed Authorization Requests
summary: Define el modelo PAR con request URI opaca y temporal, almacenamiento/consumo y validación contract-first sobre el Authorization Server de WP-239.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - par
  - pushed-authorization-request
depends_on:
  - EG-489
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-490 — Pushed Authorization Requests

## Objetivo

Implementar el modelo de Pushed Authorization Requests sobre la arquitectura avanzada de WP-240 y reutilizando `OAuthAuthorizationRequest` de WP-239.

## Modelo

`OAuthPushedAuthorizationRequest` contiene:

- request URI opaca;
- authorization request original;
- issuedAt;
- expiresAt.

`OAuthPushedAuthorizationRequestUri` encapsula la referencia opaca retornada al cliente.

## Contratos

- `OAuthPushedAuthorizationRequestServiceInterface`;
- `OAuthPushedAuthorizationRequestRepositoryInterface`;
- `OAuthPushedAuthorizationRequestUriGeneratorInterface`;
- `OAuthPushedAuthorizationRequestValidatorInterface`.

## Semántica

El flujo conceptual es:

1. el cliente construye una authorization request;
2. la envía a la frontera PAR;
3. el servidor valida y almacena el payload;
4. genera un `request_uri`;
5. el authorization endpoint posterior referencia esa request URI;
6. el registro puede consumirse según política del adapter.

## Seguridad

La implementación productiva deberá:

- autenticar al cliente cuando corresponda;
- validar redirect URI, scopes y parámetros;
- usar request URIs impredecibles;
- aplicar expiración corta;
- impedir sustitución entre clientes;
- evitar reutilización cuando la política requiera single-use;
- proteger el almacenamiento PAR.

## Neutralidad

Foundation no conoce:

- endpoint HTTP concreto;
- status code 201;
- serialización JSON;
- base de datos;
- cache;
- reverse proxy;
- proveedor IAM.

## Compatibilidad

PAR se diseña para coexistir con:

- Authorization Code;
- PKCE;
- JAR;
- RAR;
- client authentication;
- OIDC.

## Fuera de alcance

- JAR;
- request object signing;
- RAR;
- DPoP;
- HTTP endpoint implementation;
- client registration.

## Criterios de aceptación

- request URI tipada;
- lifetime explícito;
- request original preservada;
- service tipado;
- repository con save/find/consume;
- infrastructure-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
