<?php
/**
 * Manejo de envío de correos electrónicos.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Email {

    /**
     * Envía emails al administrador y al cliente.
     *
     * @param  array $quote_data Datos completos de la cotización.
     * @return array{admin: bool, client: bool}
     */
    public static function send_all( array $quote_data ): array {
        return [
            'admin'  => self::send_admin( $quote_data ),
            'client' => self::send_client( $quote_data ),
        ];
    }

    /**
     * Envía email al administrador.
     */
    public static function send_admin( array $quote_data ): bool {
        $to      = get_option( 'msq_admin_email', 'gcadore@masterstation.net' );
        $subject = get_option( 'msq_email_admin_subject', 'Nueva cotización web - {client_name} - {total}' );
        $body    = get_option( 'msq_email_admin_body', '' );

        $subject = self::replace_vars( $subject, $quote_data );
        $body    = self::replace_vars( $body, $quote_data );

        return self::send( $to, $subject, $body );
    }

    /**
     * Envía email al cliente.
     */
    public static function send_client( array $quote_data ): bool {
        $to      = $quote_data['client_email'] ?? '';
        if ( ! is_email( $to ) ) {
            return false;
        }
        $subject = get_option( 'msq_email_client_subject', 'Tu cotización de diseño web - MasterStation' );
        $body    = get_option( 'msq_email_client_body', '' );

        $subject = self::replace_vars( $subject, $quote_data );
        $body    = self::replace_vars( $body, $quote_data );

        return self::send( $to, $subject, $body );
    }

    /**
     * Función interna de envío.
     */
    private static function send( string $to, string $subject, string $body ): bool {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: MasterStation <info@masterstation.net>',
        ];
        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Reemplaza variables de plantilla con datos reales.
     */
    public static function replace_vars( string $template, array $data ): string {
        $services_rows = '';
        foreach ( $data['services'] ?? [] as $s ) {
            $services_rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td style="text-align:right;">$%s</td></tr>',
                esc_html( $s['title'] ),
                esc_html( $s['description'] ?? '' ),
                number_format( (float) $s['price'], 2 )
            );
        }
        $services_table = '<table border="0" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">'
            . '<tr><th style="text-align:left;">Servicio</th><th style="text-align:left;">Descripción</th><th style="text-align:right;">Costo</th></tr>'
            . $services_rows . '</table>';

        $addons_rows = '';
        foreach ( $data['addons'] ?? [] as $a ) {
            $addons_rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td style="text-align:right;">$%s</td></tr>',
                esc_html( $a['title'] ),
                esc_html( $a['description'] ?? '' ),
                number_format( (float) $a['price'], 2 )
            );
        }
        $addons_table = empty( $data['addons'] )
            ? '<p>(Ninguno seleccionado)</p>'
            : '<table border="0" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">'
                . '<tr><th style="text-align:left;">Adicional</th><th style="text-align:left;">Descripción</th><th style="text-align:right;">Costo</th></tr>'
                . $addons_rows . '</table>';

        $signature = nl2br( esc_html( get_option( 'msq_signature', "Equipo MasterStation net\ninfo@masterstation.net\nWhatsApp +584241837004" ) ) );

        $ai = $data['ai_recommendation'] ?? '';
        $ai_html = $ai
            ? nl2br( esc_html( $ai ) )
            : '<em style="color:#888;">No disponible.</em>';

        $replacements = [
            '{client_name}'       => esc_html( $data['client_name']    ?? '' ),
            '{client_email}'      => esc_html( $data['client_email']   ?? '' ),
            '{client_whatsapp}'   => esc_html( $data['client_whatsapp'] ?? '' ),
            '{services_table}'    => $services_table,
            '{addons_table}'      => $addons_table,
            '{client_note}'       => nl2br( esc_html( $data['client_note'] ?? '' ) ),
            '{total}'             => '$' . number_format( (float) ( $data['total'] ?? 0 ), 2 ),
            '{ai_recommendation}' => $ai_html,
            '{quote_id}'          => esc_html( (string) ( $data['quote_id'] ?? '' ) ),
            '{quote_date}'        => esc_html( $data['quote_date'] ?? '' ),
            '{signature}'         => $signature,
        ];

        return str_replace(
            array_keys( $replacements ),
            array_values( $replacements ),
            $template
        );
    }
}
