<?php
/**
 * Cliente Gemini AI para generación de recomendaciones.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Gemini {

    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /**
     * Genera una recomendación profesional basada en la selección del cliente.
     *
     * @param  array  $quote_data  Datos de la cotización (servicios, adicionales, nota).
     * @return array{success: bool, text: string, error?: string}
     */
    public static function get_recommendation( array $quote_data ): array {
        $api_key = get_option( 'msq_gemini_api_key', '' );
        $model   = get_option( 'msq_gemini_model', 'gemini-1.5-flash' );

        if ( empty( $api_key ) ) {
            return [ 'success' => false, 'text' => '', 'error' => 'API key de Gemini no configurada.' ];
        }

        $prompt = self::build_prompt( $quote_data );

        $url = self::API_BASE . sanitize_text_field( $model ) . ':generateContent?key=' . urlencode( $api_key );

        $body = wp_json_encode( [
            'contents' => [
                [
                    'parts' => [
                        [ 'text' => $prompt ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 600,
            ],
        ] );

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => $body,
            'timeout' => 25,
        ] );

        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'text'    => '',
                'error'   => $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 || empty( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            $err_msg = $data['error']['message'] ?? "HTTP $code";
            return [
                'success' => false,
                'text'    => '',
                'error'   => "Error Gemini: $err_msg",
            ];
        }

        return [
            'success' => true,
            'text'    => sanitize_textarea_field( $data['candidates'][0]['content']['parts'][0]['text'] ),
        ];
    }

    /**
     * Construye el prompt para Gemini con los datos relevantes.
     */
    private static function build_prompt( array $quote_data ): string {
        $services = array_map( fn( $s ) => '- ' . $s['title'], $quote_data['services'] ?? [] );
        $addons   = array_map( fn( $a ) => '- ' . $a['title'], $quote_data['addons']   ?? [] );
        $note     = $quote_data['client_note'] ?? '';

        $services_text = implode( "\n", $services ) ?: '(ninguno)';
        $addons_text   = implode( "\n", $addons )   ?: '(ninguno)';

        return <<<PROMPT
Eres un consultor senior de proyectos web de MasterStation.net, una agencia de diseño web profesional.

Un cliente ha solicitado una cotización con los siguientes elementos:

**Servicios:**
{$services_text}

**Adicionales:**
{$addons_text}

**Nota del cliente:**
{$note}

Por favor, genera una recomendación profesional y cautivadora (máximo 300 palabras) que incluya:
1. Una bienvenida cálida y profesional.
2. Una validación de sus elecciones (por qué son buenas decisiones).
3. Sugerencias de valor adicional o próximos pasos concretos.
4. Un cierre motivador orientado a la acción.

Usa un tono profesional, moderno y cautivador. Escribe en español. Sin listas de viñetas; usa párrafos fluidos.
PROMPT;
    }
}
