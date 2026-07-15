# ADR 0002: Utilidades como servicios de instancia

## Decisión

`Str`, `Arr` y `Reflection` se exponen como servicios de instancia, aunque no mantienen estado. Esto conserva la posibilidad de sustituirlos, decorarlos o inyectarlos sin introducir funciones globales ni clases estáticas.

## Consecuencia

Los consumidores reciben explícitamente la dependencia que utilizan y el comportamiento es testeable de forma aislada.
