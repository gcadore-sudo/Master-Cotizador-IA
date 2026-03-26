<?php
/**
 * Clase principal del plugin – arranca todos los módulos.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Plugin {

    private static ?MSQ_Plugin $instance = null;

    public static function get_instance(): MSQ_Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ 'MSQ_CPT', 'register_all' ] );
        add_action( 'init', [ 'MSQ_Shortcode', 'register' ] );
        add_action( 'rest_api_init', [ 'MSQ_Rest', 'register_routes' ] );

        if ( is_admin() ) {
            new MSQ_Admin();
        }

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    public function enqueue_frontend_assets(): void {
        // Solo encolar si hay shortcode en la página
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'masterstation_cotizador' ) ) {
            return;
        }

        wp_enqueue_style(
            'msq-front',
            MSQ_URL . 'assets/css/front.css',
            [],
            MSQ_VERSION
        );

        wp_enqueue_script(
            'msq-front',
            MSQ_URL . 'assets/js/front.js',
            [],
            MSQ_VERSION,
            true
        );

        wp_localize_script( 'msq-front', 'msqData', [
            'restUrl'     => esc_url_raw( rest_url( 'msq/v1/' ) ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'currency'    => '$',
            'aiEnabled'   => (bool) get_option( 'msq_ai_enabled', '1' ),
            'countryCode' => get_option( 'msq_country_code', '+58' ),
        ] );
    }
}
