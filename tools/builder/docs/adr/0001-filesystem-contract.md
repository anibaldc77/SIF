# ADR 0001: Acceso exclusivo mediante contrato

## Decisión

Los consumidores del Builder dependen exclusivamente de `FileSystemInterface`. Los únicos accesos a funciones nativas del sistema de archivos están encapsulados en `LocalFileSystem`.

## Consecuencia

Los drivers pueden sustituirse sin modificar consumidores y las pruebas pueden ejecutarse contra `VirtualFileSystem`.
