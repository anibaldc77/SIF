---
id: EG-489
title: Arquitectura de seguridad OAuth avanzada y contratos de capacidades
summary: Define una capa contract-first para extensiones OAuth avanzadas sobre WP-239, incluyendo PAR, JAR, RAR, DPoP, metadata y ciclo de vida de clientes sin implementar prematuramente los protocolos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - advanced-security
  - par
  - dpop
  - rar
depends_on:
  - EG-488
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-489 — Advanced OAuth Security Architecture and Capability Contracts

## Objetivo

Iniciar WP-240 como una capa de seguridad OAuth avanzada construida sobre WP-239, preservando la neutralidad de Foundation y evitando duplicar responsabilidades del Authorization Server ya terminado.

## Capacidades

`OAuthAdvancedSecurityCapability` identifica capacidades explícitas:

- PAR;
- JAR;
- RAR;
- DPoP;
- Dynamic Client Registration;
- Authorization Server Metadata.

## Security Profile

`OAuthAdvancedSecurityProfile` representa un conjunto inmutable de capacidades soportadas.

La policy concreta decide cuáles son obligatorias para cada deployment.

## Contratos

- `OAuthAdvancedSecurityProfileProviderInterface`;
- `OAuthAdvancedSecurityPolicyInterface`;
- `OAuthPushedAuthorizationRequestServiceInterface`;
- `OAuthProofOfPossessionVerifierInterface`;
- `OAuthAuthorizationDetailsValidatorInterface`;
- `OAuthClientLifecycleManagerInterface`.

## Separación respecto de WP-239

WP-239 continúa siendo responsable de:

- clientes OAuth;
- Authorization Code;
- PKCE;
- token lifecycle;
- client authentication;
- introspection/revocation;
- JWT signing boundaries;
- Device Flow;
- Client Credentials.

WP-240 añade perfiles y extensiones de seguridad alrededor de esas capacidades.

## Neutralidad

Foundation no debe depender de:

- controllers HTTP;
- PSR-7;
- storage concreto;
- JWT library concreta;
- TLS termination concreta;
- proveedores IAM externos.

## Roadmap I1-I8

1. I1 — arquitectura avanzada y capability contracts;
2. I2 — Pushed Authorization Requests;
3. I3 — JWT Secured Authorization Requests;
4. I4 — Rich Authorization Requests;
5. I5 — DPoP proof model y replay boundaries;
6. I6 — sender-constrained token integration;
7. I7 — client lifecycle, metadata y registration boundaries;
8. I8 — product completion, integración y adoption guide.

## Criterios de aceptación

- capa avanzada separada de Authorization Server;
- capability model explícito;
- contratos neutrales;
- provider-neutral;
- sin implementación prematura;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
