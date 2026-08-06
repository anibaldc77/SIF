---
id: WP-229-I2-REVIEW
title: Revisión de implementación WP-229 I2
summary: Revisa generación criptográfica de tokens opacos, protección del secreto y modelo persistible de digest.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-229
tags:
  - security
  - account-recovery
  - token
  - review
depends_on:
  - EG-402
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-229 I2 — Implementation Review

## Alcance revisado

I2 incorpora generación segura de tokens y digest persistible. No introduce almacenamiento, consumo, revocación ni canales de entrega.

## Decisiones confirmadas

- Se reutilizan primitivas criptográficas nativas de PHP y no algoritmos propios.
- El token usa Base64 URL-safe sin padding y un mínimo de 32 bytes de entropía.
- El objeto secreto se redacta y prohíbe serialización y clonación.
- El Core persiste únicamente SHA-256 hexadecimal.
- La comparación se concentra en `RecoveryTokenDigest` mediante `hash_equals()`.

## Riesgos controlados

- Predicción: `random_bytes()` es la única fuente de entropía.
- Exposición accidental: no hay getter directo, serialización ni representación textual del token.
- Persistencia del secreto: el modelo ofrece un digest explícito y estable.
- Configuración débil: políticas inferiores a 256 bits se rechazan.

## Resultado

La implementación es aditiva, neutral respecto del almacenamiento y habilita I3 sin cambios incompatibles.
