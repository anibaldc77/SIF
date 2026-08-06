---
id: EG-387
title: Authentication Credentials Requests Results and Failure Contracts
summary: Specifies the neutral credential, authentication request, deterministic result and sanitized failure contracts delivered by WP-227 I3.
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
  - credentials
  - results
  - specification
depends_on:
  - EG-386
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-387 — Contratos de credenciales, solicitudes, resultados y fallos de autenticación

## Estado

Draft for Review

## Objetivo

Definir contratos inmutables y neutrales para transportar credenciales, representar solicitudes de autenticación y comunicar resultados deterministas sin exponer material sensible ni acoplar el núcleo a un proveedor concreto.

## Decisiones

- `CredentialInterface` expone únicamente un tipo estable de credencial.
- El contenido secreto permanece encapsulado por la implementación concreta y nunca forma parte de snapshots o metadatos del núcleo.
- `AuthenticationRequest` transporta identificador, credencial e instante normalizado a UTC.
- `AuthenticationResult` representa exactamente un resultado exitoso o fallido.
- Los rechazos esperables se comunican mediante `AuthenticationFailureReason`, no mediante excepciones.
- Los fallos de infraestructura quedan diferenciados de credenciales inválidas.
- I3 no incorpora autenticadores, registros de proveedores, persistencia ni transporte HTTP.

## Invariantes

1. Una solicitud debe tener un identificador no vacío y libre de caracteres de control.
2. Un tipo de credencial debe ser un identificador estable en minúsculas.
3. Los metadatos de una solicitud nunca serializan el payload de la credencial.
4. Un resultado exitoso contiene un `AuthenticatedPrincipal` y ningún fallo.
5. Un resultado fallido contiene un motivo sanitizado y ningún principal.
6. Contraseñas, tokens, secretos, hashes y assertions no pueden aparecer en resultados o diagnósticos públicos.

## Compatibilidad futura

El contrato permite adaptadores de contraseña, API key, JWT, OAuth, OpenID Connect, LDAP, WebAuthn y mecanismos institucionales sin modificar el núcleo.
