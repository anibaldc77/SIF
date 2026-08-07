---
id: EG-463
title: Mapeo de identidad SAML, integración de autenticación y establecimiento de sesión
summary: Define la conversión de assertions validadas a identidad SIF y la frontera explícita para establecimiento de sesión.
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
  - identity
  - authentication
  - session
depends_on:
  - EG-462
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-463 — SAML Identity Mapping and Authentication Integration

## Objetivo

Separar la interpretación de una assertion SAML de la creación efectiva de una identidad autenticada y de una sesión local.

## Identity mapping

`SamlIdentityMapperInterface` transforma una `SamlAssertion` ya validada en `SamlAuthenticatedIdentity`.

La implementación por defecto utiliza `NameID` como identificador de subject.

Las aplicaciones pueden reemplazar el mapper para resolver identidad local, subject linking o atributos específicos.

## Attributes

Los atributos son explícitos y se pasan como un mapa tipado.

I7 no realiza parsing de `AttributeStatement`; ese trabajo puede agregarse en infraestructura o una extensión posterior.

## Session boundary

`SamlSessionEstablisherInterface` representa la frontera con el subsistema de sesiones de SIF.

SAML no llama `session_start`, no crea cookies y no conoce detalles de storage.

## Authentication coordinator

`SamlAuthenticationCoordinator` ejecuta:

1. identity mapping;
2. session establishment;
3. retorna `SamlAuthenticationResult`.

La entrada debe ser una assertion cuya firma, trust y semántica ya fueron validadas por etapas anteriores.

## Seguridad

- parsers no crean sesiones;
- identity mapping es reemplazable;
- session establishment se inyecta;
- no existe dependencia de proveedor;
- no existe acoplamiento directo a PHP sessions.

## Criterios de aceptación

- NameID mapeado;
- atributos preservados;
- session establishment ocurre después del mapping;
- resultado contiene identidad;
- parsers no crean sesión;
- integración framework-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
