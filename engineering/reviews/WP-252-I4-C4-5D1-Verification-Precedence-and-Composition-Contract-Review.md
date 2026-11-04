---
id: WP-252-I4-C4-5D1-VERIFICATION-PRECEDENCE
title: WP-252 I4 Verification Precedence and Composition Contract Review
summary: Define dependencias de confianza, precedencia y condiciones para una futura composicion integral de Key Attestation.
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
  - verification
  - precedence
  - trust-boundary
  - composition
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Verification Precedence and Composition Contract Review

## 1. Objetivo

Formalizar las dependencias entre garantias
de Key Attestation antes de su composicion.

Distinguir:

- Dependencias necesarias para confiar.
- Orden de ejecucion tecnicamente conveniente.
- Condiciones de rechazo.
- Condiciones de aceptacion integral.

No se implementa un verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 648776b.
- PHPUnit: 2956 tests, 19786 assertions.
- PHPStan: 3135 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Evidencia de C4.5D0

La auditoria read-only comprobo:

- Once componentes existentes.
- Veintinueve archivos de pruebas HAIP.
- Ningun consumidor productivo externo
  identificado para el evaluador temporal
  dentro de las rutas inspeccionadas.
- Ausencia de cambios locales causados
  por la auditoria.

Se identificaron controles parciales:

- Parsing JOSE.
- Verificacion ES256.
- Confianza X.509.
- Binding entre certificado y emisor.
- Politica por Credential Issuer.
- Evaluacion temporal.
- Coordinacion de pruebas.
- Resultado de conformidad.

Los componentes no constituyen todavia
una verificacion integral de Key Attestation.

## 4. Dependencias entre garantias

### 4.1 Identificacion de evidencia

La composicion debe trabajar sobre
un JWT compacto concreto.

La identidad de la evidencia puede
representarse mediante SHA-256.

La coincidencia de fingerprints solamente
establece identidad de bytes.

No acredita la autenticidad de esos bytes.

### 4.2 Parsing JOSE

El parser puede extraer header, claims,
firma y certificados presentados.

Esos elementos permanecen no verificados
hasta superar las comprobaciones exigibles.

El parsing es una dependencia estructural
de las comprobaciones posteriores.

### 4.3 Verificacion de firma ES256

La firma ES256 se contrasta contra la
clave publica del certificado presentado.

Un resultado positivo acredita la
correspondencia criptografica de firma
con esa clave, dentro del perfil admitido.

No acredita por si mismo la confianza
del certificado ni la autorizacion
del emisor de Key Attestation.

### 4.4 Confianza X.509

Debe establecerse la confianza de la
cadena respecto de trust anchors
procedentes de configuracion confiable.

Deben comprobarse las restricciones
aplicables a certificados.

La evaluacion actual de vigencia X.509
utiliza tiempo del sistema directamente.

Su tratamiento corresponde a C4.6.

### 4.5 Identidad autorizada del emisor

La identidad debe derivarse de
vinculaciones y configuracion confiables.

El claim iss por si mismo no acredita
una identidad autorizada.

Debe existir correspondencia entre
Credential Issuer, politica, certificado
y emisor de Key Attestation autorizado.

### 4.6 Evaluacion temporal JWT

El evaluador temporal recibe:

- Claims temporales.
- Proof type.
- TrustPolicy.
- ClockInterface.

Su resultado favorable es parcial.

Los claims evaluados deben corresponder
exactamente a la evidencia autenticada.

El proof type debe proceder del
contexto de prueba acreditado.

Para jwt se exige exp conforme
al perfil documentado en C4.5C0.

Para attestation se aplica su
semantica temporal correspondiente.

### 4.7 Revocacion y EKU

Los controles exigidos por la politica
deben verificarse efectivamente.

Si una comprobacion obligatoria
no esta implementada o disponible,
la evaluacion integral debe rechazar.

El resolver de identidad actualmente
rechaza politicas que exigen controles
de revocacion o EKU adicionales
que ese componente no implementa.

No debe considerarse esa limitacion
como comprobacion positiva de revocacion.

### 4.8 Vinculacion de claves

La evidencia de claves atestadas debe
vincularse con las claves relevantes
de la solicitud de emision.

La correspondencia no puede basarse
solamente en referencias declaradas
unilateralmente por la Wallet.

Las garantias criptograficas y de
posesion exigibles deben acreditarse.

### 4.9 Contexto de emision

Ademas de Key Attestation, deben
preservarse las verificaciones
aplicables al Credential Proof:

- Mecanismo permitido.
- Identidad de Credential Issuer.
- Configuracion de credencial.
- Nonce y controles de replay.
- Posesion cuando corresponda.
- Requisitos de conformidad HAIP.

No todas estas responsabilidades
pertenecen al evaluador de Key Attestation.

## 5. Decisiones de precedencia

PR-01: Toda comprobacion debe estar
vinculada a la misma evidencia JWT exacta.

PR-02: El parsing estructural no
constituye autenticacion.

PR-03: El mecanismo jwt o attestation
debe quedar acreditado por el
contexto de verificacion correspondiente.

PR-04: La TrustPolicy debe seleccionarse
por Credential Issuer desde una
fuente confiable y aislada por emisor.

PR-05: La coincidencia ES256 contra
el certificado presentado no acredita
confianza X.509.

PR-06: La confianza X.509 requiere
sus propios controles independientes.

PR-07: La identidad autorizada del
emisor requiere vinculacion confiable,
no solamente el claim iss.

PR-08: La admisibilidad temporal
solo puede contar como garantia de
evidencia autentica una vez vinculada
al JWT criptograficamente comprobado.

