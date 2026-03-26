<?php
/**
 * Admin page – Gestión de Adicionales (CRUD).
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Sin permisos.' );
}

$editing  = false;
$item     = null;
$edit_id  = absint( $_GET['edit'] ?? 0 );

if ( $edit_id > 0 ) {
    $item = get_post( $edit_id );
    if ( $item && $item->post_type === 'msq_addon' ) {
        $editing = true;
    }
}

$addons = get_posts( [
    'post_type'      => 'msq_addon',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'meta_value_num',
    'meta_key'       => 'msq_order',
    'order'          => 'ASC',
] );
?>
<div class="wrap msq-admin-wrap">
    <h1><?php echo $editing ? 'Editar Adicional' : 'Adicionales'; ?></h1>

    <?php echo MSQ_Admin::get_admin_notice(); ?>

    <!-- Formulario agregar/editar -->
    <div class="msq-card">
        <h2><?php echo $editing ? 'Editar Adicional' : 'Agregar Adicional'; ?></h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action"  value="msq_save_addon">
            <input type="hidden" name="post_id" value="<?php echo $editing ? $edit_id : 0; ?>">
            <?php wp_nonce_field( 'msq_save_addon' ); ?>

            <table class="form-table msq-form-table">
                <tr>
                    <th><label for="msq_title">Título *</label></th>
                    <td><input type="text" id="msq_title" name="msq_title" class="regular-text"
                               value="<?php echo $editing ? esc_attr( $item->post_title ) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="msq_description">Descripción</label></th>
                    <td><textarea id="msq_description" name="msq_description" rows="3" class="large-text"><?php
                        echo $editing ? esc_textarea( $item->post_content ) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="msq_price">Costo (USD $)</label></th>
                    <td><input type="number" id="msq_price" name="msq_price" step="0.01" min="0" class="small-text"
                               value="<?php echo $editing ? esc_attr( get_post_meta( $edit_id, 'msq_price', true ) ) : '0'; ?>"></td>
                </tr>
                <tr>
                    <th><label for="msq_order">Orden</label></th>
                    <td><input type="number" id="msq_order" name="msq_order" min="0" class="small-text"
                               value="<?php echo $editing ? esc_attr( get_post_meta( $edit_id, 'msq_order', true ) ) : '0'; ?>"></td>
                </tr>
                <tr>
                    <th>Activo</th>
                    <td><label>
                        <input type="checkbox" name="msq_active" value="1"
                            <?php checked( $editing ? get_post_meta( $edit_id, 'msq_active', true ) : '1', '1' ); ?>>
                        Mostrar en el cotizador
                    </label></td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php echo $editing ? 'Actualizar Adicional' : 'Agregar Adicional'; ?>
                </button>
                <?php if ( $editing ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=msq-addons' ) ); ?>" class="button">Cancelar</a>
                <?php endif; ?>
            </p>
        </form>
    </div>

    <!-- Listado -->
    <div class="msq-card">
        <h2>Adicionales Registrados</h2>
        <?php if ( empty( $addons ) ) : ?>
            <p>No hay adicionales registrados aún.</p>
        <?php else : ?>
        <table class="widefat msq-table">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Precio (USD)</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $addons as $a ) :
                    $a_active = get_post_meta( $a->ID, 'msq_active', true );
                    $a_price  = get_post_meta( $a->ID, 'msq_price',  true );
                    $a_order  = get_post_meta( $a->ID, 'msq_order',  true );
                ?>
                <tr>
                    <td><?php echo esc_html( $a_order ); ?></td>
                    <td><strong><?php echo esc_html( $a->post_title ); ?></strong></td>
                    <td><?php echo esc_html( wp_trim_words( $a->post_content, 12 ) ); ?></td>
                    <td>$<?php echo number_format( (float) $a_price, 2 ); ?></td>
                    <td><?php echo $a_active === '1' ? '<span class="msq-badge msq-badge-on">Sí</span>' : '<span class="msq-badge msq-badge-off">No</span>'; ?></td>
                    <td>
                        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'msq-addons', 'edit' => $a->ID ], admin_url( 'admin.php' ) ) ); ?>"
                           class="button button-small">Editar</a>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                            <input type="hidden" name="action"  value="msq_delete_addon">
                            <input type="hidden" name="post_id" value="<?php echo $a->ID; ?>">
                            <?php wp_nonce_field( 'msq_delete_addon' ); ?>
                            <button type="submit" class="button button-small button-link-delete"
                                    onclick="return confirm('¿Eliminar este adicional?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
