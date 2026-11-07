---
id: WP-252-I4-C4-6A2D3-X509-BOUNDARY-CHAIN-EVIDENCE
title: WP-252 I4 X509 Exact Boundary and Chain Evidence Review
summary: Consolida los experimentos de vigencia exacta, certificados intermedios y aislamiento de trust anchors.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-11-06
updated: 2026-11-06
work_package: WP-252
tags:
  - haip
  - security
  - x509
  - openssl
  - clock
  - conformance
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - X509 Exact Boundary and Chain Evidence Review

## 1. Objetivo

Consolidar los resultados experimentales
de C4.6A2D1 y C4.6A2D2.

Registrar los comportamientos conformes
y las discrepancias pendientes.

Preservar la trazabilidad de la decision
sobre un futuro backend X.509.

No implementar ni activar un backend.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: b9b820c.
- PHP: 8.2.32.
- PHP OpenSSL: 3.0.21.
- OpenSSL CLI: 3.5.7.
- PHPUnit: 2956 tests, 19786 assertions.
- PHPStan: 3135 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Procedencia de evidencia

Se consideran las salidas de consola
de los experimentos:

- C4.6A2D1.
- C4.6A2D2.

Ambos experimentos utilizaron
certificados y claves ficticias.

Los certificados se generaron
con fechas de vigencia explicitas.

Se verificaron las fechas
efectivamente emitidas.

Los laboratorios temporales
se eliminaron al finalizar.

Los experimentos no se ejecutaron
dentro del flujo productivo HAIP.

## 4. Backends evaluados

### 4.1 usr/bin

C:\Program Files\Git\usr\bin\openssl.exe

Version observada: OpenSSL 3.5.7.

SHA-256:

78340FB01DEA2DF5CAAB73DCF28833B31329226A4C9E02F717F1D399592265B9

### 4.2 mingw64/bin

C:\Program Files\Git\mingw64\bin\openssl.exe

Version observada: OpenSSL 3.5.7.

SHA-256:

822034DA8A01558C17CBE53F42F33985A6EAF7C89E21273779F9C6560D8C4D78

Ambos binarios se verificaron
contra esos hashes antes
de los experimentos.

La equivalencia total entre
los binarios no esta demostrada.

## 5. C4.6A2D1 - Limites del Leaf

Root:

- notBefore: 2029-01-01T00:00:00Z.
- notAfter: 2032-01-01T00:00:00Z.

Leaf:

- notBefore: 2030-01-01T00:00:00Z.
- notAfter: 2030-01-03T00:00:00Z.

Instantes Unix relevantes:

- notBefore: 1893456000.
- notAfter: 1893628800.

Los dos backends aceptaron
notBefore exactamente.

Los dos backends rechazaron
notAfter exactamente.

El diagnostico fue:

certificate has expired

Codigo de salida observado: 2.

El certificado era rechazado
un segundo antes de lo esperado
segun la interpretacion inclusiva
de RFC 5280.

## 6. C4.6A2D2 - Cadena e intermedio

Se utilizaron dos autoridades Root
independientes: Root A y Root B.

La cadena principal fue:

Root A -> Intermediate -> Leaf.

Root A y Root B:

- notBefore: 2029-01-01T00:00:00Z.
- notAfter: 2032-01-01T00:00:00Z.

Intermediate:

- notBefore: 2029-01-01T00:00:00Z.
- notAfter: 2030-01-02T00:00:00Z.

Leaf:

- notBefore: 2030-01-01T00:00:00Z.
- notAfter: 2030-01-10T00:00:00Z.

Ambos backends:

- Aceptaron una cadena vigente.
- Rechazaron el intermedio vencido
  mientras el Leaf seguia vigente.
- Rechazaron Root B como trust anchor
  de una cadena emitida por Root A.
- Aceptaron el intermedio un segundo
  antes de notAfter.
- Rechazaron el intermedio
  exactamente en notAfter.

En la igualdad con notAfter
del Intermediate se observo:

certificate has expired

Codigo de salida: 2.

El diagnostico identifico
depth lookup 1.

## 7. Matriz consolidada

Las filas PASS indican resultado
coincidente con el esperado.

Las filas REVIEW indican una
discrepancia temporal observada.

| ID | Backend | Escenario | Observado | Estado |
|---|---|---|---|---|
| T-01 | usr/bin | Leaf notBefore - 1 | Rechaza | PASS |
| T-02 | usr/bin | Leaf notBefore exacto | Acepta | PASS |
| T-03 | usr/bin | Leaf notBefore + 1 | Acepta | PASS |
| T-04 | usr/bin | Leaf instante intermedio | Acepta | PASS |
| T-05 | usr/bin | Leaf notAfter - 1 | Acepta | PASS |
| T-06 | usr/bin | Leaf notAfter exacto | Rechaza | REVIEW |
| T-07 | usr/bin | Leaf notAfter + 1 | Rechaza | PASS |
| T-08 | mingw64/bin | Leaf notBefore - 1 | Rechaza | PASS |
| T-09 | mingw64/bin | Leaf notBefore exacto | Acepta | PASS |
| T-10 | mingw64/bin | Leaf notBefore + 1 | Acepta | PASS |
| T-11 | mingw64/bin | Leaf instante intermedio | Acepta | PASS |
| T-12 | mingw64/bin | Leaf notAfter - 1 | Acepta | PASS |
| T-13 | mingw64/bin | Leaf notAfter exacto | Rechaza | REVIEW |
| T-14 | mingw64/bin | Leaf notAfter + 1 | Rechaza | PASS |
| T-15 | usr/bin | Cadena vigente | Acepta | PASS |
| T-16 | usr/bin | Intermediate vencido | Rechaza | PASS |
| T-17 | usr/bin | Root B incorrecta | Rechaza | PASS |
| T-18 | usr/bin | Intermediate notAfter - 1 | Acepta | PASS |
| T-19 | usr/bin | Intermediate notAfter exacto | Rechaza | REVIEW |
| T-20 | mingw64/bin | Cadena vigente | Acepta | PASS |
| T-21 | mingw64/bin | Intermediate vencido | Rechaza | PASS |
| T-22 | mingw64/bin | Root B incorrecta | Rechaza | PASS |
| T-23 | mingw64/bin | Intermediate notAfter - 1 | Acepta | PASS |
| T-24 | mingw64/bin | Intermediate notAfter exacto | Rechaza | REVIEW |

