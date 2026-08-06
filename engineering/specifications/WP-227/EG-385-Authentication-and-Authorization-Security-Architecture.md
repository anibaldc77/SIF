---
id: EG-385
title: Authentication and Authorization Security Architecture
summary: Defines the trust boundaries, principal model, authentication and authorization separation, extension contracts and compatibility constraints for WP-227.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-04
updated: 2026-08-04
work_package: WP-227
increment: I1
tags:
  - security
  - authentication
  - authorization
  - identity
  - principal
  - policies
  - architecture
depends_on:
  - EG-354
  - EG-355
  - EG-357
  - EG-358
  - EG-377
  - EG-384
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# Authentication and Authorization Security Architecture

## 1. Propósito

Esta especificación define la arquitectura gobernada para incorporar autenticación y autorización a SIF sin acoplar el framework a una base de usuarios, mecanismo de credenciales, proveedor de identidad, modelo de roles ni transporte concreto.

WP-227 extiende la base HTTP, Session, Execution Context, Container, Event Dispatcher, Audit y Application Skeleton. No reemplaza ninguno de esos subsistemas ni convierte a Session en fuente de identidad.

## 2. Principios normativos

El subsistema DEBE preservar los siguientes principios:

1. autenticación y autorización son responsabilidades distintas;
2. una sesión no implica autenticación;
3. una identidad de seguridad no es un modelo persistente;
4. el principal activo pertenece al alcance de ejecución y nunca a un singleton mutable;
5. los rechazos esperables se representan mediante resultados explícitos;
6. los errores técnicos se diferencian de credenciales inválidas o permisos insuficientes;
7. los contratos permanecen neutrales respecto de HTTP, CLI, PDO, BaseModel, LDAP, JWT, OAuth y OpenID Connect;
8. los secretos, credenciales, tokens y material de sesión no aparecen en logs, diagnósticos ni excepciones.

## 3. Vocabulario gobernado

### 3.1 Identidad

Una identidad representa una entidad reconocible por un proveedor o aplicación. Su identificador es opaco para el Core. El framework no presume que sea un número, correo electrónico, nombre de usuario, UUID o clave primaria.

### 3.2 Principal

El principal representa al sujeto de seguridad efectivo durante una ejecución. Puede ser anónimo o autenticado. Un principal autenticado puede portar atributos y evidencia de autenticación en incrementos posteriores.

El principal NO DEBE ser:

- una entidad BaseModel;
- un registro PDO;
- un objeto de sesión;
- un request;
- una instancia del contenedor;
- un token de acceso sin validar;
- una colección mutable compartida globalmente.

### 3.3 Credencial

Una credencial es evidencia presentada para intentar autenticar. Su forma concreta pertenece a adaptadores especializados. El Core no define todavía contraseña, API key, certificado, JWT ni código de un solo uso como mecanismo obligatorio.

### 3.4 Autenticación

La autenticación determina si la evidencia presentada permite establecer un principal autenticado y bajo qué contexto o método se obtuvo esa confianza.

### 3.5 Autorización

La autorización evalúa si un principal puede ejecutar una acción sobre un recurso dentro de un contexto. La decisión no debe inferirse únicamente de que el principal esté autenticado.

## 4. Fronteras de confianza

La secuencia conceptual es:

```text
entrada no confiable
    -> extracción gobernada de credenciales
    -> autenticador explícito
    -> resultado de autenticación
    -> principal request-scoped
    -> política de autorización
    -> decisión explícita
    -> ejecución o rechazo seguro
```

Ningún dato proveniente de cookies, headers, body, query, route parameters, variables de entorno o almacenamiento externo se considera identidad confiable antes de atravesar un autenticador registrado.

## 5. Separación de Session

Session conserva estado entre solicitudes. Authentication establece identidad confiable. Son subsistemas independientes.

Una sesión puede existir en estado anónimo. Un principal autenticado puede existir sin sesión en mecanismos stateless. La integración futura podrá persistir una representación mínima y versionada de autenticación, pero no objetos arbitrarios ni modelos de dominio completos.

Después de un cambio de frontera de seguridad, como autenticación o elevación de confianza, la integración con Session DEBE solicitar regeneración del identificador para resistir fijación de sesión.

`SessionId` nunca puede utilizarse como identificador de principal.

## 6. Estado de autenticación

I1 introduce solamente dos estados estables y no ambiguos:

- `anonymous`;
- `authenticated`.

Estados como expirado, inválido, bloqueado, deshabilitado o desafiado no son estados del principal. Son resultados, motivos de fallo o condiciones de política que se modelarán por separado.

## 7. Contrato mínimo de principal

`PrincipalInterface` expone el estado de autenticación y una consulta explícita `isAuthenticated()`.

I1 incorpora `AnonymousPrincipal` como representación segura por defecto. El principal autenticado, sus identificadores, atributos y evidencia serán definidos en I2 para evitar fijar prematuramente una API insuficiente.

