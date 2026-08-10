---
id: EG-501
title: Client Registration Management Lifecycle
summary: Define lectura, actualización, eliminación, autorización y rotación de registration access token para clientes OAuth registrados dinámicamente.
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
  - lifecycle
depends_on:
  - EG-500
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-501 — Client Registration Management Lifecycle

## Objetivo

Agregar administración posterior al registro dinámico sin trasladar HTTP, persistencia o secretos a Foundation.

## Registration Record

`OAuthClientRegistrationRecord` representa:

- cliente;
- metadata registrada;
- registeredAt;
- updatedAt;
- active;
- registration client URI;
- referencia opaca al registration access token.

## Management Authorization

`OAuthClientRegistrationManagementAuthorization` identifica:

- client id;
- referencia opaca al registration access token.

Foundation no recibe ni persiste obligatoriamente el token en texto plano.

## Update

`OAuthClientRegistrationUpdate` separa explícitamente:

- client id;
- nueva metadata solicitada.

El adapter productivo debe impedir cambios no autorizados de client id y otros campos inmutables.

## Contratos

- `OAuthClientRegistrationManagementServiceInterface`;
- `OAuthClientRegistrationRecordRepositoryInterface`;
- `OAuthClientRegistrationManagementAuthorizerInterface`;
- `OAuthRegistrationAccessTokenRotatorInterface`.

## Lifecycle

La frontera de servicio expone:

- lectura;
- actualización;
- eliminación.

La política concreta decide si delete implica borrado físico, revocación o deactivación.

## Seguridad

Adapters productivos deberán:

- autenticar el registration access token;
- vincularlo al client id correspondiente;
- evitar enumeration de clients;
- validar nuevamente metadata en updates;
- proteger campos inmutables;
- rotar registration access tokens cuando corresponda;
- auditar operaciones sin registrar material sensible.

## Neutralidad

Foundation no conoce:

- HTTP GET/PUT/DELETE;
- status codes;
- JSON encoder;
- storage;
- Vault concreto;
- hashing concreto;
- middleware.

## Criterios de aceptación

- record tipado;
- management authorization tipada;
- update tipado;
- read/update/delete contracts;
- repository abstraction;
- access-token rotation boundary;
- secret-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
