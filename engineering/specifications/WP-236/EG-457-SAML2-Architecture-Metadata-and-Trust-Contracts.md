---
id: EG-457
title: Arquitectura SAML 2.0, metadata y contratos de confianza
summary: Define value objects y contratos neutrales para metadata IdP/SP, endpoints SAML y trust basado en fingerprints SHA-256.
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
  - federation
  - metadata
  - trust
depends_on:
  - EG-456
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-457 — SAML 2.0 Architecture, Metadata and Trust Contracts

## Objetivo

Iniciar WP-236 con una arquitectura SAML 2.0 desacoplada de transporte, XML parser, proveedor y persistencia.

## Modelo

### SamlEntityId

Representa el entityID de una entidad SAML.

### SamlEndpoint

Representa location y binding de un endpoint.

### SamlIdentityProviderMetadata

Contiene:

- entity id;
- Single Sign-On Services;
- Single Logout Services;
- fingerprints SHA-256 de certificados de firma.

### SamlServiceProviderMetadata

Contiene:

- entity id;
- Assertion Consumer Service;
- Single Logout Service opcional.

## Trust

`SamlCertificateFingerprint` representa un digest SHA-256 validado.

`SamlTrustStoreInterface` decide si un fingerprint es confiable para un entity id determinado.

Foundation no define storage del trust store.

## Metadata provider

`SamlIdentityProviderMetadataProviderInterface` expone `get()` y `refresh()`.

El transporte, cache y parsing XML pertenecen a infraestructura posterior.

## Seguridad

- no se confía en certificados por nombre;
- fingerprints son SHA-256;
- trust siempre está asociado a entity id;
- no existe fetch HTTP dentro de Foundation;
- no existe parsing XML todavía;
- no existe dependencia concreta de Keycloak, Entra ID, Okta, OneLogin o Shibboleth.

## Fuera de alcance de I1

- XML parsing;
- XML Signature;
- XML Encryption;
- AuthnRequest;
- Response/Assertion;
- HTTP-Redirect;
- HTTP-POST;
- Single Logout flow;
- session integration.

## Criterios de aceptación

- entity ids tipados;
- endpoints tipados;
- metadata IdP/SP;
- fingerprint SHA-256;
- provider contract transport-neutral;
- trust store storage-neutral;
- provider-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
