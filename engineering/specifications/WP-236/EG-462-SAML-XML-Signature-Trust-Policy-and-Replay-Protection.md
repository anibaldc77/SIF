---
id: EG-462
title: Política de firma XML, confianza y protección contra replay SAML
summary: Define contratos de verificación criptográfica, trust por issuer/fingerprint, política de documentos firmados y replay protection sin storage ni proveedor concreto.
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
  - xml-signature
  - trust
  - replay
depends_on:
  - EG-461
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-462 — XML Signature Trust Policy and Replay Protection

## Objetivo

Introducir la frontera criptográfica del subsistema SAML sin acoplarla a una biblioteca XML Signature ni a un proveedor concreto.

## Signature verifier

`SamlXmlSignatureVerifierInterface` verifica criptográficamente un documento XML contra un fingerprint esperado.

La implementación concreta puede utilizar OpenSSL, xmlsec u otra infraestructura aprobada.

Foundation no implementa canonicalización ni transform chains directamente en I6.

## Trust

`SamlSignatureTrustValidator` aplica primero `SamlTrustStoreInterface`.

Sólo un certificado confiable para el `entityID` esperado puede pasar al signature verifier.

## Signed document policy

`SamlSignedDocumentPolicy` permite exigir:

- Response firmada;
- Assertion firmada.

Por defecto ambas son obligatorias.

No se considera que una firma válida en un documento haga innecesaria la política configurada para el otro.

## Replay

`SamlReplayStoreInterface` abstrae persistencia de identificadores procesados.

`SamlReplayGuard` rechaza cualquier identifier ya registrado y almacena nuevos IDs con expiración explícita.

La expiración real/cleanup pertenece a infraestructura.

## Seguridad

- trust se resuelve antes de aceptar firma;
- no existe confianza automática en certificados embebidos;
- replay store es obligatorio para protección productiva;
- response/assertion signing policy es explícita;
- no existe dependencia de Keycloak, OneLogin o SimpleSAMLphp;
- no se loguean claves ni certificados completos.

## Fuera de alcance de I6

- implementación concreta de XMLDSig/canonicalization;
- XML Encryption;
- persistent replay adapter;
- metadata trust rotation automática;
- session creation.

## Criterios de aceptación

- trusted + valid signature aceptada;
- untrusted no invoca verifier;
- política puede exigir Response y Assertion firmadas;
- missing signed assertion falla;
- replay detectado;
- contratos neutrales;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
