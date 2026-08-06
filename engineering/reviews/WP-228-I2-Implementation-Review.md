---
id: WP-228-I2-REVIEW
title: Revisión de implementación WP-228 I2
summary: Revisa la representación de contraseñas, hashes almacenados y resultados de verificación con redacción y contratos desacoplados.
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
  - secret-handling
depends_on:
  - EG-394
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I2 — Implementation Review

## Resultado

La implementación separa credencial recibida, secreto sensible, hash almacenado y resultado de verificación. Ningún contrato fija un algoritmo, una tabla de usuarios o una API de persistencia.

## Evaluación arquitectónica

`PasswordSecret` concentra las restricciones de exposición y redacción. `PasswordCredential` implementa el contrato general de WP-227 sin ampliar `CredentialInterface`. `StoredPasswordHash` conserva metadata canónica y entrega el hash codificado únicamente a un callback. `PasswordVerifierInterface` permite adaptadores locales, remotos o respaldados por hardware.

## Decisiones de seguridad

La implementación evita prometer borrado físico de memoria, garantía que PHP no puede ofrecer de forma portable. En su lugar reduce copias accidentales, impide serialización implícita y redacta depuración. Los mensajes de excepción no contienen secretos.

## Compatibilidad

I2 agrega tipos nuevos bajo `Foundation\\Security` y no modifica interfaces públicas anteriores. Los verificadores futuros dependerán del contrato; el Core no dependerá de implementaciones concretas.

## Riesgos controlados

- No se implementa comparación casera de hashes.
- No se expone la contraseña mediante `__toString`.
- No se serializan credenciales en sesión.
- La recomendación de rehash no puede existir para una verificación rechazada.

## Próxima implementación

I3 deberá incorporar política de hashing e implementación nativa PHP con algoritmos permitidos, validación de opciones, rehash y pruebas de interoperabilidad, sin mezclar todavía resolución de identidad con autenticación completa.
