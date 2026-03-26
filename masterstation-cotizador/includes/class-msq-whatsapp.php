<?php
/**
 * Generador de links y mensajes de WhatsApp.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_WhatsApp {

    /**
     * Genera el link wa.me con mensaje prellenado.
     *
     * @param  array  $quote_data  Datos de la cotización.
     * @return string              URL de WhatsApp.
     */
    public static function build_link( array $quote_data ): string {
        $number   = get_option( 'msq_whatsapp_number', '+584241837004' );
        $number   = preg_replace( '/[^\d]/', '', $number );
        $message  = self::build_message( $quote_data );
        return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
    }

    /**
     * Construye el mensaje usando la plantilla editable en Ajustes.
     */
    public static function build_message( array $quote_data ): string {
        $template = get_option( 'msq_whatsapp_template', '' );

        $services_lines = [];
        foreach ( $quote_data['services'] as $s ) {
            $services_lines[] = '• ' . $s['title'] . ' ($' . number_format( (float) $s['price'], 2 ) . ')';
        }
        $addons_lines = [];
        foreach ( $quote_data['addons'] as $a ) {
            $addons_lines[] = '• ' . $a['title'] . ' ($' . number_format( (float) $a['price'], 2 ) . ')';
        }

        $replacements = [
            '{client_name}'    => $quote_data['client_name']    ?? '',
            '{client_email}'   => $quote_data['client_email']   ?? '',
            '{client_whatsapp}'=> $quote_data['client_whatsapp'] ?? '',
            '{services_list}'  => implode( "\n", $services_lines ) ?: '(ninguno)',
            '{addons_list}'    => implode( "\n", $addons_lines ) ?: '(ninguno)',
            '{client_note}'    => $quote_data['client_note']    ?? '',
            '{total}'          => '$' . number_format( (float) ( $quote_data['total'] ?? 0 ), 2 ),
            '{quote_id}'       => $quote_data['quote_id']       ?? '',
            '{quote_date}'     => $quote_data['quote_date']     ?? '',
        ];

        return str_replace(
            array_keys( $replacements ),
            array_values( $replacements ),
            $template
        );
    }
}
