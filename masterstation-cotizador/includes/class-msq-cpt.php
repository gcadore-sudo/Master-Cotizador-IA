<?php
/**
 * Registro de Custom Post Types.
 */
defined( 'ABSPATH' ) || exit;

class MSQ_CPT {

    public static function register_all(): void {
        self::register_service();
        self::register_addon();
        self::register_quote();
    }

    private static function register_service(): void {
        register_post_type( 'msq_service', [
            'labels' => [
                'name'               => 'Servicios',
                'singular_name'      => 'Servicio',
                'add_new'            => 'Agregar Servicio',
                'add_new_item'       => 'Agregar Nuevo Servicio',
                'edit_item'          => 'Editar Servicio',
                'new_item'           => 'Nuevo Servicio',
                'view_item'          => 'Ver Servicio',
                'search_items'       => 'Buscar Servicios',
                'not_found'          => 'No se encontraron servicios',
                'not_found_in_trash' => 'No se encontraron servicios en la papelera',
            ],
            'public'             => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'show_in_rest'       => false,
            'supports'           => [ 'title', 'editor', 'custom-fields' ],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ] );
    }

    private static function register_addon(): void {
        register_post_type( 'msq_addon', [
            'labels' => [
                'name'               => 'Adicionales',
                'singular_name'      => 'Adicional',
                'add_new'            => 'Agregar Adicional',
                'add_new_item'       => 'Agregar Nuevo Adicional',
                'edit_item'          => 'Editar Adicional',
                'new_item'           => 'Nuevo Adicional',
                'view_item'          => 'Ver Adicional',
                'search_items'       => 'Buscar Adicionales',
                'not_found'          => 'No se encontraron adicionales',
                'not_found_in_trash' => 'No se encontraron adicionales en la papelera',
            ],
            'public'             => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'show_in_rest'       => false,
            'supports'           => [ 'title', 'editor', 'custom-fields' ],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ] );
    }

    private static function register_quote(): void {
        register_post_type( 'msq_quote', [
            'labels' => [
                'name'               => 'Cotizaciones',
                'singular_name'      => 'Cotización',
                'add_new'            => 'Nueva Cotización',
                'add_new_item'       => 'Nueva Cotización',
                'edit_item'          => 'Ver Cotización',
                'new_item'           => 'Nueva Cotización',
                'view_item'          => 'Ver Cotización',
                'search_items'       => 'Buscar Cotizaciones',
                'not_found'          => 'No se encontraron cotizaciones',
                'not_found_in_trash' => 'No se encontraron cotizaciones en la papelera',
            ],
            'public'             => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'show_in_rest'       => false,
            'supports'           => [ 'title', 'custom-fields' ],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ] );
    }
}
