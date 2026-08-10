---
id: EG-500
title: OAuth 2.0 Dynamic Client Registration
summary: Define la respuesta tipada de registro dinámico, emisión abstracta de credenciales, policy y serialización sin acoplar Foundation a HTTP ni almacenamiento de secretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-241
tags:
  - security
  - oauth
  - dynamic-client-registration
  - client-lifecycle
depends_on:
  - EG-499
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-500 — OAuth 2.0 Dynamic Client Registration

## Objetivo

Implementar la frontera de registro dinámico de clientes manteniendo separado el metadata solicitado, el `OAuthClient` finalmente registrado y las credenciales emitidas.

## Compatibilidad incremental

`OAuthDynamicClientRegistrationServiceInterface::register()` conserva su retorno `OAuthClient`.

I4 agrega `registerWithResult()` para obtener una respuesta rica sin romper la frontera introducida en I1.

## Registration Result

`OAuthDynamicClientRegistrationResult` contiene:

- cliente registrado;
- metadata efectivamente registrada;
- instante de registro;
- credenciales emitidas;
- registration client URI opcional;
- referencia opaca a registration access token opcional.

## Credenciales

`OAuthClientRegistrationCredential` representa:

- tipo;
- referencia opaca al material;
- issuedAt;
- expiresAt opcional.

Foundation no debe persistir secretos ni obligar a exponer material sensible en texto plano.

## Contratos

- `OAuthDynamicClientRegistrationServiceInterface`;
- `OAuthClientRegistrationCredentialIssuerInterface`;
- `OAuthClientRegistrationResultSerializerInterface`;
- `OAuthClientRegistrationPolicyInterface`;
- `OAuthClientRegistrationMetadataValidatorInterface`.

## Seguridad

Adapters productivos deberán:

- validar redirect URIs;
- limitar grant/response types;
- autorizar quién puede registrar clientes;
- generar client identifiers impredecibles cuando corresponda;
- proteger client secrets y registration access tokens;
- aplicar expiración/rotación de credenciales;
- registrar eventos de auditoría sin filtrar secretos.

## Separación respecto de I5

I4 cubre la creación inicial.

I5 cubrirá administración posterior del registro:

- consulta;
- actualización;
- eliminación;
- authorization mediante registration access token;
- lifecycle transitions.

## Neutralidad

Foundation no conoce:

- HTTP POST;
- JSON concreto;
- HTTP status codes;
- Vault concreto;
- base de datos;
- cache;
- proveedor IAM.

## Criterios de aceptación

- compatibilidad I1 preservada;
- respuesta de registro tipada;
- credentials por referencia opaca;
- policy contract;
- serializer abstracto;
- storage-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
