---
id: EG-436
title: Resolución JWKS, rotación de claves y verificación de firma
summary: Define JWK/JWKS, resolución por kid, refresh ante rotación y delegación criptográfica sin acoplar el Core a HTTP ni proveedores concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - jwt
  - jwks
  - key-rotation
depends_on:
  - EG-435
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-436 — JWKS, resolución por kid y rotación

## Objetivo

Incorporar la infraestructura de resolución de claves JWK para JWT sin integrar todavía un transporte HTTP concreto.

## JWK

`Jwk` representa:

- `kid`;
- `kty`;
- `alg` opcional;
- parámetros escalares de clave.

## JWK Set

`JwkSet` mantiene un índice determinístico por `kid` y rechaza duplicados.

## Provider

`JwkSetProviderInterface` ofrece:

- `get()`;
- `refresh()`.

La implementación concreta puede utilizar cache, archivo, HTTP, discovery u otra fuente.

## Resolver

`JwkResolver`:

1. consulta el set actual;
2. si el `kid` no existe, solicita un refresh;
3. vuelve a intentar una sola vez;
4. si continúa ausente, falla cerrado.

Este comportamiento soporta rotación de claves sin loops de refresh.

## Signature verification

`JwksJwtSignatureVerifier`:

- exige `kid`;
- resuelve la clave;
- verifica compatibilidad de `alg` cuando JWK lo declara;
- delega la criptografía a `JwkSignatureVerifierInterface`.

## Seguridad

- ausencia de `kid` falla cerrado;
- `kid` desconocido luego de refresh falla cerrado;
- algoritmo incompatible falla antes de la criptografía;
- el Core no realiza HTTP;
- el Core no depende de Keycloak.

## Criterios de aceptación

- Resolución por kid.
- Refresh único ante rotación.
- Alg mismatch fail-closed.
- Delegación criptográfica.
- Sin HTTP dentro del resolver.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
