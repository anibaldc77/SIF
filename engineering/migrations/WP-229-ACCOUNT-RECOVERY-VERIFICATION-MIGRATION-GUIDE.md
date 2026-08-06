---
id: WP-229-MIGRATION-GUIDE
title: WP-229 Account Recovery and Verification Migration Guide
summary: Describe la adopción incremental de recuperación de contraseña y verificación de identidad.
status: Draft for Review
version: 1.0.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - verification
  - migration
  - guide
depends_on:
  - EG-408
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-229 Account Recovery and Verification Migration Guide

## 1. Adopción opt-in

Las aplicaciones existentes continúan funcionando sin registrar servicios, endpoints o comandos de WP-229.

## 2. Implementar almacenamiento

Implementar `RecoveryChallengeStoreInterface` con consumo y revocación atómicos. Persistir únicamente digest y metadata del desafío.

## 3. Implementar entrega

Implementar `RecoveryChallengeDeliveryInterface` para correo, SMS u otro canal. El token en claro sólo debe existir durante la entrega.

## 4. Integrar password reset

Construir `PasswordResetService` con los contratos de identidad, hashing y almacenamiento de WP-228. Mantener respuestas anti-enumeración.

## 5. Integrar verificación

Implementar `IdentityVerificationActivatorInterface` según el dominio y registrar `IdentityVerificationService` con propósito independiente.

## 6. Protección contra abuso

En despliegues distribuidos usar una implementación compartida de `RecoveryRequestProtectorInterface`.

## 7. Eventos

Conectar `RecoverySecurityEventHandlerInterface` a auditoría sin registrar tokens, digest, lookup directo ni contraseñas.

## 8. HTTP y CLI

Registrar rutas y comandos explícitamente. Aplicar TLS, CSRF, autorización administrativa y políticas de cache.

## 9. Validación

Probar expiración, replay, revocación, cruce de propósito, identidad inexistente, fallos de infraestructura y ausencia de secretos en logs.
