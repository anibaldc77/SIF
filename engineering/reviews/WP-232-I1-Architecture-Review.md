---
id: WP-232-I1-REVIEW
title: WP-232 I1 Architecture Review
summary: Revisa la arquitectura avanzada de permisos, roles y atributos construida sobre el motor de autorización de WP-227.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - permissions
  - roles
  - architecture-review
depends_on:
  - EG-425
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-232 I1 Architecture Review

## Alcance revisado

Se revisa la incorporación de permisos, roles y atributos como entradas del motor de autorización existente.

## Hallazgos

- WP-227 conserva la responsabilidad de decisión.
- Los nuevos value objects no ejecutan autorización.
- Los resolvers son neutrales respecto de persistencia y proveedores externos.
- Los identificadores son canónicos e inmutables.
- RBAC y ABAC quedan preparados sin imponer un modelo de almacenamiento.

## Riesgo evitado

Duplicar policies y decision engine en WP-232 produciría dos fuentes de verdad y reglas incompatibles.

La arquitectura adoptada mantiene un único punto semántico de decisión y amplía solamente sus entradas.

## Decisión

La arquitectura es apta para continuar hacia role hierarchy y permission resolution en I2.
