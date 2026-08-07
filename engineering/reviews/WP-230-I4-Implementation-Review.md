---
id: WP-230-I4-REVIEW
title: Revisión de implementación WP-230 I4
summary: Revisa el almacenamiento neutral de factores TOTP, el enrolamiento pending, la activación por prueba de posesión y la protección contra replay.
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
  - enrollment
  - replay-protection
  - review
depends_on:
  - EG-412
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-230 I4 — Implementation Review

## Alcance revisado

I4 incorpora el registro persistible del factor TOTP, el enrolamiento, la activación mediante un código válido, la revocación y la protección contra replay basada en el último contador aceptado.

## Decisiones confirmadas

- El secreto permanece encapsulado en `TotpSecret` y no aparece en snapshots.
- La activación es una transición explícita `pending` → `active`.
- El contador usado para activar el factor queda consumido.
- El uso operativo requiere un contador estrictamente mayor que el último persistido.
- `TotpFactorStoreInterface::acceptCounter()` constituye el límite atómico para implementaciones persistentes.
- La implementación en memoria es de referencia y permite reemplazo por PDO, Redis o almacenamiento institucional.
- La revocación impide que el factor vuelva a resolverse como activo.
- El servicio de enrolamiento y el verificador operativo permanecen separados.

## Riesgos y observaciones

El secreto TOTP debe cifrarse en reposo en adaptadores productivos. Esta implementación no impone una estrategia criptográfica porque la gestión de claves pertenece a la aplicación o infraestructura desplegada.

Una implementación distribuida debe realizar la comparación y actualización del contador como una única operación atómica. Un flujo read-then-write permitiría replay concurrente.

## Resultado

La implementación es aditiva, neutral respecto del almacenamiento y prepara I5 para coordinar factores activos con desafíos MFA y elevación del nivel de autenticación.
