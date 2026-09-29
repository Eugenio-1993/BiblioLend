# Manual de Usuario — BiblioLend

> **Documento fuente para una página de manual.** Al convertir este archivo en una web, crear una barra de navegación fija con enlaces ancla a las secciones indicadas en el índice. Conservar la jerarquía de títulos y mostrar los avisos con estilo destacado.

## Índice de navegación

- [1. Bienvenida](#1-bienvenida)
- [2. Requisitos y acceso](#2-requisitos-y-acceso)
- [3. Navegación general](#3-navegación-general)
- [4. Catálogo de libros](#4-catálogo-de-libros)
- [5. Mi cuenta (estudiante)](#5-mi-cuenta-estudiante)
- [6. Panel del bibliotecario](#6-panel-del-bibliotecario)
- [7. Panel de administración](#7-panel-de-administración)
- [8. Páginas informativas](#8-páginas-informativas)
- [9. Cerrar sesión](#9-cerrar-sesión)
- [10. Problemas frecuentes](#10-problemas-frecuentes)
- [11. Alcance actual del prototipo](#11-alcance-actual-del-prototipo)

---

## 1. Bienvenida

BiblioLend es una biblioteca escolar digital de la Institución Educativa República de Honduras. Permite consultar un catálogo de libros y presenta paneles diferenciados para estudiantes, bibliotecarios y administradores.

Desde la página de bienvenida se puede leer una descripción de los beneficios de la plataforma y seleccionar **Iniciar sesión**. El botón **Descubrir funciones** desplaza la vista hasta la sección de beneficios.

### Beneficios presentados

- Consulta del catálogo de libros y recursos académicos.
- Gestión visual de préstamos y devoluciones.
- Consulta de actividad e historial académico desde el perfil.

## 2. Requisitos y acceso

### Requisitos

- Navegador web actualizado.
- Conexión a internet para cargar Bootstrap, fuentes y otros recursos externos.
- Correo institucional, contraseña y tipo de usuario asignados por la institución.

### Iniciar sesión

1. En la página de inicio, seleccione **Iniciar sesión**.
2. En **Tipo de usuario**, elija una opción: **Estudiante**, **Bibliotecario** o **Administrador**.
3. Escriba el **Correo institucional**.
4. Escriba la **Contraseña**.
5. Seleccione **Entrar al sistema**.

El rol seleccionado debe coincidir con las credenciales. Si no coincide, se muestra un mensaje indicando que el correo, la contraseña o el rol son incorrectos.

> **Cuentas de demostración del código actual:**
>
> | Rol | Correo | Contraseña |
> | --- | --- | --- |
> | Estudiante | `estudiante@ierepublicadehonduras.edu.co` | `123est` |
> | Bibliotecario | `biblio@ierepublicadehonduras.edu.co` | `123bib` |
> | Administrador | `admin@ierepublicadehonduras.edu.co` | `123adm` |
>
> Estas cuentas pertenecen al prototipo. No deben utilizarse como credenciales reales en una versión publicada.

### Destino según el rol

| Rol | Área de trabajo prevista |
| --- | --- |
| Estudiante | Panel principal y cuenta de usuario |
| Bibliotecario | Panel del bibliotecario |
| Administrador | Panel de administración |

## 3. Navegación general

Después de ingresar como estudiante, la barra superior incluye estas opciones:

- **Inicio:** abre el panel principal.
- **Sobre nosotros:** muestra el propósito de BiblioLend y el equipo CodeForge.
- **Contáctanos:** presenta los canales del equipo de desarrollo.
- **Reseñas:** permite escribir una opinión sobre la plataforma.
- **Catálogo:** abre la colección de libros disponible.
- **Icono de perfil:** abre la cuenta del usuario.

En pantallas pequeñas, use el botón de menú (icono de tres líneas) para expandir o contraer la navegación.

## 4. Catálogo de libros

El catálogo permite consultar los títulos exhibidos por la biblioteca.

### Buscar y filtrar

1. Abra **Catálogo** desde la barra de navegación.
2. Escriba un título o autor en el campo **Buscar por título o autor**.
3. Si lo necesita, elija una categoría: Literatura, Ciencia, Historia, Matemáticas o Inglés.
4. Revise las tarjetas de libros y su estado: **Disponible** o **Prestado**.

Cada tarjeta muestra título, autor, categoría, estado y el botón **Ver detalle**. Entre los títulos visibles se encuentran *100 Años de soledad*, *Principios de la Física*, *Meditaciones*, *Álgebra de Baldor*, *Inglés con Felipe* y *Biología Escolar*.

> **Aviso:** en el prototipo actual, los campos de búsqueda, el filtro y los botones **Ver detalle** son elementos visuales; todavía no aplican filtros ni abren una ficha del libro.

## 5. Mi cuenta (estudiante)

Acceda mediante el icono de perfil de la barra de navegación.

La pantalla contiene:

- Datos de usuario: nombre, rol, correo y matrícula.
- Resumen de **Préstamos activos**, tickets y libros vencidos.
- Botones para **Generar nuevo préstamo** y **Generar nueva devolución**.
- Estado de los libros asociados a la cuenta.
- Opción **Cerrar sesión**.

> **Aviso:** editar el perfil y generar tickets aún no ejecutan cambios ni crean solicitudes. Los datos, contadores y libros de esta vista son demostrativos.

## 6. Panel del bibliotecario

El rol **Bibliotecario** accede a una vista enfocada en la gestión diaria de la biblioteca.

### Información disponible

- Indicadores de libros disponibles, préstamos activos, devoluciones y retrasos.
- Tabla de **Préstamos pendientes** con estudiante, libro, fechas y estado.
- Lista de actividad reciente.

### Acciones mostradas

- **Gestionar catálogo**.
- **Registrar préstamo**.
- **Registrar devolución**.
- **Agregar libro**.
- **Ver usuarios**.

Use **Cerrar sesión** en el encabezado para salir de la cuenta.

> **Aviso:** las acciones rápidas son accesos de interfaz sin lógica conectada. El enlace de gestión de catálogo abre la vista del catálogo; los demás no registran información en esta versión.

## 7. Panel de administración

El rol **Administrador** abre el panel de gestión general de la plataforma.

### Resumen general

Muestra tarjetas de referencia para usuarios registrados, libros disponibles, préstamos activos y reseñas activas.

### Gestión de libros

La tabla de gestión presenta el título, autor, estado y botones de acción para los libros listados. También incluye el botón **Agregar libro**.

> **Aviso:** los botones **Agregar libro**, **Editar** y **Eliminar** no almacenan ni modifican datos en el prototipo actual. Las cifras y registros de la tabla son de ejemplo.

Puede salir mediante **Cerrar sesión** o regresar a la pantalla de acceso con **Volver al inicio**.

## 8. Páginas informativas

### Sobre nosotros

Describe a CodeForge, su misión de facilitar el acceso a recursos bibliográficos y a sus integrantes. Seleccione cada nombre del acordeón para expandir su rol y responsabilidad.

### Contáctanos

Presenta tarjetas del equipo de desarrollo con funciones y canales de contacto. Algunos enlaces abren un correo electrónico, GitHub o WhatsApp según la información configurada.

### Reseñas

1. Abra **Reseñas**.
2. Escriba su comentario en el campo **Reseña**.
3. Seleccione **Enviar reseña**.

> **Aviso:** el formulario valida que el campo no esté vacío, pero no guarda ni publica la reseña porque todavía no hay un servicio de almacenamiento conectado.

## 9. Cerrar sesión

Para cerrar la sesión:

1. Abra **Mi cuenta** si es estudiante, o use **Cerrar sesión** en el encabezado de los paneles de bibliotecario y administrador.
2. Seleccione **Cerrar sesión**.
3. El sistema elimina los datos locales de sesión y vuelve a la página de inicio.

## 10. Problemas frecuentes

### No puedo acceder a una página

Primero inicie sesión. Además, confirme que su rol tiene permiso para ver esa sección. Estudiantes, bibliotecarios y administradores tienen paneles distintos.

### El sistema dice que los datos son incorrectos

Revise el correo, la contraseña y el tipo de usuario. Los tres valores deben corresponder a la misma cuenta.

### No se guarda un préstamo, una devolución, un libro o una reseña

Estas funciones forman parte de la interfaz del prototipo, pero todavía no están conectadas a una base de datos ni a servicios de gestión. Solicite al equipo de desarrollo su implementación.

### Olvidé mi contraseña

El enlace de recuperación aparece en el acceso, pero aún no conduce a un proceso de restablecimiento. Contacte al responsable de la plataforma.

## 11. Alcance actual del prototipo

Esta documentación describe lo que se muestra en el repositorio actual. BiblioLend es una interfaz frontend basada en HTML, CSS, Bootstrap y JavaScript de demostración.

Para una versión operativa se requiere, como mínimo:

- Conectar correctamente los archivos de JavaScript de autenticación que las páginas referencian.
- Implementar una base de datos y un backend para usuarios, libros, préstamos, devoluciones y reseñas.
- Activar búsqueda, filtros, detalle de libros y edición de perfil.
- Sustituir credenciales fijas por autenticación segura y recuperación de contraseña real.

---

**BiblioLend · CodeForge · Institución Educativa República de Honduras · 2026**
