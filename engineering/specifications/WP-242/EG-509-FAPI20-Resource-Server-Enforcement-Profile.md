---
id: EG-509
title: FAPI 2.0 Resource Server Enforcement Profile
summary: Define requisitos, contexto y enforcement FAPI para Resource Servers protegidos, reutilizando la validación OAuth existente sin duplicar token processing.
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
  - resource-server
depends_on:
  - EG-508
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-509 — FAPI 2.0 Resource Server Enforcement Profile

## Objetivo

Aplicar reglas FAPI al Resource Server reutilizando la frontera de Protected Resource Validation existente en SIF.

## Requirements

`FapiResourceServerSecurityRequirements` expresa:

- sender constraint obligatorio;
- exact resource matching;
- token activo;
- rechazo de bearer downgrade;
- audience validation.

## Request Context

`FapiResourceServerRequestContext` representa:

- resource;
- client id;
- subject;
- token activity;
- sender constraint validation;
- audience validation.

## Assessment

`FapiResourceServerAssessment` expresa conformidad y violaciones.

## Contratos

- `FapiResourceServerPolicyInterface`;
- `FapiResourceServerSecurityRequirementsProviderInterface`;
- `FapiProtectedResourceEnforcementInterface`.

## Compatibilidad

`FapiResourceServerPolicyInterface::validateConfiguration()` se conserva desde I1.

I5 agrega `assess()` sin ruptura.

## Reutilización

El procesamiento del access token, DPoP y sender constraint permanece en WP-240.

I5 únicamente evalúa y exige el perfil FAPI sobre resultados ya validados.

## Seguridad

Implementaciones productivas deberán:

- rechazar tokens inactivos;
- validar audience/resource;
- exigir sender constraint;
- impedir bearer downgrade;
- rechazar acceso cuando la validación del sender constraint no corresponda al recurso y cliente esperados.

## Neutralidad

Foundation no conoce middleware HTTP, framework web, storage, Redis, JWT library ni TLS termination.

## Criterios de aceptación

Requirements tipados, context/assessment explícitos, compatibilidad I1 preservada, enforcement contract, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
