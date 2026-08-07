---
id: EG-444
title: Validación de ID Token, nonce e identidad federada
summary: Define validación de ID Token OIDC sobre JWT/JWKS existente, verificación de nonce y mapping estable issuer+subject a identidad federada.
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
  - id-token
  - nonce
  - federation
depends_on:
  - EG-443
  - EG-436
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-444 — ID Token Validation, Nonce y Federated Identity

## Objetivo

Validar el ID Token obtenido mediante token exchange y convertir sus claims confiables en una identidad federada estable.

## ID Token

`OidcIdToken` representa material sensible:

- redactado;
- no serializable;
- fingerprint SHA-256;
- exposición sólo mediante callback explícito.

## Reutilización JWT/JWKS

La validación reutiliza:

- `ParsedJwt`;
- `JwtSignatureVerifierInterface`;
- infraestructura JWKS de WP-233.

OIDC sólo agrega las reglas específicas del ID Token.

## Policy

`OidcIdTokenValidationPolicy` define:

- issuer esperado;
- client id esperado;
- algoritmos permitidos;
- clock skew.

## Reglas de validación

Un ID Token se acepta sólo si:

1. algoritmo permitido;
2. firma válida;
3. issuer coincide;
4. audience contiene client id;
5. exp está vigente;
6. iat no está indebidamente en el futuro;
7. nbf se respeta cuando existe;
8. nonce coincide con la transacción.

## Federated Identity

`OidcFederatedIdentity` utiliza:

- issuer;
- subject.

El identificador estable deriva de ambos y no de email, username u otros claims mutables.

## Claims

Claims adicionales escalares pueden acompañar a la identidad para pasos posteriores de account linking o provisioning.

No deben redefinir la identidad estable.

## Seguridad

- nonce mismatch falla cerrado;
- issuer/audience mismatch falla cerrado;
- firma inválida falla cerrado;
- `alg=none` queda fuera de allow-list;
- ID Token no crea sesión directamente;
- no existe código específico de Keycloak.

## Criterios de aceptación

- ID Token válido produce federated identity.
- nonce verificado.
- issuer/audience/time verificados.
- identidad estable por issuer+sub.
- ID Token protegido.
- sin sesión.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
