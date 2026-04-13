# 🚀 Proyecto Big Data — Spark + Superset + Chatbot IA

Entorno de trabajo con **Apache Spark** (procesamiento de datos), **Apache Superset** (visualización) y un **Chatbot text-to-SQL** con **N8N** + **Ollama** (IA), todo ejecutándose en contenedores Docker.

---

## 📋 Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y funcionando.
- **Mínimo 8 GB de RAM** (recomendado 16 GB). Consumo estimado del entorno:

  | Servicio | RAM estimada |
  |----------|-------------|
  | Spark (JVM) | ~1 – 2 GB |
  | Superset | ~500 MB – 1 GB |
  | Ollama (qwen2.5-coder:1.5b) | ~1.5 – 2 GB |
  | N8N | ~200 – 400 MB |
  | **Total** | **~3.5 – 5.5 GB** |

- Los archivos del proyecto descargados en tu ordenador:
  ```
  bi_big_data_simex/
  ├── docker-compose.yml
  ├── Dockerfile.superset
  └── datos/               ← Aquí colocarás tus CSV
  ```

---

## 1️⃣ Arrancar el entorno

Abre una terminal, ve a la carpeta del proyecto y ejecuta:

```bash
docker compose up -d
```

> La primera vez tardará unos minutos porque tiene que descargar las imágenes y construir el contenedor de Superset.

Para comprobar que todo funciona:

```bash
docker compose ps
```

Deberías ver los contenedores corriendo: `spark`, `superset`, `ollama` y `n8n`.

| Servicio | URL | Descripción |
|----------|-----|-------------|
| Spark Master UI | http://localhost:8080 | Panel de administración de Spark |
| Superset | http://localhost:8088 | Herramienta de visualización de datos |
| Ollama | http://localhost:11434 | Servidor de IA (LLM local) |
| N8N | http://localhost:5678 | Automatización de workflows / Chatbot |

---

## 2️⃣ Colocar tus datos

Copia tus archivos CSV en la carpeta `datos/` del proyecto. Por ejemplo:

```
datos/
└── ventas_sucias.csv
```

Estos ficheros estarán accesibles dentro del contenedor de Spark automáticamente.

---

## 3️⃣ Procesar datos con PySpark

### Entrar al contenedor de Spark

```bash
docker compose exec spark /opt/spark/bin/pyspark
```

Esto te abrirá una consola interactiva de PySpark donde puedes escribir código Python directamente.

### Ejemplo: limpiar y guardar una tabla

```python
from pyspark.sql import SparkSession

spark = SparkSession.builder \
    .appName("ProyectoClase") \
    .getOrCreate()

# 1. Leer un CSV crudo desde la carpeta datos
df = spark.read.csv("datos/ventas_sucias.csv", header=True, inferSchema=True)

# 2. Hacer transformaciones (limpieza, agregación...)
df_clean = df.filter(df.precio > 0).groupBy("categoria").sum("precio")

# 3. Guardar como tabla — ESTO ES LO QUE LEERÁ SUPERSET
df_clean.write.mode("overwrite").saveAsTable("ventas_final")
```

> **⚠️ Importante:** El paso 3 (`saveAsTable`) es imprescindible. Si no guardas los datos como tabla, Superset no podrá verlos. Puedes crear tantas tablas como necesites.

Para salir de la consola PySpark escribe `exit()` o pulsa `Ctrl+D`.

---

## 4️⃣ Conectar Superset a Spark

1. Abre **Superset** en tu navegador: http://localhost:8088
2. Inicia sesión con las credenciales:
   - **Usuario:** `admin`
   - **Contraseña:** `admin`
3. Ve a **Settings** → **Database Connections** → **+ Database**
4. Selecciona **Other** como tipo de base de datos.
5. En el campo **SQLALCHEMY URI** introduce:
   ```
   hive://spark:10000/default
   ```
6. Ve a la pestaña **Advanced** → **Security** y marca la casilla **"Expose database in SQL Lab"**.
7. Haz clic en **Test Connection** para verificar que funciona.
8. Haz clic en **Finish** para guardar.

> **💡 Nota:** Usamos `spark` (el nombre del servicio en Docker) en la URI, no `localhost`. Los contenedores se comunican entre sí por nombre de servicio.

---

## 5️⃣ Visualizar los datos

### Consultar en SQL Lab

1. En Superset, ve a **SQL Lab** → **SQL Editor**.
2. Selecciona la base de datos que acabas de crear.
3. Escribe tu consulta, por ejemplo:
   ```sql
   SELECT * FROM ventas_final
   ```
