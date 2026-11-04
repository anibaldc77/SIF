---
id: WP-252-I4-C4-6A1-X509-VERIFICATION-TIME
title: WP-252 I4 X509 Verification Time Architecture Decision
summary: Examina alternativas para validar cadenas X.509 con un instante verificable y controlado.
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
  - x509
  - openssl
  - clock
  - architecture
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - X509 Verification Time Architecture Decision

## 1. Objetivo

Establecer condiciones de seguridad y
compatibilidad para una futura validacion
X.509 con un instante temporal controlado.

No seleccionar prematuramente un backend
criptografico ni modificar el evaluador
actual hasta completar las pruebas.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: f73956c.
- PHPUnit: 2956 tests, 19786 assertions.
- PHPStan: 3135 archivos, sin errores.
- PHP: 8.2.32.
- OpenSSL PHP: 3.0.21.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Evidencia de C4.6A0

Se inspeccionaron doce componentes
y archivos de pruebas relevantes.

El evaluador X.509 actual utiliza:

- Trust anchors explicitos.
- Parsing X.509 con OpenSSL.
- Comprobaciones basicas de Key Usage.
- Comprobaciones de Basic Constraints.
- Verificacion de enlaces de certificados.
- openssl_x509_checkpurpose.
- X509_PURPOSE_ANY.
- time() para vigencia de certificados.

El evaluador temporal JWT utiliza
ClockInterface y obtiene una unica
lectura de reloj por evaluacion.

No se identificaron comprobaciones
funcionales OCSP/CRL en los adaptadores
HAIP examinados.

El resolver de emisor rechaza las
politicas que requieren revocacion
o EKU adicional que no implementa.

Esta evidencia no demuestra que
exista un verificador integral.

## 4. Problema identificado

La funcion PHP openssl_x509_checkpurpose
no ofrece un parametro para indicar
un instante arbitrario de verificacion.

Modificar exclusivamente la funcion
isCurrent para recibir ClockInterface
no garantiza que el motor OpenSSL
valide la cadena contra ese instante.

Por lo tanto, una misma evaluacion
podria utilizar dos relojes o
dos instantes de referencia.

El cambio no debe implementarse
sin resolver esa discrepancia.

## 5. Opciones tecnicas

### 5.1 Opcion A - Mantener el evaluador actual

Conservar la implementacion vigente
durante el periodo de transicion.

Ventajas:

- Preserva compatibilidad.
- No introduce dependencias adicionales.
- Mantiene las pruebas existentes.

Limitaciones:

- Usa tiempo del sistema.
- No permite pruebas temporales
  completamente deterministas.
- No asegura un instante compartido
  con la evaluacion temporal JWT.

No constituye la solucion objetivo.

### 5.2 Opcion B - Backend con tiempo explicito

Evaluar una implementacion especializada
que permita indicar el instante de
verificacion al motor X.509.

OpenSSL ofrece mecanismos nativos
de verificacion temporal explicita.

La herramienta openssl verify
incluye la opcion -attime.

La API nativa ofrece mecanismos
de configuracion del tiempo de
verificacion de certificados.

La adopcion requiere demostrar:

- Compatibilidad Windows y Linux.
- Aislamiento de trust anchors.
- Ejecucion segura.
- Gestion segura de temporales.
- Control de errores.
- Ausencia de shell injection.
- Disponibilidad controlada del backend.
- Regresion con cadenas existentes.

No se aprueba todavia una
dependencia de CLI o FFI.

### 5.3 Opcion C - API criptografica especializada

Evaluar una abstraccion de Adapters
que permita suministrar un
instante temporal explicito.

Foundation debera permanecer
independiente del proveedor concreto.

La implementacion podria utilizar
un backend criptografico con acceso
controlado a parametros X.509.

Se requiere revisar viabilidad,
mantenimiento, portabilidad y riesgos.

### 5.4 Opcion D - Doble comprobacion temporal

Combinar un ClockInterface inyectado
con openssl_x509_checkpurpose.

Esta alternativa no garantiza
que ambas comprobaciones utilicen
el mismo instante.

No puede declararse equivalente
a un verificador de tiempo unico.

No aprobar como solucion definitiva.

## 6. Decisiones de seguridad

TD-01: Preservar el evaluador
X.509 existente durante la auditoria.

