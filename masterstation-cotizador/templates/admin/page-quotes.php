<?php
/**
 * Admin page – Listado y detalle de Cotizaciones.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Sin permisos.' );
}

// ¿Ver detalle?
$detail_id = absint( $_GET['detail'] ?? 0 );

if ( $detail_id > 0 ) {
    $post = get_post( $detail_id );
    if ( $post && $post->post_type === 'msq_quote' ) {
        include __DIR__ . '/page-quote-detail.php';
        return;
    }
}

// Filtros
$filter_name  = sanitize_text_field( $_GET['filter_name']  ?? '' );
$filter_email = sanitize_email( $_GET['filter_email']      ?? '' );
$filter_date  = sanitize_text_field( $_GET['filter_date']  ?? '' );

$meta_query = [];
if ( $filter_name ) {
    $meta_query[] = [ 'key' => 'msq_client_name', 'value' => $filter_name, 'compare' => 'LIKE' ];
}
if ( $filter_email ) {
    $meta_query[] = [ 'key' => 'msq_client_email', 'value' => $filter_email, 'compare' => 'LIKE' ];
}

$args = [
    'post_type'      => 'msq_quote',
    'posts_per_page' => 20,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ( $filter_date && preg_match( '/^\d{4}-\d{2}$/', $filter_date ) ) {
    $args['date_query'] = [
        [ 'year' => substr( $filter_date, 0, 4 ), 'month' => substr( $filter_date, 5, 2 ) ],
    ];
}

if ( ! empty( $meta_query ) ) {
    $args['meta_query'] = $meta_query;
}

$quotes = get_posts( $args );
?>
<div class="wrap msq-admin-wrap">
    <h1>Cotizaciones</h1>

    <?php echo MSQ_Admin::get_admin_notice(); ?>

    <!-- Filtros -->
    <div class="msq-card">
        <form method="get">
            <input type="hidden" name="page" value="msq-quotes">
            <div class="msq-filters">
                <input type="text" name="filter_name" placeholder="Nombre del cliente"
                       value="<?php echo esc_attr( $filter_name ); ?>" class="regular-text">
                <input type="email" name="filter_email" placeholder="Email del cliente"
                       value="<?php echo esc_attr( $filter_email ); ?>" class="regular-text">
                <input type="month" name="filter_date" value="<?php echo esc_attr( $filter_date ); ?>">
                <button type="submit" class="button">Filtrar</button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=msq-quotes' ) ); ?>" class="button">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="msq-card">
        <?php if ( empty( $quotes ) ) : ?>
            <p>No se encontraron cotizaciones.</p>
        <?php else : ?>
        <table class="widefat msq-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Emails</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $quotes as $q ) :
                    $qid    = $q->ID;
                    $name   = get_post_meta( $qid, 'msq_client_name',      true );
                    $email  = get_post_meta( $qid, 'msq_client_email',     true );
                    $wa     = get_post_meta( $qid, 'msq_client_whatsapp',  true );
                    $total  = (float) get_post_meta( $qid, 'msq_total',    true );
                    $date   = get_post_meta( $qid, 'msq_quote_date',       true );
                    $e_adm  = get_post_meta( $qid, 'msq_email_admin_sent', true );
                    $e_cli  = get_post_meta( $qid, 'msq_email_client_sent',true );

                    // Datos para WhatsApp link
                    $qd = MSQ_Rest::quote_data_from_post( $q );
                    $wa_link = MSQ_WhatsApp::build_link( $qd );
                ?>
                <tr>
                    <td><?php echo esc_html( $qid ); ?></td>
                    <td><?php echo esc_html( $name ); ?></td>
                    <td><?php echo esc_html( $email ); ?></td>
                    <td><?php echo esc_html( $wa ); ?></td>
                    <td><strong>$<?php echo number_format( $total, 2 ); ?></strong></td>
                    <td><?php echo esc_html( $date ); ?></td>
                    <td>
                        <span class="msq-badge <?php echo $e_adm === '1' ? 'msq-badge-on' : 'msq-badge-off'; ?>">Admin</span>
                        <span class="msq-badge <?php echo $e_cli === '1' ? 'msq-badge-on' : 'msq-badge-off'; ?>">Cliente</span>
                    </td>
                    <td class="msq-actions">
                        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'msq-quotes', 'detail' => $qid ], admin_url( 'admin.php' ) ) ); ?>"
                           class="button button-small">Ver</a>
                        <button class="button button-small msq-resend-btn" data-id="<?php echo esc_attr( $qid ); ?>">
                            📧 Reenviar
                        </button>
                        <a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener" class="button button-small msq-wa-btn">
                            💬 WhatsApp
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
