---
id: WP-252-I4-C4-4B0-ISSUER-IDENTITY-BINDING
title: WP-252 I4 Attestation Issuer Certificate Identity Binding Review
summary: Define una estrategia inicial explicita para asociar certificados firmantes con emisores autorizados.
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
  - x509
  - issuer-authorization
  - trust-policy
depends_on:
  - EG-585
  - EG-588
related_adrs:
  - ADR-0006
supersedes: null
superseded_by: null
---

# WP-252 I4 - Attestation Issuer Certificate Identity Binding Review

## 1. Objetivo

Definir la asociacion confiable entre el certificado
que firma una Key Attestation y la identidad de su emisor.

Esta revision complementa C4.3B y C4.4A.

No habilita el verificador integral.

## 2. Baseline

- Rama: feature/wp-252-openid4vc-haip.
- Checkpoint: 889d2d6.
- PHPUnit: 2910 tests, 19321 assertions.
- PHPStan: 3129 archivos, sin errores.
- ADR-0006: Approved.
- EG-588: Draft for Review.

## 3. Contexto normativo

HAIP 1.0 Final, apartado 4.5.1, deja fuera
de su alcance los perfiles X.509 de Key Attestation.

OpenID4VCI 1.0, Apendice D.1, permite atestaciones
firmadas por Wallet Providers o componentes de
almacenamiento de claves.

No existe una regla universal de HAIP que permita
equiparar CN, subject o SAN con la identidad autorizada.

Las restricciones adicionales deben proceder de una
politica confiable del ecosistema correspondiente.

Referencias:

https://openid.net/specs/openid4vc-high-assurance-interoperability-profile-1_0-final.html

https://openid.net/specs/openid-4-verifiable-credential-issuance-1_0.html

## 4. Alternativas consideradas

### A. Identidad obtenida directamente del JWT iss

Rechazada como criterio de autenticacion aislado.

El claim puede ser estructuralmente valido sin que
se haya establecido la identidad del firmante.

### B. Identidad obtenida exclusivamente de CN o SAN

No adoptada como requisito universal.

Requiere un perfil de certificados y reglas de
comparacion definidos por un ecosistema confiable.

### C. Vinculacion explicita por certificado configurado

Seleccionada como primera estrategia especializada.

Una fuente confiable del Runtime asocia la huella
SHA-256 DER del certificado firmante con una identidad
de emisor autorizable para un Credential Issuer concreto.

Esta asociacion no sustituye la validacion criptografica.

## 5. Decision preliminar

B-01: La vinculacion se configura desde el Runtime
confiable, nunca desde la solicitud de la Wallet.

B-02: La entrada de configuracion incluye
Credential Issuer, huella SHA-256 DER del certificado
firmante e identificador del emisor de atestacion.

B-03: La comprobacion de firma JWS y confianza X.509
debe ser satisfactoria antes de autenticar la identidad.

B-04: El emisor obtenido mediante vinculacion debe
figurar en authorizedAttestationIssuerIdentifiers
de la TrustPolicy del Credential Issuer.

B-05: Un claim iss presente no puede contradecir la
identidad autenticada. La obligatoriedad del claim
queda sujeta al perfil y a las reglas normativas.

B-06: No se admite una vinculacion predeterminada,
wildcard ni una coincidencia entre distintos Issuers.

B-07: La huella del certificado procede del primer
elemento x5c efectivamente empleado para verificar
la firma y no de un fingerprint declarado por el cliente.

B-08: La vinculacion de identidad solo acredita
esta garantia especifica y no conformidad integral.

B-09: La configuracion debe contemplar rotacion
controlada de certificados y rechazo de entradas
ambiguas o duplicadas.

B-10: Otros perfiles de identidad certificados
podran incorporarse mediante decisiones separadas.

## 6. Fronteras de confianza

La identidad del Credential Issuer selecciona
una TrustPolicy confiable.

La identidad del emisor de Key Attestation identifica
a quien firmo la evidencia y no necesita coincidir
con el Credential Issuer.

Los certificados suministrados en x5c son evidencia
no confiable hasta completar la validacion aplicable.

Una firma valida sin cadena confiable debe rechazarse.

Una cadena confiable sin vinculacion autorizada
tambien debe rechazarse.

La vinculacion del firmante no satisface por si sola
revocacion, frescura, EKU, JWK binding o Key Possession.

## 7. Casos de aceptacion y rechazo

| ID | Caso | Resultado |
|---|---|---|
| T-01 | Vinculacion configurada ausente | Rechazo |
| T-02 | Firma ES256 invalida | Rechazo |
| T-03 | Cadena X.509 no confiable | Rechazo |
| T-04 | Certificado firmante no vinculado | Rechazo |
| T-05 | Emisor vinculado no autorizado en TrustPolicy | Rechazo |
| T-06 | Claim iss contradictorio | Rechazo |
| T-07 | Vinculacion de otro Credential Issuer | Rechazo |
| T-08 | Entradas duplicadas o ambiguas | Rechazo |
| T-09 | Certificado renovado sin vinculacion actualizada | Rechazo |
| T-10 | Firma, cadena y vinculacion validas | Identidad autorizada, no atestacion integral |

T-10 no autoriza construir un
OpenId4VcHaipKeyAttestationVerificationResult valido
mientras falten otras garantias obligatorias.

## 8. Restricciones de implementacion

Mantener Foundation independiente de Adapters.

No modificar el contrato del proveedor de TrustPolicy
sin una necesidad de compatibilidad demostrada.

La implementacion de vinculacion no debe aceptar
un identificador autenticado suministrado por el cliente.

Se requiere verificar firma y cadena mediante los
adaptadores especializados antes de promover identidad.

No activar el verificador integral.

## 9. Trabajo posterior

C4.4B1: Implementacion aislada de vinculacion explicita
y pruebas criptograficas positivas y negativas.

C4.4B2: Auditoria de integracion, rotacion de certificados
y comprobacion de frontera de confianza.

C4.5: Temporalidad y reloj controlable.

C4.6: Restricciones X.509 y revocacion.

C4.7: Vinculacion criptografica de claves.

C4.8: Composicion y regresion integral.

## 10. Estado

Draft for Review.

La estrategia C se propone como perfil inicial de SIF,
no como requisito normativo universal de HAIP.

Su activacion requiere pruebas criptograficas reales
y validacion de las dependencias de confianza.

ADR-0006 permanece Approved.
EG-588 permanece Draft for Review.
