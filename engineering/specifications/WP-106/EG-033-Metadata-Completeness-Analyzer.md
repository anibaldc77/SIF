# EG-033 — Metadata Completeness Analyzer

## Estado

- Work Package: WP-106
- Incremento: 1
- Versión: 1.0.0
- Estado: Proposed
- Tipo: Engineering Specification

## 1. Propósito

Implementar el analizador incorporado `metadata.completeness` para detectar ausencias y vacíos en los metadatos normalizados del repositorio sin escribir archivos ni modificar el contexto de ejecución.

## 2. Entradas

El analizador requiere:

- `RepositoryWorkspace`;
- `MetadataRegistry`;
- `RepositoryIndex`.

La ausencia de cualquiera de estas entradas produce `ANALYZER-101` con severidad `error`.

## 3. Reglas

| Código | Severidad | Regla |
|---|---|---|
| `METACOMP-201` | error | Existe una entrada indexada sin documento equivalente en `MetadataRegistry`. |
| `METACOMP-202` | error | Un valor requerido normalizado (`title`, `document_class`, `category`, `status` o `version`) está vacío. |
| `METACOMP-203` | warning | Falta un campo recomendado (`document_class` en el front matter o `summary`). |
| `METACOMP-204` | warning | El documento no declara etiquetas. |
| `METACOMP-205` | warning | Un documento de categoría `Work Package` no declara `work_package`. |
| `METACOMP-206` | error | Existe un documento de metadatos que no aparece en el índice. |

Las validaciones sintácticas obligatorias del esquema base permanecen bajo `CoreMetadataValidator` y la etapa de descubrimiento. Este analizador no duplica el parser ni sustituye al validador de WP-100; evalúa completitud y paridad sobre el workspace ya construido.

## 4. Determinismo

Los hallazgos se ordenan por:

1. severidad;
2. código;
3. ruta normalizada;
4. identificador;
5. campo;
6. mensaje.

Las rutas usan `/` y no se incorporan fechas, tiempos ni identificadores aleatorios.

## 5. Integración CLI

`DefaultCliApplicationFactory` registra el analizador y lo publica en `StaticComponentCatalog`.

```powershell
php bin\sif-builder list
php bin\sif-builder validate --analyzer=metadata.completeness
```

## 6. Compatibilidad

No se modifican `AnalyzerInterface`, `BuilderContext`, `AnalysisResult`, `Diagnostic` ni la semántica de selección. La implementación es una extensión ordinaria del Engine.

## 7. Criterios de aceptación

- identificador público estable;
- precondición expresada como diagnóstico;
- reglas funcionales documentadas;
- orden determinista;
- registro en CLI;
- pruebas del inspector, analizador y composición;
- PHPStan nivel 8 y suite completa sin errores.