TD-02: No modificar unilateralmente
Foundation o Adapters mientras exista
la prueba de paridad por namespace.

TD-03: El instante temporal de
verificacion debe tener procedencia
confiable y verificable.

TD-04: Una comprobacion manual de
vigencia no sustituye la validacion
temporal interna del motor X.509.

TD-05: No aceptar diferencias de
reloj sin una semantica explicita
y previamente aprobada.

TD-06: La futura implementacion
debe conservar trust anchors
procedentes de configuracion confiable.

TD-07: No recurrir implicitamente
al almacen de CA del sistema.

TD-08: La validacion temporal X.509
debe abarcar los certificados
relevantes de la cadena, conforme
a la politica aprobada.

TD-09: No interpretar una cadena
valida como autorizacion del emisor.

TD-10: No interpretar una cadena
valida como comprobacion de revocacion.

TD-11: Los requisitos de EKU
adicionales deben verificarse
por mecanismos especificos.

TD-12: Los fallos de infraestructura
o backend deben producir rechazo.

TD-13: Los resultados parciales
no deben construir una Key
Attestation integral valida.

TD-14: Cualquier sustitucion del
backend requiere regresion y
compatibilidad documentada.

## 7. Pruebas requeridas

| ID | Escenario | Resultado requerido |
|---|---|---|
| T-01 | Certificado vigente en el instante configurado | Aceptacion parcial X.509 |
| T-02 | Certificado aun no vigente | Rechazo |
| T-03 | Certificado vencido | Rechazo |
| T-04 | Diferencia entre reloj configurado y reloj del sistema | Uso coherente del instante elegido |
| T-05 | Cadena con intermedio vencido | Rechazo |
| T-06 | Trust anchor no configurado | Rechazo |
| T-07 | Certificado CA sin capacidades necesarias | Rechazo |
| T-08 | Certificado de firma con privilegios CA | Rechazo |
| T-09 | Backend criptografico no disponible | Rechazo |
| T-10 | Error de validacion X.509 | Rechazo |

Las pruebas T-01 a T-10 deben
implementarse y validarse en
incrementos posteriores.

La aceptacion parcial X.509
no implica conformidad HAIP.

## 8. Compatibilidad

El test existente:

OpenId4VcHaipAdapterX509TrustEvaluatorParityTest

contiene el metodo:

testSourcesDifferOnlyByNamespace

Exige equivalencia textual entre
las implementaciones legacy
y adapter, salvo el namespace.

La evolucion del adaptador debe
mantener o sustituir deliberadamente
esa garantia con pruebas equivalentes.

No modificar ambas implementaciones
sin evaluar sus consumidores.

No deshabilitar pruebas para
ocultar diferencias de comportamiento.

## 9. Bloqueos pendientes

B-01: Confirmar capacidades del
backend temporal candidato.

B-02: Verificar disponibilidad
operativa del backend en Windows.

B-03: Verificar portabilidad
al entorno Linux previsto.

B-04: Disenar pruebas deterministas
para certificados y cadenas.

B-05: Definir estrategia de
compatibilidad y migracion.

B-06: Acordar la semantica exacta
de instantes limite y errores.

B-07: Resolver la coherencia entre
reloj JWT y tiempo X.509.

B-08: Mantener revocacion y EKU
como garantias independientes.

## 10. Referencias tecnicas

PHP openssl_x509_checkpurpose:

https://www.php.net/manual/en/function.openssl-x509-checkpurpose.php

OpenSSL verification options:

https://docs.openssl.org/3.0/man1/openssl-verification-options/

OpenSSL X509_STORE_CTX_set_time:

https://docs.openssl.org/3.0/man3/X509_STORE_CTX_new/

ADR-0006 establece la separacion
de Foundation y Adapters.

## 11. Decision de continuidad

La direccion objetivo es disponer
de una evaluacion X.509 cuyo tiempo
sea explicito, controlado y verificable.

No se selecciona todavia una
implementacion concreta.

Primero deben realizarse pruebas
de viabilidad del backend candidato.

Se conserva sin modificaciones
la implementacion X.509 actual.

## 12. Estado

Draft for Review.

Decisiones de seguridad registradas.

Seleccion de backend pendiente.

Sin cambios de codigo productivo.

Sin activacion integral HAIP.
