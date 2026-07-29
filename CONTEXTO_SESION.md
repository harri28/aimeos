# Contexto de sesión — Kallpa Room

> Archivo de traspaso para retomar el trabajo en otra cuenta de Claude Code
> **sin perder contexto**. Está en la raíz `aimeos-laravel-master/` (fuera del
> repo git de `laravel-app/`, así no se sube a GitHub).
>
> Última actualización: sesión del 28/07/2026 (efecto de trabajo del "Inicio").

---

## 1. Qué es el proyecto

**Kallpa Room** — e-commerce de **hotel + turismo** (región San Martín / Tarapoto,
Perú) hecho con **Aimeos + Laravel 12**, PostgreSQL. Repurposeado desde el demo de
Aimeos. Empresa: *Servicios Globales Green E.I.R.L.*

- **Código:** `c:\xampp\htdocs\aimeos-laravel-master\laravel-app` (el repo git está AQUÍ).
- **Repo GitHub:** `harri28/aimeos` (rama `master`).

## 2. URLs y accesos

| Entorno | URL | Panel admin |
|---|---|---|
| **Local** | http://127.0.0.1:8000 | http://127.0.0.1:8000/admin |
| **Producción** | https://kallparoom.cloud | https://kallparoom.cloud/admin |

- **Cuenta admin (local Y producción):** `harristr045@gmail.com` / `73463054`
- **Servidor local:** se levanta con `php artisan serve --port=8000` desde `laravel-app`.
  (Si "no responde", matar el proceso php del puerto 8000 y volver a levantarlo.)

## 3. Reglas de trabajo (IMPORTANTES)

- **NO hay acceso directo al VPS.** Todo comando del VPS se le pasa al usuario
  **paso a paso, uno a la vez**, y él los ejecuta y pega el resultado.
- **En el VPS hay OTROS sistemas en producción** (genpharma, FarmaSystem, etc.).
  Evaluar impacto antes de cualquier reinicio de Apache/PostgreSQL/Docker.
- **Fase actual = trabajar en LOCAL primero**, validar visualmente con el usuario,
  y recién desplegar al VPS.
- **Cache busting:** cada cambio de CSS/JS sube `APP_VERSION` en `.env`
  (local va en **33**; producción quedó en **21**). En cada deploy hay que subir
  `APP_VERSION` en el `.env` de producción también.

## 4. Cómo el usuario despliega al VPS (secuencia)

El VPS usa **Docker** (contenedor `kallparoom-php`, PHP 8.2-fpm) montado en
`/var/www/kallparoom`, tras Apache con `mod_proxy_fcgi`, PostgreSQL en el host.

```bash
cd /var/www/kallparoom && git pull
chown -R www-data:www-data /var/www/kallparoom
sed -i 's/^APP_VERSION=.*/APP_VERSION=<N>/' /var/www/kallparoom/.env
docker exec -it -w /var/www/kallparoom kallparoom-php php scripts/setup_content.php   # contenido de BD
docker exec -it -w /var/www/kallparoom kallparoom-php php artisan config:clear
docker exec -it -w /var/www/kallparoom kallparoom-php php artisan view:clear
docker exec -it -w /var/www/kallparoom kallparoom-php php artisan aimeos:clear
docker restart kallparoom-php   # solo si hace falta limpiar opcache
```

> Nota: los `artisan` van DENTRO del contenedor con `-w /var/www/kallparoom`
> (el volumen está montado en esa ruta dentro del contenedor, no en /var/www/html).

## 5. Contenido que vive en la BD (NO viaja con git)

Se recrea con **`php scripts/setup_content.php`** (idempotente, no destructivo):
- Página CMS **Empresa** (`/p/about`) y **Términos** (`/p/terms`).
- **Categoría "Eventos"** (oculta del menú) para los eventos del inicio.
- Config del sitio: **redes sociales** (`social/facebook`, `social/instagram`…),
  **frases del inicio** (`home/typing/N`), **calendario** (`home/calendar/N` con
  formato `"fecha | texto"`).

