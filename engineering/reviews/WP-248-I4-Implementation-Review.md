---
id: WP-248-I4-REVIEW
title: WP-248 I4 Implementation Review
summary: Revisa namespaces, data elements y device response boundaries para ISO mdoc.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
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
  - implementation-review
depends_on:
  - EG-556
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-248 I4 Implementation Review

## Alcance revisado

Se incorporan data element, namespace, issuer-signed data, device-signed data, document y device response, junto con contracts de parser y policies.

## Hallazgos

- La estructura lógica de mdoc queda separada de su serialización.
- Issuer-signed y device-signed data permanecen explícitamente diferenciados.
- Device response no presupone BLE, NFC, QR, HTTP o Digital Credentials API.
- CBOR/COSE y criptografía permanecen fuera de Foundation.

## Decisión

Apto para I5 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
