---
id: EG-340
title: Console Kernel and Input Output Adapters
summary: Defines the console execution kernel, explicit process input and output contracts, governed result rendering and exit-code translation for the SIF Developer CLI.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-01
updated: 2026-08-01
work_package: WP-221
tags:
  - foundation
  - cli
  - console
  - kernel
  - input-output
depends_on:
  - EG-337
  - EG-338
  - EG-339
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-340 — Console Kernel and Input/Output Adapters

## 1. Purpose

I4 defines the explicit execution boundary of the Developer CLI. It connects token parsing, command resolution, command execution, result rendering and process exit codes without allowing commands to depend on process globals or terminal streams.

## 2. Input boundary

`CliInputInterface` SHALL expose already-tokenized process input and an explicit environment map. Array and native-process adapters MAY implement this boundary. The parser and commands SHALL NOT read `argv` or process environment directly.

## 3. Output boundary

`CliOutputInterface` SHALL expose separate standard and error channels. Buffered and callback adapters SHALL permit deterministic testing and process integration without direct output calls from commands.

## 4. Console kernel

The console kernel SHALL parse one invocation, resolve one command, execute it exactly once, render one result and return the governed integer exit code. Parsing and command lookup failures SHALL be translated to their governed categories.

Unexpected command failures SHALL become execution failures. Failures inside the console boundary itself SHALL return the internal-failure exit code.

## 5. Rendering

Result rendering SHALL remain independent from commands. I4 supports deterministic text and JSON forms. Successful results SHALL use the standard channel; unsuccessful results SHALL use the error channel.

Renderers SHALL preserve structured result data and SHALL NOT inspect command internals.

## 6. Scope boundary

I4 does not introduce operational commands, interactive prompts, runtime composition or launchers. Those responsibilities belong to later increments.
