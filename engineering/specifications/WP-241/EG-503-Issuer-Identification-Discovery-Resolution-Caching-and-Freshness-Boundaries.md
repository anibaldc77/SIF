---
id: EG-503
title: Issuer Identification, Discovery Resolution, Caching and Freshness Boundaries
summary: Define identificación exacta de issuer, resolución de discovery, cache abstracta y freshness policy para metadata OAuth sin acoplar Foundation a networking o cache concretos.
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
  - metadata
  - discovery
  - issuer
depends_on:
  - EG-502
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-503 — Issuer Identification, Discovery Resolution, Caching and Freshness Boundaries

## Objetivo

Cerrar la arquitectura de discovery de WP-241 mediante issuer identification exacta, resolución de metadata y fronteras explícitas de cache/freshness.

## Issuer Identifier

`OAuthIssuerIdentifier` encapsula el identificador del Authorization Server.

La comparación es exacta. Normalizaciones implícitas que alteren el valor no pertenecen a esta capa.

## Resolution Context

`OAuthMetadataResolutionContext` expresa:

- issuer esperado;
- instante de resolución;
- si puede usarse cache;
- si se exige metadata fresca.

## Resolution Result

`OAuthMetadataResolutionResult` devuelve:

- metadata validada;
- si provino de cache;
- freshness;
- URI de discovery utilizada.

## Cache Entry

`OAuthAuthorizationServerMetadataCacheEntry` contiene:

- metadata;
- storedAt;
- expiresAt.

## Contratos

- `OAuthMetadataResolverInterface`;
- `OAuthAuthorizationServerIssuerValidatorInterface`;
- `OAuthAuthorizationServerMetadataUriResolverInterface`;
- `OAuthAuthorizationServerMetadataDocumentLoaderInterface`;
- `OAuthAuthorizationServerMetadataCacheInterface`;
- `OAuthMetadataFreshnessEvaluatorInterface`.

## Compatibilidad

`OAuthMetadataResolverInterface::resolve(string $issuer)` se conserva.

I7 agrega `resolveWithContext()` para incorporar cache/freshness sin romper la frontera de I1.

## Seguridad

Adapters productivos deberán:

- comparar issuer esperado con issuer publicado;
- validar `iss` cuando provenga de authorization response;
- evitar mix-up attacks;
- construir discovery URI conforme al estándar;
- imponer HTTPS;
- aplicar protección SSRF;
- limitar redirects;
- definir timeouts;
- limitar tamaño de documentos;
- invalidar cache cuando corresponda;
- no aceptar metadata stale cuando la policy exige freshness.

## Neutralidad

Foundation no conoce:

- cURL;
- Guzzle;
- DNS resolver;
- HTTP client;
- Redis;
- filesystem cache;
- base de datos.

## Criterios de aceptación

- issuer identifier tipado;
- exact matching;
- contextual resolver sin ruptura;
- cache entry tipada;
- freshness contract;
- discovery URI contract;
- document loading abstracto;
- network-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
