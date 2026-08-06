---
id: EG-406
title: Protección contra abuso y eventos de seguridad para recuperación
summary: Define límites de solicitud y eventos sanitizados para los flujos de recuperación y verificación.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - abuse-protection
  - security-events
depends_on:
  - EG-401
  - EG-402
  - EG-403
  - EG-404
  - EG-405
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-406 — Recovery Abuse Protection and Security Events

## Objetivo

Incorporar protección contra solicitudes abusivas y observabilidad segura sin registrar tokens, lookups directos ni secretos.

## Decisiones normativas

- La protección se aplica antes de resolver la identidad.
- La clave combina proveedor, lookup y propósito, pero sólo expone una huella SHA-256.
- Los límites son configurables y el almacenamiento es reemplazable.
- La implementación en memoria es de referencia y no sustituye un backend distribuido.
- Los eventos sólo incluyen tipo, propósito, huella, instante e identificador de desafío opcional.
- Ningún evento contiene token, digest, contraseña o lookup directo.
- Las respuestas públicas permanecen genéricas incluso cuando una solicitud es bloqueada.
- Los fallos técnicos de observabilidad no deben transformar un rechazo seguro en una autorización.

## Compatibilidad

Los nuevos protectores y handlers se agregan como dependencias opcionales al final de los constructores existentes.

## Criterios de aceptación

- Bloqueo determinista después del umbral configurado.
- Separación por propósito.
- Eventos sanitizados.
- Compatibilidad hacia atrás.
- PHPUnit, PHPStan y Builder sin diagnósticos.
