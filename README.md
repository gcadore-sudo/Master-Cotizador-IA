# MasterStation Cotizador IA

Plugin de WordPress para **MasterStation.net** que funciona como **cotizador de diseño web** con formulario multi-step, panel de administración, envío de correos y recomendaciones generadas por IA (Gemini).

---

## Instalación

1. Descarga o clona este repositorio.
2. Copia la carpeta `masterstation-cotizador/` a `/wp-content/plugins/` de tu instalación WordPress.
3. Activa el plugin desde **WP Admin → Plugins → Cotizador MasterStation IA**.
4. Al activar, se crearán servicios y adicionales de ejemplo automáticamente.
5. Configura tu **API Key de Gemini** en **Cotizador MasterStation → Ajustes**.

---

## Uso del Shortcode

Inserta el cotizador en cualquier página o entrada con:

```
[masterstation_cotizador]
```

El wizard de 5 pasos aparecerá con:
- Diseño minimalista moderno y responsive.
- Barra de progreso interactiva.
- Selección de servicios y adicionales mediante tarjetas.
- Recomendación de IA generada por Gemini (si está configurada).
- Envío automático de emails al administrador y al cliente.
- Botón de WhatsApp con mensaje prellenado.

---

## Configuración

Accede a **WP Admin → Cotizador MasterStation → Ajustes** para configurar:

| Campo | Descripción | Valor por defecto |
|-------|-------------|-------------------|
| Email(s) administrador | Destinatario(s) de nuevas cotizaciones (separar con coma) | `gcadore@masterstation.net` |
| WhatsApp destino | Número de WhatsApp de MasterStation (formato `+XXXXXXXXXXX`) | `+584241837004` |
| Código de país por defecto | Se antepone si el cliente no incluye `+` | `+58` |
| Firma de emails | Texto al pie de todos los correos | `Equipo MasterStation net...` |
| Activar IA | Habilitar/deshabilitar recomendaciones de Gemini | Activado |
| Gemini API Key | Clave de API de Google AI Studio | *(vacío)* |
| Modelo Gemini | Modelo a usar para generación de texto | `gemini-1.5-flash` |
| Plantilla email Admin | HTML del email al administrador (con variables) | *(plantilla por defecto)* |
| Plantilla email Cliente | HTML del email al cliente (con variables) | *(plantilla por defecto)* |
| Plantilla WhatsApp | Mensaje prellenado del botón WhatsApp | *(plantilla por defecto)* |

### Variables de plantilla disponibles

| Variable | Descripción |
|----------|-------------|
| `{client_name}` | Nombre del cliente |
| `{client_email}` | Email del cliente |
| `{client_whatsapp}` | WhatsApp normalizado del cliente |
| `{services_table}` | Tabla HTML de servicios seleccionados |
| `{addons_table}` | Tabla HTML de adicionales seleccionados |
| `{client_note}` | Nota del cliente |
| `{total}` | Total estimado en USD |
| `{ai_recommendation}` | Recomendación generada por Gemini |
| `{quote_id}` | ID interno de la cotización |
| `{quote_date}` | Fecha y hora de la cotización |
| `{signature}` | Firma configurada en Ajustes |
| `{services_list}` | Lista de servicios (para WhatsApp) |
| `{addons_list}` | Lista de adicionales (para WhatsApp) |

---

## Gestión de Servicios

**WP Admin → Cotizador MasterStation → Servicios**

- Agrega, edita y elimina servicios que aparecen en el paso 2 del wizard.
- Campos: **Título**, **Descripción**, **Costo (USD)**, **Orden**, **Activo/Inactivo**.
- Solo los servicios marcados como **Activo** aparecen en el cotizador.

---

## Gestión de Adicionales

**WP Admin → Cotizador MasterStation → Adicionales**

- Idéntico al módulo de servicios, pero para funcionalidades complementarias (paso 3 del wizard).
- Campos: **Título**, **Descripción**, **Costo (USD)**, **Orden**, **Activo/Inactivo**.

---

## Gestión de Cotizaciones

**WP Admin → Cotizador MasterStation → Cotizaciones**

- Listado filtrable por nombre, email y mes.
- Ver detalle completo de cada cotización (cliente, servicios, adicionales, nota, recomendación IA, total).
- Acciones disponibles:
  - **📧 Reenviar** – Reenvía el email de cotización al cliente.
  - **💬 WhatsApp** – Abre WhatsApp con un mensaje prellenado dirigido al número de MasterStation.

---

## Flujo del Wizard (5 Pasos)

| Paso | Título | Descripción |
|------|--------|-------------|
| 1 | Datos del cliente | Nombre, email y WhatsApp. Validación en cliente y servidor. |
| 2 | Selección de servicios | Tarjetas de servicios activos con título, descripción y precio. Selección múltiple. |
| 3 | Adicionales + Nota | Tarjetas de adicionales activos y campo de nota libre. |
| 4 | Resumen + IA | Resumen de toda la selección y recomendación generada por Gemini. |
| 5 | Total + Envío | Total final, estado de envío de emails, botón WhatsApp y despedida. |

---

## Seguridad

- Nonces en todas las peticiones REST y formularios de administración.
- Sanitización con `sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`, `wp_kses_post`.
- Validación de email con `is_email()` en servidor.
- Control de capacidades (`manage_options`) en todas las pantallas de administración.
- API Key de Gemini almacenada en opciones de WP y mostrada como campo `password`.

---

## Stack

- WordPress: 6.x (probado en 6.9.4)
- PHP: 8.0+ (optimizado para 8.3)
- Moneda: USD ($)
- IA: Google Gemini (via `wp_remote_post()`)
- WhatsApp: link `wa.me` (sin envío automático)

---

## Estructura del Plugin

```
masterstation-cotizador/
├── masterstation-cotizador.php        # Bootstrap del plugin
├── includes/
│   ├── class-msq-install.php          # Activación e instalación
│   ├── class-msq-plugin.php           # Init y assets
│   ├── class-msq-cpt.php              # Custom Post Types
│   ├── class-msq-admin.php            # Menús y handlers de admin
│   ├── class-msq-rest.php             # Endpoints REST API
│   ├── class-msq-email.php            # Envío de emails
│   ├── class-msq-gemini.php           # Cliente Gemini AI
│   ├── class-msq-whatsapp.php         # Generador de links WhatsApp
│   ├── class-msq-shortcode.php        # Shortcode [masterstation_cotizador]
│   └── class-msq-validators.php       # Validación de email y WhatsApp
├── templates/
│   ├── wizard.php                     # HTML del wizard frontend
│   └── admin/
│       ├── page-services.php          # Admin: CRUD Servicios
│       ├── page-addons.php            # Admin: CRUD Adicionales
│       ├── page-quotes.php            # Admin: Listado de cotizaciones
│       ├── page-quote-detail.php      # Admin: Detalle de cotización
│       └── page-settings.php         # Admin: Ajustes
└── assets/
    ├── css/
    │   ├── front.css                  # Estilos del wizard (minimalista moderno)
    │   └── admin.css                  # Estilos del panel admin
    └── js/
        ├── front.js                   # Lógica del wizard (Vanilla JS)
        └── admin.js                   # Acciones admin (jQuery)
```

---

© MasterStation.net – Todos los derechos reservados.