La ausencia de principal no debe representarse con `null` cuando el runtime de seguridad esté habilitado. Debe utilizarse `AnonymousPrincipal`.

## 8. Autenticación

Los incrementos posteriores deberán definir contratos para:

- solicitud de autenticación;
- credencial;
- autenticador;
- resultado exitoso;
- rechazo esperado;
- fallo técnico;
- registro y selección determinista de autenticadores.

Un autenticador no debe lanzar excepciones para representar simplemente credenciales incorrectas. Las excepciones quedan reservadas para violaciones de contrato, configuración inválida o fallos de infraestructura.

## 9. Autorización

La autorización deberá modelar explícitamente:

- sujeto;
- acción;
- recurso opcional;
- contexto;
- política;
- decisión;
- motivo seguro de denegación.

Los roles podrán aportarse como atributos o mediante adaptadores, pero no serán el contrato central. Esta decisión preserva compatibilidad con RBAC, ABAC, ownership, multi-tenancy y políticas institucionales futuras.

Una denegación esperable no es una excepción de infraestructura. La capa HTTP podrá traducir decisiones a `401 Unauthorized` o `403 Forbidden` según exista o no autenticación válida.

## 10. Execution Context

El principal activo debe integrarse con `ExecutionContext` mediante una representación controlada y request-scoped. La integración no debe:

- mutar contextos compartidos;
- serializar credenciales;
- propagar secretos;
- incluir atributos sensibles por defecto;
- asumir transporte HTTP.

La propagación entre procesos requerirá una política explícita y no forma parte de I1.

## 11. Eventos y auditoría

Eventos futuros podrán registrar resultados de autenticación y autorización, pero nunca material sensible.

Metadatos permitidos:

- resultado;
- autenticador o política por identificador estable;
- tipo de principal;
- correlation identifier;
- motivo normalizado y no sensible;
- timestamp provisto por Clock.

Metadatos prohibidos:

- contraseña;
- token;
- cookie;
- session identifier;
- credential payload;
- secreto criptográfico;
- contenido completo de claims o atributos personales.

## 12. Container y ciclo de vida

Autenticadores, políticas y registries pueden ser servicios del Container. El principal efectivo y cualquier estado mutable de evaluación deben ser request-scoped o pasarse explícitamente.

No se permite almacenar el principal activo en propiedades estáticas, singletons mutables ni variables globales.

## 13. Compatibilidad

WP-227 es opt-in. Sin registrar el runtime o middleware de seguridad:

- el HTTP runtime conserva su comportamiento;
- Session conserva su comportamiento;
- los controladores existentes no cambian;
- CLI y Skeleton continúan funcionando;
- no se incorporan dependencias externas.

Las nuevas APIs se incorporan bajo `Sif\Foundation\Security` y no modifican contratos públicos existentes.

## 14. Extensibilidad

La arquitectura debe admitir adaptadores futuros para:

- repositorios BaseModel o PDO;
- LDAP y Active Directory;
- API keys;
- autenticación por certificado;
- JWT;
- OAuth 2.0;
- OpenID Connect;
- MFA;
- identidad de procesos y service accounts.

Estos mecanismos no serán implementados en I1 y no deberán condicionar el contrato base.

## 15. Riesgos controlados

1. **Acoplamiento a usuarios persistidos:** evitado separando identidad de modelos.
2. **Confusión sesión-identidad:** evitada mediante fronteras normativas y prueba de dependencia.
3. **Autorización reducida a roles:** evitada usando políticas y decisiones como dirección arquitectónica.
4. **Principal global mutable:** prohibido por contrato de ciclo de vida.
5. **Filtración de secretos:** prohibida en logging, eventos, auditoría y diagnósticos.
6. **API prematura de claims:** diferida a I2 para definir un modelo inmutable completo.

## 16. Entrega I1

I1 entrega:

- `AuthenticationState`;
- `PrincipalInterface`;
- `AnonymousPrincipal`;
- pruebas de invariantes arquitectónicas;
- esta especificación;
- Architecture Review de I1.

## 17. Secuencia de WP-227

1. arquitectura de seguridad y fronteras de confianza;
2. modelo inmutable de identidad y principal;
3. contratos y resultados de autenticación;
4. orquestación y registry determinista;
5. integración con Execution Context y Session;
6. autorización basada en políticas y decisiones;
7. middleware HTTP, Controller, CLI y Skeleton;
8. compatibilidad, migración y product completion.

## 18. Exclusiones de I1

I1 no implementa:

- usuarios;
- passwords;
- hashing de passwords;
- login forms;
- persistencia de identidades;
- roles o permisos concretos;
- JWT;
- OAuth;
- OpenID Connect;
- middleware HTTP;
- restauración desde Session;
- autorización ejecutable.
