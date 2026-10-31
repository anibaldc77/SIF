---
id: EG-588
title: HAIP Credential Endpoint Proof Key Attestation and Key Binding Profile
summary: Define the HAIP credential endpoint proof, key attestation and key-binding conformance boundaries over existing OpenID4VCI issuance and credential proof verification capabilities.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-10-09
updated: 2026-10-09
work_package: WP-252
tags:
  - security
  - verifiable-credentials
  - haip
  - openid4vci
  - key-attestation
  - key-binding
depends_on:
  - EG-587
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-588 — HAIP Credential Endpoint Proof, Key Attestation and Key Binding Profile

## 1. Objetivo

Definir los límites de conformidad de OpenID4VC HAIP 1.0 aplicables a las pruebas de emisión de credenciales, Key Attestation y vinculación criptográfica de claves.

I4 constituye una capa especializada sobre OpenID4VCI y los mecanismos de verificación existentes en SIF.

No introduce un Credential Endpoint alternativo ni un motor criptográfico paralelo.

## 2. Alcance

I4 contempla:

- Evaluación de pruebas presentadas para emisión.
- Evidencia de Key Attestation verificada.
- Correspondencia entre las claves acreditadas y las utilizadas para emisión.
- Soporte de escenarios individuales y por lotes.
- Resultados explícitos de conformidad.
- Separación entre requisitos HAIP y políticas adicionales del ecosistema.

Quedan fuera del alcance:

- Transporte HTTP.
- Parsing JWT/JWS.
- Verificación criptográfica concreta.
- Construcción y validación de cadenas X.509.
- Implementaciones HSM/KMS.
- Persistencia de evidencias.
- Emisión material de credenciales.

## 3. Dependencias

I4 compone las capacidades existentes de:

- WP-245: OpenID4VCI.
- WP-248: formatos de credenciales.
- WP-250: Credential Trust.
- WP-252 I1: arquitectura HAIP.
- WP-252 I2: autorización.
- WP-252 I3: Wallet Attestation y autenticación.

La Key Attestation no se equipara a Wallet Attestation.

La vinculación de claves durante la emisión tampoco se equipara automáticamente al Key Binding utilizado en presentaciones SD-JWT VC.

## 4. Modelos propuestos

### 4.1 OpenId4VcHaipCredentialProofContext

Representa la evidencia disponible para evaluar la conformidad de una solicitud de emisión.

Debe distinguir:

- Mecanismo de prueba seleccionado.
- Resultado de la verificación de la prueba.
- Evidencia de Key Attestation.
- Identidades criptográficas de claves verificadas.
- Requisitos aplicables al mecanismo.
- Contexto de emisión individual o por lotes.

Los datos proporcionados por la solicitud no se consideran evidencia criptográfica verificada por el solo hecho de estar presentes.

### 4.2 OpenId4VcHaipKeyAttestation

Representa el resultado de una Key Attestation verificada por un componente especializado.

Debe permitir identificar las claves acreditadas y conservar la información necesaria para evaluar los requisitos aplicables.

No representa una Wallet Attestation.

### 4.3 OpenId4VcHaipCredentialProofAssessment

Representa el resultado de conformidad.

Debe contener:

- Estado de conformidad.
- Lista de violaciones.
- Lista de advertencias.

La presencia de violaciones impide considerar conforme la evaluación.

## 5. Contratos

### 5.1 OpenId4VcHaipCredentialProofPolicyInterface

Define la evaluación de conformidad de pruebas y Key Attestation bajo HAIP.

### 5.2 OpenId4VcHaipKeyAttestationVerifierInterface

Define el límite de integración con un verificador especializado de Key Attestation.

La implementación concreta es responsable de las verificaciones criptográficas y de confianza correspondientes.

### 5.3 Reutilización de contratos existentes

I4 debe reutilizar, cuando corresponda:

- CredentialProofVerifierInterface.
- CredentialProofValidationResult.
- CredentialProofReplayStoreInterface.
- CredentialIssuanceProofValidatorInterface.

No deben modificarse los contratos públicos existentes sin una necesidad arquitectónica demostrada.

## 6. Invariantes de seguridad

### 6.1 Evidencia verificada

Una declaración de identidad de clave procedente de la solicitud no constituye evidencia suficiente de vinculación criptográfica.

### 6.2 Separación de validaciones

La validez de una prueba de posesión no implica la validez de su Key Attestation.

La validez de una Key Attestation no implica que la clave acreditada corresponda a la clave requerida para la emisión.

### 6.3 Fallo cerrado

Cuando un requisito obligatorio resulte aplicable y su cumplimiento no pueda acreditarse, la evaluación no debe considerarse conforme.

### 6.4 Mecanismos de prueba

Las reglas deben depender del mecanismo de prueba seleccionado.

No debe imponerse indiscriminadamente un mismo requisito de firma de posesión a mecanismos con semánticas distintas.

### 6.5 Emisión por lotes

