---
id: WP-231-MIGRATION-GUIDE
title: Guía de adopción de dispositivos confiables y autenticación persistente
summary: Describe la adopción opt-in de credenciales persistentes y trusted-device grants.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - trusted-device
  - migration
depends_on:
  - EG-424
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-231

## Impacto

WP-231 es completamente opt-in.

Las aplicaciones existentes continúan funcionando con Session, Password y MFA sin autenticación persistente.

## Adopción de autenticación persistente

1. Implementar `PersistentAuthenticationCredentialLifecycleStoreInterface`.
2. Garantizar rotación atómica por selector y digest esperado.
3. Registrar `PersistentAuthenticationService`.
4. Registrar un `PersistentAuthenticationPrincipalFactoryInterface`.
5. Configurar un nivel de autenticación restaurada apropiado.
6. Registrar `PersistentSessionRestorationService`.
7. Definir nombre y atributos seguros de cookie.
8. Reemplazar la cookie después de cada restauración exitosa.
9. Revocar credenciales según las políticas de seguridad de la aplicación.

## Adopción de trusted-device

1. Implementar `TrustedDeviceGrantLifecycleStoreInterface`.
2. Registrar `TrustedDeviceGrantService`.
3. Usar `DefaultTrustedDevicePolicy` o una policy explícita de aplicación.
4. No usar trusted-device como autenticación primaria.
5. No interpretar trusted-device como MFA satisfecho salvo una política explícita y documentada.

## Cambios sensibles

La aplicación debería evaluar revocación global de credenciales y/o dispositivos después de:

- cambio de contraseña;
- recuperación de cuenta;
- cambio de factores MFA;
- sospecha de compromiso;
- cierre administrativo de sesiones o dispositivos.

## Cookies

La configuración productiva debería considerar:

- `Secure`;
- `HttpOnly`;
- `SameSite`;
- Path mínimo;
- Domain mínimo;
- expiración coherente con la expiración absoluta del credential.

## Compatibilidad con proveedores externos

La resolución del principal está desacoplada. Puede adaptarse a proveedores locales o externos sin modificar el dominio WP-231.

## No incluido

OAuth/OIDC, JWT, WebAuthn, passkeys, Keycloak, LDAP y device attestation quedan fuera de WP-231.
