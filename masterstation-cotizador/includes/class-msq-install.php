<?php
/**
 * Instalación y activación del plugin.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_Install {

    public static function activate(): void {
        // Crear CPTs
        MSQ_CPT::register_all();
        flush_rewrite_rules();

        // Insertar opciones por defecto si no existen
        $defaults = [
            'msq_admin_email'        => 'gcadore@masterstation.net',
            'msq_whatsapp_number'    => '+584241837004',
            'msq_country_code'       => '+58',
            'msq_gemini_api_key'     => '',
            'msq_gemini_model'       => 'gemini-1.5-flash',
            'msq_ai_enabled'         => '1',
            'msq_signature'          => "Equipo MasterStation net\ninfo@masterstation.net\nWhatsApp +584241837004",
            'msq_email_admin_subject' => 'Nueva cotización web - {client_name} - {total}',
            'msq_email_client_subject' => 'Tu cotización de diseño web - MasterStation',
            'msq_email_admin_body'   => self::default_admin_email(),
            'msq_email_client_body'  => self::default_client_email(),
            'msq_whatsapp_template'  => self::default_whatsapp_template(),
        ];

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }

        // Crear servicios y adicionales de ejemplo si no existen
        if ( ! get_option( 'msq_demo_data_inserted' ) ) {
            self::insert_demo_data();
            update_option( 'msq_demo_data_inserted', '1' );
        }
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    private static function default_admin_email(): string {
        return <<<HTML
<h2>Nueva Cotización Web - MasterStation</h2>
<p><strong>Fecha:</strong> {quote_date}</p>
<p><strong>ID Cotización:</strong> #{quote_id}</p>

<h3>Datos del Cliente</h3>
<p><strong>Nombre:</strong> {client_name}<br>
<strong>Email:</strong> {client_email}<br>
<strong>WhatsApp:</strong> {client_whatsapp}</p>

<h3>Servicios Seleccionados</h3>
{services_table}

<h3>Adicionales Seleccionados</h3>
{addons_table}

<h3>Nota del Cliente</h3>
<p>{client_note}</p>

<h3>Total</h3>
<p style="font-size:1.4em;font-weight:bold;">{total}</p>

<h3>Recomendación IA</h3>
<div>{ai_recommendation}</div>

<hr>
<p>{signature}</p>
HTML;
    }

    private static function default_client_email(): string {
        return <<<HTML
<h2>¡Hola, {client_name}!</h2>
<p>Gracias por confiar en <strong>MasterStation</strong> para tu proyecto web. A continuación encontrarás el resumen de tu cotización personalizada:</p>

<h3>Servicios Seleccionados</h3>
{services_table}

<h3>Adicionales</h3>
{addons_table}

<h3>Nota</h3>
<p>{client_note}</p>

<h3>Total Estimado</h3>
<p style="font-size:1.4em;font-weight:bold;color:#2563eb;">{total}</p>

<h3>Nuestra Recomendación para Ti</h3>
<div style="background:#f0f9ff;padding:16px;border-radius:8px;">{ai_recommendation}</div>

<p style="margin-top:24px;">Estamos listos para dar el siguiente paso contigo. No dudes en respondernos o escribirnos por WhatsApp.</p>

<hr>
<p>{signature}</p>
HTML;
    }

    private static function default_whatsapp_template(): string {
        return "Hola MasterStation! 👋\n\nSoy *{client_name}* y acabo de completar mi cotización (#{quote_id}).\n\n*Servicios:*\n{services_list}\n\n*Adicionales:*\n{addons_list}\n\n*Total estimado: {total}*\n\nMe gustaría avanzar con el proyecto. ¿Podemos hablar?";
    }

    private static function insert_demo_data(): void {
        $services = [
            [ 'title' => 'Sitio Web Corporativo', 'desc' => 'Diseño y desarrollo de sitio web corporativo con hasta 5 páginas.', 'price' => 800 ],
            [ 'title' => 'Tienda en Línea (eCommerce)', 'desc' => 'Tienda WooCommerce completa con catálogo, carrito y pagos.', 'price' => 1500 ],
            [ 'title' => 'Landing Page', 'desc' => 'Página de aterrizaje optimizada para conversión.', 'price' => 350 ],
            [ 'title' => 'Blog Profesional', 'desc' => 'Blog con diseño personalizado y sistema de gestión de contenido.', 'price' => 450 ],
        ];

        $addons = [
            [ 'title' => 'SEO Básico', 'desc' => 'Optimización básica para motores de búsqueda.', 'price' => 150 ],
            [ 'title' => 'Integración de Redes Sociales', 'desc' => 'Vinculación con Instagram, Facebook, LinkedIn, etc.', 'price' => 80 ],
            [ 'title' => 'Formulario de Contacto Avanzado', 'desc' => 'Formulario con validaciones, notificaciones y anti-spam.', 'price' => 60 ],
            [ 'title' => 'Chat en Vivo (WhatsApp)', 'desc' => 'Botón flotante de WhatsApp con mensaje personalizado.', 'price' => 50 ],
            [ 'title' => 'Mantenimiento Mensual', 'desc' => 'Actualizaciones, respaldos y soporte técnico mensual.', 'price' => 120 ],
        ];

        foreach ( $services as $i => $s ) {
            wp_insert_post( [
                'post_title'   => $s['title'],
                'post_content' => $s['desc'],
                'post_status'  => 'publish',
                'post_type'    => 'msq_service',
                'meta_input'   => [
                    'msq_price'  => $s['price'],
                    'msq_active' => '1',
                    'msq_order'  => $i + 1,
                ],
            ] );
        }

        foreach ( $addons as $i => $a ) {
            wp_insert_post( [
                'post_title'   => $a['title'],
                'post_content' => $a['desc'],
                'post_status'  => 'publish',
                'post_type'    => 'msq_addon',
                'meta_input'   => [
                    'msq_price'  => $a['price'],
                    'msq_active' => '1',
                    'msq_order'  => $i + 1,
                ],
            ] );
        }
    }
}
