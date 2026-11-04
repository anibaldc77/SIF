---
id: WP-252-I4-C4-5B1-CLOCK-CONTRACT-REUSE
title: WP-252 I4 Clock Contract Reuse and Temporal Dependency Review
summary: Registra la reutilizacion del contrato general de reloj y las dependencias temporales pendientes para HAIP.
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
  - clock
  - temporal-validation
  - dependency-injection
  - architecture
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Clock Contract Reuse and Temporal Dependency Review

## 1. Objetivo

Resolver la dependencia de reloj del futuro
evaluador temporal de Key Attestation.

Esta revision complementa C4.5A.

No introduce implementaciones nuevas.

No modifica los contratos publicos existentes.

No habilita el verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: bd4371e.
- PHPUnit: 2931 tests, 19714 assertions.
- PHPStan: 3132 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Evidencia de auditoria C4.5B0

### 3.1 Contrato general de reloj

Existe:

Sif\Foundation\Contracts\ClockInterface

Metodo:

now(): DateTimeImmutable

Su implementacion identificada es:

Sif\Foundation\Context\SystemClock

La implementacion obtiene la hora actual
como DateTimeImmutable en UTC.

### 3.2 Reloj para pruebas

Existe:

Sif\Tests\Foundation\Fixtures\Context\FrozenClock

Implementa el contrato general de Foundation.

Permite establecer un instante fijo para
comprobar limites temporales exactos.

### 3.3 Otros contratos especializados

Existe:

Sif\Foundation\ErrorHandling\Contracts\FailureClockInterface

Su responsabilidad corresponde a ErrorHandling.

Existe tambien:

Sif\Foundation\Logging\Contracts\ClockInterface

Este devuelve LogTimestamp y se utiliza
para las necesidades propias de Logging.

No se recomienda introducir dependencias
desde HAIP hacia esos contratos especializados.

### 3.4 Composer

No se identificaron dependencias de:

- psr/clock.
- symfony/clock.
- nesbot/carbon.
- cakephp/chronos.

No se requiere incorporarlas para esta etapa.

### 3.5 Dependencia temporal X.509

Se identifico uso directo de time() en:

src/Adapters/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipExplicitX509TrustEvaluator.php

Existe tambien la implementacion transitoria
equivalente en Foundation.

La evaluacion de vigencia X.509 utiliza
un reloj de sistema no inyectable actualmente.

Esta deuda debera revisarse en C4.6,
sin modificarla dentro de C4.5B1.

## 4. Alternativas

### A. Reutilizar contrato general

Utilizar:

Sif\Foundation\Contracts\ClockInterface

Ventajas:

- Contrato existente y reutilizado.
- DateTimeImmutable disponible.
- Implementacion de produccion existente.
- Fixture de reloj fijo existente.
- No requiere nuevas dependencias.
- Respeta la independencia de Foundation.

Decision: seleccionada.

### B. Adaptar contrato especializado

Reutilizar Logging o ErrorHandling.

Decision: descartada para este caso.

Introduciria acoplamiento innecesario
con responsabilidades especializadas.

### C. Incorporar contrato nuevo

Crear una interfaz exclusiva de HAIP
o incorporar PSR Clock.

Decision: no justificada actualmente.

La auditoria ya identifico un contrato
suficiente para la dependencia temporal basica.

## 5. Decisiones arquitectonicas

CR-01: El futuro evaluador temporal utilizara
Sif\Foundation\Contracts\ClockInterface.

CR-02: La dependencia se recibira mediante
inyeccion de dependencias.

CR-03: El evaluador no utilizara time()
ni creara directamente un reloj de sistema.

CR-04: Se obtendra un solo instante actual
para evaluar los claims de un mismo JWT.

CR-05: DateTimeImmutable sera la representacion
recibida desde el contrato de reloj.

CR-06: Los calculos temporales utilizaran
segundos Unix independientes de la zona horaria.

CR-07: Se reutilizara FrozenClock o una
implementacion de prueba compatible.

CR-08: No se incorporara una dependencia externa
de reloj sin una necesidad tecnica demostrada.

