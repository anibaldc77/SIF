---
id: EG-036
title: Repository Policy Analyzer
summary: Aplicar reglas institucionales explícitas e inmutables sobre el conjunto documental descubierto por SIF Builder, sin incorporar políticas arbitrarias dentro del Engine ni leer configuración global.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - repository
  - policy
  - analyzer
work_package: WP-106
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-036 — Repository Policy Analyzer

## Estado

- Work Package: WP-106
- Incremento: 4
- Versión: 1.0.0
- Estado: Implemented
- Analizador: `repository.policy`

## 1. Propósito

Aplicar reglas institucionales explícitas e inmutables sobre el conjunto documental descubierto por SIF Builder, sin incorporar políticas arbitrarias dentro del Engine ni leer configuración global.

## 2. Arquitectura

La implementación separa mecanismo y política:

- `RepositoryPolicyAnalyzer`: adaptación al contrato `AnalyzerInterface`;
- `RepositoryPolicyInspector`: ejecución y orden determinista;
- `RepositoryPolicySet`: colección inmutable y sin identificadores duplicados;
- `RepositoryPolicyRuleInterface`: contrato de reglas componibles;
- reglas incorporadas: `RequiredCategoryPolicy` y `RequiredMetadataPolicy`.

La composición CLI registra el analizador con un conjunto vacío. Esto conserva compatibilidad y evita imponer reglas institucionales sin una decisión explícita de composición.

## 3. Diagnósticos

| Código | Severidad | Condición |
|---|---|---|
| `ANALYZER-104` | Error | No existe workspace o registro de metadatos |
| `REPPOL-201` | Configurable | Falta una categoría requerida en el repositorio |
| `REPPOL-202` | Configurable | Un documento alcanzado por una regla carece de un metadato requerido |

Cada diagnóstico funcional incluye `rule_id` en su contexto estructurado.

## 4. Determinismo

Las políticas se normalizan por identificador y los hallazgos se ordenan por código, regla, documento, ruta y mensaje mediante una identidad estable.

## 5. Compatibilidad

No se modifican contratos públicos del Engine, metadatos, CLI o reporters. Las políticas concretas se inyectan por constructor.

## 6. Criterios de aceptación

- registro visible mediante `php bin/sif-builder list`;
- precondición expresada como `ANALYZER-104`;
- conjunto vacío no invalida el repositorio;
- reglas configurables por composición;
- rechazo de identificadores duplicados;
- diagnósticos con `rule_id` serializable;
- orden estable entre ejecuciones;
- PHPUnit y PHPStan nivel 8 sin errores.
