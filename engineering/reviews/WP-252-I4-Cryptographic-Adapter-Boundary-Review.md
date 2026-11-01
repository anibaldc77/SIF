---
id: WP-252-I4-CRYPTO-BOUNDARY-REVIEW
title: WP-252 I4 Cryptographic Adapter Boundary Review
summary: Revisión de separación entre Foundation, JOSE, OpenSSL, X.509 y políticas de confianza para HAIP Key Attestation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-10-31
updated: 2026-10-31
work_package: WP-252
tags:
  - security
  - haip
  - cryptography
  - x509
  - architecture-review
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-252 I4 — Cryptographic Adapter Boundary Review

## 1. Objetivo

Evaluar la separación arquitectónica entre contratos de Foundation
y mecanismos criptográficos especializados utilizados por HAIP.

Esta revisión no constituye aprobación de una nueva arquitectura.

## 2. Línea base

- Rama: feature/wp-252-openid4vc-haip
- Checkpoint: faad6b3
- PHPUnit: 2853 tests, 19053 assertions.
- PHPStan: 3112 archivos, sin errores.
- EG-588: Draft for Review.

## 3. Hallazgo arquitectónico

EG-585 y EG-588 establecen que Foundation conserva neutralidad
respecto de proveedores criptográficos concretos.

Actualmente existen tres componentes especializados dentro de Foundation:

- OpenId4VcHaipUnverifiedKeyAttestationJwt.
- OpenId4VcHaipUntrustedEs256SignatureMatcher.
- OpenId4VcHaipExplicitX509TrustEvaluator.

Los componentes incorporan parsing JOSE, verificación ES256
y validación X.509 mediante OpenSSL.

Su ubicación actual requiere revisión arquitectónica.

## 4. Responsabilidades propuestas

### Foundation

- Contratos públicos.
- Modelos inmutables de requisitos y políticas.
- Contextos y resultados de conformidad.
- Orquestación independiente del proveedor.
- Rechazo por defecto ante evidencia insuficiente.

### Adaptador especializado

- Parsing JOSE/JWS.
- Verificación criptográfica ES256.
- Construcción y validación de cadenas X.509.
- Evaluación de revocación según política.
- Extracción de evidencia autenticada.
- Integración con proveedor criptográfico concreto.

### Configuración confiable

- Trust anchors autorizados.
- Algoritmos permitidos.
- Emisores autorizados.
- Perfiles Key Usage y Extended Key Usage.
- Política de revocación.
- Parámetros de temporalidad y frescura.

## 5. Alternativas

A. Mantener implementaciones concretas en Foundation.

B. Separarlas en un paquete o módulo adaptador especializado.

C. Incorporar una frontera transitoria y migrar progresivamente
las implementaciones existentes.

La selección requiere aprobación arquitectónica.

## 6. Propuesta de evolución

Se propone evaluar B como arquitectura objetivo y C como
estrategia de migración, preservando los contratos públicos.

No crear paquetes definitivos ni modificar autoload antes de
aprobar la decisión correspondiente.

## 7. Invariantes de seguridad

- Ninguna firma válida acredita por sí sola confianza.
- Ninguna cadena X.509 determina autorización por sí sola.
- Ningún claim no verificado genera evidencia autenticada.
- Ningún trust anchor procede de la solicitud del cliente.
- La ausencia de una verificación obligatoria produce rechazo.
- La implementación concreta no modifica la semántica HAIP.

## 8. Compatibilidad

La migración deberá preservar:

- OpenId4VcHaipKeyAttestationVerifierInterface.
- Los contratos públicos previamente validados.
- Los flujos OpenID4VCI y JWT existentes.
- La semántica de Key Attestation y Key Possession.
- Las pruebas positivas y negativas de I4.

## 9. Verificaciones pendientes

- Seleccionar y aprobar la arquitectura del adaptador.
- Determinar la necesidad de un ADR específico.
- Definir paquete, namespace y autoload.
- Definir contrato de política de confianza.
- Definir la estrategia de migración.
- Mantener cobertura completa de regresión.
- Completar la trazabilidad normativa de EG-588.

## 10. Estado de revisión

Draft for Review.

No se autoriza todavía la migración física de las clases,
la activación del verificador criptográfico integral ni
la promoción del estado documental de EG-588.

## Auditoría I4-03B1.2C0 — Dependencias y packaging

La búsqueda de referencias directas identificó:

- Parser JWT: seis archivos.
- Verificador ES256: dos archivos.
- Evaluador X.509: dos archivos.

Las referencias corresponden a las tres implementaciones
especializadas y sus pruebas.

No se identificaron consumidores directos adicionales
en el código inspeccionado.

La estructura actual contiene únicamente src/Foundation.

Composer deberá registrar Sif\Adapters\ y PHPStan deberá
analizar explícitamente src/Adapters durante la migración.

Se recomienda aprobar B como arquitectura objetivo
y C como estrategia de transición.

La revisión permanece Draft for Review.

## 11. Decision arquitectonica I4-03B1.2C0

ADR-0006 registra la aceptacion de la alternativa B
como arquitectura objetivo y C como estrategia de migracion.

La aprobacion se limita a la frontera arquitectonica.
No constituye aprobacion de una implementacion concreta.

La migracion de componentes, la politica de confianza,
la integracion y las pruebas de I4 siguen pendientes.

Esta revision permanece Draft for Review.