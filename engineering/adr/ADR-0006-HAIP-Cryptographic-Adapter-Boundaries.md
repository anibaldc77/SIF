---
id: ADR-0006
title: HAIP Cryptographic Adapter Boundaries
summary: Define la separacion propuesta entre Foundation y los adaptadores criptograficos especializados para HAIP Key Attestation.
status: Approved
version: 1.0.0
category: Architecture Decision Record
document_class: GovernanceDocument
authors:
  - SIF Team
created: 2026-10-31
updated: 2026-10-31
work_package: WP-252
tags:
  - architecture
  - security
  - haip
  - cryptography
  - x509
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# ADR-0006 — HAIP Cryptographic Adapter Boundaries

## 1. Status

Accepted.

La direccion arquitectonica B/C queda aprobada.
La ejecucion requiere incrementos separados y validados.

## 2. Context

WP-252 incorpora conformidad HAIP sobre las capacidades
existentes de OpenID4VCI y Credential Trust.

EG-585 y EG-588 establecen que Foundation debe permanecer
independiente de proveedores criptograficos concretos.

Actualmente existen en Foundation tres componentes:

- OpenId4VcHaipUnverifiedKeyAttestationJwt.
- OpenId4VcHaipUntrustedEs256SignatureMatcher.
- OpenId4VcHaipExplicitX509TrustEvaluator.

Sus responsabilidades incluyen parsing JOSE, verificacion
ES256 y evaluacion X.509 mediante OpenSSL.

Esta ubicacion requiere una decision arquitectonica.

## 3. Decision

Se establece una frontera explicita entre
Foundation y los adaptadores criptograficos.

Foundation SHALL conservar:

- Contratos publicos independientes de proveedores.
- Modelos inmutables de requisitos y politicas.
- Contextos y resultados de conformidad.
- Orquestacion y evaluacion de garantias.
- Rechazo por defecto ante evidencia insuficiente.

Los adaptadores especializados SHALL proporcionar:

- Decodificacion JOSE/JWS.
- Verificacion criptografica.
- Validacion de cadenas X.509.
- Comprobacion de revocacion cuando resulte exigible.
- Extraccion de evidencias autenticadas.

El Runtime SHALL resolver las implementaciones mediante
composicion explicita conforme a ADR-0005.

No se aprueba todavia un namespace, paquete Composer
ni identificador de capability definitivo.

## 4. Trusted configuration

La politica de confianza debe proceder de una fuente
configurada y controlada por el Credential Issuer.

Debera contemplar:

- Trust anchors autorizados.
- Algoritmos permitidos.
- Emisores de Key Attestation autorizados.
- Requisitos Key Usage y Extended Key Usage.
- Politica de revocacion.
- Restricciones temporales y de frescura.

Las restricciones adicionales del ecosistema deben
distinguirse de las obligaciones normativas HAIP.

## 5. Security invariants

- Una firma ES256 valida no implica confianza X.509.
- Una cadena X.509 valida no implica autorizacion del emisor.
- Los claims no verificados no generan evidencia confiable.
- Los trust anchors no pueden provenir de la solicitud.
- Los resultados criptograficos no deben depender de
  indicadores de validez suministrados por el cliente.
- Toda verificacion obligatoria debe fallar cerrada.
- La revocacion obligatoria no comprobable implica rechazo.
- La comprobacion temporal debe utilizar un reloj
  controlable y verificable.
- No se habilitara el verificador integral antes de
  completar todas las garantias exigibles.

## 6. Alternatives

### A. Mantener implementaciones en Foundation

Ventaja: menor esfuerzo inmediato.

Desventaja: contradice la neutralidad arquitectonica.

### B. Migrar directamente a un paquete especializado

Ventaja: separacion completa.

Desventaja: mayor riesgo de incompatibilidad y regresion.

### C. Migracion progresiva hacia adaptadores

Ventaja: permite preservar funcionalidad y pruebas.

Desventaja: requiere gestionar una etapa transitoria.

## 7. Accepted direction

Se adopta B como arquitectura objetivo y C como
estrategia de transicion.

La direccion arquitectonica queda aprobada. Cada migracion
requiere revision y validacion antes de ejecutarse.

## 8. Compatibility requirements

La migracion debera preservar:

- Los contratos publicos existentes.
- La semantica de CredentialProofValidationResult.
- Los mecanismos jwt y attestation.
- La separacion entre Wallet Attestation y Key Attestation.
- La compatibilidad con WP-245 y WP-250.
- Las pruebas positivas y negativas previamente aprobadas.

Los cambios de namespace o API publica requieren una
estrategia de compatibilidad documentada.

## 9. Migration plan

1. Aprobar la frontera arquitectonica.
2. Definir contratos y politica inmutable de confianza.
3. Seleccionar estructura de adaptadores y autoload.
4. Migrar parsing JOSE y verificacion ES256.
5. Migrar la evaluacion X.509.
6. Implementar autorizacion del emisor y controles pendientes.
7. Conectar el verificador integral a la orquestacion.
8. Ejecutar regresion focalizada, global y analisis estatico.
9. Registrar trazabilidad normativa y revision de I4.

Cada etapa debe mantenerse aislada y ser reversible.

## 10. Acceptance gates

- Composer validate --strict.
- PHPUnit focalizado y global.
- PHPStan sin errores.
- SIF Builder sin diagnosticos.
- git diff --check.
- Revision de contratos y compatibilidad.
- Trazabilidad hacia EG-588.

## 11. Baseline

Branch: feature/wp-252-openid4vc-haip.

Checkpoint: faad6b3.

PHPUnit: 2853 tests, 19053 assertions.

PHPStan: 3112 archivos, sin errores.

EG-588 permanece en Draft for Review.

## 12. Approval and implementation limits

Se aprueban la arquitectura objetivo B y la transicion C.

La estructura fisica definitiva, los contratos adicionales,
el autoload y la activacion requieren decisiones incrementales.

## 13. Audited dependency evidence

The I4-03B1.2C0 dependency audit identified:

- OpenId4VcHaipUnverifiedKeyAttestationJwt: 6 referencing files.
- OpenId4VcHaipUntrustedEs256SignatureMatcher: 2 referencing files.
- OpenId4VcHaipExplicitX509TrustEvaluator: 2 referencing files.

No direct production consumer outside these three components
was identified by the repository search.

The audit does not exclude dynamic references or external consumers.

## 14. Proposed physical architecture

The proposed target structure introduces:

- src/Adapters/
- Namespace Sif\Adapters\

The initial migration remains within the existing Composer package.

Foundation retains provider-independent contracts, policies,
contexts, orchestration and verification results.

Adapters depend on Foundation contracts.

Foundation SHALL NOT depend on concrete adapter classes.

## 15. Build and testing implications

The migration requires:

- Composer PSR-4 autoload registration for Sif\Adapters\.
- Inclusion of src/Adapters in phpstan.neon.
- Updating test imports to the new adapter namespace.
- Preserving all positive and negative test scenarios.
- Verifying no Foundation-to-Adapter dependency.
- Composer, PHPUnit, PHPStan and SIF Builder validation.

A successful PHPStan execution that excludes src/Adapters
does not constitute migration acceptance.

## 16. Architecture decision

Accepted target: Alternative B.

Accepted transition: Alternative C.

Architectural direction approved for WP-252.

Physical migration and runtime activation require separate
reviewed and regression-tested implementation increments.
