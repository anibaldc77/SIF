---
id: EG-477
title: Segregation of Duties, reglas de conflicto y evaluación de riesgo de gobierno
summary: Define reglas SoD, conflictos entre entitlements, niveles de riesgo y evaluación determinista sin remediación automática.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - sod
  - conflict
  - risk
depends_on:
  - EG-476
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-477 — Segregation of Duties, Conflict Rules and Governance Risk Evaluation

## Objetivo

Agregar detección determinista de conflictos de acceso y evaluación de riesgo sin ejecutar cambios sobre Authorization o SCIM.

## Segregation of Duties

`SegregationOfDutiesRule` define una combinación incompatible de dos entitlements distintos.

Cada regla incluye:

- id;
- entitlement izquierdo;
- entitlement derecho;
- risk level;
- descripción.

El conflicto es simétrico.

## Risk levels

`GovernanceRiskLevel` admite:

- low;
- medium;
- high;
- critical.

Cada nivel tiene un peso determinista.

## Conflict

`GovernanceConflict` vincula:

- subject;
- regla;
- dos effective assignments.

## Risk assessment

`GovernanceRiskAssessment` contiene los conflictos detectados y produce un score acumulado limitado a 100.

## Evaluator

`DefaultGovernanceRiskEvaluator`:

1. obtiene assignments efectivos;
2. obtiene reglas SoD;
3. compara pares de entitlements;
4. registra conflictos;
5. produce assessment.

El evaluator no revoca ni modifica accesos.

## Contracts

`SegregationOfDutiesRuleProviderInterface` abstrae la fuente de reglas.

`GovernanceRiskEvaluatorInterface` abstrae la evaluación.

## Separación de responsabilidades

I5 no:

- ejecuta remediation;
- bloquea Authorization;
- modifica SCIM;
- persiste conflictos;
- decide approval;
- envía alertas.

## Fuera de alcance

- reglas N-arias;
- exceptions/waivers;
- compensating controls;
- historical risk;
- risk thresholds policy;
- remediation.

## Criterios de aceptación

- conflict rule simétrica;
- risk weights deterministas;
- conflict detection;
- safe combinations no generan conflicto;
- score limitado a 100;
- contracts neutrales;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
