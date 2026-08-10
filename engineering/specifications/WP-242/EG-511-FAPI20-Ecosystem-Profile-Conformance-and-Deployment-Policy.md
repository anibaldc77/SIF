---
id: EG-511
title: FAPI 2.0 Ecosystem Profile Conformance and Deployment Policy
summary: Define perfiles de despliegue, evidencia de capacidades y readiness para ecosistemas FAPI sin acoplar Foundation a infraestructura o certificadores externos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - conformance
  - deployment
depends_on:
  - EG-510
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-511 — FAPI 2.0 Ecosystem Profile, Conformance and Deployment Policy

## Objetivo

Definir una frontera explícita entre las capacidades FAPI implementadas por SIF y los requisitos concretos de un ecosistema o despliegue.

## Deployment Profile

`FapiDeploymentProfile` identifica nombre, versión, capacidades requeridas y controles de seguridad requeridos.

No representa una certificación externa. Representa política declarativa de despliegue.

## Deployment Context

`FapiDeploymentContext` expresa evidencia disponible en runtime:

- capacidades disponibles;
- controles de seguridad activos.

## Conformance Assessment

`FapiDeploymentConformanceAssessment` separa:

- conformidad;
- capacidades faltantes;
- controles faltantes;
- warnings.

## Readiness

`FapiDeploymentReadinessReport` separa blocking issues de advisories.

Readiness no equivale a certificación formal por un organismo externo.

## Contratos

- `FapiDeploymentProfileProviderInterface`;
- `FapiDeploymentConformanceEvaluatorInterface`;
- `FapiDeploymentReadinessEvaluatorInterface`.

## Integración

El evaluator puede consumir evidencia proveniente de I1-I6: perfil del authorization server, client policy, PAR/PKCE, metadata, sender constraints, protected resources y message signing.

## Neutralidad

Foundation no conoce infraestructura, HTTP clients, persistence, certification endpoints ni proveedores externos.

## Criterios de aceptación

Modelos inmutables, contratos tipados, separación conformance/readiness, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
