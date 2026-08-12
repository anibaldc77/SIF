---
id: EG-573
title: Trust Resolution Caching Freshness and Metadata Consistency
summary: Define credential trust caching, freshness windows and metadata consistency boundaries without coupling Foundation to a concrete cache, transport, federation or persistence implementation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - caching
  - freshness
  - metadata
depends_on:
  - EG-572
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-573 — Trust Resolution Caching, Freshness and Metadata Consistency

## Objetivo

Definir boundaries verifier-side para reutilización segura de evidencia de trust y detección explícita de cambios relevantes en metadata.

## Resolution Evidence

`CredentialTrustResolutionEvidence` representa:

- trust-chain assessment;
- checked timestamp;
- source version;
- metadata fingerprint opcional.

## Cache Entry

`CredentialTrustCacheEntry` representa:

- cache key;
- evidence;
- stored timestamp;
- fresh-until;
- stale-until.

Fresh y usable-stale son estados distintos y explícitos.

## Metadata Snapshot

`CredentialTrustMetadataSnapshot` representa:

- entity id;
- version;
- fingerprint;
- retrieval timestamp;
- expiration opcional;
- attributes.

## Metadata Consistency

`CredentialTrustMetadataConsistencyResult` representa consistent, violations y warnings.

La comparación concreta permanece detrás de `CredentialTrustMetadataConsistencyPolicyInterface`.

## Contratos

- `CredentialTrustCacheInterface`;
- `CredentialTrustRefreshPolicyInterface`;
- `CredentialTrustMetadataSnapshotResolverInterface`;
- `CredentialTrustMetadataConsistencyPolicyInterface`.

## Seguridad

Una entrada stale no se considera trusted automáticamente. La policy de failure/resilience se definirá por separado.

Los cambios de identity, trust framework, accreditation, anchor o key material pueden clasificarse como violations según el profile aplicable.

## Neutralidad

Foundation no conoce Redis, Memcached, filesystem cache, HTTP client, OpenID Federation transport, PDO ni storage concreto.

## Compatibilidad

I5 agrega nuevas superficies sin modificar contracts de I1-I4.
