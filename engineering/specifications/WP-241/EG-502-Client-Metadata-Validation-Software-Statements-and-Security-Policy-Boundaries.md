---
id: EG-502
title: Client Metadata Validation, Software Statements and Security Policy Boundaries
summary: Define validación contextual de metadata de clientes, software statements y políticas de seguridad sin acoplar Foundation a JWT/JWS, proveedores o almacenamiento concretos.
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
  - client-registration
  - software-statement
  - policy
depends_on:
  - EG-501
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-502 — Client Metadata Validation, Software Statements and Security Policy Boundaries

## Objetivo

Reforzar el registro dinámico mediante validación contextual, software statements y políticas explícitas de seguridad.

## Software Statement

`OAuthSoftwareStatement` representa:

- contenido serializado;
- issuer;
- subject;
- issuedAt;
- expiresAt;
- claims interpretados.

La verificación criptográfica pertenece a `OAuthSoftwareStatementVerifierInterface`.

## Trust

`OAuthSoftwareStatementTrustPolicyInterface` decide si un statement verificado pertenece a una autoridad confiable.

## Security Policy

`OAuthClientRegistrationSecurityPolicy` expresa restricciones como:

- grant types permitidos;
- response types permitidos;
- esquemas de redirect URI;
- obligatoriedad de software statement;
- soporte de loopback redirect URIs.

## Metadata Validation

`OAuthClientRegistrationMetadataValidatorInterface::validate()` se conserva por compatibilidad.

I6 agrega `validateWithContext()` para incorporar software statement y devolver `OAuthClientMetadataValidationResult`.

## Metadata Merger

`OAuthClientRegistrationMetadataMergerInterface` resuelve precedencia entre:

- metadata solicitada por el cliente;
- metadata impuesta o limitada por software statement.

La policy productiva debe definir qué claims pueden restringir o sobreescribir otros valores.

## Seguridad

Adapters productivos deberán:

- validar firma y algoritmo del software statement;
- validar issuer y trust;
- validar expiración;
- impedir ampliación de privilegios por precedencia incorrecta;
- restringir redirect URIs;
- validar grant/response type consistency;
- limitar metadata no reconocida;
- auditar decisiones sin registrar material sensible.

## Neutralidad

Foundation no conoce:

- librería JWT/JWS;
- OpenSSL directo;
- HTTP;
- persistencia;
- cache;
- proveedor IAM;
- CA o trust store concreto.

## Criterios de aceptación

- software statement tipado;
- trust policy abstracta;
- registration security policy tipada;
- compatibilidad de validator preservada;
- contextual validation tipada;
- merger contract;
- crypto-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
