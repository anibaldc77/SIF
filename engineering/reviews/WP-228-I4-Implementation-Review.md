---
id: WP-228-I4-REVIEW
title: Revisión de implementación WP-228 I4
summary: Revisa el autenticador de contraseña, la separación de proveedores, la mitigación de enumeración y la señal desacoplada de rehash.
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
  - authenticator
depends_on:
  - EG-396
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-228 I4 — Implementation Review

## Resultado

La implementación coordina resolución de identidad, obtención del hash, verificación, estado de cuenta y construcción del principal sin fusionar esas responsabilidades en un repositorio o modelo único.

## Evaluación arquitectónica

`PasswordAuthenticator` depende exclusivamente de contratos. `IdentityProviderInterface` mantiene la identidad separada de secretos almacenados, mientras `PasswordHashProviderInterface` permite usar otra fuente o incluso otro límite de seguridad para hashes.

La nueva `PasswordAuthenticationCredential` compone lookup y contraseña sin romper la API pública de `PasswordCredential` creada en I2.

## Seguridad

La identidad inexistente y el hash ausente ejecutan verificación contra un hash de respaldo. Los resultados públicos no revelan cuál condición falló. Las cuentas no activas se rechazan incluso cuando la contraseña es válida.

La señal de rehash sólo comunica identidad y hash actual a un handler; no propaga la contraseña. La actualización efectiva queda fuera del autenticador y podrá gobernarse mediante transacción o cola en implementaciones futuras.

## Compatibilidad y evolución

La solución permite proveedores basados en BaseModel, PDO, LDAP o servicios externos sin introducirlos en Foundation. También permite almacenar hashes en una fuente diferente del directorio de identidades.

## Riesgos controlados

- El hash de respaldo debe ser válido para conservar una ruta de verificación comparable.
- I4 no pretende ofrecer resistencia completa a análisis temporal de alta precisión; I5 deberá incorporar throttling y protección contra abuso.
- El nivel 20 es una convención inicial para autenticación de factor único y podrá centralizarse en una política posterior sin alterar el principal.

## Próxima implementación

I5 deberá integrar protección contra fuerza bruta, rate limiting, bloqueo temporal y observación segura de intentos, sin almacenar contraseñas ni revelar existencia de cuentas.
