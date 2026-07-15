# SIF Runtime Foundation

Fase 1 de WP-003 para SIF 2.0.0-alpha1. El componente define el núcleo mínimo de ejecución sin introducir responsabilidades de Kernel, Bootstrap, Container, Configuration, Providers ni Events.

`Framework::create()` es el único punto público para crear una aplicación en ejecución. `Application` conserva su identidad inmutable y `Runtime` concentra el estado mutable de ciclo de vida.

## Uso

```php
$framework = \Sif\Foundation\Framework::create('sif', 'production');
$framework->start();
// Ejecutar trabajo de la aplicación.
$framework->stop();
```

## Pruebas

La suite se ejecuta con PHPUnit y cubre creación, metadatos, transiciones válidas y transiciones de ciclo de vida inválidas.
