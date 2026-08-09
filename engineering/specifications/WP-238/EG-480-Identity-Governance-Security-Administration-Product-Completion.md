---
id: EG-480
title: Cierre de producto de Identity Governance y Security Administration
summary: Consolida entitlements, assignments, access reviews, workflow, SoD, riesgo, excepciones y fronteras de remediación como foundation empresarial neutral.
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
  - identity
  - access-review
  - risk
  - remediation
  - product-completion
depends_on:
  - EG-479
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-480 — Identity Governance & Security Administration Product Completion

## Objetivo

Cerrar WP-238 como foundation empresarial de gobierno de identidades y administración de seguridad, manteniendo una separación estricta respecto de Authorization, SCIM, storage, transporte y proveedores externos.

## Capacidades consolidadas

- subjects de gobierno;
- entitlements;
- access assignments;
- effective assignments;
- access review campaigns;
- scopes;
- reviewers;
- work items;
- workflow transitions;
- deadlines;
- delegation;
- escalation;
- segregation of duties;
- conflict detection;
- governance risk assessment;
- governance exceptions;
- risk acceptance;
- compensating controls;
- remediation planning boundary;
- expiration processing boundary;
- governance audit/event boundary.

## Separación de subsistemas

### Authorization

WP-238 gobierna qué accesos deberían existir o permanecer.

Authorization continúa resolviendo decisiones de acceso en runtime.

### SCIM

WP-238 puede producir decisiones de remediación.

SCIM continúa siendo una posible frontera para provisioning y deprovisioning.

### Audit / Event Dispatcher

WP-238 produce eventos de dominio a través de contratos.

La persistencia y distribución concreta pertenece a adapters externos.

## Orden conceptual recomendado

1. resolver assignments efectivos;
2. evaluar scope de governance;
3. generar/revisar work items;
4. evaluar SoD y riesgo;
5. aplicar excepciones vigentes;
6. producir decisiones;
7. planificar remediación;
8. procesar expiraciones;
9. publicar eventos/auditoría;
10. ejecutar cambios mediante adapters externos.

## Neutralidad

Foundation no debe contener:

- SQL;
- PDO;
- Redis concreto;
- HTTP clients;
- SDKs de proveedores;
- scheduler concreto;
- UI administrativa;
- ejecución directa de SCIM;
- ejecución directa de Authorization.

## Riesgos residuales

Adapters productivos deberán implementar:

- persistencia;
- workflow orchestration;
- notifications;
- scheduler;
- approval authorization;
- evidence/attestation;
- remediación efectiva;
- retries;
- observabilidad;
- controles compensatorios verificables.

## Criterios de aceptación

- interoperabilidad conceptual I1-I7;
- separation of concerns preservada;
- infraestructura neutral;
- provider-neutral;
- tests de integración limpios;
- PHPStan limpio;
- Builder sin diagnósticos.
