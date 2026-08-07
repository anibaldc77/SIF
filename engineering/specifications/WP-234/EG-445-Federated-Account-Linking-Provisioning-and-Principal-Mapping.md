---
id: EG-445
title: Account linking federado, provisioning y principal mapping
summary: Define resolución explícita de vínculos federados, provisioning gobernado por política y mapping al AuthenticatedPrincipal existente.
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
  - account-linking
  - provisioning
depends_on:
  - EG-444
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-445 — Federated Account Linking, Provisioning y Principal Mapping

## Objetivo

Convertir una identidad OIDC validada en una identidad local de SIF sin utilizar atributos mutables como claves implícitas.

## Account linking

`FederatedIdentityLinkResolverInterface` resuelve un vínculo existente a partir de `OidcFederatedIdentity`.

La identidad federada está determinada por:

- issuer;
- subject.

No por email, username o display name.

## LinkedLocalIdentity

Representa:

- `IdentityId` local;
- identificador del vínculo con el proveedor.

## Provisioning

`FederatedIdentityProvisionerInterface` abstrae la creación o incorporación de una identidad local.

El provisioning automático está prohibido salvo que `FederatedProvisioningPolicy` lo permita explícitamente.

## Resolver

`FederatedAccountResolver`:

1. busca vínculo existente;
2. si existe, lo utiliza;
3. si no existe y provisioning automático está deshabilitado, devuelve null;
4. sólo provisiona cuando la política lo permite.

## Principal mapping

`FederatedPrincipalFactory` reutiliza `AuthenticatedPrincipal`.

La identidad del principal es la identidad local.

Se agregan atributos de contexto federado:

- `federation.issuer`;
- `federation.subject`;
- `federation.stable_key`.

## Seguridad

- email no es una clave de account linking;
- provisioning no es implícito;
- mapping no persiste vínculos;
- mapping no crea sesión;
- el principal conserva identidad local;
- datos del proveedor permanecen como contexto.

## Criterios de aceptación

- vínculo existente reutilizado;
- unknown identity fail-closed por defecto;
- provisioning sólo por política explícita;
- principal local con contexto federado;
- sin account linking por email;
- sin sesión/persistencia directa;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
