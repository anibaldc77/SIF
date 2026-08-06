---
id: WP-228-I1-REVIEW
title: Revisión de arquitectura WP-228 I1
summary: Revisa la frontera neutral entre proveedores de identidad, estados de cuenta y la futura autenticación por contraseña.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - review
  - security
  - identity-provider
  - password-authentication
depends_on:
  - EG-393
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I1 — Architecture Review

## Resultado

La implementación establece una frontera mínima y pública para proveedores de identidad. El contrato resuelve identidades sin conocer almacenamiento, modelos de usuario, contraseñas ni protocolos federados.

## Evaluación arquitectónica

`IdentityLookupKey` conserva la semántica específica del proveedor y evita una normalización global incorrecta. `IdentityProviderResult` representa ausencia de forma explícita y elimina contratos anulables. `IdentityProviderRecord` separa identidad neutral de estado operativo de cuenta.

## Compatibilidad

I1 agrega tipos y contratos nuevos bajo `Foundation\\Security`. No modifica interfaces de WP-227 ni APIs públicas anteriores.

## Riesgos controlados

- No se exponen secretos.
- No se fija una tabla de usuarios.
- No se confunde un proveedor local con un proveedor OIDC.
- No se incorporan roles ni permisos al registro de identidad.

## Próxima implementación

I2 deberá definir credenciales de contraseña, manejo de secretos y reglas de redacción antes de introducir hashing o autenticadores concretos.
