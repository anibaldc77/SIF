---
id: EG-397
title: Protección de intentos de contraseña y bloqueo temporal
summary: Define contratos y una implementación determinista para limitar intentos de autenticación sin acoplar seguridad a una persistencia concreta.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-228
tags:
  - specification
  - security
  - password
  - throttling
  - lockout
depends_on:
  - EG-396
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-397 — Protección de intentos de contraseña y bloqueo temporal

## 1. Objetivo

Establecer una frontera extensible para observar intentos de contraseña, limitar abuso y aplicar bloqueos temporales sin introducir BaseModel, PDO, cache distribuida o infraestructura específica en Foundation.

## 2. Invariantes

1. La clave de protección combina proveedor de identidad y clave de búsqueda.
2. La contraseña nunca forma parte de la clave, decisión, diagnóstico o almacenamiento de intentos.
3. La representación de depuración no expone la clave de búsqueda; sólo publica una huella SHA-256.
4. Una decisión bloqueada debe indicar el instante UTC de reintento.
5. Un éxito elimina fallos y bloqueos anteriores para la misma clave.
6. El autenticador consulta la protección antes de resolver la identidad.
7. Identidad desconocida, hash ausente y contraseña inválida registran fallos con la misma clave lógica.
8. La implementación por defecto nula conserva compatibilidad hacia atrás.

## 3. Contratos

`PasswordAttemptProtectorInterface` define tres operaciones:

- inspeccionar si el intento puede continuar;
- registrar un fallo;
- registrar un éxito.

La interfaz puede implementarse mediante memoria local, Redis, cache PSR, base de datos o un servicio externo sin modificar `PasswordAuthenticator`.

## 4. Política

`PasswordAttemptPolicy` establece:

- cantidad máxima de fallos;
- ventana de observación;
- duración del bloqueo temporal.

Todos los valores deben ser positivos y la cantidad máxima debe ser al menos uno.

## 5. Implementación de referencia

`InMemoryPasswordAttemptProtector` ofrece semántica determinista para pruebas, CLI, desarrollo y procesos de un solo nodo. No se presenta como solución distribuida ni garantiza coordinación entre procesos.

## 6. Integración con autenticación

`PasswordAuthenticator` recibe opcionalmente un protector. Si no se proporciona, utiliza `NullPasswordAttemptProtector`, preservando el comportamiento público previo.

Cuando una clave está bloqueada, el autenticador devuelve `Rejected` antes de consultar proveedores. Los consumidores HTTP futuros podrán mapear este resultado sin revelar la existencia de la cuenta.

## 7. Seguridad y privacidad

Los adaptadores persistentes no deben almacenar contraseñas, credenciales completas ni claves de búsqueda en texto claro cuando una huella estable resulte suficiente. La política de retención debe ser limitada y documentada.

## 8. Fuera de alcance

- bloqueo administrativo permanente;
- CAPTCHA;
- reputación de IP;
- límites globales por red o tenant;
- almacenamiento distribuido;
- respuesta HTTP `429`;
- recuperación de cuenta.
