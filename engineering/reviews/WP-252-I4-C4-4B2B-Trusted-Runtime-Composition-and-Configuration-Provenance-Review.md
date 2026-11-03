---
id: WP-252-I4-C4-4B2B-TRUSTED-RUNTIME-COMPOSITION
title: WP-252 I4 Trusted Runtime Composition and Configuration Provenance Review
summary: Audita la procedencia de politicas, asociaciones de certificados y fronteras de confianza para una composicion HAIP futura.
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
  - runtime
  - dependency-injection
  - trusted-configuration
  - fail-closed
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0005
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Trusted Runtime Composition and Configuration Provenance Review

## 1. Objetivo

Auditar las precondiciones de composicion segura
del futuro verificador integral de Key Attestation.

Esta revision complementa C4.4A, C4.4B0, C4.4B1
y C4.4B2A.

No crea un Runtime composition root.
No habilita Key Attestation integral.
No cambia interfaces publicas.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 15399f6.
- PHPUnit: 2931 tests, 19714 assertions.
- PHPStan: 3132 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Evidencia de implementacion existente

El proveedor de politicas configuradas implementa
OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface.

Su constructor recibe objetos TrustPolicy ya creados.

El proveedor selecciona la politica mediante
coincidencia exacta de Credential Issuer.

No valida por si mismo la procedencia institucional
de la configuracion que recibe.

El resolvedor de identidad del emisor recibe
el proveedor y asociaciones explicitas.

Cada asociacion contiene:

- Credential Issuer.
- Huella SHA-256 DER del certificado firmante.
- Identidad del emisor de Key Attestation.

El resolvedor verifica firma ES256, cadena X.509,
vinculacion explicita y autorizacion por TrustPolicy.

No produce KeyAttestationVerificationResult valido.

El coordinador Foundation consume contratos abstractos.

Compara identificadores del contexto de validacion
y del contexto HAIP antes de continuar.

Esa comparacion no demuestra por si sola que
ambos identificadores procedan de una operacion
autenticada por la infraestructura.

## 4. Fronteras de confianza

### 4.1 Configuracion del Runtime

La politica de confianza y las vinculaciones deben
provenir de una fuente administrada por la autoridad
responsable de la configuracion del Credential Issuer.

No se admiten trust anchors, autorizaciones o
vinculaciones provenientes de headers JOSE,
claims JWT o parametros enviados por la Wallet.

Un archivo, variable de entorno o base de datos
no es automaticamente una fuente confiable.

Su administracion, integridad, permisos y procedencia
deben evaluarse en la composicion concreta.

### 4.2 Contexto de emision

La identidad del Credential Issuer que selecciona
la TrustPolicy debe provenir del contexto autorizado
y verificado de la operacion de emision.

No es suficiente que dos objetos contengan
cadenas de texto coincidentes.

### 4.3 Evidencia criptografica

El certificado firmante se obtiene de la evidencia
estructural y se valida contra anchors configurados.

La huella vinculada procede del certificado DER
utilizado en la verificacion criptografica.

Un claim iss no puede reemplazar la vinculacion
confiable del certificado.

### 4.4 Resultado parcial

La identidad del firmante autenticado constituye
un resultado especializado y limitado.

No acredita frescura, revocacion, EKU adicional,
claves atestadas, nonce, replay ni conformidad total.

## 5. Controles de composicion requeridos

RC-01: El Runtime selecciona una fuente de configuracion
autorizada y administrada fuera de la solicitud Wallet.

RC-02: Los trust anchors y emisores autorizados
proceden exclusivamente de esa configuracion.

RC-03: El Credential Issuer utilizado para resolver
la TrustPolicy procede del contexto autenticado.

RC-04: El proveedor de TrustPolicy se construye
sin configuraciones predeterminadas entre emisores.

RC-05: Las asociaciones entre certificados e identidades
son explicitas y estan separadas por Credential Issuer.

RC-06: La configuracion rechaza entradas duplicadas,
ambiguas o inconsistentes antes de aceptar operaciones.

RC-07: La rotacion de certificados y autoridades
requiere cambios controlados y trazables.

RC-08: Un error de lectura, permisos, integridad
o inicializacion no habilita una politica alternativa.

RC-09: La decision de no exigir revocacion debe
estar autorizada por la politica confiable aplicable.

RC-10: Ninguna identidad parcial del resolvedor puede
transformarse directamente en verificacion integral valida.

RC-11: Foundation conserva independencia de Adapters
y consume exclusivamente los contratos necesarios.

RC-12: La activacion de un verificador integral
requiere completar todas las garantias aplicables
y superar pruebas criptograficas de extremo a extremo.

