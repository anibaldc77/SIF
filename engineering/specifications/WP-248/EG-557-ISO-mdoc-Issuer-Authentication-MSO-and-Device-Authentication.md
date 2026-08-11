---
id: EG-557
title: ISO mdoc Issuer Authentication MSO and Device Authentication
summary: Define Mobile Security Object, issuer authentication and device authentication boundaries for ISO mdoc without coupling Foundation to COSE, X.509, CBOR or cryptographic providers.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-248
tags:
  - security
  - verifiable-credentials
  - mdoc
  - mso
  - issuer-authentication
  - device-authentication
depends_on:
  - EG-556
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-557 — ISO mdoc Issuer Authentication, MSO and Device Authentication

## Objetivo

Separar Mobile Security Object, issuer authentication y device authentication como controles independientes de ISO mdoc.

## Mobile Security Object

`IsoMdocMobileSecurityObject` representa document type, validity interval, digest algorithm metadata y device key information.

## Issuer Authentication

`IsoMdocIssuerAuthenticationAssessment` separa signature validity, certificate path trust y MSO validity.

## Device Authentication

`IsoMdocDeviceAuthenticationContext` expresa document type, session transcript y device key id.

`IsoMdocDeviceAuthenticationAssessment` separa device key, session transcript y document type binding.

## Contratos

- `IsoMdocMobileSecurityObjectVerifierInterface`;
- `IsoMdocIssuerAuthenticationVerifierInterface`;
- `IsoMdocDeviceAuthenticationVerifierInterface`.

## Seguridad

La implementación concreta deberá validar issuer signature, certificate path, MSO digests, validity interval, device key binding y session transcript. Ninguno de estos controles debe suponerse válido por el mero hecho de haber parseado un DeviceResponse.

## Neutralidad

Foundation no conoce COSE Sign1, X.509 path builder, CBOR canonicalization, secure element, BLE/NFC, HTTP ni crypto provider concreto.

## Criterios de aceptación

MSO/issuer/device authentication tipados, contracts separados, neutralidad COSE/X.509/CBOR, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
