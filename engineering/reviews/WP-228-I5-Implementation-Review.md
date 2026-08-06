---
id: WP-228-I5-REVIEW
title: Revisión de implementación WP-228 I5
summary: Revisa los contratos de protección de intentos, la implementación en memoria y su integración compatible con el autenticador de contraseña.
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
  - throttling
  - lockout
depends_on:
  - EG-397
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I5 — Implementation Review

## Resultado

La implementación incorpora protección contra intentos repetidos sin convertir `PasswordAuthenticator` en un repositorio de contadores ni acoplarlo a una tecnología de cache.

## Evaluación arquitectónica

`PasswordAttemptProtectorInterface` mantiene la política operativa fuera del autenticador. El contrato trabaja con una clave compuesta por proveedor y lookup, lo que evita colisiones entre directorios y permite implementaciones multi-tenant posteriores.

La incorporación al constructor es opcional y se ubica al final de la firma, preservando compatibilidad fuente con I4. `NullPasswordAttemptProtector` mantiene el comportamiento anterior cuando la aplicación no configura protección.

## Seguridad

El protector se consulta antes de resolver identidad o hash. Al superar el umbral, el flujo se detiene de manera fail-closed y no consume proveedores. Los fallos de identidad desconocida, hash ausente, contraseña inválida y cuenta no activa incrementan la misma protección externa sin exponer el motivo exacto.

`PasswordAttemptKey` conserva el lookup para adaptadores que lo requieran, pero su depuración sólo publica una huella. Ningún componente recibe o almacena la contraseña.

## Implementación en memoria

`InMemoryPasswordAttemptProtector` es intencionalmente acotado a procesos individuales. Resulta adecuado como referencia semántica y para pruebas, pero una aplicación con múltiples workers deberá reemplazarlo por un adaptador atómico y compartido.

## Compatibilidad

No se modifican interfaces previas ni resultados públicos. La ampliación del constructor de `PasswordAuthenticator` es opcional y no altera llamadas existentes.

## Riesgos controlados

- Un adaptador distribuido deberá garantizar atomicidad para evitar carreras.
- El uso de lookup como única dimensión no limita todavía ataques distribuidos contra múltiples cuentas.
- El resultado público `Rejected` no expresa aún `retry_at`; la integración HTTP se posterga para una entrega posterior.

## Próxima implementación

I6 deberá incorporar contratos persistentes de cuenta y hash, adaptadores de repositorio y actualización transaccional de rehash sin acoplar el Core a BaseModel.
