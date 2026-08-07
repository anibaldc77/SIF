---
id: WP-236-MIGRATION-GUIDE
title: Guía de adopción de federación SAML 2.0
summary: Describe la adopción gradual de SAML 2.0 en aplicaciones SIF y la implementación de adapters productivos.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-236
tags:
  - security
  - saml
  - federation
  - migration
depends_on:
  - EG-464
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-236

## Requisitos

Una aplicación SIF que adopte SAML debe configurar:

- metadata del Service Provider;
- metadata/trust del Identity Provider;
- Assertion Consumer Service;
- replay store;
- signature verifier productivo;
- identity mapper;
- session establisher.

## Metadata

Implementar `SamlIdentityProviderMetadataProviderInterface`.

El fetch remoto debe realizarse fuera de Foundation con:

- TLS;
- timeout;
- límites de tamaño;
- cache;
- refresh controlado;
- validación previa al reemplazo.

## Trust store

Implementar `SamlTrustStoreInterface`.

La confianza debe vincular:

- IdP entityID;
- fingerprints autorizados.

Durante rotación puede existir más de un fingerprint confiable.

## XML Signature

Implementar `SamlXmlSignatureVerifierInterface` mediante una biblioteca XMLDSig madura o infraestructura equivalente.

No implementar canonicalization manual ad hoc en una aplicación.

Debe validarse:

- SignedInfo;
- Reference URI;
- digest;
- canonicalization;
- transforms permitidos;
- signature algorithm;
- certificado esperado.

## Replay store

Implementar `SamlReplayStoreInterface` en almacenamiento durable y compartido.

En despliegues multi-node no debe usarse memoria local como protección productiva.

## Identity mapping

Reemplazar `DefaultSamlIdentityMapper` cuando el `NameID` no sea la clave local apropiada.

Aplicar allowlist a atributos confiables.

## Session establishment

Implementar `SamlSessionEstablisherInterface` reutilizando la foundation de sesiones y autenticación de SIF.

## Orden obligatorio

No establecer sesión hasta que:

1. Response sea válida;
2. Assertion sea válida;
3. trust/firma sea válida;
4. IDs no sean replay;
5. identidad haya sido mapeada.

## Keycloak / Entra / ADFS / Okta

Cada proveedor debe integrarse mediante metadata y adapters externos.

No agregar clases específicas de proveedor a `Foundation\Security\Saml`.
