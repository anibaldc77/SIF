---
id: WP-230-I3-REVIEW
title: Revisión de implementación WP-230 I3
summary: Revisa los adaptadores nativos de Base32, generación de secretos, cálculo RFC 6238 y verificación TOTP con ventanas temporales explícitas.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
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
  - review
depends_on:
  - EG-411
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-230 I3 — Implementation Review

## Alcance revisado

I3 implementa los adaptadores nativos necesarios para generar semillas, producir códigos y verificar TOTP conforme a RFC 6238. No incorpora todavía enrolamiento, persistencia, desafío MFA, replay protection ni integración HTTP.

## Decisiones confirmadas

- La aleatoriedad se obtiene exclusivamente mediante `random_bytes()`.
- La semilla de referencia contiene al menos 160 bits de entropía.
- Base32 se implementa sin padding y sin dependencias externas.
- El contador se serializa como entero de 64 bits big-endian mediante dos palabras de 32 bits.
- HMAC utiliza las primitivas nativas de PHP y los algoritmos permitidos por I2.
- La truncación dinámica y el módulo decimal siguen RFC 4226/6238.
- La comparación de códigos utiliza `hash_equals()`.
- La verificación respeta de forma independiente ventanas pasadas y futuras.
- Un código con cantidad de dígitos distinta se rechaza sin intentar normalizarlo.
- El contador coincidente se devuelve para que I4 pueda implementar protección contra replay.

## Riesgos y observaciones

Aceptar ventanas temporales amplias aumenta el conjunto de códigos válidos. Las aplicaciones deben utilizar la menor tolerancia operativamente viable y sincronizar sus relojes.

La verificación por sí sola no impide reutilizar un código dentro de la misma ventana. Esa responsabilidad se incorpora en I4 mediante estado de factor y contador consumido.

## Resultado

La implementación es aditiva, interoperable y mantiene el núcleo sin dependencias externas. Prepara I4 para enrolamiento, almacenamiento neutral y prevención de replay.
