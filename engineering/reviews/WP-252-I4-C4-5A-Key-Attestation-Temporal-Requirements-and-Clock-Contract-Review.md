---
id: WP-252-I4-C4-5A-TEMPORAL-REQUIREMENTS
title: WP-252 I4 Key Attestation Temporal Requirements and Clock Contract Review
summary: Define reglas temporales y precondiciones de reloj controlable para Key Attestation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-11-03
updated: 2026-11-03
work_package: WP-252
tags:
  - security
  - haip
  - openid4vci
  - temporal-validation
  - clock
  - fail-closed
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Key Attestation Temporal Requirements and Clock Contract Review

## 1. Objetivo

Definir los requisitos de temporalidad de
Key Attestation antes de implementar el evaluador.

Esta revision no habilita el verificador integral.

No introduce interfaces nuevas ni cambia Foundation.

Las decisiones se clasifican como:

- Obligacion normativa.
- Politica configurable del Credential Issuer.
- Restriccion de seguridad propia de SIF.
- Dependencia pendiente de implementacion.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 8fcaf51.
- PHPUnit: 2931 tests, 19714 assertions.
- PHPStan: 3132 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.
- C4.4: incrementos previstos completados.

## 3. Referencias normativas

### 3.1 OpenID4VCI 1.0 Final

Apendice D.1, Key Attestation in JWT format.

La Key Attestation JWT requiere iat entero.

El claim exp es opcional en general.

Exp DEBE estar presente cuando la Key Attestation
se utiliza con el mecanismo de prueba jwt.

Para el mecanismo attestation no corresponde
imponer automaticamente esa misma obligatoriedad.

El mecanismo y contexto deben obtenerse de la
operacion de emision autenticada y verificada.

Referencia:

https://openid.net/specs/openid-4-verifiable-credential-issuance-1_0-final.html

### 3.2 RFC 7519

Secciones 2, 4.1.4, 4.1.5 y 4.1.6.

exp establece el instante desde el cual
el JWT ya no debe aceptarse.

El tiempo actual debe ser anterior a exp,
salvo la tolerancia admisible para clock skew.

iat representa el instante de emision.

nbf representa el instante previo al cual
un token no debe aceptarse, cuando aplica.

RFC 7519 admite NumericDate como numero.

Para esta Key Attestation prevalece la
restriccion especifica de iat entero y
exp entero establecida por OpenID4VCI D.1.

Referencia:

https://www.rfc-editor.org/rfc/rfc7519

### 3.3 HAIP 1.0 Final

Apartado 4.5.1, Key Attestation.

HAIP contempla los mecanismos jwt con
key_attestation y attestation.

No define una antiguedad maxima universal
de 300 segundos para toda Key Attestation.

Referencia:

https://openid.net/specs/openid4vc-high-assurance-interoperability-profile-1_0-final.html

## 4. Configuracion ya disponible en SIF

OpenId4VcHaipKeyAttestationTrustPolicy dispone de:

- maximumAttestationAgeSeconds.
- allowedClockSkewSeconds.

El modelo valida:

- Antiguedad maxima entre 1 y 86400 segundos.
- Tolerancia de reloj entre 0 y 300 segundos.

Los valores predeterminados actuales son:

- maximumAttestationAgeSeconds: 300.
- allowedClockSkewSeconds: 0.

Estos valores pertenecen a la politica de SIF.

No deben presentarse como limites universales HAIP.

El objeto TrustPolicy no verifica temporalidad.

## 5. Matriz de decisiones temporales

TM-01: iat es obligatorio y entero.
Clasificacion: OpenID4VCI D.1.

TM-02: exp es obligatorio cuando la Key Attestation
se utiliza con el mecanismo de prueba jwt.
Clasificacion: OpenID4VCI D.1.

TM-03: exp puede faltar en el mecanismo attestation,
salvo restriccion adicional de politica confiable.
Clasificacion: OpenID4VCI D.1 y politica.

TM-04: exp presente debe evaluarse siempre.
Un JWT vencido no puede aceptarse integralmente.
Clasificacion: RFC 7519.

TM-05: La antiguedad maxima se obtiene de la
TrustPolicy correspondiente al Credential Issuer.
Clasificacion: politica SIF.

TM-06: La tolerancia de reloj se obtiene de
allowedClockSkewSeconds en TrustPolicy.
Clasificacion: politica SIF.

TM-07: Un iat futuro fuera de tolerancia sera
rechazado por la politica temporal de SIF.
Clasificacion: restriccion SIF, no regla universal JWT.

TM-08: Un iat anterior a la ventana maxima
permitida sera rechazado por la politica SIF.
Clasificacion: restriccion SIF.

TM-09: La relacion exp > iat se establece como
restriccion inicial conservadora de SIF.
Su alcance debe verificarse antes de implementacion.
Clasificacion: decision SIF propuesta.

TM-10: La evaluacion utiliza un instante actual
obtenido de un reloj controlable e inyectable.
Clasificacion: requisito arquitectonico ADR-0006.

TM-11: Las comprobaciones temporales no obtienen
datos de confianza de un contexto Wallet no verificado.
Clasificacion: frontera de confianza.

TM-12: El evaluador temporal no produce por si solo
KeyAttestationVerificationResult valido.
Clasificacion: frontera de confianza.

TM-13: La validacion temporal del JWT es distinta
de la validacion temporal de certificados X.509
y de la validez del contexto de emision.
Clasificacion: separacion de responsabilidades.

TM-14: Un error de reloj, politica o contexto
temporal obligatorio debe fallar cerrado.
Clasificacion: seguridad SIF.

