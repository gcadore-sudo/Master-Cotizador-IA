<?php
/**
 * Panel de administración: menús, páginas CRUD de servicios/adicionales,
 * listado de cotizaciones y página de ajustes.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_msq_save_service', [ $this, 'handle_save_service' ] );
        add_action( 'admin_post_msq_save_addon',   [ $this, 'handle_save_addon' ] );
        add_action( 'admin_post_msq_delete_service', [ $this, 'handle_delete_service' ] );
        add_action( 'admin_post_msq_delete_addon',   [ $this, 'handle_delete_addon' ] );
        add_action( 'admin_post_msq_save_settings',  [ $this, 'handle_save_settings' ] );
    }

    public function register_menus(): void {
        add_menu_page(
            'Cotizador MasterStation',
            'Cotizador MasterStation',
            'manage_options',
            'msq-dashboard',
            [ $this, 'page_dashboard' ],
            'dashicons-calculator',
            30
        );

        add_submenu_page( 'msq-dashboard', 'Servicios',    'Servicios',    'manage_options', 'msq-services',   [ $this, 'page_services' ] );
        add_submenu_page( 'msq-dashboard', 'Adicionales',  'Adicionales',  'manage_options', 'msq-addons',     [ $this, 'page_addons' ] );
        add_submenu_page( 'msq-dashboard', 'Cotizaciones', 'Cotizaciones', 'manage_options', 'msq-quotes',     [ $this, 'page_quotes' ] );
        add_submenu_page( 'msq-dashboard', 'Ajustes',      'Ajustes',      'manage_options', 'msq-settings',   [ $this, 'page_settings' ] );
    }

    public function enqueue_assets( string $hook ): void {
        if ( ! str_contains( $hook, 'msq-' ) ) {
            return;
        }
        wp_enqueue_style( 'msq-admin', MSQ_URL . 'assets/css/admin.css', [], MSQ_VERSION );
        wp_enqueue_script( 'msq-admin', MSQ_URL . 'assets/js/admin.js', [ 'jquery' ], MSQ_VERSION, true );
        wp_localize_script( 'msq-admin', 'msqAdmin', [
            'restUrl' => esc_url_raw( rest_url( 'msq/v1/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    // ──────────────────────────────────────────────────────────
    // Páginas
    // ──────────────────────────────────────────────────────────

    public function page_dashboard(): void {
        echo '<div class="wrap msq-admin-wrap">';
        echo '<h1>Cotizador MasterStation</h1>';
        echo '<p>Bienvenido al panel del cotizador. Usa el menú lateral para gestionar servicios, adicionales, cotizaciones y ajustes.</p>';
        echo '</div>';
    }

    public function page_services(): void {
        include MSQ_DIR . 'templates/admin/page-services.php';
    }

    public function page_addons(): void {
        include MSQ_DIR . 'templates/admin/page-addons.php';
    }

    public function page_quotes(): void {
        include MSQ_DIR . 'templates/admin/page-quotes.php';
    }

    public function page_settings(): void {
        include MSQ_DIR . 'templates/admin/page-settings.php';
    }

    // ──────────────────────────────────────────────────────────
    // Handlers de formularios
    // ──────────────────────────────────────────────────────────

    public function handle_save_service(): void {
        check_admin_referer( 'msq_save_service' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Sin permisos.' );
        }

        $post_id  = absint( $_POST['post_id'] ?? 0 );
        $title    = sanitize_text_field( $_POST['msq_title']       ?? '' );
        $desc     = sanitize_textarea_field( $_POST['msq_description'] ?? '' );
        $price    = (float) ( $_POST['msq_price'] ?? 0 );
        $active   = isset( $_POST['msq_active'] ) ? '1' : '0';
        $order    = absint( $_POST['msq_order'] ?? 0 );

        if ( empty( $title ) ) {
            wp_safe_redirect( add_query_arg( [ 'page' => 'msq-services', 'msq_msg' => 'empty_title' ], admin_url( 'admin.php' ) ) );
            exit;
        }

        $args = [
            'post_title'   => $title,
            'post_content' => $desc,
            'post_status'  => 'publish',
            'post_type'    => 'msq_service',
        ];

        if ( $post_id > 0 ) {
            $args['ID'] = $post_id;
            wp_update_post( $args );
        } else {
            $post_id = wp_insert_post( $args );
        }

        update_post_meta( $post_id, 'msq_price',  $price );
        update_post_meta( $post_id, 'msq_active', $active );
        update_post_meta( $post_id, 'msq_order',  $order );

        wp_safe_redirect( add_query_arg( [ 'page' => 'msq-services', 'msq_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_save_addon(): void {
        check_admin_referer( 'msq_save_addon' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Sin permisos.' );
        }

        $post_id  = absint( $_POST['post_id'] ?? 0 );
        $title    = sanitize_text_field( $_POST['msq_title']       ?? '' );
        $desc     = sanitize_textarea_field( $_POST['msq_description'] ?? '' );
        $price    = (float) ( $_POST['msq_price'] ?? 0 );
        $active   = isset( $_POST['msq_active'] ) ? '1' : '0';
        $order    = absint( $_POST['msq_order'] ?? 0 );

        if ( empty( $title ) ) {
            wp_safe_redirect( add_query_arg( [ 'page' => 'msq-addons', 'msq_msg' => 'empty_title' ], admin_url( 'admin.php' ) ) );
            exit;
        }

        $args = [
            'post_title'   => $title,
            'post_content' => $desc,
            'post_status'  => 'publish',
            'post_type'    => 'msq_addon',
        ];

        if ( $post_id > 0 ) {
            $args['ID'] = $post_id;
            wp_update_post( $args );
        } else {
            $post_id = wp_insert_post( $args );
        }

        update_post_meta( $post_id, 'msq_price',  $price );
        update_post_meta( $post_id, 'msq_active', $active );
        update_post_meta( $post_id, 'msq_order',  $order );

        wp_safe_redirect( add_query_arg( [ 'page' => 'msq-addons', 'msq_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_service(): void {
        check_admin_referer( 'msq_delete_service' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Sin permisos.' );
        }
        $post_id = absint( $_POST['post_id'] ?? 0 );
        wp_delete_post( $post_id, true );
        wp_safe_redirect( add_query_arg( [ 'page' => 'msq-services', 'msq_msg' => 'deleted' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_addon(): void {
        check_admin_referer( 'msq_delete_addon' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Sin permisos.' );
        }
        $post_id = absint( $_POST['post_id'] ?? 0 );
        wp_delete_post( $post_id, true );
        wp_safe_redirect( add_query_arg( [ 'page' => 'msq-addons', 'msq_msg' => 'deleted' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_save_settings(): void {
        check_admin_referer( 'msq_save_settings' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Sin permisos.' );
        }

        $fields = [
            'msq_admin_email'          => 'sanitize_text_field',
            'msq_whatsapp_number'      => 'sanitize_text_field',
            'msq_country_code'         => 'sanitize_text_field',
            'msq_gemini_model'         => 'sanitize_text_field',
            'msq_gemini_api_key'       => 'sanitize_text_field',
            'msq_groq_api_key'         => 'sanitize_text_field',
            'msq_groq_model'           => 'sanitize_text_field',
            'msq_signature'            => 'sanitize_textarea_field',
            'msq_email_admin_subject'  => 'sanitize_text_field',
            'msq_email_client_subject' => 'sanitize_text_field',
            'msq_email_admin_body'     => 'wp_kses_post',
            'msq_email_client_body'    => 'wp_kses_post',
            'msq_whatsapp_template'    => 'sanitize_textarea_field',
        ];

        foreach ( $fields as $key => $sanitizer ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, $sanitizer( $_POST[ $key ] ) );
            }
        }

        // Toggle IA
        update_option( 'msq_ai_enabled', isset( $_POST['msq_ai_enabled'] ) ? '1' : '0' );

        // Proveedor IA (selector)
        $provider = sanitize_text_field( $_POST['msq_ai_provider'] ?? 'gemini' );
        if ( ! in_array( $provider, [ 'gemini', 'groq' ], true ) ) {
            $provider = 'gemini';
        }
        update_option( 'msq_ai_provider', $provider );

        wp_safe_redirect( add_query_arg( [ 'page' => 'msq-settings', 'msq_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    // ──────────────────────────────────────────────────────────
    // Helper: obtener mensaje de notificación
    // ──────────────────────────────────────────────────────────
    public static function get_admin_notice(): string {
        $msg = sanitize_text_field( $_GET['msq_msg'] ?? '' );
        $notices = [
            'saved'       => [ 'success', '✅ Guardado correctamente.' ],
            'deleted'     => [ 'success', '🗑️ Eliminado correctamente.' ],
            'empty_title' => [ 'error',   '⚠️ El título no puede estar vacío.' ],
        ];
        if ( ! isset( $notices[ $msg ] ) ) {
            return '';
        }
        [ $type, $text ] = $notices[ $msg ];
        return sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $type ), esc_html( $text ) );
    }
}
