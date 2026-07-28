---
id: WP-214-I3A-CORRECTION-REVIEW
title: WP-214 I3A Classification Test Hierarchy Correction Review
summary: Records the correction of the throwable hierarchy used by deterministic first-match classification tests.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-28
updated: 2026-07-28
tags:
  - error-handling
  - classification
  - testing
  - correction
  - review
work_package: WP-214
depends_on:
  - EG-283
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-214-I3A Correction Review

## Purpose

Correct the hierarchy used by the deterministic first-match tests in WP-214-I3.

## Root cause

`LogicException` and `RuntimeException` are sibling subclasses of `Exception`. A rule targeting `RuntimeException` cannot match a `LogicException`, so the original test did not actually exercise competing matching rules.

## Correction

The precedence scenarios now use `LogicException` as the general parent and `InvalidArgumentException` as the specific child. Both rules match an `InvalidArgumentException`, allowing the tests to verify that declaration order determines the selected classification.

## Production impact

None. No production source file is modified. `OrderedThrowableClassifier` already implements the documented first-match policy correctly.
