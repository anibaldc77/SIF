---
id: WP-252-I4-C4-2-HAIP-ASSURANCE-TRACEABILITY
title: WP-252 I4 HAIP Cryptographic Assurance Traceability Review
summary: Matriz de requisitos, mecanismos disponibles y garantias pendientes de Key Attestation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-11-01
updated: 2026-11-01
work_package: WP-252
tags:
  - security
  - haip
  - cryptography
  - traceability
  - key-attestation
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - HAIP Cryptographic Assurance Traceability Review

## 1. Objetivo

Registrar el estado de las garantias criptograficas de Key
Attestation conforme a EG-588 y ADR-0006.

Distinguir responsabilidades normativas, contratos, mecanismos
implementados, integracion efectiva y verificaciones pendientes.

Esta revision no constituye certificacion de conformidad HAIP.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 9d16bb8.
- PHPUnit: 2901 tests, 19307 assertions.
- PHPStan: 3127 archivos, sin errores.
- SIF Builder: 7 fases, 0 diagnosticos.
- ADR-0006: Approved.
- EG-588: Draft for Review.

La auditoria C4.1 se ejecuto sin modificar el repositorio.

## 3. Referencias normativas

HAIP 1.0 Final, secciones 4.5.1, 7 y 8:

https://openid.net/specs/openid4vc-high-assurance-interoperability-profile-1_0-final.html

OpenID4VCI 1.0 Final, secciones 8.2, 12.2.4,
Apendices D.1, F.1, F.3 y F.4:

https://openid.net/specs/openid-4-verifiable-credential-issuance-1_0.html

Se conserva la matriz preliminar N-01 a N-11 de EG-588.

Los MUST y SHOULD no se intercambian ni se convierten
automaticamente en restricciones adicionales de SIF.

## 4. Clasificacion de estado

- Implementado: comprobado en el alcance concreto indicado.
- Parcial: existen mecanismos o contratos, pero faltan garantias.
- Pendiente: no se identifico implementacion efectiva suficiente.
- Rol externo: exigencia dirigida a otra entidad del ecosistema.
- No evaluado: requiere una auditoria adicional de otra capacidad.

Ninguna clasificacion parcial implica conformidad integral.

## 5. Matriz normativa de trazabilidad

| ID | Rol principal | Evidencia SIF | Estado |
|---|---|---|---|
| N-01 | Wallet | Soporte obligatorio de Key Attestations para Wallets HAIP; no se audito una Wallet SIF | Rol externo / no evaluado |
| N-02 | Ecosistema y Wallet | Parser especializado para formato JWT D.1; adopcion interoperable no equivale a obligacion universal | Parcial |
| N-03 | Wallet e Issuer | Existen contratos de tipos de prueba y coordinacion; falta auditoria integral de metadata y cardinalidad | Parcial |
| N-04 | Wallet e Issuer | Coordinador distingue attestation de jwt y evita exigir posesion independiente universal; falta verificacion extremo a extremo | Parcial |
| N-05 | Credential Issuer | Requisito de comunicar necesidad de atestacion; implementacion de metadata o mecanismo fuera de banda no constatada | Pendiente de auditoria |
| N-06 | Credential Issuer | Prohibicion de metadata key_attestations_required cuando no se requiere atestacion; control no constatado | Pendiente de auditoria |
| N-07 | Wallet e Issuer | Parser exige iat y attested_keys; exp es opcional en el parser y debe exigirse cuando la atestacion se usa con prueba jwt | Parcial |
| N-08 | Credential Issuer | Existen matcher ES256 y contratos de prueba; falta evaluacion integrada de algoritmo, firma, nonce y frescura | Parcial |
| N-09 | Wallet e Issuer | Parser x5c, matcher ES256 y evaluador X.509 con rechazos de trust anchor y hoja autofirmada | Implementado en mecanismos aislados |
| N-10 | Wallet y ecosistema | Modelo de cobertura de claves y evaluacion de lotes; regla SHOULD de atestacion conjunta requiere auditoria especifica | Parcial |
| N-11 | Issuer, Wallet, Verifier | ES256 disponible para el mecanismo especializado; soporte completo SHA-256 de formatos perfilados no auditado aqui | Parcial |

Los resultados anteriores clasifican exclusivamente la evidencia
analizada, no la conformidad global de cada entidad.

## 6. Componentes existentes

### 6.1 Adapters

- OpenId4VcHaipUnverifiedKeyAttestationJwt.
- OpenId4VcHaipUntrustedEs256SignatureMatcher.
- OpenId4VcHaipExplicitX509TrustEvaluator.

Los tres estan en Sif\Adapters\Security\VerifiableCredentials
\Haip\Issuance y cuentan con pruebas de paridad.

### 6.2 Foundation

- OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface.
- OpenId4VcHaipKeyAttestationVerifierInterface.
- OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface.
- OpenId4VcHaipKeyAttestationTrustPolicy.
- OpenId4VcHaipKeyAttestationVerificationResult.
- OpenId4VcHaipKeyPossessionVerificationCoordinator.
- OpenId4VcHaipCredentialProofConformancePolicy.

