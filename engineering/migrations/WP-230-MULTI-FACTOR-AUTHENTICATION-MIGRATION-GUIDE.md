---
id: WP-230-MIGRATION-GUIDE
title: Guía de migración de autenticación multifactor
summary: Describe la adopción opt-in de TOTP, códigos de recuperación, desafíos MFA y elevación de sesión.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - mfa
  - migration
depends_on:
  - EG-416
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de migración — WP-230

## Impacto de compatibilidad

WP-230 es aditivo y opt-in. Ninguna aplicación queda obligada a habilitar MFA.

## Pasos de adopción

1. Implementar stores persistentes para factores TOTP, desafíos y códigos de recuperación.
2. Cifrar secretos TOTP en reposo mediante gestión de claves externa al dominio.
3. Garantizar consumo atómico de contadores, desafíos y códigos.
4. Registrar servicios MFA en el Container.
5. Registrar endpoints únicamente detrás de sesión autenticada, CSRF y rate limiting.
6. Registrar comandos CLI sólo para operadores autorizados.
7. Emitir desafíos step-up según políticas de autorización de la aplicación.
8. Persistir el principal elevado mediante `SessionAuthenticationManager`.

## Compatibilidad con sistemas existentes

Las aplicaciones pueden conservar autenticación por contraseña sin MFA. La habilitación puede realizarse por identidad, rol, operación o política externa.

## No incluido

- WebAuthn;
- dispositivos confiables;
- push authentication;
- SMS;
- OAuth/OIDC;
- Keycloak;
- LDAP;
- UI obligatoria.
