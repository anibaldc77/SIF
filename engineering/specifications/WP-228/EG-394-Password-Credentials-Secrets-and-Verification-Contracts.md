---
id: EG-394
title: Credenciales de contraseña, secretos y contratos de verificación
summary: Define objetos sensibles, hashes almacenados y resultados de verificación sin fijar algoritmos ni persistencia concreta.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - security
  - password
  - credentials
  - secret-handling
depends_on:
  - EG-393
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-394 — Password Credentials, Secrets and Verification Contracts

## Objetivo

Definir la representación segura y transport-neutral de una contraseña recibida, un hash almacenado y el resultado de su verificación, sin acoplar el Core a algoritmos, extensiones PHP, persistencia ni proveedores de identidad concretos.

## Decisiones normativas

- La contraseña recibida se representa mediante `PasswordSecret` y nunca mediante una cadena pública en contratos de autenticación.
- `PasswordSecret` y `PasswordCredential` deben impedir serialización implícita y redactar su representación de depuración.
- El acceso al valor sensible se limita a callbacks de alcance acotado. Esto reduce exposición accidental, aunque PHP no permite garantizar borrado físico de todas las copias internas de una cadena.
- El hash almacenado se representa separadamente mediante `StoredPasswordHash`.
- El hash codificado se considera material sensible y no debe aparecer en depuración, logs ni excepciones.
- El identificador de algoritmo y sus parámetros son metadata; no obligan a bcrypt, Argon2 ni una implementación PHP concreta.
- La verificación devuelve un resultado explícito y puede recomendar rehash únicamente cuando la contraseña fue verificada.
- `PasswordVerifierInterface` no conoce repositorios, BaseModel, PDO ni ciclo HTTP.

## Límites de seguridad

La redacción reduce exposición accidental, pero no sustituye controles del runtime, aislamiento de procesos ni políticas de memoria segura. Ningún componente debe afirmar que una cadena PHP fue borrada físicamente de memoria.

## Impacto futuro

I3 podrá incorporar una implementación basada en `password_hash` y `password_verify`, políticas de hashing y rehash, manteniendo estos contratos como frontera estable. Adaptadores externos podrán implementar verificadores propios sin modificar el Core.

## Criterios de aceptación I2

- Contraseña y hash no aparecen en `__debugInfo`.
- La serialización implícita de credenciales y secretos es rechazada.
- Algoritmo y parámetros se normalizan de forma determinista.
- Verificación y recomendación de rehash son conceptos separados.
- PHPUnit, PHPStan y Builder finalizan sin diagnósticos.
