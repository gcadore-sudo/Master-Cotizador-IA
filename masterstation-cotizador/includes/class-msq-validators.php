<?php
/**
 * Validaciones de email y WhatsApp.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Validators {

    /**
     * Valida email con la función nativa de WP.
     */
    public static function validate_email( string $email ): bool {
        return (bool) is_email( $email );
    }

    /**
     * Normaliza un número WhatsApp al formato +XXXXXXXXXXX.
     * Acepta: espacios, guiones, paréntesis, puntos.
     * Si no comienza con "+", prepone el código de país configurado.
     *
     * @param  string $raw  Número ingresado por el usuario.
     * @return string       Número normalizado (solo dígitos y + inicial).
     */
    public static function normalize_whatsapp( string $raw ): string {
        // Quitar todo excepto dígitos y el símbolo +
        $clean = preg_replace( '/[^\d+]/', '', $raw );

        if ( '' === $clean ) {
            return '';
        }

        // Si ya comienza con '+', dejarlo como está
        if ( str_starts_with( $clean, '+' ) ) {
            return $clean;
        }

        // Si comienza con '00', reemplazar por '+'
        if ( str_starts_with( $clean, '00' ) ) {
            return '+' . substr( $clean, 2 );
        }

        // Preponer código de país por defecto configurado en admin
        $country_code = get_option( 'msq_country_code', '+58' );
        $country_code = ltrim( $country_code, '+' );

        return '+' . $country_code . $clean;
    }

    /**
     * Valida que el número normalizado tenga entre 7 y 15 dígitos (E.164).
     */
    public static function validate_whatsapp( string $normalized ): bool {
        if ( '' === $normalized ) {
            return false;
        }
        $digits = preg_replace( '/[^\d]/', '', $normalized );
        return strlen( $digits ) >= 7 && strlen( $digits ) <= 15;
    }
}
