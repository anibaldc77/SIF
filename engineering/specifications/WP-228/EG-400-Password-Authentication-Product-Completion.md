---
id: EG-400
title: Password Authentication Product Completion
summary: Define el cierre de producto, compatibilidad y hardening del subsistema de proveedores de identidad y autenticación por contraseña.
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - security
  - identity-provider
  - password
  - authentication
  - completion
depends_on:
  - EG-393
  - EG-394
  - EG-395
  - EG-396
  - EG-397
  - EG-398
  - EG-399
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-400 — Password Authentication Product Completion

## Propósito

Cerrar WP-228 como subsistema de autenticación por contraseña modular, seguro y apto para integración productiva, conservando la independencia respecto de BaseModel, PDO, LDAP, OpenID Connect, Keycloak y cualquier almacenamiento concreto.

## Invariantes de cierre

- El proveedor de identidad y el almacenamiento de hashes permanecen separados por contratos.
- Las contraseñas no se serializan, registran ni incorporan a respuestas, eventos o snapshots.
- Los hashes se generan y verifican exclusivamente mediante adaptadores criptográficos explícitos.
- Las identidades inexistentes, hashes ausentes y contraseñas incorrectas producen rechazos públicos indistinguibles.
- Las cuentas deshabilitadas o bloqueadas fallan de forma cerrada.
- La protección de intentos puede sustituirse por una implementación distribuida sin modificar el autenticador.
- El rehash se ejecuta únicamente después de una verificación correcta y el almacén recibe solo material derivado.
- El login exitoso establece el principal y solicita regeneración de sesión.
- El logout elimina el principal y solicita regeneración de sesión.
- Las respuestas HTTP de autenticación son no almacenables y no exponen secretos.

## Prueba de producto

La prueba de integración debe verificar con componentes reales:

1. resolución de identidad activa;
2. verificación nativa de contraseña;
3. establecimiento del principal en sesión;
4. actualización de un hash bcrypt de costo inferior;
5. rechazo genérico de contraseña inválida sin mutar sesión;
6. cierre de sesión y eliminación del principal.

## Compatibilidad

WP-228 es aditivo y opt-in. No modifica los contratos públicos de WP-226 ni WP-227. Las aplicaciones existentes pueden continuar sin registrar proveedores de identidad, autenticador de contraseña, protección de intentos o endpoints HTTP.

## Extensibilidad

Los contratos admiten adaptadores futuros para BaseModel, PDO, LDAP, Active Directory, servicios remotos y almacenes distribuidos. Keycloak y OpenID Connect deben integrarse mediante proveedores externos y no como dependencias del núcleo de contraseña.
