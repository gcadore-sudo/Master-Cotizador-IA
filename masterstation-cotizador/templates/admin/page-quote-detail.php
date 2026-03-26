<?php
/**
 * Admin page – Detalle de una Cotización.
 * $post  y  $detail_id  vienen del contexto de page-quotes.php.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Sin permisos.' );
}

$qid      = $post->ID;
$name     = get_post_meta( $qid, 'msq_client_name',       true );
$email    = get_post_meta( $qid, 'msq_client_email',      true );
$wa       = get_post_meta( $qid, 'msq_client_whatsapp',   true );
$total    = (float) get_post_meta( $qid, 'msq_total',     true );
$date     = get_post_meta( $qid, 'msq_quote_date',        true );
$note     = get_post_meta( $qid, 'msq_note',              true );
$ai_rec   = get_post_meta( $qid, 'msq_ai_recommendation', true );
$services = json_decode( get_post_meta( $qid, 'msq_services', true ) ?: '[]', true );
$addons   = json_decode( get_post_meta( $qid, 'msq_addons',   true ) ?: '[]', true );
$e_cli    = get_post_meta( $qid, 'msq_email_client_sent', true );

$qd      = MSQ_Rest::quote_data_from_post( $post );
$wa_link = MSQ_WhatsApp::build_link( $qd );
?>
<div class="wrap msq-admin-wrap">
    <h1>Detalle de Cotización #<?php echo esc_html( $qid ); ?></h1>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=msq-quotes' ) ); ?>">&larr; Volver a cotizaciones</a></p>

    <div class="msq-card">
        <h2>Datos del Cliente</h2>
        <table class="form-table">
            <tr><th>Nombre</th><td><?php echo esc_html( $name ); ?></td></tr>
            <tr><th>Email</th><td><?php echo esc_html( $email ); ?></td></tr>
            <tr><th>WhatsApp</th><td><?php echo esc_html( $wa ); ?></td></tr>
            <tr><th>Fecha</th><td><?php echo esc_html( $date ); ?></td></tr>
        </table>
    </div>

    <div class="msq-card">
        <h2>Servicios</h2>
        <?php if ( empty( $services ) ) : ?>
            <p>(Ninguno)</p>
        <?php else : ?>
        <table class="widefat msq-table">
            <thead><tr><th>Título</th><th>Descripción</th><th style="text-align:right">Precio</th></tr></thead>
            <tbody>
                <?php foreach ( $services as $s ) : ?>
                <tr>
                    <td><?php echo esc_html( $s['title'] ); ?></td>
                    <td><?php echo esc_html( $s['description'] ); ?></td>
                    <td style="text-align:right">$<?php echo number_format( (float) $s['price'], 2 ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="msq-card">
        <h2>Adicionales</h2>
        <?php if ( empty( $addons ) ) : ?>
            <p>(Ninguno)</p>
        <?php else : ?>
        <table class="widefat msq-table">
            <thead><tr><th>Título</th><th>Descripción</th><th style="text-align:right">Precio</th></tr></thead>
            <tbody>
                <?php foreach ( $addons as $a ) : ?>
                <tr>
                    <td><?php echo esc_html( $a['title'] ); ?></td>
                    <td><?php echo esc_html( $a['description'] ); ?></td>
                    <td style="text-align:right">$<?php echo number_format( (float) $a['price'], 2 ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="msq-card">
        <h2>Nota del Cliente</h2>
        <p><?php echo $note ? nl2br( esc_html( $note ) ) : '<em>(Sin nota)</em>'; ?></p>
    </div>

    <div class="msq-card">
        <h2>Recomendación IA</h2>
        <?php if ( $ai_rec ) : ?>
            <div class="msq-ai-box"><?php echo nl2br( esc_html( $ai_rec ) ); ?></div>
        <?php else : ?>
            <p><em>No se generó recomendación de IA.</em></p>
        <?php endif; ?>
    </div>

    <div class="msq-card">
        <h2>Total: <span class="msq-total-highlight">$<?php echo number_format( $total, 2 ); ?></span></h2>
    </div>

    <div class="msq-card msq-actions-card">
        <h2>Acciones</h2>
        <button class="button button-primary msq-resend-btn" data-id="<?php echo esc_attr( $qid ); ?>">
            📧 Reenviar email al cliente
            <?php echo $e_cli === '1' ? '<em style="font-weight:normal;font-size:0.85em">(ya enviado)</em>' : ''; ?>
        </button>
        <a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener" class="button msq-wa-btn">
            💬 Abrir WhatsApp con mensaje prellenado
        </a>
    </div>
</div>