4. Pulsa **Run** para ver los resultados.

### Crear gráficos

1. Tras ejecutar una consulta en SQL Lab, haz clic en **Create Chart**.
2. Elige el tipo de gráfico que quieras (barras, líneas, tarta…).
3. Configura los ejes y métricas según los datos de tu tabla.
4. Guarda el gráfico y, si quieres, añádelo a un **Dashboard**.

---

## 🔄 Flujo de trabajo resumido

```
CSV en datos/ → PySpark (limpiar + saveAsTable) → Superset (consultar + visualizar)
                                                 → Chatbot N8N (preguntar en lenguaje natural)
```

1. **Coloca** tus CSV en `datos/`.
2. **Procesa** los datos dentro del contenedor de Spark con PySpark.
3. **Guarda** el resultado como tabla con `saveAsTable("nombre_tabla")`.
4. **Consulta** la tabla desde Superset en SQL Lab.
5. **Crea** gráficos y dashboards con los resultados.
6. **Pregunta** en lenguaje natural mediante el chatbot de N8N.

---

## 6️⃣ Chatbot Text-to-SQL con N8N + Ollama

El chatbot permite **preguntar en lenguaje natural** y recibir datos directamente de Spark. Utiliza Ollama (modelo Qwen 2.5-Coder:1.5b) para convertir tu pregunta en SQL.

### Verificar que Ollama tiene el modelo

La primera vez, el servicio `ollama-init` descarga el modelo automáticamente. Puedes verificarlo con:

```bash
docker compose exec ollama ollama list
```

Deberías ver `qwen2.5-coder:1.5b` en la lista. Si no aparece, descárgalo manualmente:

```bash
docker compose exec ollama ollama pull qwen2.5-coder:1.5b
```

### Crear el workflow en N8N

1. Abre **N8N** en tu navegador: http://localhost:5678
2. Crea un nuevo workflow con estos nodos:

```
[Chat Trigger] → [Ollama (generar SQL)] → [HTTP Request (ejecutar SQL en Spark)] → [Respuesta al usuario]
```

#### Nodo 1: Chat Trigger
- Añade un nodo **Chat Trigger** (es el punto de entrada del chatbot).

#### Nodo 2: Ollama (generar SQL)
- Añade un nodo **Ollama Chat Model** o **AI Agent**.
- **Base URL:** `http://ollama:11434`
- **Model:** `qwen2.5-coder:1.5b`
- En el prompt del sistema, usa algo como:
  ```
  Eres un asistente que convierte preguntas en consultas SQL.
  Solo responde con la consulta SQL, sin explicaciones.
  Las tablas disponibles están en una base de datos Hive/Spark.
  ```

#### Nodo 3: HTTP Request (ejecutar SQL en Spark)
- Añade un nodo **HTTP Request**.
- **Method:** `POST`
- **URL:** `http://spark:10000` (Thrift Server)
- Alternativamente, puedes usar un nodo de **base de datos** con la conexión JDBC a Spark.

#### Nodo 4: Respuesta
- El resultado de la consulta se devuelve automáticamente al chat.

3. **Activa** el workflow y usa el botón **Chat** en la esquina inferior para probarlo.

> **💡 Tip:** Puedes preguntarle cosas como *"¿Cuál es la categoría con más ventas?"* y el chatbot generará el SQL, lo ejecutará y te devolverá el resultado.

---

## 🛑 Parar el entorno

Cuando termines de trabajar:

```bash
docker compose down
```

Tus datos se conservan en las carpetas `datos/`, `spark-warehouse/`, `ollama_data/` y `n8n_data/`. La próxima vez que ejecutes `docker compose up -d` todo seguirá ahí.

---

## ❓ Resolución de problemas

| Problema | Solución |
|----------|----------|
| `docker compose up` da error | Asegúrate de que Docker Desktop esté abierto y funcionando |
| Superset no carga | Espera 1-2 minutos, el servidor tarda en arrancar |
| `Test Connection` falla en Superset | Verifica que el contenedor de Spark esté corriendo con `docker compose ps` |
| No veo mi tabla en Superset | Asegúrate de haber usado `saveAsTable()` en PySpark |
| Error al leer CSV | Comprueba que el fichero está en la carpeta `datos/` y el nombre coincide |
| Ollama no tiene el modelo | Ejecuta `docker compose exec ollama ollama pull qwen2.5-coder:1.5b` |
| N8N no conecta con Ollama | Verifica que Ollama esté corriendo: `docker compose ps ollama` |
