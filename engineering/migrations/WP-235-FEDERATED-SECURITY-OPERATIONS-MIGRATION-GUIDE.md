---
id: WP-235-MIGRATION-GUIDE
title: Guía de adopción de operaciones de seguridad federada
summary: Describe la integración gradual de revocación, journal, retry y acciones administrativas en aplicaciones SIF.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - migration
depends_on:
  - EG-456
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-235

## Impacto

WP-235 es opt-in y complementa WP-234.

Las aplicaciones pueden adoptar revocación local primero y agregar operaciones remotas posteriormente.

## Journal

Implementar `FederatedRevocationJournalInterface` usando almacenamiento durable.

Recomendaciones:

- clave única por operation id;
- escritura atómica;
- timestamps confiables;
- retención definida;
- datos mínimos necesarios.

## Session revocation

Implementar `FederatedSessionRevokerInterface` reutilizando el subsistema de sesiones existente.

## Provider revocation

Implementar:

- `FederatedProviderRevocationCapabilityProviderInterface`;
- `FederatedProviderRevocationAdapterInterface`.

El adapter debe mapear respuestas remotas a:

- transient;
- permanent;
- unsupported.

## Retry

Usar `FederatedRevocationRetryAdvisor` para calcular elegibilidad.

El framework no duerme ni agenda tareas. Un worker o scheduler externo debe decidir cuándo reintentar.

## Administración

La UI/CLI host debe:

- autorizar al operador;
- requerir confirmación;
- construir operation id;
- invocar los commands;
- presentar resultados estructurados.

## Keycloak y otros proveedores

La integración se realiza mediante adapters externos. No agregar dependencias de proveedor dentro de Foundation.

## Seguridad operacional

- exigir autorización para revocaciones;
- no aceptar operation ids arbitrarios sin validación;
- no loguear tokens;
- usar TLS;
- establecer timeout;
- limitar retries;
- auditar acciones administrativas;
- proteger el journal contra modificación no autorizada.
