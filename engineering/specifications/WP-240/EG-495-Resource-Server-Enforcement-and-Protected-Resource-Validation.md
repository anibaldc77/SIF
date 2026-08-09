---
id: EG-495
title: Resource Server Enforcement and Protected Resource Validation
summary: Define la frontera neutral de resource server para validar access tokens, DPoP y sender constraints antes de autorizar recursos protegidos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-240
tags:
  - security
  - oauth
  - resource-server
  - dpop
depends_on:
  - EG-494
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-495 — Resource Server Enforcement and Protected Resource Validation

## Objetivo
Definir la frontera de protección de recursos que consume las capacidades OAuth avanzadas sin acoplar Foundation a un framework HTTP.

## Modelo
`OAuthProtectedResourceRequest` representa método, URI, access token y proof DPoP opcional.

`OAuthResourceServerPolicy` expresa si el recurso exige DPoP y sender constraint.

`OAuthProtectedResourceValidationResult` explicita autorización, sujeto, cliente y validación del sender constraint.

## Contratos
- `OAuthProtectedResourceValidatorInterface`;
- `OAuthResourceServerPolicyProviderInterface`;
- `OAuthAccessTokenResolverInterface`.

## Reglas
Cuando la política exige sender constraint, una autorización válida requiere que la proof presentada sea válida y corresponda a la confirmation del access token.

## Neutralidad
Foundation no depende de middleware HTTP, PSR-7, Symfony, Laravel, persistencia, Redis ni una implementación concreta de JWT/JWS.

## Criterios de aceptación
Modelos tipados, policy explícita, contratos de resolución y validación, sender constraint visible en el resultado y quality gates limpios.
