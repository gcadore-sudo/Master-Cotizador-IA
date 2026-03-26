<?php
/**
 * Endpoints REST para el wizard del cotizador.
 * Namespace: msq/v1
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Rest {

    public static function register_routes(): void {
        // Obtener servicios activos
        register_rest_route( 'msq/v1', '/services', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_services' ],
            'permission_callback' => '__return_true',
        ] );

        // Obtener adicionales activos
        register_rest_route( 'msq/v1', '/addons', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_addons' ],
            'permission_callback' => '__return_true',
        ] );

        // Generar recomendación IA (Paso 4)
        register_rest_route( 'msq/v1', '/ai-recommendation', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'ai_recommendation' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'services' => [ 'type' => 'array',  'required' => false ],
                'addons'   => [ 'type' => 'array',  'required' => false ],
                'note'     => [ 'type' => 'string', 'required' => false ],
            ],
        ] );

        // Guardar cotización + enviar emails (Paso 5)
        register_rest_route( 'msq/v1', '/submit', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'submit_quote' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'client_name'      => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
                'client_email'     => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_email' ],
                'client_whatsapp'  => [ 'type' => 'string', 'required' => true ],
                'services'         => [ 'type' => 'array',  'required' => false ],
                'addons'           => [ 'type' => 'array',  'required' => false ],
                'note'             => [ 'type' => 'string', 'required' => false ],
                'ai_recommendation'=> [ 'type' => 'string', 'required' => false ],
            ],
        ] );

        // Reenviar email al cliente (desde admin)
        register_rest_route( 'msq/v1', '/resend-email/(?P<id>\d+)', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'resend_email' ],
            'permission_callback' => function() { return current_user_can( 'manage_options' ); },
        ] );
    }

    // ─────────────────────────────────────────────
    // Callbacks
    // ─────────────────────────────────────────────

    public static function get_services( WP_REST_Request $req ): WP_REST_Response {
        $posts = get_posts( [
            'post_type'      => 'msq_service',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'msq_order',
            'order'          => 'ASC',
            'meta_query'     => [
                [ 'key' => 'msq_active', 'value' => '1', 'compare' => '=' ],
            ],
        ] );

        $data = array_map( fn( $p ) => [
            'id'          => $p->ID,
            'title'       => $p->post_title,
            'description' => $p->post_content,
            'price'       => (float) get_post_meta( $p->ID, 'msq_price', true ),
        ], $posts );

        return new WP_REST_Response( $data, 200 );
    }

    public static function get_addons( WP_REST_Request $req ): WP_REST_Response {
        $posts = get_posts( [
            'post_type'      => 'msq_addon',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'msq_order',
            'order'          => 'ASC',
            'meta_query'     => [
                [ 'key' => 'msq_active', 'value' => '1', 'compare' => '=' ],
            ],
        ] );

        $data = array_map( fn( $p ) => [
            'id'          => $p->ID,
            'title'       => $p->post_title,
            'description' => $p->post_content,
            'price'       => (float) get_post_meta( $p->ID, 'msq_price', true ),
        ], $posts );

        return new WP_REST_Response( $data, 200 );
    }

    public static function ai_recommendation( WP_REST_Request $req ): WP_REST_Response {
        // Verificar nonce (header X-WP-Nonce)
        if ( ! wp_verify_nonce( $req->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_REST_Response( [ 'error' => 'Nonce inválido.' ], 403 );
        }

        if ( ! get_option( 'msq_ai_enabled', '1' ) ) {
            return new WP_REST_Response( [ 'success' => false, 'text' => '', 'error' => 'IA desactivada.' ], 200 );
        }

        $services_raw = $req->get_param( 'services' ) ?? [];
        $addons_raw   = $req->get_param( 'addons' )   ?? [];
        $note         = sanitize_textarea_field( $req->get_param( 'note' ) ?? '' );

        $services = self::sanitize_items( $services_raw );
        $addons   = self::sanitize_items( $addons_raw );

        $result = MSQ_Gemini::get_recommendation( [
            'services'    => $services,
            'addons'      => $addons,
            'client_note' => $note,
        ] );

        return new WP_REST_Response( $result, 200 );
    }

    public static function submit_quote( WP_REST_Request $req ): WP_REST_Response {
        // Verificar nonce
        if ( ! wp_verify_nonce( $req->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_REST_Response( [ 'error' => 'Nonce inválido.' ], 403 );
        }

        // Validar campos obligatorios
        $name     = sanitize_text_field( $req->get_param( 'client_name' )  ?? '' );
        $email    = sanitize_email( $req->get_param( 'client_email' )       ?? '' );
        $whatsapp = sanitize_text_field( $req->get_param( 'client_whatsapp' ) ?? '' );

        if ( empty( $name ) || ! MSQ_Validators::validate_email( $email ) ) {
            return new WP_REST_Response( [ 'error' => 'Datos de cliente inválidos.' ], 422 );
        }

        $whatsapp_normalized = MSQ_Validators::normalize_whatsapp( $whatsapp );
        if ( ! MSQ_Validators::validate_whatsapp( $whatsapp_normalized ) ) {
            return new WP_REST_Response( [ 'error' => 'Número WhatsApp inválido.' ], 422 );
        }

        $services_raw = $req->get_param( 'services' ) ?? [];
        $addons_raw   = $req->get_param( 'addons' )   ?? [];
        $note         = sanitize_textarea_field( $req->get_param( 'note' ) ?? '' );
        $ai_rec       = sanitize_textarea_field( $req->get_param( 'ai_recommendation' ) ?? '' );

        $services = self::sanitize_items( $services_raw );
        $addons   = self::sanitize_items( $addons_raw );

        // Calcular total
        $total = array_sum( array_column( $services, 'price' ) )
               + array_sum( array_column( $addons,   'price' ) );

        $date = current_time( 'Y-m-d H:i:s' );
        $date_label = date_i18n( 'd/m/Y H:i', current_time( 'timestamp' ) );

        // Guardar cotización como CPT
        $post_id = wp_insert_post( [
            'post_title'  => sprintf( 'Cotización – %s – %s', $name, $date_label ),
            'post_status' => 'publish',
            'post_type'   => 'msq_quote',
            'meta_input'  => [
                'msq_client_name'      => $name,
                'msq_client_email'     => $email,
                'msq_client_whatsapp'  => $whatsapp_normalized,
                'msq_services'         => wp_json_encode( $services ),
                'msq_addons'           => wp_json_encode( $addons ),
                'msq_note'             => $note,
                'msq_ai_recommendation'=> $ai_rec,
                'msq_total'            => $total,
                'msq_quote_date'       => $date,
                'msq_email_admin_sent' => '0',
                'msq_email_client_sent'=> '0',
            ],
        ] );

        if ( is_wp_error( $post_id ) ) {
            return new WP_REST_Response( [ 'error' => 'No se pudo guardar la cotización.' ], 500 );
        }

        $quote_data = [
            'quote_id'          => $post_id,
            'quote_date'        => $date,
            'client_name'       => $name,
            'client_email'      => $email,
            'client_whatsapp'   => $whatsapp_normalized,
            'services'          => $services,
            'addons'            => $addons,
            'client_note'       => $note,
            'ai_recommendation' => $ai_rec,
            'total'             => $total,
        ];

        // Enviar emails
        $email_results = MSQ_Email::send_all( $quote_data );

        update_post_meta( $post_id, 'msq_email_admin_sent',  $email_results['admin']  ? '1' : '0' );
        update_post_meta( $post_id, 'msq_email_client_sent', $email_results['client'] ? '1' : '0' );

        // Generar link WhatsApp
        $wa_link = MSQ_WhatsApp::build_link( $quote_data );

        return new WP_REST_Response( [
            'success'      => true,
            'quote_id'     => $post_id,
            'total'        => $total,
            'wa_link'      => $wa_link,
            'email_admin'  => $email_results['admin'],
            'email_client' => $email_results['client'],
        ], 200 );
    }

    public static function resend_email( WP_REST_Request $req ): WP_REST_Response {
        $post_id = (int) $req->get_param( 'id' );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'msq_quote' ) {
            return new WP_REST_Response( [ 'error' => 'Cotización no encontrada.' ], 404 );
        }

        $quote_data = self::quote_data_from_post( $post );
        $result     = MSQ_Email::send_client( $quote_data );

        if ( $result ) {
            update_post_meta( $post_id, 'msq_email_client_sent', '1' );
        }

        return new WP_REST_Response( [ 'success' => $result ], 200 );
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Sanitiza un array de items (servicios/adicionales) proveniente de la REST request.
     */
    private static function sanitize_items( array $raw ): array {
        $items = [];
        foreach ( $raw as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $items[] = [
                'id'          => absint( $item['id']          ?? 0 ),
                'title'       => sanitize_text_field( $item['title']       ?? '' ),
                'description' => sanitize_textarea_field( $item['description'] ?? '' ),
                'price'       => (float) ( $item['price'] ?? 0 ),
            ];
        }
        return $items;
    }

    /**
     * Reconstruye el array quote_data a partir de un WP_Post de tipo msq_quote.
     */
    public static function quote_data_from_post( WP_Post $post ): array {
        $id = $post->ID;
        return [
            'quote_id'          => $id,
            'quote_date'        => get_post_meta( $id, 'msq_quote_date',       true ),
            'client_name'       => get_post_meta( $id, 'msq_client_name',      true ),
            'client_email'      => get_post_meta( $id, 'msq_client_email',     true ),
            'client_whatsapp'   => get_post_meta( $id, 'msq_client_whatsapp',  true ),
            'services'          => json_decode( get_post_meta( $id, 'msq_services', true ) ?: '[]', true ),
            'addons'            => json_decode( get_post_meta( $id, 'msq_addons',   true ) ?: '[]', true ),
            'client_note'       => get_post_meta( $id, 'msq_note',             true ),
            'ai_recommendation' => get_post_meta( $id, 'msq_ai_recommendation',true ),
            'total'             => (float) get_post_meta( $id, 'msq_total',    true ),
        ];
    }
}
