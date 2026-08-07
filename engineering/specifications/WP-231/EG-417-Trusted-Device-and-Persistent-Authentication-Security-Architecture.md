---
id: EG-417
title: Arquitectura de dispositivos confiables y autenticación persistente
summary: Define límites, vocabulario e invariantes para credenciales persistentes y concesiones de dispositivo confiable sin convertirlas en una sesión paralela ni en un bypass implícito de MFA.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - trusted-device
  - architecture
depends_on:
  - EG-416
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-417 — Arquitectura de dispositivos confiables y autenticación persistente

## Objetivo

Definir la arquitectura base para autenticación persistente de navegador y confianza de dispositivo, manteniendo ambos conceptos separados y subordinados al modelo canónico de autenticación, sesión y MFA de SIF.

## Decisión fundamental

Una credencial persistente y una concesión de dispositivo confiable no son equivalentes.

La credencial persistente permite intentar restaurar una autenticación cuando no existe una sesión válida. La concesión de dispositivo confiable representa una decisión de política sobre un dispositivo previamente reconocido.

Una credencial persistente válida **no implica** que el dispositivo sea confiable y una concesión de dispositivo confiable **no autentica** por sí sola a una identidad.

## Credencial persistente

El modelo utiliza selector y validador:

- el selector es material público de búsqueda;
- el validador permanece únicamente en el cliente;
- el servidor conserva exclusivamente un digest del validador;
- las implementaciones posteriores deberán rotar el validador después de cada uso exitoso;
- una discrepancia posterior a una rotación debe poder tratarse como posible replay o robo de credencial;
- la expiración absoluta no puede extenderse indefinidamente por actividad.

## Dispositivo confiable

La confianza de dispositivo:

- se representa mediante una concesión separada;
- posee identidad, identificador, vigencia y estado propios;
- no contiene nivel de autenticación;
- no modifica por sí sola `AuthenticationLevel`;
- no omite MFA salvo decisión explícita de una política de aplicación futura;
- puede revocarse independientemente de las credenciales persistentes.

## Persistencia

Los contratos de almacenamiento son neutrales respecto de:

- BaseModel;
- PDO;
- Redis;
- Cookie;
- Session;
- MFA;
- proveedores externos.

Los stores productivos podrán implementar persistencia relacional, cache distribuida u otros mecanismos sin afectar el dominio.

## Seguridad

Los snapshots deben usar huellas para identidad, selector e identificadores correlacionables. Nunca deben incluir:

- validador en claro;
- digest reutilizable;
- cookie completa;
- secretos MFA;
- identificadores directos de identidad.

## Compatibilidad

WP-231 es aditivo. No modifica contratos públicos de WP-226 a WP-230 y no habilita autenticación persistente de forma automática.

## Criterios de aceptación

- Modelos inmutables.
- Expiración normalizada a UTC.
- Separación explícita entre autenticación persistente y confianza de dispositivo.
- Contratos de almacenamiento neutrales.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