## 6. Reglas de evaluacion propuestas

### 6.1 Instante y unidades

Utilizar segundos Unix referidos a UTC.

No aplicar transformaciones por zona horaria local.

Utilizar un unico valor de now para evaluar
todos los claims del mismo JWT.

Evitar sumas y restas con desbordamiento.

### 6.2 iat

Rechazar iat ausente o no entero.

Rechazar si iat supera now mas tolerancia.

Rechazar si la antiguedad de iat excede
el maximo configurado mas tolerancia.

La tolerancia debe aplicarse expresamente
en los limites definidos por la politica.

### 6.3 exp

Si exp existe, debe ser entero.

La regla ordinaria RFC 7519 requiere
que now sea estrictamente anterior a exp.

Una tolerancia configurada puede ampliar
la ventana aceptada por desfase de relojes.

La igualdad con el limite efectivo de
vencimiento debe producir rechazo.

Cuando exp falte:

- Mecanismo jwt: rechazo.
- Mecanismo attestation: evaluar la politica
  de antiguedad basada en iat.

No se acepta un exp ausente sin verificar
el mecanismo real y las reglas aplicables.

### 6.4 Relacion entre iat y exp

Se propone rechazar exp menor o igual a iat.

Esta regla adicional no debe atribuirse
a una obligacion textual de OpenID4VCI D.1.

Su adopcion final requiere revision
de compatibilidad con los mecanismos soportados.

### 6.5 nbf

El comportamiento ante nbf presente requiere
una decision expresa en el perfil admitido.

No ignorar silenciosamente restricciones
temporales que afecten la aceptacion del JWT.

La implementacion inicial puede fallar
cerrada ante un caso no soportado.

## 7. Contrato de reloj controlable

Antes de crear una interfaz se debera buscar
y evaluar si SIF ya dispone de un contrato
de reloj compatible.

No duplicar abstracciones existentes.

El reloj debe poder:

- Proporcionar un instante actual verificable.
- Sustituirse por un reloj fijo en pruebas.
- Permitir pruebas de limites exactos.
- Evitar dependencias directas de time()
  en el evaluador temporal.
- Mantener independencia de Foundation respecto
  de implementaciones concretas del Runtime.

La infraestructura concreta sera una
decision de implementacion posterior.

## 8. Frontera de autenticacion

El evaluador temporal puede procesar claims
estructuralmente decodificados.

Pero un resultado temporal favorable sobre
claims no autenticados no acredita su origen.

La aceptacion integral requiere que la
evidencia exacta haya superado previamente
las verificaciones criptograficas aplicables.

La vinculacion de identidad C4.4 no reemplaza
la evaluacion temporal C4.5.

La evaluacion temporal tampoco reemplaza
la vinculacion de identidad.

## 9. Matriz de pruebas prevista

| ID | Escenario | Resultado esperado |
|---|---|---|
| T-01 | iat ausente | Rechazo |
| T-02 | iat no entero | Rechazo |
| T-03 | iat futuro fuera de tolerancia | Rechazo |
| T-04 | iat futuro dentro del limite permitido | Temporalmente admisible |
| T-05 | iat mas antiguo que el maximo permitido | Rechazo |
| T-06 | iat exactamente en el limite de antiguedad | Evaluar igualdad |
| T-07 | Mecanismo jwt sin exp | Rechazo |
| T-08 | Mecanismo attestation sin exp | Puede ser admisible |
| T-09 | exp presente pero vencido | Rechazo |
| T-10 | exp igual a now sin tolerancia | Rechazo |
| T-11 | exp igual al limite efectivo con tolerancia | Rechazo |
| T-12 | exp posterior a now y dentro de politica | Temporalmente admisible |
| T-13 | exp menor o igual a iat | Rechazo por politica SIF |
| T-14 | Reloj o politica no disponible | Rechazo |
| T-15 | Claims no autenticados con tiempos validos | Sin conformidad integral |
| T-16 | Mecanismo de prueba no acreditado | Rechazo |
| T-17 | nbf no soportado o contradictorio | Fallo cerrado pendiente |
| T-18 | JWT vigente con certificado X.509 vencido | Rechazo en control X.509 |

Los resultados temporalmente admisibles
no representan aceptacion de Key Attestation.

Los casos de igualdad deben precisarse
antes de escribir el evaluador.

## 10. Dependencias pendientes

P-01: Auditar contratos de reloj ya existentes
en Foundation y Runtime.

P-02: Definir interfaz o reutilizacion aprobada
sin duplicar abstracciones.

P-03: Definir representacion del resultado
temporal sin confundirlo con conformidad total.

P-04: Obtener proofType desde el contexto
de emision verificado.

P-05: Confirmar limites de igualdad, tolerancia
y comportamiento ante desbordamientos.

P-06: Resolver formalmente la compatibilidad
del control exp > iat.

P-07: Definir tratamiento de nbf adicional.

P-08: Implementar pruebas con reloj fijo.

P-09: Integrar temporalidad exclusivamente
en el verificador integral futuro.

## 11. Secuencia posterior

C4.5B: Auditoria de contratos de reloj existentes
y decision de dependencia temporal reutilizable.

C4.5C: Evaluador temporal aislado y pruebas
positivas, negativas y de valores limite.

C4.5D: Revision de compatibilidad y fronteras.

C4.6: Restricciones X.509 y revocacion.

C4.7: Claves atestadas y binding criptografico.

C4.8: Composicion y regresion integral.

## 12. Estado

Draft for Review.

La matriz normativa y de politica es preliminar.

No se ha implementado un evaluador temporal nuevo.

No se habilita verificador integral.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