Resumen:

- Verificaciones ejecutadas: 24.
- Resultados esperados: 20.
- Discrepancias REVIEW: 4.
- Backends comparados: 2.

Las cuatro discrepancias
corresponden exclusivamente
a igualdad exacta con notAfter.

## 8. Referencia normativa

RFC 5280, apartado 4.1.2.5,
define el periodo de validez
incluyendo notBefore y notAfter.

La igualdad exacta con notAfter
debe tratarse como vigente
segun esa definicion.

Por ello, los rechazos observados
constituyen una discrepancia
respecto de esa semantica.

El rechazo anticipado no supone
aceptacion de certificados vencidos.

Sin embargo, impide afirmar
conformidad temporal exacta.

## 9. Hallazgo OpenSSL

Existe un reporte publico
sobre el mismo comportamiento:

OpenSSL issue #32890.

Un comentario del proyecto indica
que la correccion esta incluida
en OpenSSL 4.0 y no fue
retroportada a OpenSSL 3.x.

Esta informacion es externa
a nuestros experimentos.

No se ha comprobado localmente
OpenSSL 4.0 ni otra biblioteca.

El reporte publico no reemplaza
la futura validacion experimental.

## 10. Conclusiones de arquitectura

F-01: Las dos implementaciones
OpenSSL CLI examinadas permiten
verificacion con tiempo explicito.

F-02: El inicio de vigencia del Leaf
se comporta inclusivamente.

F-03: Un segundo antes de notBefore
se obtiene rechazo.

F-04: Un segundo antes de notAfter
del Leaf se obtiene aceptacion.

F-05: En notAfter exacto del Leaf
ambos backends producen rechazo.

F-06: Un segundo despues de notAfter
del Leaf se obtiene rechazo.

F-07: La cadena Root A, Intermediate
y Leaf se acepta cuando esta vigente.

F-08: El Intermediate vencido
produce rechazo aun si Leaf
permanece temporalmente vigente.

F-09: Root B, diferente de Root A,
no autoriza la cadena presentada.

F-10: En notAfter exacto del
Intermediate tambien se observa
el rechazo anticipado.

F-11: El hallazgo afecta la
semantica de vigencia X.509,
sin acreditar por si solo
otras fallas de seguridad.

F-12: No existe evidencia suficiente
para aprobar ninguno de los
dos backends para produccion.

## 11. Decisiones de continuidad

No cambiar el significado
de -attime para forzar resultados.

No restar artificialmente
un segundo al instante configurado.

No desactivar comprobaciones
temporales internas de OpenSSL.

No interpretar el rechazo
anticipado como prueba de
otras garantias criptograficas.

Preservar el principio fail-closed.

Mantener la confianza del
certificado separada de:

- Autenticacion de firma JWT.
- Autorizacion del emisor.
- Revocacion.
- EKU adicional.
- Temporalidad de claims JWT.
- Binding de claves atestadas.

No modificar las implementaciones
actuales Foundation y Adapters.

## 12. Verificaciones pendientes

P-01: Resolver la decision formal
sobre conformidad temporal exacta
y compatibilidad con RFC 5280.

P-02: Evaluar experimentalmente
un backend o version que trate
correctamente la igualdad notAfter.

P-03: Comprobar disponibilidad,
portabilidad y seguridad en Linux.

P-04: Construir pruebas
automatizadas y reproducibles
con fixtures gobernados.

P-05: Evaluar revocacion CRL/OCSP
segun las politicas aplicables.

P-06: Evaluar EKU adicional,
Key Usage y restricciones
de certificados exigibles.

P-07: Definir aislamiento de procesos,
recursos, temporales, errores
y dependencias operativas.

P-08: Comparar compatibilidad con
PHP OpenSSL, evaluadores actuales
y test de paridad entre capas.

## 13. Referencias

RFC 5280:

https://datatracker.ietf.org/doc/html/rfc5280#section-4.1.2.5

OpenSSL issue #32890:

https://github.com/openssl/openssl/issues/32890

OpenSSL verification options:

https://docs.openssl.org/3.5/man1/openssl-verification-options/

Revisiones previas:

- WP-252-I4-C4-6A1-X509-Verification-Time-Architecture-Decision.
- WP-252-I4-C4-6A2C-OpenSSL-Explicit-Time-Feasibility-Evidence-Review.

## 14. Estado

Draft for Review.

Evidencia C4.6A2D1 y C4.6A2D2
consolidada.

Discrepancia notAfter abierta.

Sin seleccion de backend productivo.

Sin cambios de codigo productivo.

Sin activacion integral HAIP.