La busqueda C4.1 no identifico una implementacion concreta
del verificador integral ni del proveedor de politica de confianza.

Las referencias dinamicas o implementaciones externas no
quedan descartadas mediante la busqueda estatica.

## 7. Matriz de garantias tecnicas

| Garantia | Evidencia | Pendiente |
|---|---|---|
| Parsing JOSE | Parser estructural en Adapters | No autentica evidencia |
| Firma ES256 | Verificacion OpenSSL con x5c leaf | Integracion con politica y confianza |
| Confianza X.509 | Cadena contra anchors explicitos | Politica completa de confianza |
| Algoritmos permitidos | Lista en TrustPolicy | Aplicacion efectiva al verificar |
| Emisor autorizado | Lista en TrustPolicy | Identidad autenticada y autorizacion |
| Identidad de evidencia | Hash y modelos de resultado | Asociacion autenticada al JWT exacto |
| Temporalidad JWT | iat y exp estructurales | Validacion temporal y condiciones por tipo |
| Nonce | Modelos y orquestacion | Comprobacion criptografica contextual |
| Vigencia X.509 | Evaluacion mediante time() | Abstraccion de reloj verificable |
| Extended Key Usage | OIDs configurables en TrustPolicy | Aplicacion efectiva a la cadena |
| Revocacion | Requisito configurable | Resolucion y rechazo ante fallo |
| Vinculacion de claves | Resultado y cobertura completa | Identidad criptografica de JWK verificada |
| Emision por lotes | Coordinacion y modelos existentes | Trazabilidad de atestacion conjunta |

Los atributos de TrustPolicy no constituyen, por su sola
existencia, evidencia de cumplimiento de sus restricciones.

## 8. Distinciones normativas importantes

### 8.1 exp condicional

OpenID4VCI D.1 define exp como opcional en general,
pero obligatorio cuando Key Attestation se usa con
el tipo de prueba jwt.

No imponer universalmente esta condicion al tipo
attestation sin fundamento normativo adicional.

### 8.2 Key Attestation y Key Possession

El mecanismo attestation no exige automaticamente
una prueba independiente de posesion de las claves
atestadas.

El mecanismo jwt requiere evaluar sus garantias
especificas de posesion y vinculacion.

### 8.3 Emisor de la atestacion

El Credential Issuer que selecciona TrustPolicy no
tiene que coincidir con el emisor de Key Attestation.

La identidad del firmante debe autenticarse mediante
un mecanismo de confianza apropiado.

No confiar exclusivamente en un claim iss no verificado.

### 8.4 Politicas del ecosistema

Los perfiles X.509, EKU adicionales, reglas de
revocacion y restricciones de confianza requieren
decisiones y configuracion explicitamente documentadas.

No todas esas restricciones son MUST universales de HAIP.

## 9. Riesgos y dependencias

R-01: Firma valida sin cadena confiable.

R-02: Cadena confiable sin emisor autorizado.

R-03: Emisor declarado sin autenticacion suficiente.

R-04: Revocacion obligatoria no comprobable.

R-05: iat, exp o nonce sin verificacion contextual.

R-06: JWK declarado no vinculado criptograficamente.

R-07: Cobertura parcial de las claves de emision.

R-08: Politica definida pero no aplicada por el verificador.

R-09: Dependencia de reloj global no controlable.

R-10: Duplicacion transitoria de clases historicas.

Los riesgos R-01 a R-09 bloquean cualquier afirmacion
de conformidad integral cuando corresponden a una garantia
obligatoria del mecanismo o de la politica aplicable.

## 10. Secuencia propuesta

C4.3: Definir contratos y pruebas de verificador integral
con rechazo por defecto ante ausencia de garantias.

C4.4: Seleccion de politica confiable y autorizacion
del emisor de la atestacion.

C4.5: Temporalidad, nonce, reloj inyectable y reglas
condicionales de exp.

C4.6: Extensiones X.509 y revocacion segun politica.

C4.7: Verificacion criptografica y vinculacion de JWK.

C4.8: Composicion de garantias y evaluacion integral,
con regresion positiva y negativa completa.

La secuencia es preliminar y puede subdividirse en
incrementos menores tras sus revisiones respectivas.

## 11. Limites de aprobacion

No se autoriza:

- Tratar un matcher ES256 como verificador integral.
- Tratar confianza X.509 como autorizacion del emisor.
- Derivar confianza de claims no verificados.
- Ignorar una revocacion exigible no comprobable.
- Emitir resultados validos con garantias pendientes.
- Retirar implementaciones historicas sin resolver compatibilidad.
- Activar el verificador integral antes de su aceptacion.

## 12. Estado

Draft for Review.

Esta matriz representa evidencia arquitectonica y
trazabilidad preliminar, no conformidad HAIP aprobada.

EG-588 permanece Draft for Review.
ADR-0006 permanece Approved.