CR-09: Los contratos de Logging y ErrorHandling
mantendran sus responsabilidades especializadas.

CR-10: Los errores del reloj no podran provocar
una aceptacion temporal predeterminada.

CR-11: La validacion temporal X.509 conservara
su tratamiento separado dentro de C4.6.

CR-12: El resultado temporal favorable no
constituira conformidad integral HAIP.

## 6. Contrato del futuro evaluador

El evaluador debera recibir:

- ClockInterface confiable.
- Claims temporales de la evidencia.
- TrustPolicy del Credential Issuer.
- Mecanismo de prueba acreditado.

La representacion de los claims no podra
atribuirles autenticidad criptografica
por el mero hecho de ser evaluados.

La evaluacion debe ejecutarse sobre el
mismo JWT exacto utilizado por las
comprobaciones criptograficas integrales.

No se admitira que un proofType declarado
por la Wallet sustituya al mecanismo
verificado por la infraestructura.

El resultado sera parcial y no debera
representarse directamente como
KeyAttestationVerificationResult valido.

## 7. Obtencion del instante actual

Se utilizara conceptualmente:

$now = $clock->now();

$nowSeconds = $now->getTimestamp();

Esta conversion proporciona segundos Unix
independientes de la zona horaria representada.

La obtencion se efectuara una sola vez
por evaluacion.

La semantica de limites, tolerancias y
operaciones aritmeticas corresponde a C4.5C.

No introducir dependencias directas de
DateTimeImmutable('now') en el evaluador.

## 8. Fronteras y compatibilidad

Foundation puede consumir el contrato
general de reloj sin dependencia de Adapters.

Las implementaciones concretas se seleccionaran
mediante composicion confiable del Runtime.

El evaluador temporal no debera alterar:

- La semantica de las pruebas jwt y attestation.
- El contrato existente de TrustPolicy.
- Los resultados de Key Attestation actuales.
- La separacion de mecanismos de prueba.
- Las garantias de C4.4.
- La compatibilidad con WP-245 y WP-250.

La composicion integral queda pendiente.

## 9. Matriz de pruebas para C4.5C

| ID | Escenario | Resultado esperado |
|---|---|---|
| T-01 | Reloj fijo y iat valido | Temporalmente admisible |
| T-02 | Reloj fijo con iat futuro | Evaluar tolerancia |
| T-03 | iat fuera de antiguedad maxima | Rechazo |
| T-04 | Instante exacto del limite de antiguedad | Resultado determinista |
| T-05 | exp vencido | Rechazo |
| T-06 | exp exactamente en el limite efectivo | Rechazo |
| T-07 | jwt sin exp | Rechazo |
| T-08 | attestation sin exp | Aplicar politica |
| T-09 | Distintas zonas horarias mismo instante | Mismo resultado |
| T-10 | Error del reloj | Fallo cerrado |
| T-11 | Claims sin firma autentica | Sin conformidad integral |
| T-12 | Reutilizacion de reloj existente | Sin nuevas interfaces |

No se consideran implementadas estas pruebas
por la presente revision documental.

## 10. Dependencias pendientes

P-01: Aprobar formalmente los limites
de igualdad y tolerancia definidos en C4.5A.

P-02: Resolver la regla adicional exp > iat.

P-03: Definir el tratamiento de nbf.

P-04: Definir el resultado temporal parcial
y su responsabilidad arquitectonica.

P-05: Implementar el evaluador aislado
con ClockInterface y pruebas deterministas.

P-06: Revisar en C4.6 el uso de time()
dentro del evaluador X.509.

## 11. Continuidad

C4.5C: Implementacion aislada de temporalidad
y pruebas positivas, negativas y de limites.

C4.5D: Revision de compatibilidad,
precedencia y frontera de confianza.

C4.6: Controles X.509, reloj y revocacion.

C4.7: Autenticacion y vinculacion de claves.

C4.8: Composicion integral.

## 12. Estado

Draft for Review.

Se selecciona reutilizar el contrato
general de reloj existente en Foundation.

No se crean nuevas interfaces.

No se implementa todavia el evaluador.

No se activa Key Attestation integral.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
