# SIF Support Library

Biblioteca fundacional de SIF 2.0. Proporciona colecciones, value objects, utilidades inyectables y medición de tiempo, sin depender de otros componentes.

## Uso

Las dependencias se inyectan explícitamente. `Str`, `Arr` y `Reflection` son servicios de instancia, por lo que los consumidores no dependen de helpers globales ni de estado compartido.

`Version`, `Uuid`, `Path`, `Environment` y `JsonDocument` encapsulan sus respectivas invariantes. `ArrayCollection` ofrece una colección inmutable basada en valores.

```bash
composer install
composer test
```

Consulte [SPEC.md](SPEC.md) y [la guía de uso](docs/support-usage.md).
