---
id: WP-228-I6-REVIEW
title: Revisión de implementación WP-228 I6
summary: Revisa los contratos de almacén de hashes y la coordinación de rehash integrada de forma compatible al autenticador.
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
  - password
  - rehash
  - persistence
depends_on:
  - EG-398
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I6 — Implementation Review

## Resultado

La implementación completa el rehash efectivo que I4 sólo podía notificar. La credencial verificada permanece dentro del límite de seguridad y el almacén recibe únicamente el hash de reemplazo.

## Evaluación arquitectónica

El contrato de almacén reutiliza la lectura existente y agrega una única mutación explícita. No se prescribe BaseModel, PDO, Redis ni una transacción concreta.

El coordinador encapsula hashing y escritura. `PasswordAuthenticator` conserva el handler previo como mecanismo de observación y agrega el coordinador como dependencia opcional final, evitando ruptura de llamadas existentes.

## Seguridad

El rehash sólo se invoca después de verificar contraseña, confirmar existencia de hash y validar estado activo. La implementación nula evita efectos secundarios cuando la aplicación no configura persistencia.

## Riesgos controlados

Los adaptadores reales deben resolver concurrencia optimista o bloqueo transaccional. I6 no intenta simular garantías que dependen del motor de persistencia.

## Próxima implementación

I7 deberá integrar login HTTP, sesión y respuestas seguras sobre el autenticador de contraseña, además de wiring de CLI y Skeleton.
