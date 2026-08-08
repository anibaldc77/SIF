---
id: WP-237-ADOPTION-GUIDE
title: Guía de adopción SCIM 2.0 Identity Provisioning
summary: Describe la adopción de SCIM 2.0 en aplicaciones SIF y la implementación de adapters productivos.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-237
tags:
  - security
  - scim
  - provisioning
  - adoption
depends_on:
  - EG-472
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-237

## Objetivo

Integrar un endpoint o adapter SCIM sobre Foundation sin introducir dependencias de transporte o proveedor en el Core.

## Discovery

Exponer desde el adapter:

- `/ServiceProviderConfig`;
- `/ResourceTypes`;
- `/Schemas`.

La representación debe derivarse de los contratos y modelos definidos en WP-237.

## Users y Groups

Implementar:

- `ScimUserProvisionerInterface`;
- `ScimGroupProvisionerInterface`.

La aplicación decide cómo traducir el recurso SCIM a su identidad local.

## Query

Implementar `ScimQueryExecutorInterface`.

El adapter de persistencia debe traducir filtros y sorting mediante parámetros seguros, nunca concatenando SQL desde expresiones SCIM.

## PATCH

Implementar `ScimPatchValidatorInterface` y `ScimPatchApplierInterface`.

Validar allowlists de atributos y mutabilidad antes de aplicar cambios.

## Bulk

Implementar `ScimBulkValidatorInterface` y `ScimBulkExecutorInterface`.

Definir límites explícitos:

- cantidad máxima de operaciones;
- tamaño máximo del payload;
- tiempo máximo;
- failOnErrors;
- política de transacciones.

## Versioning

Generar `ScimResourceVersion` desde una revisión opaca del storage.

Combinar `If-Match` con escritura atómica para impedir lost updates.

## Lifecycle

Aplicar `ScimLifecyclePlanner` y conectar:

- membership consistency;
- event/audit publisher;
- provisioners.

## Providers empresariales

Microsoft Entra ID, Okta, Keycloak y otros clientes SCIM deben interoperar mediante la capa HTTP/adapters.

No agregar código específico de proveedor a `Foundation\Security\Scim`.

## Seguridad mínima

- autenticar y autorizar el endpoint SCIM;
- limitar atributos escribibles;
- proteger secretos/tokens;
- rate limit;
- request size limit;
- audit;
- masking de datos sensibles;
- TLS obligatorio;
- control optimista de concurrencia;
- límites de Bulk.
