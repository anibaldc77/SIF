---
id: WP-240-I5-REVIEW
title: WP-240 I5 Implementation Review
summary: Revisa DPoP proof, key binding, nonce y replay protection boundaries.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-240
tags:
  - security
  - oauth
  - dpop
depends_on:
  - EG-493
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-240 I5 Implementation Review

## Alcance revisado
Se incorporan DPoP proof, verification context/result, verifier tipado, replay store, nonce service y excepción específica.

## Hallazgos
La proof queda vinculada a método/URI y a un thumbprint de clave. Replay y nonce quedan detrás de contratos. La criptografía concreta no pertenece a Foundation. Sender-constrained token issuance queda reservado para I6.

## Decisión
Apto para continuar a I6 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
