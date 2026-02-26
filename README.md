# Prueba Técnica Práctica (KAWAK) - PHP + MySQL
#Vladimir Montes Betancur - 1053845248

[Repositorio](https://github.com/montesbetancurvladimir/Prueba_Kawak_Vladimir)

CRUD de registro de documentos con autenticación, búsqueda y generación de código único consecutivo.

## Requisitos
- PHP 7.0 o superior
- MySQL (recomendado: Laragon en Windows) - Uso MySQL Workbench
- Composer *(opcional: solo para autoload con vendor/)*

## Instalación (Laragon)

### 1) Clonar / copiar el proyecto
Clona el repositorio en la carpeta `www` de Laragon:

```bash
cd C:\laragon\www
git clone https://github.com/montesbetancurvladimir/Prueba_Kawak_Vladimir.git PRUEBA_KAWAK
```

El proyecto debe quedar en: (Para este caso se usa LARAGON)
- `C:\laragon\www\PRUEBA_KAWAK\`

### 2) Instalar dependencias (autoload)
En la carpeta del proyecto (opcional pero recomendado):

```bash
composer install
```
> Nota: **el proyecto funciona sin Composer** porque incluye un autoload de respaldo cuando no existe `vendor/`.  
> Si al ejecutar `composer` te sale “no se reconoce como comando”, instala Composer en Windows o ejecútalo desde una terminal que lo tenga en PATH.

### 3) Crear base de datos y datos precargados (DDL/DML)
Los scripts están en:
1 - DDL: `database/ddl.sql`
2 - DML (semillas): `database/dml.sql`

El **DDL ya crea la base de datos** `kawak_docs` no es necesario crearla para ejecutar los scripts
Ejecútalos en MySQL (en este orden).

#### PASO A PASO A) MySQL Workbench (recomendado)
- Abre MySQL Workbench y conéctate.
- `File > Open SQL Script` → abre `C:\Users\vladi\Documents\PRUEBA_KAWAK\database\ddl.sql` ejemplo de ruta en el computador local propio.
- En la pestaña del editor, clic en Execute.
- Repite con el segundo script `C:\Users\vladi\Documents\PRUEBA_KAWAK\database\dml.sql`

OTRAS OPCIONES:
```bash
mysql -u root < database/ddl.sql
mysql -u root < database/dml.sql
```

```powershell
cmd /c "mysql -u root < database\\ddl.sql"
cmd /c "mysql -u root < database\\dml.sql"
```

> Si `mysql` no está en tu PATH, ejecuta los comandos desde la terminal integrada de Laragon o usando los binarios de Laragon.

Por defecto los scripts crean la base de datos `kawak_docs`.

### 4) Configurar conexión a base de datos
Edita:
- `config/database.php`

Parámetros por defecto:
- host: `127.0.0.1`
- dbname: `kawak_docs`
- user: `root`
- password: *(vacío)*

### 5) Configurar Virtual Host / Document Root
La app usa front-controller en `public/index.php`.

Cómo abrirlo en Laragon (recomendado):
- En Laragon haz clic en **Start All** (Apache + MySQL).
- En el menú **www** de Laragon, abre el proyecto. Laragon normalmente crea un dominio tipo `.test`.
- La app debe ejecutarse con `public/` como raíz (DocumentRoot), es decir:
  - `C:\laragon\www\PRUEBA_KAWAK\public\`
- Abre en el navegador:
  - `http://PRUEBA_KAWAK.test/` (te redirige a login si no hay sesión)
  - `http://PRUEBA_KAWAK.test/login`

Si no te abre o ves 404:
- Revisa que el **DocumentRoot** del virtual host esté apuntando a `...\PRUEBA_KAWAK\public\` (no a la raíz del proyecto).
- Evita usar `http://localhost/PRUEBA_KAWAK/public/` como forma principal: puede fallar porque la app redirige a rutas absolutas (`/login`, `/documents`) sin el prefijo del subdirectorio. Con el dominio `.test` eso no pasa.

### 6) Credenciales de login
Credenciales configuradas en `config/auth.php`:
- usuario: `admin`
- contraseña: `Admin123*`

## Funcionalidades
- Login / Logout
- Listado de documentos
- Búsqueda por nombre o código
- Crear / Editar / Eliminar documentos
- Generación de código: `TIP_PREFIJO-PRO_PREFIJO-<consecutivo>`
  - No reutiliza consecutivos por combinación (Tipo, Proceso)
  - Si en edición cambian Tipo o Proceso, se recalcula el código con un consecutivo nuevo

## Rutas (URL amigables)
- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /documents`
- `GET /documents/create`
- `POST /documents/create`
- `GET /documents/{id}/edit`
- `POST /documents/{id}/edit`
- `POST /documents/{id}/delete`

## Pruebas técnicas (smoke tests)
Ejecuta:
```bash
php tests/run.php
```

- Verifica conexión a la BD y que existan **Tipos** y **Procesos** (semillas del `dml.sql`).
- Limpia la tabla `DOC_DOCUMENTO` para correr la prueba.
- Crea/edita/elimina documentos y valida la regla de **código** y **consecutivo** (incrementa y no reutiliza).

