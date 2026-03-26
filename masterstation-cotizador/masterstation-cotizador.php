<?php
/**
 * Plugin Name: MasterStation Cotizador IA
 * Plugin URI:  https://masterstation.net
 * Description: Cotizador de diseño web con formulario multi-step, panel de administración, envío de correos y recomendaciones generadas por IA (Google Gemini / Groq).
 * Version:     1.0.1
 * Author:      MasterStation
 * Author URI:  https://masterstation.net
 * License:     GPL-2.0+
 * Text Domain: masterstation-cotizador
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

// Constantes del plugin
define( 'MSQ_VERSION',   '1.0.1' );
define( 'MSQ_FILE',      __FILE__ );
define( 'MSQ_DIR',       plugin_dir_path( __FILE__ ) );
define( 'MSQ_URL',       plugin_dir_url( __FILE__ ) );
define( 'MSQ_SLUG',      'masterstation-cotizador' );

// Autoload de clases
require_once MSQ_DIR . 'includes/class-msq-install.php';
require_once MSQ_DIR . 'includes/class-msq-cpt.php';
require_once MSQ_DIR . 'includes/class-msq-validators.php';
require_once MSQ_DIR . 'includes/class-msq-whatsapp.php';

require_once MSQ_DIR . 'includes/class-msq-gemini.php';
require_once MSQ_DIR . 'includes/class-msq-groq.php';
require_once MSQ_DIR . 'includes/class-msq-ai.php';

require_once MSQ_DIR . 'includes/class-msq-email.php';
require_once MSQ_DIR . 'includes/class-msq-shortcode.php';
require_once MSQ_DIR . 'includes/class-msq-rest.php';
require_once MSQ_DIR . 'includes/class-msq-admin.php';
require_once MSQ_DIR . 'includes/class-msq-plugin.php';

register_activation_hook( __FILE__,   [ 'MSQ_Install', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'MSQ_Install', 'deactivate' ] );

// Arrancar el plugin
MSQ_Plugin::get_instance();
