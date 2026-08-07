---
id: EG-454
title: Revocación remota coordinada, clasificación de retry y política operacional
summary: Integra outcomes remotos con política operacional y retry advisor para distinguir fallas transitorias, terminales y capabilities unsupported.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - retry
  - provider
depends_on:
  - EG-453
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-454 — Coordinated Remote Revocation, Retry Classification y Operational Policy

## Objetivo

Integrar la clasificación de outcomes remotos con la política de reintentos de WP-235.

## Provider policy

`FederatedProviderRevocationPolicy` determina:

- si un outcome es retryable;
- si un outcome es terminal;
- cómo tratar una capability unsupported.

Por defecto `unsupported` no se considera éxito ni terminal.

## Coordinator

`FederatedProviderRevocationCoordinator` ejecuta una capability mediante el service de I5 y produce `FederatedProviderRevocationAssessment`.

## Retry bridge

`FederatedRemoteRetryBridge` conecta el outcome remoto con `FederatedRevocationRetryAdvisor`.

Reglas:

- success: terminal, no retry;
- transient: retry policy;
- permanent: terminal, no retry;
- unsupported: no retry y no terminal por defecto;
- unsupported puede aceptarse como terminal sólo mediante política explícita.

## Seguridad

- no hay fallback silencioso;
- no hay loops de retry automáticos;
- no hay transporte HTTP;
- no hay lógica específica de proveedor;
- terminal y retryable son decisiones explícitas.

## Criterios de aceptación

- transient usa retry advisor;
- permanent termina;
- unsupported no se trata como éxito por defecto;
- policy puede aceptar unsupported explícitamente;
- success es terminal;
- sin scheduler/transport;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
