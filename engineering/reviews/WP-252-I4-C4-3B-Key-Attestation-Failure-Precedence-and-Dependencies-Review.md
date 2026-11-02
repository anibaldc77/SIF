---
id: WP-252-I4-C4-3B-FAILURE-PRECEDENCE
title: WP-252 I4 Key Attestation Failure Precedence and Dependencies Review
summary: Define precedencia de fallos, dependencias confiables y escenarios negativos del verificador integral.
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
  - key-attestation
  - fail-closed
  - trust-policy
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Key Attestation Failure Precedence and Dependencies Review

## 1. Objetivo

Definir el orden logico de evaluacion y las responsabilidades
de las dependencias necesarias para verificar Key Attestation.

Esta revision complementa C4.2 y C4.3A.

No implementa ni habilita un verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: aecad9c.
- PHPUnit: 2901 tests, 19307 assertions.
- PHPStan: 3127 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.
- C4.2: matriz de garantias.
- C4.3A: gates G-01 a G-12 y tests T-01 a T-12.

## 3. Principio de fallo cerrado

Ninguna comprobacion aislada puede producir una
Key Attestation integralmente valida.

La ausencia de una garantia obligatoria implica rechazo,
aunque todas las comprobaciones anteriores hayan pasado.

No debe utilizarse un indicador declarado por el solicitante
para sustituir una verificacion criptografica.

Las verificaciones no aplicables deben determinarse mediante
el mecanismo normativo y la configuracion confiable.

Un resultado no concluyente nunca equivale a aceptacion.

## 4. Precedencia logica de evaluacion

La siguiente secuencia es un orden de dependencias, no
necesariamente una lista de codigos de error externos.

F-01: Identificar el Credential Issuer desde el
contexto autenticado de la operacion.

F-02: Resolver la TrustPolicy desde configuracion
confiable y comprobar su correspondencia con el Issuer.

F-03: Comprobar existencia del JWT exacto y realizar
parsing estructural limitado, sin otorgar confianza.

F-04: Verificar que el algoritmo esta soportado
y autorizado por la politica confiable.

F-05: Comprobar criptograficamente la firma del JWT.

F-06: Validar la cadena X.509 contra los trust anchors
confiables, nunca contra certificados aportados como anchors.

F-07: Identificar y autorizar al emisor de atestacion
mediante identidad autenticada y politica aplicable.

F-08: Aplicar temporalidad y restricciones X.509
exigibles, incluyendo revocacion cuando corresponda.

F-09: Extraer las claves acreditadas desde claims
autenticados y validar sus representaciones criptograficas.

F-10: Construir la identidad de evidencia vinculada
al JWT exacto sometido a verificacion.

F-11: Comprobar en la orquestacion el mecanismo de prueba,
nonce, replay, configuracion y cobertura de claves exigible.

F-12: Producir una decision positiva solo si todas
las garantias aplicables estan acreditadas.

El orden final entre controles criptograficos independientes
podra optimizarse sin alterar sus precondiciones de confianza.

No se debe interpretar un claim como autenticado antes
de verificar la firma y establecer su contexto de confianza.

## 5. Dependencias existentes

D-01: OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface.
Origen permitido: configuracion controlada por Credential Issuer.

D-02: OpenId4VcHaipKeyAttestationTrustPolicy.
Contiene anchors, emisores autorizados, algoritmos y requisitos.

D-03: OpenId4VcHaipUnverifiedKeyAttestationJwt.
Realiza parsing estructural, sin autenticar claims.

D-04: OpenId4VcHaipUntrustedEs256SignatureMatcher.
Verifica firma contra la clave del certificado presentado.

D-05: OpenId4VcHaipExplicitX509TrustEvaluator.
Evalua cadena X.509 contra anchors explicitos.

D-06: OpenId4VcHaipKeyAttestationVerifierInterface.
Contrato del futuro componente especializado.

D-07: OpenId4VcHaipKeyAttestationVerificationResult.
Transporta el resultado, sin ejecutar criptografia.

D-08: OpenId4VcHaipKeyPossessionVerificationCoordinator.
Coordina el mecanismo y el contexto de emision.

Los componentes D-03 a D-05 existen en Adapters y
Foundation conserva copias historicas temporalmente.

## 6. Dependencias tecnicas pendientes

P-01: Implementacion confiable de TrustPolicyProvider
o mecanismo equivalente controlado por el Issuer.

P-02: Asociacion autenticada entre el certificado firmante
y la identidad autorizada del emisor de atestacion.

P-03: Evaluador temporal con reloj inyectable y
politica de antiguedad y tolerancia.

P-04: Comprobacion de restricciones X.509 adicionales,
incluidos los EKU configurados.

P-05: Servicio de revocacion con semantica explicita
de fallo y disponibilidad.

P-06: Extraccion y canonicalizacion verificable
de claves criptograficas desde attested_keys.

P-07: Vinculacion entre claves acreditadas y claves
efectivamente requeridas para la emision.

