<?php
/**
 * Cliente Groq AI para generación de recomendaciones (OpenAI compatible).
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Groq {

    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * @param array $quote_data
     * @return array{success: bool, text: string, error?: string}
     */
    public static function get_recommendation( array $quote_data ): array {
        $api_key = get_option( 'msq_groq_api_key', '' );
        $model   = get_option( 'msq_groq_model', 'llama-3.1-8b-instant' );

        if ( empty( $api_key ) ) {
            return [ 'success' => false, 'text' => '', 'error' => 'API key de Groq no configurada.' ];
        }

        $prompt = self::build_prompt( $quote_data );

        $payload = [
            'model' => sanitize_text_field( $model ),
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'Eres un consultor senior de proyectos web de MasterStation.net. Responde en español, profesional y cautivador, en párrafos (sin viñetas).',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 600,
        ];

        $response = wp_remote_post( self::API_URL, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 25,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'text' => '', 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        $text = $data['choices'][0]['message']['content'] ?? '';

        if ( $code !== 200 || empty( $text ) ) {
            $err_msg = $data['error']['message'] ?? "HTTP $code";
            return [ 'success' => false, 'text' => '', 'error' => "Error Groq: $err_msg" ];
        }

        return [ 'success' => true, 'text' => sanitize_textarea_field( $text ) ];
    }

    private static function build_prompt( array $quote_data ): string {
        $services = array_map( fn( $s ) => '- ' . ( $s['title'] ?? '' ), $quote_data['services'] ?? [] );
        $addons   = array_map( fn( $a ) => '- ' . ( $a['title'] ?? '' ), $quote_data['addons']   ?? [] );
        $note     = $quote_data['client_note'] ?? '';

        $services_text = implode( "\n", $services ) ?: '(ninguno)';
        $addons_text   = implode( "\n", $addons )   ?: '(ninguno)';

        return <<<PROMPT
Un cliente ha solicitado una cotización con los siguientes elementos:

Servicios:
{$services_text}

Adicionales:
{$addons_text}

Nota del cliente:
{$note}

Genera una recomendación (máximo 300 palabras) que incluya:
1) Bienvenida cálida y profesional.
2) Validación de sus elecciones.
3) Próximos pasos concretos sugeridos.
4) Cierre motivador orientado a la acción.

Estilo: español, profesional, moderno y cautivador. En párrafos, sin listas.
PROMPT;
    }
}
