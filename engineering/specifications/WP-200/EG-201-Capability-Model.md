---
id: EG-201
title: Runtime Capability Model
summary: Defines the conceptual identity, requirement modes, provider declarations, selection rules, decoration, replacement, and observability of Runtime capabilities.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - capabilities
  - registry
  - resolution
work_package: WP-200
depends_on:
  - EG-200
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-201 — Runtime Capability Model

## 1. Definition

A capability is a governed promise that the Runtime can provide a framework-level behavior through one or more replaceable providers. It is not an implementation class, container alias, global singleton, or arbitrary application service.

## 2. Capability identity

Each capability has:

- a canonical string identifier;
- a contract or verifiable behavioral shape;
- a requirement mode;
- a compatibility version;
- zero or more providers;
- a deterministic resolution policy.

Identifier comparison is exact after canonical validation. Aliases may only be introduced through explicit compatibility metadata.

## 3. Requirement modes

### Required single

Exactly one active provider must be resolvable before Runtime readiness.

### Optional single

Zero or one provider may be active. Absence is not an error.

### Multiple ordered

Zero or more providers may coexist and are returned in deterministic order.

### Required multiple

At least one provider must exist and all active providers are returned in deterministic order.

## 4. Provider declaration

A declaration is immutable after the registration phase and contains:

- provider ID;
- capability ID;
- service/factory reference;
- source component;
- priority;
- default marker when permitted;
- replacement target when applicable;
- decorators and their order;
- supported capability version range;
- lifetime classification;
- environment constraints;
- metadata safe for diagnostics.

## 5. Registration rules

- duplicate provider IDs are invalid;
- registration order is recorded but must not be the only ambiguity-resolution rule;
- a replacement must name its target;
- a decorator must name the capability/provider chain it decorates;
- providers incompatible with the requested capability version are rejected during validation;
- registration after readiness is prohibited unless a future dynamic-runtime profile explicitly permits it.

## 6. Selection rules

For single capabilities, selection proceeds in this order:

1. explicit environment/profile selection;
2. valid explicit replacement;
3. unique default provider;
4. unique remaining provider;
5. otherwise, an ambiguity diagnostic.

Priority orders multiple providers and decorator chains. Priority SHALL NOT silently solve an ambiguous single-provider configuration unless the capability definition explicitly permits ranked selection.

## 7. Replacement

Replacement is explicit, traceable, and validated. A replacement inherits the capability obligation but not necessarily the implementation contract beyond the capability contract itself.

The final resolution report must identify:

- original provider;
- replacing provider;
- source of the replacement;
- applicable profile/environment;
- compatibility decision.

## 8. Decoration

Decorators wrap an already selected provider. Decoration ordering is deterministic using declared order/priority followed by a stable tie-breaker. Cycles and duplicate decorator application are errors.

## 9. Resolution and caching

The registry selects providers; the container or provider factory creates instances. Instance caching follows the declared lifetime and belongs to the construction layer, not to provider selection policy.

Permitted initial lifetimes:

- singleton per Runtime instance;
- execution scoped;
- transient.

Global process-wide state is prohibited by default.

## 10. Diagnostics

WP-202 SHALL define stable codes for at least:

- invalid capability identifier;
- duplicate provider ID;
- missing required capability;
- ambiguous provider selection;
- unknown replacement target;
- replacement cycle;
- decoration cycle;
- incompatible capability version;
- resolution cycle;
- late registration.

## 11. Observability

The Runtime must be able to produce a redacted capability inventory containing:

- registered capabilities;
- requirement modes;
- provider sources;
- selected providers;
- replacements and decorators;
- compatibility versions;
- unresolved diagnostics.

## 12. Anti-abuse rules

A capability SHALL NOT be created merely to:

- avoid constructor injection;
- access an arbitrary model or repository globally;
- expose application business services;
- bypass module dependency declarations;
- hide a circular dependency;
- replace proper Runtime Context data.

## 13. Mandatory baseline capabilities

The exact mandatory set is profile-dependent. The base Runtime architecture anticipates:

- `runtime.clock`;
- `runtime.diagnostics`;
- `config.repository`.

Logging and events may remain optional during early boot, provided a bootstrap-safe diagnostic sink exists.

## 14. Acceptance criteria for WP-202

WP-202 must implement this model without expanding the registry into a general-purpose container and must provide deterministic tests for every selection, replacement, decoration, cycle, and compatibility rule.
