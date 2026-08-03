---
id: EG-359
title: HTTP Runtime, Native Kernel and Skeleton Integration
summary: "Specifies the optional HTTP runtime, native request kernel, application capability publication, bootstrap composition, and generated public entry-point delegation."
authors:
  - SIF Engineering
created: 2026-08-02
updated: 2026-08-02
tags:
  - http
  - runtime
  - bootstrap
  - skeleton
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-223
depends_on:
  - EG-353
  - EG-354
  - EG-355
  - EG-356
  - EG-357
  - EG-358
related_adrs: []
---

# HTTP Runtime, Native Kernel and Skeleton Integration

## Purpose

Define the optional runtime boundary that composes the HTTP lifecycle, native request adaptation and response emission without performing I/O during application bootstrap.

## Requirements

- `HttpRuntime` owns a `NativeHttpKernel` and exposes request handling separately from native emission.
- Native request capture occurs only when `runNative()` is explicitly invoked.
- Response emission is delegated to a supplied `ResponseEmitterInterface`.
- `Application` exposes the runtime only when explicitly composed.
- `Bootstrap` registers an HTTP service provider only when a runtime is supplied.
- Capabilities `http`, `http.lifecycle` and `http.native-transport` describe availability, not execution.
- The generated `public/index.php` creates the application, requires an HTTP runtime and delegates one native lifecycle to it.
- Bootstrap composition must not read request globals, execute routing or emit headers.
