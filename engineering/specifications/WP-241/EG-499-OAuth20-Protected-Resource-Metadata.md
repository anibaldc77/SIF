---
id: EG-499
title: OAuth 2.0 Protected Resource Metadata
summary: Define metadata tipada de recursos protegidos, capacidades de bearer, mTLS, RAR y DPoP, junto con validación exacta de resource y fronteras de discovery/signed metadata.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-241
tags:
  - security
  - oauth
  - protected-resource
  - metadata
depends_on:
  - EG-498
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-499 — OAuth 2.0 Protected Resource Metadata

## Objetivo

Completar la representación de OAuth Protected Resource Metadata y conectar las capacidades de resource server incorporadas previamente por SIF.

## Metadata

`OAuthProtectedResourceMetadata` representa:

- resource;
- authorization servers;
- JWKS URI;
- scopes;
- bearer methods;
- resource signing algorithms;
- nombre y documentación;
- policy URI;
- terms-of-service URI;
- soporte mTLS certificate-bound tokens;
- authorization detail types;
- DPoP signing algorithms;
- obligatoriedad de DPoP-bound tokens;
- signed metadata opcional.

## Compatibilidad

La ampliación conserva la firma inicial de I1:

`resource`, `authorizationServers`, `scopesSupported`

y agrega nuevos parámetros únicamente después de esos argumentos.

## Builder

`OAuthProtectedResourceMetadataBuilder` ofrece construcción persistente y evita mutaciones compartidas.

## Contratos

- `OAuthProtectedResourceMetadataProviderInterface`;
- `OAuthProtectedResourceMetadataSerializerInterface`;
- `OAuthProtectedResourceMetadataValidatorInterface`;
- `OAuthProtectedResourceMetadataUriResolverInterface`;
- `OAuthSignedProtectedResourceMetadataVerifierInterface`.

## Validación

La frontera de validación recibe el `expectedResource` para permitir comprobar igualdad exacta entre el resource esperado y el valor publicado.

## Discovery

La construcción concreta de la URI `/.well-known/oauth-protected-resource` pertenece a un adapter implementando `OAuthProtectedResourceMetadataUriResolverInterface`.

## Seguridad

Adapters productivos deberán:

- exigir coincidencia exacta del resource identifier;
- usar HTTPS;
- validar signed metadata y trust cuando corresponda;
- aplicar controles SSRF al resolver metadata remota;
- cross-check authorization servers según la policy de aplicación;
- evitar ampliar scopes automáticamente por el solo hecho de estar publicados.

## Neutralidad

Foundation no conoce:

- HTTP GET;
- `WWW-Authenticate`;
- JSON encoder;
- DNS;
- networking;
- cache;
- JWT/JWS library concreta.

## Criterios de aceptación

- metadata tipada;
- compatibilidad I1 preservada;
- builder persistente;
- validator con expected resource;
- signed metadata detrás de contrato;
- DPoP/RAR/mTLS representables;
- infrastructure-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
