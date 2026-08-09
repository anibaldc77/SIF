---
id: EG-492
title: Rich Authorization Requests
summary: Define authorization_details tipados, normalización y políticas de validación para solicitudes OAuth ricas sin incorporar reglas de negocio específicas en Foundation.
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
  - rar
  - authorization-details
depends_on:
  - EG-491
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-492 — Rich Authorization Requests

## Objetivo

Agregar un modelo genérico de `authorization_details` que permita expresar autorizaciones más ricas que scopes simples, manteniendo cualquier semántica específica de negocio detrás de políticas externas.

## Authorization Detail

`OAuthAuthorizationDetail` contiene:

- type;
- attributes arbitrarios.

`OAuthAuthorizationDetailType` encapsula el tipo lógico del detalle.

## Rich Authorization Request

`OAuthRichAuthorizationRequest` contiene uno o más authorization details.

Puede consultar si una determinada categoría de detalle está presente.

## Contracts

- `OAuthAuthorizationDetailsValidatorInterface`;
- `OAuthAuthorizationDetailTypePolicyInterface`;
- `OAuthAuthorizationDetailsNormalizerInterface`.

## Separación de responsabilidades

Foundation valida estructura y ofrece fronteras.

Las reglas concretas para tipos como pagos, documentos, identidades o recursos pertenecen a application/domain adapters.

## Compatibilidad

RAR puede combinarse con:

- PAR;
- JAR;
- Authorization Code;
- PKCE;
- OIDC.

## Neutralidad

Foundation no conoce:

- schemas de pagos;
- reglas jurídicas o financieras;
- bases de datos;
- HTTP;
- proveedores concretos;
- serializadores específicos.

## Seguridad

Adapters productivos deberán:

- restringir tipos soportados;
- validar atributos requeridos;
- rechazar campos inesperados cuando corresponda;
- limitar tamaño y complejidad;
- vincular authorization details con el cliente y resource server correctos.

## Fuera de alcance

- DPoP;
- sender-constrained tokens;
- domain-specific authorization detail implementations;
- HTTP serialization.

## Criterios de aceptación

- detail type tipado;
- attributes preservados;
- RAR exige al menos un detalle;
- normalizer tipado;
- policy contract;
- domain-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
