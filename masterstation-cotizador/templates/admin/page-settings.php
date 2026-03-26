<?php
/**
 * Admin page – Ajustes del plugin.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Sin permisos.' );
}
?>
<div class="wrap msq-admin-wrap">
    <h1>Ajustes – Cotizador MasterStation</h1>

    <?php echo MSQ_Admin::get_admin_notice(); ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="msq_save_settings">
        <?php wp_nonce_field( 'msq_save_settings' ); ?>

        <!-- ── General ── -->
        <div class="msq-card">
            <h2>General</h2>
            <table class="form-table msq-form-table">
                <tr>
                    <th><label for="msq_admin_email">Email(s) administrador</label></th>
                    <td>
                        <input type="text" id="msq_admin_email" name="msq_admin_email" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_admin_email', 'gcadore@masterstation.net' ) ); ?>">
                        <p class="description">Separar múltiples correos con coma.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="msq_whatsapp_number">WhatsApp destino (MasterStation)</label></th>
                    <td>
                        <input type="text" id="msq_whatsapp_number" name="msq_whatsapp_number" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_whatsapp_number', '+584241837004' ) ); ?>">
                        <p class="description">Número en formato internacional: +584241837004</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="msq_country_code">Código de país por defecto</label></th>
                    <td>
                        <input type="text" id="msq_country_code" name="msq_country_code" class="small-text"
                               value="<?php echo esc_attr( get_option( 'msq_country_code', '+58' ) ); ?>">
                        <p class="description">Se usa cuando el cliente no incluye "+" en su número.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="msq_signature">Firma de emails</label></th>
                    <td>
                        <textarea id="msq_signature" name="msq_signature" rows="4" class="large-text"><?php
                            echo esc_textarea( get_option( 'msq_signature', "Equipo MasterStation net\ninfo@masterstation.net\nWhatsApp +584241837004" ) );
                        ?></textarea>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ── IA (Google Gemini / Groq) ── -->
        <div class="msq-card">
            <h2>Inteligencia Artificial (Google Gemini / Groq)</h2>
            <table class="form-table msq-form-table">
                <tr>
                    <th>Activar IA</th>
                    <td>
                        <label>
                            <input type="checkbox" name="msq_ai_enabled" value="1"
                                <?php checked( get_option( 'msq_ai_enabled', '1' ), '1' ); ?>>
                            Generar recomendaciones automáticas con IA
                        </label>
                    </td>
                </tr>

                <tr>
                    <th><label for="msq_ai_provider">Proveedor IA</label></th>
                    <td>
                        <select id="msq_ai_provider" name="msq_ai_provider">
                            <option value="gemini" <?php selected( get_option( 'msq_ai_provider', 'gemini' ), 'gemini' ); ?>>
                                Google Gemini
                            </option>
                            <option value="groq" <?php selected( get_option( 'msq_ai_provider', 'gemini' ), 'groq' ); ?>>
                                Groq (OpenAI compatible)
                            </option>
                        </select>
                        <p class="description">Selecciona el proveedor usado para generar la recomendación automática.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="msq_gemini_api_key">Gemini API Key</label></th>
                    <td>
                        <input type="password" id="msq_gemini_api_key" name="msq_gemini_api_key" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_gemini_api_key', '' ) ); ?>"
                               autocomplete="new-password">
                        <p class="description">Obtén tu API key en <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a>.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="msq_gemini_model">Modelo Gemini</label></th>
                    <td>
                        <input type="text" id="msq_gemini_model" name="msq_gemini_model" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_gemini_model', 'gemini-1.5-flash' ) ); ?>">
                        <p class="description">Recomendado: <code>gemini-1.5-flash</code></p>
                    </td>
                </tr>

                <tr>
                    <th><label for="msq_groq_api_key">Groq API Key</label></th>
                    <td>
                        <input type="password" id="msq_groq_api_key" name="msq_groq_api_key" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_groq_api_key', '' ) ); ?>"
                               autocomplete="new-password">
                        <p class="description">Solo se usa si el proveedor IA es Groq.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="msq_groq_model">Modelo Groq</label></th>
                    <td>
                        <input type="text" id="msq_groq_model" name="msq_groq_model" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'msq_groq_model', 'llama-3.1-8b-instant' ) ); ?>">
                        <p class="description">Recomendado: <code>llama-3.1-8b-instant</code></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ── Emails ── -->
        <div class="msq-card">
            <h2>Plantillas de Email</h2>
            <p class="description">Variables disponibles: <code>{client_name}</code> <code>{client_email}</code> <code>{client_whatsapp}</code> <code>{services_table}</code> <code>{addons_table}</code> <code>{client_note}</code> <code>{total}</code> <code>{ai_recommendation}</code> <code>{quote_id}</code> <code>{quote_date}</code> <code>{signature}</code></p>

            <table class="form-table msq-form-table">
                <tr>
                    <th><label for="msq_email_admin_subject">Asunto email Admin</label></th>
                    <td><input type="text" id="msq_email_admin_subject" name="msq_email_admin_subject" class="large-text"
                               value="<?php echo esc_attr( get_option( 'msq_email_admin_subject' ) ); ?>"></td>
                </tr>
                <tr>
                    <th><label for="msq_email_admin_body">Cuerpo email Admin</label></th>
                    <td><textarea id="msq_email_admin_body" name="msq_email_admin_body" rows="12" class="large-text"><?php
                        echo esc_textarea( get_option( 'msq_email_admin_body' ) ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="msq_email_client_subject">Asunto email Cliente</label></th>
                    <td><input type="text" id="msq_email_client_subject" name="msq_email_client_subject" class="large-text"
                               value="<?php echo esc_attr( get_option( 'msq_email_client_subject' ) ); ?>"></td>
                </tr>
                <tr>
                    <th><label for="msq_email_client_body">Cuerpo email Cliente</label></th>
                    <td><textarea id="msq_email_client_body" name="msq_email_client_body" rows="12" class="large-text"><?php
                        echo esc_textarea( get_option( 'msq_email_client_body' ) ); ?></textarea></td>
                </tr>
            </table>
        </div>

        <!-- ── WhatsApp ── -->
        <div class="msq-card">
            <h2>Plantilla Mensaje WhatsApp</h2>
            <p class="description">Variables: <code>{client_name}</code> <code>{services_list}</code> <code>{addons_list}</code> <code>{total}</code> <code>{quote_id}</code></p>
            <table class="form-table msq-form-table">
                <tr>
                    <th><label for="msq_whatsapp_template">Mensaje WhatsApp</label></th>
                    <td><textarea id="msq_whatsapp_template" name="msq_whatsapp_template" rows="8" class="large-text"><?php
                        echo esc_textarea( get_option( 'msq_whatsapp_template' ) ); ?></textarea></td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary button-large">Guardar Ajustes</button>
        </p>
    </form>
</div>