P-08: Composicion del verificador integral con
resultados no ambiguos y rechazo por defecto.

Estas dependencias no justifican automaticamente
crear una interfaz nueva por cada responsabilidad.

La decision debe considerar contratos existentes,
neutralidad de Foundation y necesidades comprobadas.

## 7. Distribucion de responsabilidades

### 7.1 Adaptador de Key Attestation

Debe garantizar:

- Politica del Credential Issuer obtenida confiablemente.
- Parsing del JWT recibido.
- Firma criptografica y algoritmo autorizado.
- Confianza X.509 y restricciones exigibles.
- Identidad autenticada del emisor de atestacion.
- Temporalidad propia de la evidencia.
- Claves obtenidas de contenido autenticado.
- Identidad verificada del JWT exacto.
- Rechazo cuando una garantia exigible no pueda probarse.

### 7.2 Foundation y coordinacion de emision

Deben evaluar:

- Contexto y configuracion de la credencial.
- Mecanismo jwt o attestation efectivamente utilizado.
- Prueba de posesion cuando corresponda.
- Nonce y proteccion contra repeticion exigibles.
- Reglas condicionales del mecanismo.
- Cobertura completa de las claves solicitadas.
- Requisitos particulares de emision por lotes.

No debe duplicarse la verificacion criptografica en Foundation.

### 7.3 Limite del contrato actual

El metodo verify(attestation, issuerIdentifier) no recibe
directamente el tipo de prueba exterior, el nonce ni
las claves requeridas por la solicitud de emision.

Por consiguiente, algunas garantias pertenecen
necesariamente al contexto de coordinacion.

Antes de implementar controles que dependen del mecanismo
exterior, se debera decidir como proporcionar ese contexto
de manera confiable y compatible.

No se autoriza inferirlo desde flags o claims no verificados.

## 8. Semantica de los resultados

Un resultado valido requiere que el verificador
haya acreditado todas las garantias de su responsabilidad.

El resultado invalid debe:

- Mantener verifiedKeyReferences vacio.
- No presentar identidades no autenticadas como verificadas.
- Identificar un motivo estable de rechazo.
- Evitar exponer certificados, claves, JWT o detalles internos.

La semantica de errores operativos debe garantizar
que no se produzca emision ante dependencia fallida.

Las excepciones tecnicas no deben transformarse en valid=true.

Los mensajes publicos deben evitar revelar diferencias
sensibles en la configuracion de confianza.

Los codigos concretos y su estabilidad quedan sujetos
a una decision posterior; esta revision no los introduce.

## 9. Matriz de escenarios negativos

| ID | Condicion | Resultado esperado |
|---|---|---|
| T-01 | TrustPolicy ausente | Rechazo sin aceptar anchors externos |
| T-02 | JWT ausente o malformado | Rechazo antes de utilizar claims |
| T-03 | Firma invalida o alg prohibido | Rechazo sin identidad autenticada |
| T-04 | Cadena X.509 no confiable | Rechazo aunque ES256 sea valida |
| T-05 | Emisor no autorizado | Rechazo aunque la cadena sea valida |
| T-06 | Evidencia vencida o temporalidad invalida | Rechazo |
| T-07 | Revocacion obligatoria no comprobable | Rechazo |
| T-08 | EKU o Key Usage obligatorio incumplido | Rechazo |
| T-09 | JWT sustituido despues de identificarlo | Rechazo |
| T-10 | Claves declaradas sin acreditacion criptografica | Rechazo |
| T-11 | Cobertura incompleta de claves de lote | Rechazo |
| T-12 | Todas las garantias exigibles verificadas | Unico camino potencial de aceptacion |

T-12 no se considera satisfecho hasta contar con
pruebas criptograficas positivas de extremo a extremo.

Los mocks solo acreditan contratos y orquestacion.

## 10. Condiciones de no aplicabilidad

Las verificaciones no aplicables deben proceder
de reglas normativas o configuracion confiable.

El mecanismo attestation no exige universalmente
posesion independiente de las claves atestadas.

La obligacion de exp para atestacion utilizada con
prueba jwt no se extrapola a todos los mecanismos.

La ausencia de obligacion de revocacion debe provenir
de una politica confiable, nunca de la Wallet.

La omision de una verificacion exigible constituye fallo.

## 11. Criterios para implementacion posterior

Antes de introducir un verificador concreto:

- Resolver el limite contextual del contrato existente.
- Definir identidad autenticada del emisor.
- Definir la politica de revocacion y error operativo.
- Definir el tratamiento de tiempo y exp condicional.
- Definir canonicalizacion y correspondencia de JWK.
- Establecer trazabilidad de cada gate a pruebas reales.
- Mantener comportamiento fail-closed por defecto.
- Mantener Foundation independiente de Adapters.
- Preservar compatibilidad con consumidores historicos.

La implementacion debera fragmentarse en incrementos
auditables y reversibles, con pruebas positivas y negativas.

## 12. Estado

Draft for Review.

No se aprueba todavia un verificador integral.
No se habilita produccion de resultados positivos.
ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
