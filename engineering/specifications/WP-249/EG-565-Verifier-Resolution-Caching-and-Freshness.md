---
id: EG-565
title: Verifier Resolution Caching and Freshness
summary: Define verifier-side credential status cache entries, freshness windows, refresh policy and resolution failure policy without coupling Foundation to a cache or transport implementation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - credential-status
  - verifier
  - cache
  - freshness
depends_on:
  - EG-564
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-565 — Verifier Resolution, Caching and Freshness

## Objetivo

Definir el boundary verifier-side para reutilización segura de resultados de credential status y comportamiento ante indisponibilidad de resolución.

## Cache entry

`CredentialStatusCacheEntry` contiene cache key, resultado, instante de almacenamiento, límite de freshness y límite máximo de stale usability.

## Freshness

Un entry puede encontrarse fresh, stale pero todavía utilizable por policy, o expirado.

## Failure policy

La indisponibilidad del origen no implica automáticamente fail-open. La policy decide entre fail-closed y utilización controlada de stale data cuando el perfil lo permita.

## Contratos

- `CredentialStatusCacheInterface`;
- `CredentialStatusRefreshPolicyInterface`;
- `CredentialStatusResolutionFailurePolicyInterface`.

## Neutralidad

Foundation no conoce Redis, Memcached, filesystem cache, HTTP client, queue o scheduler concretos.

## Seguridad

Los perfiles high-assurance pueden imponer fail-closed y ventanas stale nulas o mínimas. La aceptación de stale status debe ser explícita y auditable.

## Compatibilidad

I5 agrega tipos y contratos sin modificar contratos anteriores.

## Criterios de aceptación

Fresh/stale windows explícitas, failure policy separada, cache abstracta, neutralidad de infraestructura y validaciones verdes.
