---
id: EG-428
title: Requisitos ABAC y composición contextual de autorización
summary: Define atributos de subject, resource y environment y requisitos ABAC fail-closed sobre el motor de autorización existente.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - abac
  - attributes
  - context
depends_on:
  - EG-427
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-428 — Requisitos ABAC y composición contextual

## Objetivo

Incorporar evaluación declarativa de atributos de sujeto, recurso y entorno sin convertir ABAC en un motor de decisiones independiente.

## Ámbitos

Se definen tres ámbitos:

- `subject`: atributos del principal;
- `resource`: atributos del recurso protegido;
- `environment`: atributos contextuales de la operación.

## Subject

Los atributos del sujeto se obtienen mediante `AuthorizationAttributeProviderInterface`.

La implementación puede respaldar ese provider con almacenamiento local, claims externos, LDAP, Keycloak u otra fuente sin acoplar el Core.

## Resource y Environment

Los atributos del recurso y del entorno son aportados explícitamente por el caller.

No se consultan globales ni singletons.

## Comparaciones

Se soportan:

- igualdad estricta;
- desigualdad estricta;
- comparaciones numéricas mayores/menores.

Las comparaciones numéricas requieren valores realmente `int` o `float`. No se realizan conversiones implícitas desde strings.

## Fail closed

Un atributo ausente nunca satisface un requisito.

La diferencia de tipo tampoco produce coincidencia implícita.

## AbacAuthorizationPolicy

La policy:

1. obtiene atributos del sujeto;
2. recibe contexto de recurso y entorno;
3. evalúa requisitos;
4. devuelve únicamente satisfacción booleana.

No produce `AuthorizationDecision`.

## Seguridad

ABAC no:

- autentica;
- modifica principal;
- muta sesión;
- eleva `AuthenticationLevel`;
- ejecuta expresiones arbitrarias;
- evalúa código dinámico.

## Criterios de aceptación

- Ámbitos subject/resource/environment.
- Atributos ausentes fail-closed.
- Comparaciones type-safe.
- Composición contextual determinística.
- Sin segundo decision engine.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