## 6. Secuencia propuesta para el Runtime

Paso 1: Resolver identidad autorizada del Credential Issuer.

Paso 2: Obtener configuracion confiable del Issuer.

Paso 3: Construir TrustPolicy inmutable.

Paso 4: Construir proveedor de politicas explicitas.

Paso 5: Obtener asociaciones de certificados desde
la misma frontera administrativa confiable.

Paso 6: Construir resolvedor especializado.

Paso 7: Componer posteriormente un verificador integral
que cumpla el contrato existente de Foundation.

Paso 8: Inyectar ese verificador en la coordinacion
solo despues de completar las garantias obligatorias.

El paso 7 y el paso 8 NO se ejecutan en C4.4B2B.

## 7. Riesgos y decisiones pendientes

P-01: Definir origen concreto de configuracion
segura por aplicacion o Credential Issuer.

P-02: Definir controles de permisos, integridad,
auditoria y administracion de esa configuracion.

P-03: Determinar el origen autenticado del identificador
de Credential Issuer en el flujo real de emision.

P-04: Definir como se construyen politicas y
asociaciones en el Runtime sin introducir dependencia
inversa desde Foundation hacia Adapters.

P-05: Definir comportamiento ante fallos operativos,
errores de lectura y configuraciones incompletas.

P-06: Definir politica institucional para rotacion,
sustitucion y retiro de certificados firmantes.

P-07: Determinar la gobernanza de revocacion
cuando una politica permite no exigir su comprobacion.

P-08: Impedir que un consumidor utilice el resultado
parcial de identidad como autorizacion de emision.

P-09: Completar temporalidad, restricciones X.509,
revocacion y binding de claves antes de activacion.

## 8. Matriz de pruebas futuras

Los siguientes escenarios son criterios de aceptacion
previstos. No se consideran implementados en esta revision.

| ID | Escenario | Resultado esperado |
|---|---|---|
| T-01 | Trust anchors aportados por Wallet | Rechazo |
| T-02 | Politica inexistente para Credential Issuer | Rechazo |
| T-03 | Contexto de emision no autenticado | Rechazo |
| T-04 | Configuracion cruzada entre Issuers | Rechazo |
| T-05 | Asociacion de certificado no autorizada | Rechazo |
| T-06 | Configuracion duplicada o ambigua | Fallo controlado |
| T-07 | Rotacion sin actualizar vinculaciones | Rechazo |
| T-08 | Error de lectura de configuracion | Fallo cerrado |
| T-09 | Revocacion exigible sin servicio | Rechazo |
| T-10 | JWT antiguo con identidad de firmante valida | Sin aceptacion integral |
| T-11 | Resultado parcial utilizado como verificadores completos | Integracion prohibida |
| T-12 | Todas las garantias futuras acreditadas | Aceptacion solo tras pruebas integrales |

La aprobacion de la composicion requiere pruebas
independientes del Runtime y del verificador real.

Los mocks no demuestran conformidad criptografica.

## 9. Riesgo de configuraciones permisivas

La politica TrustPolicy admite configurar
revocationCheckRequired=false.

Ese valor no acredita ausencia de revocacion.

Tampoco representa autorizacion universal para
omitir la comprobacion en cualquier ecosistema.

El Runtime debera establecer que esa decision
es normativa y administrativamente admisible.

No se puede desactivar una garantia obligatoria
como solucion a una dependencia no implementada.

## 10. Compatibilidad y activacion

Mantener compatibilidad con WP-245 y WP-250.

Mantener distincion entre prueba jwt y attestation.

Preservar condiciones normativas diferenciadas
de nonce, replay y prueba de posesion.

Evitar modificaciones prematuras de contratos
publicos sin decision de compatibilidad.

No conectar el resolvedor de identidad
directamente como verificador integral.

No exponer un resultado parcial como valid=true.

## 11. Criterios para el siguiente incremento

C4.5: Definir y probar temporalidad con reloj
controlable, antiguedad maxima y tolerancia.

C4.6: Completar restricciones X.509 y revocacion
cuando resulte exigible por la politica.

C4.7: Autenticar y vincular claves atestadas
con las claves efectivamente solicitadas.

C4.8: Completar composicion, integracion y
pruebas positivas y negativas extremo a extremo.

C4.4B2B no aprueba de manera anticipada
ninguna de estas implementaciones.

## 12. Estado

Draft for Review.

Auditoria de precondiciones de composicion documentada.

No existe activacion integral aprobada.

Las dependencias pendientes continuan abiertas.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
