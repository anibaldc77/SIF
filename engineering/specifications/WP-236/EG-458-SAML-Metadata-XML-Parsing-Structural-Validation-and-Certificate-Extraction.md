---
id: EG-458
title: Parsing XML de metadata SAML, validación estructural y extracción de certificados
summary: Define parsing seguro de EntityDescriptor IdP, endpoints y certificados de firma sin introducir XML Signature ni transporte remoto.
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
  - metadata
  - xml
  - certificates
depends_on:
  - EG-457
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-458 — SAML Metadata XML Parsing

## Objetivo

Convertir metadata XML SAML 2.0 en los modelos tipados definidos en I1.

## Parser

`SamlMetadataParserInterface` abstrae el parsing.

`NativeSamlMetadataParser` utiliza DOM + XPath y deshabilita acceso de red mediante `LIBXML_NONET`.

## Validación estructural

El parser exige:

- root `md:EntityDescriptor`;
- `entityID`;
- exactamente un `md:IDPSSODescriptor`;
- al menos un `md:SingleSignOnService`;
- `Location` en cada endpoint.

## Certificados

Los elementos `ds:X509Certificate` de `KeyDescriptor` signing o sin atributo `use` se normalizan como `SamlX509Certificate`.

La confianza no se decide durante parsing.

El parser únicamente calcula fingerprints SHA-256 y los incorpora al metadata model.

## Separación de responsabilidades

I2 no:

- descarga metadata;
- verifica XML Signature;
- confía automáticamente en un certificado;
- procesa AuthnRequest o Response;
- interpreta assertions.

## Seguridad XML

- `LIBXML_NONET`;
- no hay transporte HTTP;
- no hay DTD/network fetch;
- parsing y trust permanecen separados.

## Criterios de aceptación

- parse válido;
- endpoints extraídos;
- signing fingerprint extraído;
- XML malformado rechazado;
- root inválido rechazado;
- metadata sin SSO rechazada;
- network deshabilitada;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
