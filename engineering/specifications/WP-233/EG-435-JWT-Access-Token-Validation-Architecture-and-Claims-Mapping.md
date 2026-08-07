---
id: EG-435
title: Arquitectura de validación JWT y mapeo de claims
summary: Define parsing, claims, policy temporal, verificación por contrato y adaptación de JWT a ValidatedAccessToken sin introducir todavía JWKS.
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
  - claims
  - resource-server
depends_on:
  - EG-434
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-435 — Validación JWT y mapeo de claims

## Objetivo

Incorporar JWT como una implementación posible de access token para WP-233 manteniendo separadas parsing, criptografía, policy y claims.

## Componentes

- `JwtHeader`;
- `JwtClaims`;
- `ParsedJwt`;
- `JwtParserInterface`;
- `JwtSignatureVerifierInterface`;
- `JwtValidationPolicy`;
- `JwtClaimsMapper`;
- `JwtAccessTokenValidator`.

## Validación

La validación exige:

1. algoritmo permitido explícitamente;
2. firma válida;
3. `exp` presente y vigente;
4. `nbf` respetado;
5. issuer esperado cuando esté configurado;
6. audience aceptada cuando esté configurada.

Se admite clock skew explícito.

## Claims

Se reconocen:

- `sub`;
- `iss`;
- `aud`;
- `exp`;
- `iat`;
- `nbf`;
- `scope`.

Los claims adicionales sólo se copian cuando son escalares o null.

## Scopes

El claim `scope` se divide por espacios y se convierte a `ScopeSet`.

Los scopes todavía no equivalen automáticamente a permissions WP-232.

## Seguridad

- `alg=none` no debe aceptarse salvo configuración explícita, que no se recomienda.
- El algoritmo se toma de una allow-list de policy.
- Parsing no implica confianza.
- Claims no se utilizan antes de verificar policy y firma para producir un token validado.
- JWKS queda fuera de I3.

## Criterios de aceptación

- Mapping estándar de claims.
- Allow-list de algoritmo.
- Signature verifier por contrato.
- Validación issuer/audience/time.
- Sin acceso HTTP/JWKS.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
