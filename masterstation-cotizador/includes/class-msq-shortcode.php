<?php
/**
 * Shortcode [masterstation_cotizador] – renderiza el wizard.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Shortcode {

    public static function register(): void {
        add_shortcode( 'masterstation_cotizador', [ __CLASS__, 'render' ] );
    }

    public static function render( array $atts = [] ): string {
        ob_start();
        include MSQ_DIR . 'templates/wizard.php';
        return ob_get_clean();
    }
}
