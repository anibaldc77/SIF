---
id: EG-496
title: Advanced OAuth Security Product Completion
summary: Consolida PAR, RAR, JAR, DPoP, sender-constrained access tokens y resource server enforcement como superficie coherente de seguridad OAuth avanzada.
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
  - product-completion
depends_on:
  - EG-495
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-496 — Advanced OAuth Security Product Completion

## Objetivo

Cerrar WP-240 mediante una superficie coherente y verificable de capacidades OAuth avanzadas construidas incrementalmente en I1-I7.

## Capacidades consolidadas

La superficie de producto reconoce explícitamente:

- Pushed Authorization Requests (PAR);
- Rich Authorization Requests (RAR);
- JWT-Secured Authorization Requests (JAR);
- DPoP;
- sender-constrained access tokens;
- resource server enforcement.

## Product Profile

`OAuthAdvancedSecurityProductProfile` permite expresar requisitos de seguridad del perfil sin introducir dependencias de infraestructura.

El perfil puede exigir PKCE, PAR y sender constraint, manteniendo la política separada de los adapters concretos.

## Readiness

`OAuthAdvancedSecurityReadinessReport` representa si el conjunto requerido se encuentra disponible y qué capacidades faltan.

`OAuthAdvancedSecurityReadinessEvaluatorInterface` define la frontera para evaluaciones de preparación de producto.

## Neutralidad arquitectónica

Foundation no prescribe:

- framework HTTP;
- middleware;
- almacenamiento;
- Redis;
- base de datos;
- implementación JWT/JWS;
- proveedor de identidad;
- librería criptográfica.

## Criterios de aceptación

WP-240 se considera completo cuando:

1. las capacidades I1-I7 permanecen representadas por contratos tipados;
2. existe una superficie de product profile y readiness;
3. las pruebas de product completion verifican la composición;
4. PHPUnit y PHPStan quedan limpios;
5. Composer validation queda limpia;
6. SIF Builder genera y valida artefactos sin diagnósticos;
7. `git diff --check` no informa errores.
