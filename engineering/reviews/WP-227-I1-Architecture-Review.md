---
id: WP-227-I1-REVIEW
title: WP-227 I1 Architecture Review
summary: Revisa las fronteras de confianza, la separación entre sesión, identidad, autenticación y autorización, y el contrato mínimo de principal definido para WP-227.
status: Draft for Review
version: 1.0.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-04
updated: 2026-08-04
work_package: WP-227
increment: I1
tags:
  - security
  - authentication
  - authorization
  - identity
  - principal
  - architecture
  - review
depends_on:
  - EG-385
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-227 I1 Architecture Review

## Alcance de la revisión

Esta revisión evalúa EG-385 y la implementación mínima de I1 como arquitectura gobernante para autenticación y autorización en SIF.

## Hallazgos

### Separación entre Session y Authentication

**Aceptado.** La sesión se mantiene como continuidad de estado y no como prueba de identidad. Se prohíbe utilizar `SessionId` como identificador de principal.

### Neutralidad de persistencia

**Aceptado.** El principal y la identidad no dependen de BaseModel, PDO, tablas de usuarios ni repositorios concretos.

### Separación Authentication/Authorization

**Aceptado.** La autenticación establece un principal confiable; la autorización produce una decisión sobre acción, recurso y contexto. Ninguna de las dos responsabilidades absorbe a la otra.

### Modelo inicial de principal

**Aceptado con alcance deliberadamente mínimo.** `PrincipalInterface`, `AuthenticationState` y `AnonymousPrincipal` permiten establecer la ausencia explícita de autenticación sin fijar prematuramente el modelo completo de identidad que corresponde a I2.

### Estado anónimo explícito

**Aceptado.** Cuando el runtime de seguridad esté habilitado, la ausencia de autenticación deberá representarse mediante `AnonymousPrincipal`, no mediante `null` ni objetos de aplicación improvisados.

### Extensibilidad

**Aceptado.** La arquitectura deja abiertos adaptadores para BaseModel, LDAP, Active Directory, API keys, JWT, OAuth, OpenID Connect, MFA y service accounts sin introducir dependencias ni semánticas específicas.

### Compatibilidad

**Aceptado.** WP-227 permanece opt-in y no modifica contratos de HTTP, Session, Controller, CLI o Skeleton existentes.

## Riesgos y controles obligatorios

1. I2 no debe convertir atributos del principal en un array mutable sin esquema ni reglas de normalización.
2. Los identificadores de identidad deben ser opacos y no asumir claves numéricas o correos electrónicos.
3. La evidencia de autenticación no debe contener secretos reutilizables.
4. El principal activo no puede almacenarse en singletons mutables o estado estático.
5. La futura persistencia en Session debe ser mínima, versionada y regenerar el identificador tras autenticación.
6. Los motivos de fallo deben diferenciar rechazo esperado de error técnico sin filtrar información sensible.
7. La autorización no debe reducirse a un helper booleano basado únicamente en roles.

## Verificación de implementación

La implementación I1 contiene únicamente:

- `AuthenticationState`;
- `PrincipalInterface`;
- `AnonymousPrincipal`;
- pruebas del estado anónimo y valores estables;
- una prueba arquitectónica que impide dependencia directa de `SessionId` desde Security.

No se detecta invasión de alcance hacia autenticadores, persistencia, middleware, roles o proveedores externos.

## Decisión

**Aprobado para validación técnica de I1.**

WP-227 puede avanzar a I2 una vez que PHPUnit, PHPStan y las verificaciones de estilo y diferencias resulten limpias para este incremento.