PR-09: La ausencia de controles
obligatorios de revocacion o EKU
implica rechazo de conformidad integral.

PR-10: Las claves atestadas deben
vincularse con las claves requeridas
mediante garantias efectivamente verificadas.

PR-11: Las comprobaciones de nonce,
replay y Credential Proof conservaran
su responsabilidad arquitectonica.

PR-12: La aceptacion integral exige
la conjuncion de todas las garantias
aplicables y obligatorias.

PR-13: Se permite rechazo anticipado
por cualquier condicion invalida
sin ejecutar comprobaciones innecesarias.

PR-14: El orden de ejecucion puede
optimizarse, pero no puede reemplazar
dependencias logicas de confianza.

PR-15: Ningun resultado parcial puede
construir por si mismo un
KeyAttestationVerificationResult valido.

PR-16: Los errores de infraestructura,
configuracion, reloj o criptografia
deben fallar cerrados.

PR-17: Debe auditarse la interpretacion
unica de JSON, incluyendo claims
duplicados, antes de la composicion.

PR-18: La coherencia entre temporalidad
JWT y tiempo de validacion X.509
se revisara expresamente en C4.6.

## 6. Modelo de precedencia

### 6.1 Entrada y seleccion confiable

Antes de atribuir confianza deben
identificarse:

- Credential Issuer.
- Evidencia JWT exacta.
- Politica configurada.
- Mecanismo de prueba acreditado.

La solicitud no puede elegir
unilateralmente la politica aplicable.

### 6.2 Evaluaciones parciales

Las comprobaciones pueden ejecutarse
en distinto orden si mantienen sus
dependencias y sus identidades.

La evaluacion temporal puede utilizarse
para rechazo temprano.

Pero un resultado temporal positivo
no puede elevar claims no verificados
a la categoria de claims autenticos.

### 6.3 Decisiones de confianza

La aceptacion requiere demostrar:

- Integridad y autenticidad criptografica.
- Confianza certificada aplicable.
- Identidad de emisor autorizada.
- Admisibilidad temporal.
- Revocacion y restricciones exigibles.
- Vinculacion de claves.
- Contexto de emision conforme.

### 6.4 Resultado final

Un resultado integral valido
solamente puede producirse al
superar todas las condiciones exigibles.

Una garantia faltante, inconclusa,
no implementada o fallida
no puede convertirse en aprobacion.

## 7. Frontera Foundation / Adapters

Foundation conserva:

- Contratos.
- Politicas.
- Contextos y resultados.
- Evaluacion de requisitos.
- Reglas temporales independientes.
- Coordinacion de garantias.

Adapters conserva:

- Parsing especializado JOSE.
- Criptografia ES256.
- Evaluacion especializada X.509.
- Mecanismos de confianza criptografica.

Runtime debe componer implementaciones
confiables mediante dependencias explicitas.

No introducir dependencias desde
Foundation hacia Adapters concretos.

## 8. Escenarios de aceptacion posteriores

| ID | Escenario | Resultado requerido |
|---|---|---|
| S-01 | JWT sin firma autentica | Rechazo integral |
| S-02 | Firma correcta con certificado no confiable | Rechazo integral |
| S-03 | Certificado confiable con emisor no autorizado | Rechazo integral |
| S-04 | JWT autentico temporalmente vencido | Rechazo integral |
| S-05 | Claims de una evidencia y firma de otra | Rechazo integral |
| S-06 | Politica de Credential Issuer diferente | Rechazo integral |
| S-07 | Revocacion exigible sin comprobacion | Rechazo integral |
| S-08 | EKU adicional exigido sin comprobacion | Rechazo integral |
| S-09 | Clave atestada diferente de la requerida | Rechazo integral |
| S-10 | Proof type no acreditado | Rechazo integral |
| S-11 | Temporalidad favorable sin otras garantias | Sin conformidad integral |
| S-12 | Todas las garantias exigibles comprobadas | Elegible para resultado integral valido |

Estos escenarios son requisitos
para fases posteriores.

No se consideran implementados
por la presente revision.

## 9. Dependencias pendientes

P-01: Definir la representacion
confiable de evidencia autentica
para la composicion integral.

P-02: Precisar la procedencia
acreditada de proof type en ambos
mecanismos de Credential Proof.

P-03: Resolver precedencia operativa
y tratamiento de errores sin alterar
las dependencias de confianza.

P-04: Unificar o coordinar el
instante temporal de X.509 y JWT
con un reloj controlable.

P-05: Implementar comprobaciones
de revocacion exigibles.

P-06: Resolver restricciones EKU
adicionales de los ecosistemas.

P-07: Completar binding criptografico
entre claves atestadas y claves
relevantes de la solicitud.

P-08: Auditar interpretacion JSON
y preparar las pruebas integrales
de aislamiento entre emisores.

## 10. Compatibilidad

Preservar:

- ADR-0006.
- EG-585 y EG-588.
- WP-245 y WP-250.
- Interfaces publicas existentes.
- Mecanismos jwt y attestation.
- Semantica de Credential Proof.
- Resultados previos de conformidad.
- Configuracion independiente por emisor.
- Regla de rechazo por defecto.

No se aprueba ampliar interfaces
publicas en este incremento.

## 11. Continuidad

C4.5D: Revisar precedencia,
compatibilidad y dependencias.

C4.6: X.509, reloj, restricciones
de certificados y revocacion.

C4.7: Vinculacion criptografica
de claves atestadas.

C4.8: Composicion integral de
garantias y pruebas de aceptacion.

## 12. Estado

Draft for Review.

Contrato de precedencia propuesto.

Sin cambios de codigo productivo.

Sin activacion de Key Attestation integral.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