Los **eventos de ejemplo** (productos id 35 "Festival de San Juan", 36 "Feria de
Lamas") se crearon SOLO en local con un script temporal ya borrado; en producción
el usuario los creará desde el panel (Catálogo → Productos, categoría "Eventos",
sin precio).

## 6. Estado del despliegue al VPS (PENDIENTE)

En el VPS quedó a medias:
- `git pull` hecho hasta commit **`5298fdc`**; `chown` hecho; `APP_VERSION=21`.
- `setup_content.php` ejecutado (creó Empresa + redes sociales).
- **FALTA en el VPS:** traer commits nuevos (`fcde9e3`, `e96d01e` + el commit del
  Inicio que aún no existe), re-ejecutar `setup_content.php` (crea Términos +
  categoría Eventos + frases + calendario), subir `APP_VERSION`, limpiar cachés
  y reiniciar contenedor.

## 7. LO QUE FALTA HACER (retomar aquí)

1. **Commit + push** de los cambios locales sin commitear (todo el "Inicio"
   nuevo). Archivos modificados:
   - `config/shop.php` (catalog-home sin catalog/lists; página cms con componentes)
   - `resources/views/vendor/shop/catalog/home.blade.php` (portada + frases + eventos + calendario, layout 2 columnas)
   - `resources/views/vendor/shop/base.blade.php` (enlace "Inicio" en navbar)
   - `public/vendor/shop/themes/default/aimeos.css` (estilos del inicio, calendario, botón categorías)
   - `public/vendor/shop/themes/default/catalog-filter.js` (toggle del botón de categorías)
   - `ext/kallpa-theme/templates/client/html/catalog/filter/tree-partial.php` (ocultar categoría "eventos" del menú)
   - `scripts/setup_content.php` (agrega categoría Eventos + frases + calendario)
   - NUEVOS: `public/vendor/shop/themes/default/assets/home-portada.webp`, `public/vendor/shop/themes/default/home-typing.js`
2. **Desplegar TODO al VPS** con la secuencia de la sección 4 + subir la portada
   (la webp SÍ va en git, se despliega con git pull).
3. Pendientes previos NO desplegados aún: página **Términos**, fix **navbar CMS**.

## 8. Qué se hizo en esta sesión (el "Inicio")

El inicio (`/`) se rehízo por completo (ya no muestra la cuadrícula de productos):
- **Fase 1 — Portada:** imagen `home-portada.webp` (optimizada a 206 KB), centrada, max-width 1150px.
- **Fase 2 — Frases máquina de escribir:** `home-typing.js`, leen de `home/typing/N` (config del sitio).
- **Fase 3 — Eventos:** categoría "Eventos" oculta; cada evento = producto (imagen+título+texto); tarjetas animadas.
- **Fase 4 — Calendario lateral:** columna derecha "Próximos acontecimientos" (sticky), lee `home/calendar/N` (`"fecha | texto"`); en móvil va abajo. Layout de 2 columnas (`.home-layout` > `.home-main` + `.home-calendar`).
- **Enlace "Inicio"** agregado al navbar (texto, estilo igual al del sidebar).
- **Botón de categorías rediseñado:** ahora es un **toggle** (mismo botón abre/cierra),
  fijo pegado al filo izquierdo, gris (`--ai-secondary`), se desliza al borde del panel
  al abrir; ícono a 125% para igualar el alto de la barra "Categorías". Se ocultó el
  botón de cerrar del panel.

## 9. Notas técnicas útiles (aprendidas en el camino)

- El core de Aimeos **ignora el CSS** del JSON grapesjs de las páginas CMS → los estilos
  de las páginas van en `aimeos.css`, el HTML en la BD.
- Aimeos guarda la URL de las páginas CMS con slash: `/about`, `/terms` (buscar por ambos).
- El bug de "Productos sin existencias" en el carrito era el plugin **ProductStock**
  (desactivado, status=0, en local y producción).
- Formatos de imagen del panel: se agregó `image/avif` a `mshop/media/manager/extensions`
  Y `allowedtypes`; el Docker de producción se reconstruyó con GD +avif +webp.
- El menú de categorías usa el mismo botón hamburguesa en escritorio y móvil (se desactivó
  el megamenu de escritorio).
