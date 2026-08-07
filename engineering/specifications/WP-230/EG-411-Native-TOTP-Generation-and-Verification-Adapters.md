---
id: EG-411
title: Adaptadores nativos de generación y verificación TOTP
summary: Define Base32, generación criptográfica de semillas, cálculo RFC 6238 y verificación TOTP mediante primitivas nativas de PHP.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-230
tags:
  - security
  - multi-factor-authentication
  - totp
  - rfc6238
  - adapters
depends_on:
  - EG-410
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-411 — Native TOTP Generation and Verification Adapters

## Objetivo

Implementar adaptadores TOTP interoperables y libres de dependencias externas sobre los contratos sensibles definidos en EG-410.

## Decisiones normativas

- La generación de semillas debe utilizar `random_bytes()` y producir al menos 160 bits de entropía por defecto.
- La representación persistible e intercambiable de la semilla debe ser Base32 sin padding.
- La decodificación Base32 debe rechazar caracteres fuera del alfabeto RFC 4648.
- El contador TOTP se calcula con división entera del timestamp Unix por el período configurado.
- El contador se codifica como entero sin signo de 64 bits en orden big-endian.
- El cálculo debe utilizar HMAC con SHA-1, SHA-256 o SHA-512 según la política.
- La truncación dinámica debe seguir RFC 4226 y el resultado debe conservar ceros iniciales.
- La verificación debe comparar códigos con `hash_equals()`.
- Las ventanas pasadas y futuras deben evaluarse por separado y nunca exceder la política de EG-410.
- El resultado exitoso debe conservar el contador coincidente para permitir prevención de replay posterior.
- Un código cuya cantidad de dígitos no coincida con la política se rechaza.
- Timestamps anteriores a Unix epoch y contadores negativos no están soportados.
- Los adaptadores no deben registrar ni serializar semillas o códigos.

## Compatibilidad

La implementación utiliza solamente primitivas de PHP 8.2 y no depende de BaseModel, PDO, extensiones de terceros ni aplicaciones autenticadoras concretas.

## Exclusiones I3

- Enrolamiento y activación del factor.
- Persistencia de semillas.
- Cifrado de secretos en reposo.
- Prevención de replay.
- Coordinación del desafío MFA.
- HTTP, CLI y Skeleton.

## Criterios de aceptación I3

- Base32 round-trip validado.
- Semillas con entropía criptográfica suficiente.
- Vector RFC 6238 validado.
- Verificación exacta y por ventanas validada.
- Comparación segura y rechazo de códigos inválidos.
- PHPUnit, PHPStan y Builder sin diagnósticos.
