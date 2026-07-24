---
id: EG-032
title: Built-in Analyzers Architecture
summary: Defines the architecture, contracts, ordering and governance principles for the built-in SIF Builder analyzers.
status: Draft for Review
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-106
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - analyzers
  - architecture
depends_on:
  - EG-011
  - EG-014
  - EG-009
related_adrs: []
references:
  - EG-011
  - EG-014
  - EG-009
---

# EG-032 — Built-in Analyzers Architecture

## Estado

- Work Package: WP-106
- Versión: 1.0.0
- Estado: Draft for Review
- Tipo: Engineering Specification
- Alcance: SIF Builder

## 1. Propósito

Definir la arquitectura de los analizadores incorporados de SIF Builder. Los analizadores deberán evaluar el estado del repositorio sin modificarlo, emitir diagnósticos estructurados y reutilizar las capacidades de descubrimiento, indexación y resolución implementadas en WP-101 y WP-102.

WP-106 introduce una capa de validación automática para documentos gobernados, referencias y políticas de ingeniería. Esta capa debe ejecutarse mediante el contrato `AnalyzerInterface` y la infraestructura de registros, selección y ciclo de vida establecida por WP-103.

## 2. Objetivos

1. Proveer analizadores incorporados listos para usar desde la CLI.
2. Mantener separación estricta entre análisis, generación y persistencia.
3. Emitir diagnósticos deterministas, trazables y aptos para CI/CD.
4. Permitir selección individual o conjunta mediante identificadores públicos estables.
5. Reutilizar `RepositoryWorkspace`, `RepositoryIndex` y `ResolutionResult`.
6. Mantener compatibilidad con ejecución estricta y tolerante.
7. Evitar lógica especial dentro de `CliApplication` o `BuilderLifecycle`.

## 3. Principios arquitectónicos

### 3.1 Extensiones normales del Engine

Un analizador incorporado es una implementación ordinaria de `AnalyzerInterface`. No debe ser tratado como caso especial por el Engine ni por la CLI.

### 3.2 Solo lectura

Los analizadores no escriben archivos ni alteran el `BuilderContext`. Su salida consiste exclusivamente en diagnósticos y estado de ejecución.

### 3.3 Diagnósticos como contrato público

Cada hallazgo debe expresarse mediante `Diagnostic`, incluyendo como mínimo:

- código estable;
- severidad;
- mensaje;
- origen o ruta relativa cuando corresponda;
- contexto estructurado suficiente para reportería y automatización.

### 3.4 Determinismo

Con entradas equivalentes, un analizador debe producir diagnósticos equivalentes y en el mismo orden. No se incorporarán fechas, tiempos, identificadores aleatorios ni rutas absolutas.

### 3.5 Política separada de mecanismo

La mecánica para recorrer documentos y referencias debe permanecer separada de las reglas concretas. Las reglas susceptibles de configuración deberán modelarse mediante políticas o catálogos inmutables.

### 3.6 Compatibilidad progresiva

Los nuevos analizadores se registrarán de forma explícita en la composición predeterminada de la CLI. El contrato `AnalyzerInterface`, la semántica del Engine y los códigos de salida existentes no deben modificarse salvo especificación independiente.

## 4. Catálogo inicial

| Orden | Identificador | Responsabilidad | Entradas mínimas |
|---:|---|---|---|
| 10 | `metadata.completeness` | Verificar presencia y consistencia de metadatos obligatorios y recomendados | Workspace + Index |
| 20 | `reference.integrity` | Detectar referencias rotas, ambiguas, duplicadas y ciclos prohibidos | Workspace + Index + ResolutionResult |
| 30 | `document.consistency` | Evaluar coherencia entre identificador, tipo, ruta, work package, estado y versión | Workspace + Index |
| 40 | `repository.policy` | Aplicar reglas institucionales configurables sobre el conjunto documental | Workspace + Index + políticas |
| 50 | `generated.artifacts` | Verificar presencia, procedencia y consistencia básica de artefactos generados | Workspace + configuración |

El catálogo puede ampliarse mediante nuevas especificaciones sin modificar el núcleo del Engine.

## 5. Espacio de nombres y organización

Las implementaciones se ubicarán bajo:

```text
tools/builder/src/Analyzer/
    MetadataCompleteness/
    ReferenceIntegrity/
    DocumentConsistency/
    RepositoryPolicy/
    GeneratedArtifacts/
```

Las pruebas correspondientes se ubicarán bajo:

```text
tools/builder/tests/Analyzer/
```

## 6. Identificadores públicos

Los identificadores son parte de la interfaz pública de la CLI y deben:

- usar minúsculas ASCII;
- emplear segmentos separados por punto;
- ser estables;
- no incluir versiones;
- no depender del nombre de la clase PHP.

Ejemplos válidos:

```text
metadata.completeness
reference.integrity
document.consistency
repository.policy
generated.artifacts
```

## 7. Modelo de ejecución

Cada analizador recibe un `BuilderContext` y devuelve el resultado definido por `AnalyzerInterface`.

El ciclo conceptual es:

```text
BuilderContext
  → validar precondiciones
  → construir vista de análisis
  → aplicar reglas
  → ordenar diagnósticos
  → devolver resultado
```

Los analizadores no deben:

- lanzar excepciones por ausencia esperable de entradas;
- escribir archivos;
- registrar otros analizadores;
- invocar generadores;
- modificar el índice o el grafo;
- depender directamente de la consola.

## 8. Precondiciones y diagnósticos de configuración

Cuando falte una entrada necesaria, el analizador debe devolver un diagnóstico de configuración estable en vez de provocar un error no controlado.

Rango reservado:

```text
ANALYZER-100 a ANALYZER-199
```

Asignación inicial:

| Código | Analizador |
|---|---|
| `ANALYZER-101` | Metadata Completeness |
| `ANALYZER-102` | Reference Integrity |
| `ANALYZER-103` | Document Consistency |
| `ANALYZER-104` | Repository Policy |
| `ANALYZER-105` | Generated Artifacts |

Los códigos de hallazgos funcionales utilizarán rangos propios por analizador y deberán documentarse en cada incremento.

## 9. Severidades

Se utilizarán las severidades existentes del Builder.

Criterio general:

- `error`: incumplimiento que invalida el repositorio o impide una operación confiable;
- `warning`: riesgo, inconsistencia o incumplimiento no bloqueante;
- `info`: observación útil que no representa incumplimiento.

La severidad debe ser estable y estar definida por especificación. No debe inferirse dinámicamente desde el texto del mensaje.

## 10. Orden de diagnósticos

El orden canónico será:

1. severidad;
2. código;
3. ruta relativa;
4. identificador documental;
5. línea o posición;
6. mensaje.

Cada analizador podrá añadir claves de desempate documentadas, pero no alterar la estabilidad global.

## 11. Selección desde CLI

La CLI deberá exponer los analizadores mediante:

```powershell
php bin\sif-builder list
```

La selección se realizará mediante el mecanismo existente:

```powershell
php bin\sif-builder validate --analyzer=reference.integrity
```

Una selección vacía conservará la semántica vigente del Engine para ejecutar todos los analizadores registrados. Cualquier necesidad de expresar “ningún analizador” deberá resolverse en la composición o modo de ejecución, no modificando silenciosamente el contrato de `BuilderRequest`.

## 12. Ejecución estricta y tolerante

### 12.1 Modo estricto

Un diagnóstico de error debe provocar resultado de validación fallido conforme al mapeo de salida de WP-104.

### 12.2 Modo tolerante

El pipeline podrá continuar recolectando diagnósticos de analizadores posteriores, siempre que no exista una precondición técnica que impida su ejecución.

### 12.3 Fallos internos

Una excepción inesperada debe convertirse en el mecanismo de fallo de etapa ya definido por WP-103, conservando la causa original.

## 13. Configuración de políticas

Las reglas configurables deberán ingresar mediante contratos explícitos. No se leerán variables globales ni archivos directamente desde el analizador.

Modelo recomendado:

```text
RepositoryPolicySet
    → colección inmutable de reglas
    → identificadores estables
    → severidad definida
    → parámetros normalizados
```

La carga de archivos de configuración queda fuera del alcance de esta arquitectura y deberá definirse en un work package independiente si resulta necesaria.

## 14. Integración con reporters

Los reporters existentes deben poder representar los diagnósticos sin conocer implementaciones concretas.

El contexto estructurado podrá incluir:

- `document_id`;
- `document_type`;
- `path`;
- `field`;
- `reference_target`;
- `rule_id`;
- `expected`;
- `actual`.

No se incorporarán objetos de dominio no serializables dentro del contexto.

## 15. Integración con generadores

Los analizadores no dependen de generadores. Los generadores pueden ejecutarse aunque existan advertencias, conforme al modo de ejecución y a la política del pipeline.

Los artefactos generados no deben considerarse fuente primaria del análisis documental salvo en `generated.artifacts`, cuyo alcance se limita a verificar su presencia y procedencia.

## 16. Estrategia de pruebas

Cada incremento deberá incluir:

1. pruebas unitarias de reglas;
2. pruebas de fábrica o construcción de vistas;
3. pruebas del analizador completo;
4. prueba de registro en la composición predeterminada;
5. casos deterministas con distinto orden de entrada;
6. casos de ausencia de precondiciones;
7. validación PHPStan nivel 8;
8. validación de integración con la suite completa.

El cierre de WP-106 incluirá una prueba end-to-end que ejecute todos los analizadores sobre un fixture válido y otro inválido.

## 17. Incrementos previstos

### Incremento 1 — Metadata Completeness Analyzer

Validará campos obligatorios, recomendados y formatos básicos.

### Incremento 2 — Reference Integrity Analyzer

Evaluará referencias rotas, duplicadas, ambiguas y ciclos según política.

### Incremento 3 — Document Consistency Analyzer

Verificará coherencia entre identificadores, rutas, tipos, versiones, estados y work packages.

### Incremento 4 — Repository Policy Analyzer

Introducirá reglas institucionales inmutables y configurables por composición.

### Incremento 5 — Generated Artifacts Analyzer

Validará existencia y procedencia de artefactos incorporados por WP-105.

### Incremento 6 — End-to-End Validation

Validará selección, ejecución conjunta, reportería y códigos de salida.

## 18. Fuera de alcance

Quedan fuera de WP-106:

- corrección automática de documentos;
- escritura de metadatos;
- generación de archivos;
- carga dinámica de plugins;
- editor interactivo;
- análisis semántico mediante IA;
- validación jurídica o de contenido de negocio;
- acceso a red;
- hashing físico de archivos no expuesto por contratos existentes.

## 19. Compatibilidad

WP-106 no modifica contratos públicos de WP-100 a WP-105. Las implementaciones se incorporarán mediante registro explícito y podrán versionarse bajo SemVer.

Cualquier modificación futura a `AnalyzerInterface`, `BuilderContext`, `Diagnostic` o la semántica de selección requerirá una especificación y plan de migración independientes.

## 20. Criterios de aceptación de la arquitectura

La arquitectura se considera aprobada cuando:

- existe un catálogo inicial de analizadores;
- los identificadores públicos están definidos;
- las responsabilidades y límites están documentados;
- se establece política de diagnósticos y determinismo;
- se define la integración con CLI, Engine y reporters;
- se establece la estrategia incremental y de pruebas;
- no se introduce dependencia inversa desde Core hacia analizadores concretos.
