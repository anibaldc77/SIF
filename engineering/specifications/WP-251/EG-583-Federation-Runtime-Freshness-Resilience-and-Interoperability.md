---
id: EG-583
title: Federation Runtime Freshness Resilience and Interoperability
summary: Define OpenID Federation runtime freshness, controlled stale-evidence fallback and interoperability readiness boundaries for OIDC, OpenID4VCI, OpenID4VP and wallet metadata.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - freshness
  - resilience
  - interoperability
depends_on:
  - EG-582
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-583 — Federation Runtime Freshness, Resilience and Interoperability

## Objetivo

Definir boundaries de runtime para freshness, fallback controlado y readiness de interoperabilidad de OpenID Federation.

## Runtime Evidence

`OpenIdFederationRuntimeEvidence` conserva entity id, resolution timestamp, fresh-until, stale-until y source version.

## Freshness

`OpenIdFederationRuntimeFreshnessStatus` distingue:

- fresh;
- stale usable;
- expired.

`OpenIdFederationRuntimeFreshnessPolicyInterface` desacopla la policy de freshness.

## Resilience

`OpenIdFederationRuntimeFailurePolicyInterface` decide si evidencia cacheada puede utilizarse ante un failure de resolución.

`DefaultOpenIdFederationRuntimeFailurePolicy` es fail-closed por defecto.

Stale usable requiere habilitación explícita y nunca permite evidencia expired.

## Interoperability

`OpenIdFederationInteroperabilityProfile` expresa compatibilidad esperada con:

- OpenID Connect;
- OpenID4VCI;
- OpenID4VP;
- wallet metadata.

## Operational Readiness

`OpenIdFederationRuntimeReadinessReport` y `OpenIdFederationRuntimeReadinessEvaluatorInterface` definen el deployment gate del runtime.

## Seguridad

Freshness protocol-specific no reemplaza caching ni high-assurance enforcement de WP-250.

En perfiles high-assurance la aplicación puede prohibir stale evidence aun cuando el runtime la considere stale-usable.

## Neutralidad

Foundation no implementa HTTP, retry loops, scheduler, JWT/JWS, Redis, PDO ni crypto concreto.

## Compatibilidad

I7 agrega runtime freshness/resilience/interoperability sin modificar I1-I6 ni WP-250.
