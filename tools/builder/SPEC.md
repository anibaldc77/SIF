# Especificación: Support Library

Support no depende de componentes SIF. Sus value objects validan datos al crearse y sus colecciones evitan mutaciones de la instancia original. Los fallos se comunican con excepciones específicas; ningún método usa `false` como señal de error.

Las utilidades son servicios de instancia para mantener sustituibilidad. `Stopwatch` representa explícitamente el ciclo de inicio/parada y produce un `Timer` inmutable.

`Path` convierte separadores a `/`, resuelve `.` y `..`, y rechaza intentos de escapar de una raíz relativa.