La evaluación debe contemplar todas las claves relevantes para la emisión por lotes y los requisitos de acreditación aplicables.

No debe presumirse que acreditar una clave acredita automáticamente las restantes.

### 6.6 Políticas del ecosistema

Las restricciones adicionales deben identificarse separadamente de los requisitos del perfil HAIP.

Las recomendaciones normativas no deben convertirse automáticamente en obligaciones universales.

## 7. Neutralidad de Foundation

Foundation no implementa:

- Bibliotecas JWT/JWS.
- Validación X.509 concreta.
- Operaciones criptográficas.
- Transporte HTTP.
- Platform wallet APIs.
- HSM/KMS.
- Persistencia.

Estas responsabilidades corresponden a adaptadores especializados.

## 8. Compatibilidad

I4 no debe modificar el comportamiento de:

- WP-245 OpenID4VCI.
- WP-248 SD-JWT VC / ISO mdoc.
- WP-250 Credential Trust.
- WP-252 I1–I3.

La incorporación de reglas HAIP no debe alterar otros perfiles de emisión.

## 9. Pruebas de aceptación

Se requieren, como mínimo:

1. Evaluación conforme con evidencia válida y vinculada.
2. Rechazo de prueba criptográfica inválida.
3. Rechazo de Key Attestation inválida cuando resulte exigible.
4. Rechazo de claves acreditadas que no correspondan a las requeridas.
5. Rechazo de evidencia insuficiente para requisitos obligatorios.
6. Evaluación diferenciada según mecanismo de prueba.
7. Evaluación de múltiples claves en emisión por lotes.
8. Preservación de compatibilidad con contratos existentes.
9. Ausencia de dependencias concretas de HTTP, JWT y criptografía.
10. Resultados deterministas de conformidad y violaciones.

## 10. Criterios de aceptación

I4 podrá considerarse completada cuando:

- Los contratos especializados estén implementados.
- La política de conformidad esté cubierta por pruebas positivas y negativas.
- Las dependencias existentes se reutilicen correctamente.
- PHPUnit apruebe las pruebas focalizadas y la suite completa.
- PHPStan no reporte errores.
- Composer strict validation apruebe.
- SIF Builder valide los artefactos gobernados.
- git diff --check no reporte problemas.
- Se incorpore la revisión de implementación de I4.

## 11. Validación normativa y decisiones de interoperabilidad

### 11.1 Referencias normativas

La evaluación de conformidad de I4 debe contrastarse con:

- OpenID4VC High Assurance Interoperability Profile 1.0 (HAIP):
  https://openid.net/specs/openid4vc-high-assurance-interoperability-profile-1_0-final.html
- OpenID for Verifiable Credential Issuance 1.0 (OpenID4VCI):
  https://openid.net/specs/openid-4-verifiable-credential-issuance-1_0.html

Los requisitos MUST y SHOULD deben conservar su significado normativo y no convertirse automáticamente en restricciones adicionales del framework.

### 11.2 Mecanismos de prueba

La evaluación debe distinguir el mecanismo de prueba declarado, el mecanismo efectivamente verificado y las garantías exigibles para ese mecanismo.

En particular:

- El mecanismo `jwt` puede acreditar posesión mediante una prueba criptográfica firmada.
- El mecanismo `attestation` requiere evaluar la evidencia de atestación conforme a sus reglas específicas.
- No debe imponerse universalmente una prueba independiente de posesión cuando el mecanismo normativo aplicable establece una garantía diferente.
- La selección de un mecanismo declarado no constituye por sí misma evidencia de su verificación.
- Un mecanismo no soportado o insuficientemente verificado debe rechazarse.

La admisión definitiva de cada mecanismo debe depender de las capacidades efectivamente implementadas y de la configuración de credencial correspondiente.

### 11.3 Key Attestation

Debe distinguirse entre:

- La obligación de soportar Key Attestation cuando corresponda al perfil.
- La exigencia de presentar Key Attestation en una solicitud concreta.
- Las recomendaciones de interoperabilidad.
- Las restricciones adicionales establecidas por un ecosistema.

La existencia de una prueba de posesión válida no acredita automáticamente una Key Attestation válida.

La existencia de una Key Attestation válida tampoco demuestra por sí sola que todas las claves requeridas para la emisión estén correctamente vinculadas.

Los requisitos aplicables deben determinarse mediante una fuente confiable y no mediante indicadores suministrados por el solicitante.

### 11.4 Verificación criptográfica y confianza

La validación especializada de Key Attestation debe contemplar, cuando corresponda al mecanismo y al perfil:

- Integridad y autenticidad de la evidencia.
- Identidad verificable de la evidencia presentada.
- Certificados y cadenas de confianza.
- Identidad del emisor y relaciones de confianza exigibles.
- Identificación de las claves acreditadas.
- Correspondencia con las claves requeridas para la emisión.
- Capacidades criptográficas mínimas aplicables.

Foundation conserva la separación entre contratos de evaluación y adaptadores criptográficos concretos.

