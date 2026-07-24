---
id: ADR-BUILDER-0001-FILESYSTEM-CONTRACT
title: ADR 0001: Acceso exclusivo mediante contrato
summary: Los consumidores del Builder dependen exclusivamente de FileSystemInterface. Los únicos accesos a funciones nativas del sistema de archivos están encapsulados en LocalFileSystem.
status: Draft for Review
version: 0.1.0
category: Architecture Decision Record
document_class: GovernanceDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - 0001
  - acceso
  - exclusivo
  - mediante
  - contrato
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# ADR 0001: Acceso exclusivo mediante contrato

## Decisión

Los consumidores del Builder dependen exclusivamente de `FileSystemInterface`. Los únicos accesos a funciones nativas del sistema de archivos están encapsulados en `LocalFileSystem`.

## Consecuencia

Los drivers pueden sustituirse sin modificar consumidores y las pruebas pueden ejecutarse contra `VirtualFileSystem`.
