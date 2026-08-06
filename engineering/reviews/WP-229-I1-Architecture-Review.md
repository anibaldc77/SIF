---
id: WP-229-I1-REVIEW
title: Revisión arquitectónica WP-229 I1
summary: Revisa la arquitectura inicial para recuperación de cuentas y verificación de identidad mediante desafíos transitorios seguros.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - identity-verification
  - review
depends_on:
  - EG-401
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-229 I1 — Architecture Review

## Alcance revisado

I1 introduce únicamente el vocabulario y las fronteras públicas mínimas para desafíos de recuperación y verificación. No incorpora tokens, digest, consumo, revocación, entrega por correo/SMS ni persistencia concreta.

## Decisiones confirmadas

- Recuperación de contraseña y verificación de identidad comparten infraestructura, pero conservan propósitos no intercambiables.
- El sujeto se representa mediante una clave neutral cuya normalización pertenece al adaptador.
- Los snapshots exponen una huella del sujeto y nunca su valor directo.
- La expiración se normaliza a UTC y se evalúa de forma determinista.
- El contrato de almacenamiento permanece libre de BaseModel, PDO, Redis y proveedores de entrega.

## Riesgos controlados

- Enumeración de cuentas: las capas públicas futuras deberán responder de forma indistinguible.
- Reutilización cruzada: el propósito forma parte obligatoria del desafío.
- Exposición de secretos: I1 no incorpora token alguno y fija la prohibición normativa para implementaciones posteriores.
- Acoplamiento tecnológico: almacenamiento y entrega permanecen detrás de contratos.

## Resultado

La arquitectura es compatible con WP-226, WP-227 y WP-228, y permite avanzar a I2 sin modificar APIs públicas existentes.
