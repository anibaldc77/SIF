---
id: WP-252-I4-C4-3A-VERIFIER-ACCEPTANCE
title: WP-252 I4 Key Attestation Verifier Acceptance Review
summary: Establece las condiciones necesarias para producir resultados verificados de Key Attestation.
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

# WP-252 I4 - Key Attestation Verifier Acceptance Review

## 1. Objetivo

Definir las condiciones que debera cumplir una futura
implementacion confiable del verificador integral de
Key Attestation.

No se autoriza activar dicho verificador en este incremento.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 90cce4a.
- PHPUnit: 2901 tests, 19307 assertions.
- PHPStan: 3127 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.
- C4.2: matriz preliminar de garantias documentada.

## 3. Contratos existentes

Se deben reutilizar:

- OpenId4VcHaipKeyAttestationVerifierInterface.
- OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface.
- OpenId4VcHaipKeyAttestationTrustPolicy.
- OpenId4VcHaipKeyAttestationVerificationResult.
- OpenId4VcHaipKeyAttestationEvidenceIdentity.
- OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface.

No se justifica duplicar estos contratos sin una necesidad
tecnica verificada y una decision arquitectonica expresa.

## 4. Frontera de confianza

OpenId4VcHaipKeyAttestationVerificationResult representa
el resultado de una verificacion especializada.

Su constructor no ejecuta operaciones criptograficas.

Un resultado valid=true creado por pruebas, mocks o
codigo solicitante no constituye evidencia de autenticidad.

La implementacion confiable debe establecer las garantias
antes de construir y devolver un resultado positivo.

La orquestacion Foundation consume el contrato de verificacion,
no implementaciones criptograficas concretas.

## 5. Condiciones obligatorias de aceptacion

G-01: Politica de confianza obtenida de configuracion
controlada por el Credential Issuer correspondiente.

G-02: Parsing estructural seguro del JWT exacto recibido,
con rechazo ante evidencia ausente, invalida o malformada.

G-03: Algoritmo permitido por la politica y firma
criptografica verificada.

G-04: Cadena X.509 validada contra trust anchors
configurados, sin derivarlos de la solicitud.

G-05: Identidad del emisor de Key Attestation autenticada
y autorizada segun la politica del Credential Issuer.

G-06: Temporalidad del JWT verificada con reloj controlable,
incluyendo reglas condicionales de exp y tolerancia admisible.

G-07: Restricciones de certificados aplicables, incluido
Key Usage, EKU exigible y revocacion cuando sea requerida.

G-08: Claves atestadas extraidas de evidencia autenticada,
sin aceptar referencias declaradas como prueba suficiente.

G-09: Identidad verificada vinculada al JWT exacto presentado.

G-10: Cobertura de todas las claves exigibles para la emision,
incluidas las reglas aplicables al mecanismo y a lotes.

G-11: Nonce, replay y pruebas de posesion comprobados donde
correspondan al mecanismo normativo y al contexto verificado.

G-12: Resultado positivo exclusivamente si todas las
garantias obligatorias aplicables fueron acreditadas.

Los gates se aplican segun la responsabilidad y el mecanismo;
no convierten recomendaciones SHOULD en requisitos universales.

## 6. Rechazo por defecto

El verificador debe devolver una evaluacion invalida ante:

- Politica confiable inexistente o no autorizada.
- Evidencia JWT ausente o estructuralmente invalida.
- Firma invalida o algoritmo no permitido.
- Cadena X.509 no confiable.
- Emisor de atestacion no autenticado o no autorizado.
- Evidencia temporal ausente, vencida o inconsistente.
- EKU o Key Usage obligatorio incumplido.
- Revocacion obligatoria no comprobable.
- Identidad de evidencia incongruente.
- Claves acreditadas insuficientes o no verificadas.
- Otra garantia obligatoria no comprobada.

Las fallas de servicios criptograficos y dependencias
externas no deben convertirse en resultados positivos.

Se debera definir el tratamiento de errores operativos
sin divulgar material sensible ni relajar el rechazo.

## 7. Semantica del resultado

Para valid=false:

- verifiedKeyReferences debe permanecer vacio.
- No se debe informar identidad de emisor autenticada
  sin evidencia suficiente.
- Las violaciones deben ser identificables y deterministas.
- No se debe presentar una comprobacion parcial como total.

Para valid=true:

- Deben existir claves verificadas.
- No debe existir ninguna violacion obligatoria pendiente.
- Debe existir identidad de emisor autenticada.
- Debe existir identidad verificable del JWT exacto.
- Las claves informadas deben provenir de evidencia autenticada.

Estos requisitos pertenecen a la implementacion confiable.
No implican necesariamente modificar el constructor
del modelo publico existente.

## 8. Compatibilidad de mecanismos

La prueba jwt y la prueba attestation tienen requisitos
normativos distintos.

No imponer una prueba independiente de posesion a todo
mecanismo attestation de manera universal.

La obligatoriedad de exp para Key Attestation utilizada
con prueba jwt no debe extrapolarse automaticamente
a todos los usos del mecanismo attestation.

Los requisitos de nonce y replay deben determinarse
por mecanismo, configuracion y contexto verificado.

## 9. Pruebas de aceptacion requeridas

T-01: Rechazo sin politica confiable.

T-02: Rechazo de JWT ausente o malformado.

T-03: Rechazo de firma incorrecta y algoritmo prohibido.

T-04: Rechazo de cadena X.509 no confiable.

T-05: Rechazo de emisor no autorizado.

T-06: Rechazo por vencimiento y reglas temporales.

T-07: Rechazo ante revocacion obligatoria no comprobable.

T-08: Rechazo por Key Usage o EKU incumplido.

T-09: Rechazo ante sustitucion del JWT.

T-10: Rechazo de claves no acreditadas.

T-11: Rechazo de cobertura incompleta en lotes.

T-12: Aceptacion positiva solo con todas las garantias
aplicables verificadas mediante pruebas criptograficas reales.

Los tests con mocks de verificador solo prueban la orquestacion.
No acreditan la implementacion criptografica integral.

## 10. Secuencia posterior

C4.3B: Especificacion de precedencia de fallos, contratos
de dependencias y escenarios de pruebas negativas.

C4.4: Politica confiable y autorizacion del emisor.

C4.5: Temporalidad y reloj controlable.

C4.6: Restricciones X.509 y revocacion.

C4.7: Claves autenticadas y binding criptografico.

C4.8: Composicion final y pruebas positivas/negativas.

Cada subincremento requiere validacion y revision propias.

## 11. Estado

Draft for Review.

No se registra implementacion integral aprobada.
No se habilitan resultados de confianza positivos.
ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
