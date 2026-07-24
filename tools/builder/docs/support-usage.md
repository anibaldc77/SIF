---
id: SUPPORT-USAGE
title: Uso de Support
summary: use Sif\Support\Collections\ArrayCollection; use Sif\Support\ValueObjects\Version;.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - support
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Uso de Support

```php
use Sif\Support\Collections\ArrayCollection;
use Sif\Support\ValueObjects\Version;

$version = Version::fromString('2.0.0-alpha1');
$ids = (new ArrayCollection(['a', 'b']))->with('c');
```
