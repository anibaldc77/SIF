---
id: EG-213
title: Event Dispatcher Core
summary: Defines the synchronous deterministic event dispatch contracts and listener registry for the SIF Runtime.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-24
updated: 2026-07-24
work_package: WP-205
tags:
  - runtime
  - events
  - dispatcher
  - listeners
depends_on:
  - WP-204
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-213 — Event Dispatcher Core

## Decision

SIF SHALL provide an in-process synchronous event dispatcher that returns the same event instance it receives. Listener resolution SHALL support exact classes, parent classes, and implemented interfaces.

Listener execution order SHALL be deterministic: descending numeric priority followed by registration sequence for equal priorities. Subscribers SHALL declare event mappings explicitly; discovery, reflection, attributes, and filesystem scanning are outside this increment.

Events MAY implement `StoppableEventInterface`. A dispatcher SHALL invoke no additional listener once propagation is stopped. Exceptions raised by listeners SHALL propagate unchanged.

## Public contracts

- `EventDispatcherInterface`
- `ListenerProviderInterface`
- `EventSubscriberInterface`
- `StoppableEventInterface`

## Non-goals

- Runtime lifecycle wiring
- asynchronous dispatch
- queues or transports
- event sourcing or persistence
- automatic subscriber discovery
- exception aggregation

## Acceptance criteria

- exact, inherited, and interface listeners resolve correctly;
- priority and insertion order are stable;
- subscribers register without reflection;
- stopped events cease propagation;
- listener exceptions preserve identity;
- PHPUnit and PHPStan pass.
