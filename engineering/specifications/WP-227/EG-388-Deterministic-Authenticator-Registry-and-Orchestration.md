---
id: EG-388
title: Registro determinista de autenticadores y orquestación
summary: Define el registro exclusivo por tipo de credencial, la selección determinista y el aislamiento de fallos técnicos de WP-227 I4.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-05
updated: 2026-08-05
work_package: WP-227
tags:
  - security
  - authentication
  - authenticator
  - orchestration
  - registry
depends_on:
  - EG-387
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-388 — Registro determinista de autenticadores y orquestación

## 1. Objetivo

Definir una frontera de ejecución determinista para seleccionar un autenticador por tipo de credencial, ejecutar la autenticación y convertir fallos técnicos en resultados públicos sanitizados.

## 2. Propiedad exclusiva del tipo de credencial

Cada tipo de credencial pertenece a un único autenticador registrado. El registro rechaza identificadores duplicados, declaraciones repetidas y tipos ya asignados. Las estrategias de fallback o cadena deberán implementarse en el futuro mediante un autenticador compuesto explícito, no mediante ambigüedad implícita del registro.

## 3. Orden y resolución

El registro conserva el orden de inserción para inspección y resuelve en tiempo constante por el valor canónico de `CredentialType`. La resolución no depende de prioridades ocultas, orden de descubrimiento del contenedor ni comportamiento del sistema de archivos.

## 4. Fallos funcionales y técnicos

Los rechazos esperables permanecen en `AuthenticationResult`. Una excepción lanzada por un autenticador se considera fallo técnico: el orquestador la entrega a `AuthenticationTechnicalFailureHandlerInterface` para observabilidad interna y devuelve únicamente `InfrastructureFailure` al consumidor.

El manejador nulo predeterminado evita imponer logging, auditoría o Event Dispatcher al núcleo. Adaptadores futuros podrán integrar esos subsistemas sin cambiar el contrato de autenticación.

## 5. Límites

I4 no incorpora sesión, HTTP, middleware, proveedores concretos, descubrimiento automático, persistencia, reintentos ni autorización.

## 6. Criterios de aceptación

- Identificadores estables y validados.
- Al menos un tipo de credencial por autenticador.
- Propiedad exclusiva de cada tipo.
- Resolución determinista.
- Rechazos funcionales preservados.
- Excepciones técnicas sanitizadas y observables internamente.
- PHPUnit y PHPStan limpios.
- SIF Builder sin diagnósticos.
