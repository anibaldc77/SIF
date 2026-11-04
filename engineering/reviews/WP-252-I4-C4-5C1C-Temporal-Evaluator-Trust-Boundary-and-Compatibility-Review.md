---
id: WP-252-I4-C4-5C1C-TEMPORAL-TRUST-BOUNDARY
title: WP-252 I4 Temporal Evaluator Trust Boundary and Compatibility Review
summary: Audita el alcance de la validacion temporal y sus fronteras de confianza antes de la composicion integral HAIP.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-11-04
updated: 2026-11-04
work_package: WP-252
tags:
  - security
  - haip
  - temporal-validation
  - trust-boundary
  - fail-closed
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Temporal Evaluator Trust Boundary and Compatibility Review

## 1. Objetivo

Auditar el evaluador temporal implementado
en C4.5C1A y endurecido en C4.5C1B.

Distinguir las garantias temporales demostradas
de las garantias criptograficas pendientes.

Esta revision no conecta componentes
ni habilita el verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 36f5751.
- PHPUnit: 2956 tests, 19786 assertions.
- PHPStan: 3135 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Componentes examinados

### 3.1 Evaluador temporal

OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator

Implementa admisibilidad temporal de:

- iat.
- exp.
- nbf.
- maximumAttestationAgeSeconds.
- allowedClockSkewSeconds.

Obtiene el reloj mediante ClockInterface.

Devuelve un resultado booleano parcial.

### 3.2 Parser JOSE

OpenId4VcHaipUnverifiedKeyAttestationJwt

El parser devuelve claims estructuralmente
decodificados mediante un contrato
de evidencia expresamente no verificada.

La decodificacion no autentica el JWT.

### 3.3 Contrato de evidencia no verificada

OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface

Expone:

- signingInput.
- rawSignature.
- unverifiedHeader.
- unverifiedClaims.
- unverifiedCertificateDerChain.

Ninguno de estos metodos acredita
autenticidad por si mismo.

### 3.4 Contrato del verificador integral

OpenId4VcHaipKeyAttestationVerifierInterface

Define un metodo verify que debe verificar
la evidencia serializada conforme a una
politica confiable del Credential Issuer.

No se permite considerar suficiente
un fingerprint o referencias declaradas.

### 3.5 Coordinador HAIP

OpenId4VcHaipKeyPossessionVerificationCoordinator

Distingue rutas de los mecanismos
jwt y attestation.

El camino attestation verifica la
correspondencia de evidencia serializada
y su identidad antes de invocar el
contrato del verificador especializado.

Esa correspondencia no equivale a
autenticacion criptografica.

La ruta jwt requiere conservar la
separacion entre proof validado,
mecanismo acreditado y Key Attestation.

### 3.6 Resultado de conformidad

OpenId4VcHaipKeyAttestationVerificationResult

Distingue:

- valid.
- verifiedKeyReferences.
- violations.
- verifiedIssuerIdentifier.
- verifiedEvidenceIdentity.

El evaluador temporal no construye
ni devuelve este resultado.

## 4. Controles de frontera

BC-01: La validacion temporal favorable
no acredita autenticidad de claims.

BC-02: Los claims deben corresponder
al JWT exacto cuya firma se verifica.

BC-03: No deben mezclarse claims de
una evidencia con la firma de otra.

BC-04: El proof type utilizado para
decidir obligatoriedad de exp debe
proceder de contexto de prueba acreditado.

BC-05: Una declaracion unilateral
de la Wallet no acredita proof type.

BC-06: La TrustPolicy debe proceder
de configuracion confiable del
Credential Issuer correspondiente.

BC-07: La politica no puede obtenerse
de claims ni headers no autenticados.

BC-08: El booleano temporal no debe
convertirse directamente en un resultado
integral de Key Attestation valido.

BC-09: Una firma ES256 valida no
sustituye la validacion X.509.

BC-10: Una cadena X.509 valida no
sustituye autorizacion del emisor.

BC-11: La temporalidad del JWT es
distinta de la vigencia de certificados
y de las comprobaciones de revocacion.

BC-12: El control de claves atestadas
y su binding permanece separado.

BC-13: Una falla operativa de reloj
debe impedir la admision temporal.

BC-14: El evaluador debe recibir el
instante temporal una sola vez por
evaluacion mediante ClockInterface.

BC-15: Los claims evaluados no deben
reconstruirse desde una fuente distinta
de la evidencia criptografica vinculada.

BC-16: La futura composicion debe
fallar cerrada ante ausencia de
cualquier garantia obligatoria.

## 5. Riesgos de integracion identificados