Una huella SHA-256 permite comparar identidades de evidencia, pero no sustituye la verificación criptográfica de dicha evidencia.

### 11.5 Vinculación de claves y emisión por lotes

La conformidad debe evaluarse respecto de todas las claves relevantes para la emisión.

Para escenarios por lotes:

- Debe comprobarse la cobertura completa de las claves exigibles.
- Deben distinguirse requisitos obligatorios y recomendaciones sobre atestación conjunta.
- No debe aceptarse una cobertura parcial como si acreditara el conjunto completo.
- Las políticas adicionales del ecosistema deben mantenerse diferenciadas de los requisitos normativos.

### 11.6 Implicaciones para la arquitectura de I4

El modelo de requisitos debe expresar las garantías necesarias para cada mecanismo, sin equiparar automáticamente posesión y atestación.

La orquestación debe:

1. Validar el contexto y la configuración de credencial.
2. Seleccionar los verificadores aplicables mediante información confiable.
3. Obtener evidencia verificada del mecanismo efectivamente utilizado.
4. Evaluar las garantías exigidas y la vinculación de claves.
5. Rechazar por defecto las solicitudes con evidencia obligatoria ausente, inválida o insuficiente.

Los resultados declarados por el solicitante no pueden sustituir la verificación especializada.

La evolución de estos contratos debe preservar la compatibilidad de los flujos existentes de OpenID4VCI y del mecanismo `jwt`.

### 11.7 Verificaciones pendientes para aprobación

Antes de cambiar el estado de este documento deben completarse y registrarse:

- La matriz normativa definitiva de mecanismos de prueba admitidos.
- La identificación exacta de obligaciones MUST y recomendaciones SHOULD.
- Los requisitos concretos de certificados y cadenas de confianza.
- Las condiciones normativas de vinculación criptográfica.
- Las reglas definitivas de emisión por lotes.
- Los algoritmos y capacidades criptográficas mínimas.
- La trazabilidad entre requisitos normativos, contratos, implementación y pruebas de aceptación.

#### 11.7.1 Matriz normativa preliminar

Esta matriz registra requisitos contrastados con HAIP 1.0 Final y OpenID4VCI 1.0 Final. Su inclusión no constituye evidencia de implementación ni aprobación arquitectónica.

| ID | Requisito | Fuente | Carácter |
|---|---|---|---|
| N-01 | Wallets HAIP deben soportar Key Attestations. | HAIP 4.5.1 | MUST |
| N-02 | El uso interoperable del formato del Apéndice D y los tipos jwt con key_attestation y attestation es recomendado para los ecosistemas aplicables. | HAIP 4.5.1 | SHOULD |
| N-03 | Si se declara proof_types_supported para la configuración, la solicitud debe incluir proofs con exactamente un tipo de prueba. | OpenID4VCI 8.2 | MUST |
| N-04 | El tipo attestation contiene exactamente un JWT y no exige una prueba independiente de posesión de las claves atestadas. | OpenID4VCI F | MUST estructural |
| N-05 | Cuando se requiere Key Attestation, el Issuer debe comunicarlo mediante metadata o un mecanismo fuera de banda. | OpenID4VCI D | MUST |
| N-06 | Si el Issuer no requiere Key Attestation, key_attestations_required no debe estar presente en la metadata. | OpenID4VCI 12.2.4 | MUST NOT |
| N-07 | Una atestación utilizada con jwt requiere exp, y la prueba JWT debe estar firmada por una clave incluida en attested_keys. | OpenID4VCI D.1 | MUST |
| N-08 | La verificación debe aplicar las reglas correspondientes de firma, algoritmo, temporalidad y nonce. | OpenID4VCI F.4 | MUST |
| N-09 | Para Key Attestation en formato D.1 bajo HAIP, x5c debe contener el certificado firmante, excluir el trust anchor y no utilizar un certificado firmante autofirmado. | HAIP 4.5.1 | MUST / MUST NOT |
| N-10 | En emisión por lotes con vinculación criptográfica requerida, las claves utilizadas deberían estar atestadas conjuntamente. | HAIP 4.5.1 | SHOULD |
| N-11 | ES256 debe soportarse en las verificaciones aplicables y SHA-256 en los digests de formatos perfilados. | HAIP 7 y 8 | MUST |

#### 11.7.2 Trazabilidad pendiente

Para cada requisito anterior resta registrar:

- Contrato de Foundation aplicable.
- Verificador criptográfico o adaptador responsable.
- Punto de integración en la orquestación I4.
- Pruebas de aceptación positivas y negativas.
- Decisiones del ecosistema SIF diferenciadas de los requisitos normativos.

Los perfiles concretos de certificados, los trust anchors admitidos y las políticas adicionales de seguridad permanecen sujetos a decisiones arquitectónicas explícitas.

Hasta completar estas verificaciones y la revisión arquitectónica correspondiente, el documento permanece en estado Draft for Review.
