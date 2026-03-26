<?php
defined( 'ABSPATH' ) || exit;

class MSQ_AI {

    /**
     * @param array $quote_data
     * @return array{success: bool, text: string, error?: string}
     */
    public static function get_recommendation( array $quote_data ): array {
        $provider = get_option( 'msq_ai_provider', 'gemini' );

        if ( $provider === 'groq' ) {
            return MSQ_Groq::get_recommendation( $quote_data );
        }

        // Default: Google Gemini
        return MSQ_Gemini::get_recommendation( $quote_data );
    }
}