### 5.1 Confusion entre validez parcial e integral

El metodo isTemporallyAdmissible retorna bool.

Un resultado true significa exclusivamente
que la entrada cumple las reglas temporales.

No representa autenticidad criptografica,
confianza X.509, identidad de emisor
ni vinculacion de claves.

### 5.2 Procedencia del mecanismo

El evaluador recibe proofType como string.

Por si mismo no puede determinar si el
mecanismo ha sido acreditado.

La ruta jwt y la ruta attestation deben
proporcionar contexto derivado de las
verificaciones aplicables y no de una
declaracion no autenticada.

### 5.3 Vinculacion de evidencia exacta

La identidad SHA-256 de evidencia permite
identificar bytes, pero no demuestra confianza.

La futura composicion debe conservar
identidad de evidencia y correspondencia
entre el JWT firmado y sus claims.

No debe reutilizar el resultado temporal
de un JWT diferente.

### 5.4 Error de reloj y observabilidad

El evaluador rechaza ante Throwable
durante la consulta del reloj.

Ese comportamiento es fail-closed.

La futura composicion debera resolver
observabilidad sin exponer datos sensibles
ni alterar la decision de rechazo.

### 5.5 Separacion X.509

La evaluacion X.509 actual presenta
accesos independientes al reloj de sistema.

La coherencia temporal de una verificacion
integral requiere revision en C4.6.

### 5.6 Ambiguedad de parsing

Debe evitarse que diferentes componentes
interpreten de manera distinta el JSON
del mismo JWT.

Antes de la composicion deben revisarse
claims duplicados y consistencia de
decodificacion de la evidencia.

La presente auditoria registra ese
riesgo sin afirmar su explotabilidad.

## 6. Escenarios para pruebas posteriores

| ID | Escenario | Resultado requerido |
|---|---|---|
| S-01 | Claims temporalmente validos sin firma valida | No conformidad integral |
| S-02 | Firma valida pero JWT vencido | Rechazo |
| S-03 | Claims de JWT A con firma de JWT B | Rechazo |
| S-04 | proofType no acreditado | Rechazo |
| S-05 | jwt sin exp | Rechazo |
| S-06 | attestation sin exp y politica valida | Evaluacion temporal contextual |
| S-07 | TrustPolicy de otro Credential Issuer | Rechazo |
| S-08 | Certificado vencido con JWT vigente | Rechazo X.509 |
| S-09 | Revocacion obligatoria no verificable | Rechazo |
| S-10 | Clave atestada diferente de la solicitada | Rechazo |
| S-11 | Fallo de reloj | Rechazo |
| S-12 | Diferentes interpretaciones del mismo JWT | Rechazo o normalizacion unica verificada |

Estos escenarios no se consideran
implementados por esta revision.

## 7. Compatibilidad arquitectonica

La futura integracion debera preservar:

- ADR-0006.
- Contratos existentes de Foundation.
- Neutralidad frente a proveedores concretos.
- Separacion entre Foundation y Adapters.
- Mecanismos jwt y attestation.
- Compatibilidad con WP-245 y WP-250.
- Resultados de conformidad existentes.
- Politicas por Credential Issuer.
- Semantica de rechazo por defecto.

No corresponde cambiar la interfaz
del verificador integral en esta etapa.

## 8. Dependencias pendientes

P-01: Confirmar la obtencion confiable
de proofType para ambos mecanismos.

P-02: Vincular los claims temporales
a la misma evidencia criptografica
autenticada por el verificador.

P-03: Verificar procedencia de la
TrustPolicy y aislamiento entre emisores.

P-04: Determinar precedencia entre
firma, identidad, temporalidad,
certificados y revocacion.

P-05: Revisar la coherencia de relojes
en la futura composicion X.509.

P-06: Resolver el tratamiento de errores
y su observabilidad sin introducir
aceptaciones ni fugas de informacion.

P-07: Auditar consistencia del parser
y posibles claims JSON duplicados.

P-08: Mantener pendiente la conexion
del verificador integral hasta finalizar
todas las garantias obligatorias.

## 9. Decision de la revision

El evaluador temporal aislado puede
conservarse sin modificaciones dentro
del alcance C4.5C1.

Sus pruebas actuales no demuestran
las garantias pendientes de integracion.

No se aprueba todavia su conexion al
flujo de emision ni la produccion
de resultados integrales validos.

La revision de fronteras debera
continuar en C4.5D, C4.6, C4.7 y C4.8.

## 10. Estado

Draft for Review.

Revision de frontera documentada.

Sin modificaciones de codigo productivo.

Sin activacion integral HAIP.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
