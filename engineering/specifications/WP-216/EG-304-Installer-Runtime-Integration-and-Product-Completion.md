---
id: EG-304
title: Installer Runtime Integration and Product Completion
summary: Defines the public runtime integration, service-provider registration and end-to-end completion criteria for the governed installer subsystem.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-216
tags:
  - installer
  - runtime
  - service-provider
  - completion
depends_on:
  - EG-303
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-304 — Installer Runtime Integration and Product Completion

## 1. Purpose

This increment exposes the governed installer as an optional runtime capability while preserving compatibility for applications that do not configure installation services.

## 2. Runtime service

`InstallerRuntime` SHALL compose requirement assessment, deterministic step planning, dry-run generation and authorized execution. Registered probes and steps SHALL remain ordered inputs to the existing deterministic components. The runtime SHALL NOT invent mutations or authorize execution implicitly.

## 3. Application integration

Applications MAY expose an installer through `InstallerAwareApplicationInterface`. Mutable applications SHALL accept the configured installer through `MutableInstallerApplicationInterface`. When no installer is configured, the application SHALL remain valid and return `null`.

## 4. Service provider

`RuntimeInstallerServiceProvider` SHALL publish the configured runtime service and the `installer` capability. Capability publication SHALL follow the normal provider lifecycle and SHALL not occur before boot.

## 5. Bootstrap

Bootstrap SHALL accept an optional `InstallerRuntime`. When present, it SHALL register the provider and preserve object identity throughout the application lifecycle. When absent, existing bootstrap behavior SHALL remain unchanged.

## 6. Completion criteria

WP-216 is complete when the subsystem provides:

- immutable installation requests and value objects;
- deterministic requirement assessment;
- dependency-safe step planning;
- immutable mutation plans and fingerprints;
- handler-based execution and journaling;
- compensating rollback;
- deterministic dry-run and explicit authorization;
- optional runtime integration;
- focused unit and runtime acceptance tests;
- governed specifications and implementation reviews.

## 7. Non-goals

This increment does not provide a CLI, web installer, concrete filesystem or database handlers, credential storage, remote execution, persistence or automatic authorization.
