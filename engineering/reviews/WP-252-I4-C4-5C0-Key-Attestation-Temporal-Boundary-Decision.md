---
id: WP-252-I4-C4-5C0-TEMPORAL-BOUNDARY-DECISION
title: WP-252 I4 Key Attestation Temporal Boundary Decision
summary: Fija la semantica de limites temporales para el futuro evaluador aislado.
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

# WP-252 I4 - Temporal Boundary Decision

## 1. Objetivo

Resolver los limites de igualdad, tolerancia,
relacion entre claims y nbf antes de implementar
el evaluador temporal aislado.

No habilita el verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 7ecc6a9.
- PHPUnit: 2931 tests, 19714 assertions.
- PHPStan: 3132 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Referencias

OpenID4VCI 1.0 Final, apendice D.1:

https://openid.net/specs/openid-4-verifiable-credential-issuance-1_0-final.html

RFC 7519, apartados 4.1.4 a 4.1.6:

https://www.rfc-editor.org/rfc/rfc7519

Las restricciones adicionales de SIF se
identifican separadamente de las normas.

## 4. Representacion temporal

TB-01: Utilizar segundos Unix UTC, sin
conversiones por zona horaria local.

TB-02: Obtener now una sola vez desde
Sif\Foundation\Contracts\ClockInterface.

TB-03: No utilizar time() ni construir un
reloj de sistema dentro del evaluador.

TB-04: iat es obligatorio y entero.
exp, cuando existe, debe ser entero.

TB-05: El perfil inicial de SIF tambien
exige nbf entero cuando este presente.
Esta es una restriccion adicional SIF.

No convertir cadenas, booleanos o numeros
decimales a enteros implicitamente.

## 5. Limites de iat

Definiciones:

N = instante actual en segundos.
M = maximumAttestationAgeSeconds.
S = allowedClockSkewSeconds.
I = iat.

TB-06: Un iat futuro es admisible solo si:

I <= N + S

La igualdad es admisible.

TB-07: La antiguedad es admisible solo si:

I >= N - M - S

La igualdad es admisible.

Ambos controles deben cumplirse.

La antiguedad maxima y el clock skew
son restricciones configurables de SIF.

## 6. Limites de exp

E = exp.

TB-08: Cuando exp esta presente,
la condicion temporal de aceptacion es:

N < E + S

La igualdad con el limite efectivo
de vencimiento produce rechazo.

TB-09: exp es obligatorio cuando la
Key Attestation se usa con proof type jwt.

Para proof type attestation puede faltar,
salvo restricciones adicionales aplicables.

TB-10: SIF rechazara exp <= iat.

Esta regla conservadora es una decision
adicional SIF para el perfil inicial.

No se atribuye a un MUST de OpenID4VCI D.1.

## 7. Limites de nbf

B = nbf.

TB-11: nbf es opcional.

Cuando existe debe evaluarse:

B <= N + S

La igualdad es admisible.

No ignorar un nbf presente.

TB-12: Cuando nbf y exp coexisten,
SIF rechazara nbf >= exp.

Esta coherencia de intervalo es
una restriccion adicional SIF.

## 8. Seguridad aritmetica

TB-13: Evitar desbordamientos enteros.

Los calculos de limites deben usar
comparaciones seguras o tratamiento
explicito de extremos representables.

No se permite que un desbordamiento
convierta una evaluacion invalida
en una admision temporal.

## 9. Contrato del evaluador aislado

TB-14: Utilizar ClockInterface existente.

Recibir claims, TrustPolicy y proof type
proporcionado por el contexto confiable.

El evaluador no autentica esos datos.

Un resultado favorable significa solamente
que las condiciones temporales evaluadas
resultaron admisibles.

No puede producir por si mismo un
KeyAttestationVerificationResult valido.

TB-15: Rechazar proof types desconocidos,
ausentes o no acreditados.

La autenticacion del contexto corresponde
a la composicion integral futura.

TB-16: Los errores operativos de reloj
deben impedir una admision temporal.

No utilizar un reloj alternativo implicito.

## 10. Matriz de pruebas de C4.5C1

| ID | Escenario | Resultado esperado |
|---|---|---|
| T-01 | iat ausente | Rechazo |
| T-02 | iat no entero | Rechazo |
| T-03 | iat futuro igual a N + S | Admisible |
| T-04 | iat futuro mayor a N + S | Rechazo |
| T-05 | iat igual a N - M - S | Admisible |
| T-06 | iat anterior al minimo | Rechazo |
| T-07 | jwt sin exp | Rechazo |
| T-08 | attestation sin exp | Admisible si cumple politica |
| T-09 | exp igual a N + 0 | Rechazo sin skew |
| T-10 | exp en limite efectivo | Rechazo |
| T-11 | exp posterior al limite | Admisible |
| T-12 | exp menor o igual a iat | Rechazo |
| T-13 | nbf futuro dentro de skew | Admisible |
| T-14 | nbf futuro fuera de skew | Rechazo |
| T-15 | nbf mayor o igual a exp | Rechazo |
| T-16 | Reloj con zona horaria distinta | Mismo resultado |
| T-17 | Error del reloj | Rechazo |
| T-18 | Proof type desconocido | Rechazo |
| T-19 | Claims temporalmente validos no autenticados | Sin conformidad integral |
| T-20 | Limites enteros extremos | Sin desbordamiento |

## 11. Alcance y dependencias

C4.5C1 implementara un evaluador temporal
aislado y sus pruebas deterministas.

No se incorporara al flujo de emision
ni al verificador integral en C4.5C1.

La procedencia autenticada de los claims
y del proof type sera responsabilidad
de la composicion posterior.

La validez X.509 y su reloj corresponden
a C4.6.

La vinculacion de claves corresponde
a C4.7.

La composicion integral corresponde
a C4.8.

## 12. Estado

Draft for Review.

Limites temporales definidos para la
implementacion inicial propuesta.

Sin cambios de codigo productivo.

Sin activacion de Key Attestation integral.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
