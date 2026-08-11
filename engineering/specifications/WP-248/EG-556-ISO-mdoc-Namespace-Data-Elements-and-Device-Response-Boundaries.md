---
id: EG-556
title: ISO mdoc Namespace Data Elements and Device Response Boundaries
summary: Define ISO mdoc namespace, data element, issuer-signed, device-signed and device response boundaries without coupling Foundation to CBOR, COSE, crypto or transport implementations.
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
  - namespace
  - device-response
depends_on:
  - EG-555
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-556 — ISO mdoc Namespace, Data Elements and Device Response Boundaries

## Objetivo

Modelar la estructura lógica de ISO mdoc sin introducir CBOR, COSE, MSO parsing ni transportes concretos en Foundation.

## Data Element

`IsoMdocDataElement` representa identifier y value.

## Namespace

`IsoMdocNamespace` agrupa data elements bajo un namespace explícito.

## Issuer-Signed Data

`IsoMdocIssuerSignedData` representa document type, namespaces e issuer authentication metadata.

## Device-Signed Data

`IsoMdocDeviceSignedData` representa namespaces y device authentication metadata.

## Document

`IsoMdocDocument` separa issuer-signed y device-signed data.

## Device Response

`IsoMdocDeviceResponse` agrupa versión, documents y metadata de respuesta.

## Contratos

- `IsoMdocDeviceResponseParserInterface`;
- `IsoMdocNamespacePolicyInterface`;
- `IsoMdocDocumentPolicyInterface`.

## Seguridad

La implementación productiva deberá preservar namespace semantics, evitar element substitution, validar document type y no asumir que una estructura parseada implica issuer/device authentication válida.

## Neutralidad

Foundation no conoce CBOR decoder, COSE verifier, MSO parser, X.509, BLE/NFC, QR, HTTP o persistence concreta.

## Criterios de aceptación

Element/namespace/issuer-signed/device-signed/document/response tipados, contratos separados, neutralidad CBOR/COSE/transporte, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
