---
id: WP-252-I4-C4-6A2C-OPENSSL-TIME-FEASIBILITY
title: WP-252 I4 OpenSSL Explicit Time Feasibility Evidence Review
summary: Registra los resultados y limitaciones de los experimentos Windows C4.6A2A y C4.6A2B.
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
  - haip
  - security
  - x509
  - openssl
  - clock
  - feasibility
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - OpenSSL Explicit Time Feasibility Evidence Review

## 1. Objetivo

Registrar la evidencia experimental
de C4.6A2A y C4.6A2B.

Distinguir capacidades demostradas
de garantias pendientes.

No seleccionar ni integrar todavia
un backend criptografico productivo.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 4fc5d08.
- PHP: 8.2.32.
- PHP OpenSSL: 3.0.21.
- OpenSSL CLI: 3.5.7.
- PHPUnit: 2956 tests, 19786 assertions.
- PHPStan: 3135 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Procedencia de la evidencia

Los resultados proceden de las salidas
de consola de C4.6A2A y C4.6A2B.

Se ejecutaron experimentos locales
en Windows con certificados ficticios.

No se conservaron certificados,
claves privadas ni registros
criptograficos de las cadenas.

La evidencia documental no sustituye
una ejecucion futura reproducible.

## 4. Backends identificados

### 4.1 Git for Windows - usr/bin

Ruta:

C:\Program Files\Git\usr\bin\openssl.exe

Version:

OpenSSL 3.5.7

SHA-256 observado:

78340FB01DEA2DF5CAAB73DCF28833B31329226A4C9E02F717F1D399592265B9

### 4.2 Git for Windows - mingw64/bin

Ruta:

C:\Program Files\Git\mingw64\bin\openssl.exe

Version:

OpenSSL 3.5.7

SHA-256 observado:

822034DA8A01558C17CBE53F42F33985A6EAF7C89E21273779F9C6560D8C4D78

Los binarios tienen hashes distintos.

Compartir version y resultados de
laboratorio no implica equivalencia
total entre sus implementaciones.

## 5. Capacidades identificadas

Ambos ejecutables anunciaron:

- -attime.
- -CAfile.
- -no-CAfile.
- -no-CApath.
- -no-CAstore.
- -untrusted.
- -x509_strict.
- -purpose.

La disponibilidad de opciones no
acredita por si sola seguridad operativa.

## 6. Laboratorio criptografico

Se generaron:

- Root CA ficticia.
- Intermediate CA ficticia.
- Certificado Leaf ficticio.
- Claves EC P-256 de laboratorio.
- Extensiones X.509 de prueba.

La generacion utilizo un ejecutable
OpenSSL identificado previamente.

Las dos implementaciones verificaron
la misma cadena generada.

Los archivos se crearon fuera
del repositorio SIF.

El laboratorio temporal fue
eliminado al finalizar.

## 7. Instantes experimentales

Los instantes Unix informados fueron:

- Actual: 1793818916.
- Valido: 1793822516.
- Anterior: 1793732516.
- Posterior: 1794423716.

La generacion uso certificados
de aproximadamente dos dias
de vigencia.

Las pruebas no fijaron ni documentaron
los segundos exactos notBefore
y notAfter de cada certificado.

## 8. Matriz de resultados

| ID | Backend | Escenario | ExitCode | Resultado |
|---|---|---|---|---|
| E-01 | usr/bin | Vigente con -attime | 0 | PASS |
| E-02 | usr/bin | Anterior al inicio | 2 | PASS |
| E-03 | usr/bin | Posterior al vencimiento | 2 | PASS |
| E-04 | usr/bin | Sin trust anchor | 2 | PASS |
| E-05 | usr/bin | Sin intermedio | 2 | PASS |
| E-06 | mingw64/bin | Vigente con -attime | 0 | PASS |
| E-07 | mingw64/bin | Anterior al inicio | 2 | PASS |
| E-08 | mingw64/bin | Posterior al vencimiento | 2 | PASS |
| E-09 | mingw64/bin | Sin trust anchor | 2 | PASS |
| E-10 | mingw64/bin | Sin intermedio | 2 | PASS |

Diez verificaciones obtuvieron
los resultados esperados.

Las pruebas negativas comprobaron
ademas patrones de error compatibles
con la condicion examinada.

## 9. Conclusiones de evaluacion

EV-01: Los dos ejecutables
fueron identificados en Windows.

EV-02: Ambos anunciaron -attime.

EV-03: Ambos anunciaron opciones
para excluir almacenes CA por defecto.

EV-04: Ambos aceptaron una cadena
de laboratorio vigente con -attime.

EV-05: Ambos rechazaron la cadena
antes de su inicio de vigencia.

EV-06: Ambos rechazaron la cadena
despues de su vencimiento.

EV-07: Ambos rechazaron la cadena
sin trust anchor configurado.

EV-08: Ambos rechazaron la cadena
cuando faltaba el intermedio.

EV-09: Se comprobaron diez resultados
esperados, no una conformidad integral.

EV-10: La disponibilidad Windows
no acredita compatibilidad Linux.

EV-11: PHP OpenSSL 3.0.21
y CLI OpenSSL 3.5.7 no deben
considerarse automaticamente equivalentes.

EV-12: No se comprobaron instantes
exactos de limite temporal.

EV-13: No se comprobaron revocacion,
EKU adicional ni binding de claves.

EV-14: Ningun backend queda
habilitado para produccion
por estos experimentos.

## 10. Verificaciones pendientes

P-01: Comprobar notBefore exacto
y su comportamiento en el limite.

P-02: Comprobar notAfter exacto
y su comportamiento en el limite.

P-03: Comprobar un certificado
intermedio vencido con Leaf vigente.

P-04: Comprobar rechazo ante
trust anchor distinto del autorizado.

P-05: Comparar resultados con
la implementacion PHP existente.

P-06: Verificar capacidad de
ejecucion segura en Windows y Linux.

P-07: Disenar aislamiento,
limites y manejo de fallos para
un posible proceso externo.

P-08: Mantener revocacion,
EKU y binding como garantias
independientes pendientes.

## 11. Condiciones de una posible integracion

Una eventual implementacion debera:

- Recibir un instante confiable.
- Utilizar una unica semantica temporal.
- Evitar fallback implicito de CA.
- No ejecutar comandos mediante shell.
- Restringir rutas y argumentos.
- Gestionar temporales confidenciales.
- Limitar recursos y tiempo de ejecucion.
- Rechazar errores del backend.
- Preservar Foundation independiente.
- Demostrar regresion con los tests previos.

La seleccion final del backend
requiere una decision posterior.

## 12. Referencias

OpenSSL verification options:

https://docs.openssl.org/3.5/man1/openssl-verification-options/

OpenSSL verify:

https://docs.openssl.org/3.5/man1/openssl-verify/

Revision anterior:

WP-252-I4-C4-6A1-X509-Verification-Time-Architecture-Decision

## 13. Continuidad

C4.6A2D debera ampliar el laboratorio
con pruebas de limites temporales,
intermedios y trust anchors distintos.

La seleccion de backend sigue pendiente.

No introducir cambios en los
evaluadores X.509 existentes.

## 14. Estado

Draft for Review.

Viabilidad experimental parcial
documentada para Windows.

Sin seleccion definitiva de backend.

Sin cambios de codigo productivo.

Sin activacion integral HAIP.
