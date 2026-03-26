<?php
/**
 * Template del Wizard multi-step para el shortcode [masterstation_cotizador].
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="msq-wizard" class="msq-wizard" role="main" aria-label="Cotizador de diseño web">

    <!-- Barra de progreso -->
    <div class="msq-progress" aria-label="Progreso del formulario">
        <div class="msq-progress-bar">
            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
            <div class="msq-step-indicator" data-step="<?php echo esc_attr( $i ); ?>">
                <span class="msq-step-num"><?php echo esc_html( $i ); ?></span>
                <span class="msq-step-label"><?php
                    $labels = [ 1 => 'Datos', 2 => 'Servicios', 3 => 'Adicionales', 4 => 'Resumen', 5 => 'Total' ];
                    echo esc_html( $labels[ $i ] );
                ?></span>
            </div>
            <?php if ( $i < 5 ) : ?>
            <div class="msq-step-line"></div>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ──────────────────── PASO 1 – Datos del cliente ──────────────────── -->
    <div class="msq-panel" id="msq-panel-1" data-step="1">
        <div class="msq-panel-header">
            <h2>¿Cómo te llamamos?</h2>
            <p>Cuéntanos un poco sobre ti para comenzar tu cotización personalizada.</p>
        </div>
        <div class="msq-panel-body">
            <div class="msq-field-group">
                <label for="msq-name">Nombre completo <span class="msq-required">*</span></label>
                <input type="text" id="msq-name" name="client_name" placeholder="Ej. María González"
                       autocomplete="name" required>
                <span class="msq-field-error" id="msq-error-name"></span>
            </div>
            <div class="msq-field-group">
                <label for="msq-email">Correo electrónico <span class="msq-required">*</span></label>
                <input type="email" id="msq-email" name="client_email" placeholder="tu@correo.com"
                       autocomplete="email" required>
                <span class="msq-field-error" id="msq-error-email"></span>
            </div>
            <div class="msq-field-group">
                <label for="msq-whatsapp">WhatsApp <span class="msq-required">*</span></label>
                <input type="tel" id="msq-whatsapp" name="client_whatsapp" placeholder="+58 424 183 7004"
                       autocomplete="tel" required>
                <span class="msq-field-hint">Incluye código de país. Ej: +58 424 000 0000</span>
                <span class="msq-field-error" id="msq-error-whatsapp"></span>
            </div>
        </div>
        <div class="msq-panel-footer">
            <button type="button" class="msq-btn msq-btn-primary msq-next-btn" data-next="2">
                Continuar <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

    <!-- ──────────────────── PASO 2 – Servicios ──────────────────── -->
    <div class="msq-panel msq-hidden" id="msq-panel-2" data-step="2">
        <div class="msq-panel-header">
            <h2>¿Qué necesitas construir?</h2>
            <p>Selecciona uno o varios servicios que se adapten a tu proyecto.</p>
        </div>
        <div class="msq-panel-body">
            <div id="msq-services-list" class="msq-items-grid">
                <div class="msq-loading">
                    <div class="msq-spinner"></div>
                    <p>Cargando servicios…</p>
                </div>
            </div>
            <span class="msq-field-error" id="msq-error-services"></span>
        </div>
        <div class="msq-panel-footer">
            <button type="button" class="msq-btn msq-btn-ghost msq-prev-btn" data-prev="1">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Atrás
            </button>
            <button type="button" class="msq-btn msq-btn-primary msq-next-btn" data-next="3">
                Continuar <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

    <!-- ──────────────────── PASO 3 – Adicionales + Nota ──────────────────── -->
    <div class="msq-panel msq-hidden" id="msq-panel-3" data-step="3">
        <div class="msq-panel-header">
            <h2>¿Algo más que potenciar?</h2>
            <p>Agrega funcionalidades complementarias y cuéntanos sobre tu proyecto.</p>
        </div>
        <div class="msq-panel-body">
            <div id="msq-addons-list" class="msq-items-grid">
                <div class="msq-loading">
                    <div class="msq-spinner"></div>
                    <p>Cargando adicionales…</p>
                </div>
            </div>
            <div class="msq-field-group msq-mt">
                <label for="msq-note">Nota adicional <span class="msq-optional">(opcional)</span></label>
                <textarea id="msq-note" name="client_note" rows="4"
                          placeholder="Cuéntanos sobre tu proyecto, referencias, plazos o cualquier detalle importante…"></textarea>
            </div>
        </div>
        <div class="msq-panel-footer">
            <button type="button" class="msq-btn msq-btn-ghost msq-prev-btn" data-prev="2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Atrás
            </button>
            <button type="button" class="msq-btn msq-btn-primary msq-next-btn" data-next="4">
                Ver resumen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

    <!-- ──────────────────── PASO 4 – Resumen + IA ──────────────────── -->
    <div class="msq-panel msq-hidden" id="msq-panel-4" data-step="4">
        <div class="msq-panel-header">
            <h2>Tu propuesta personalizada</h2>
            <p>Revisa tu selección y descubre la recomendación de nuestro asesor IA.</p>
        </div>
        <div class="msq-panel-body">
            <!-- Resumen -->
            <div class="msq-summary-box">
                <div class="msq-summary-row" id="msq-summary-client"></div>
                <div class="msq-summary-row" id="msq-summary-services"></div>
                <div class="msq-summary-row" id="msq-summary-addons"></div>
                <div class="msq-summary-row" id="msq-summary-note"></div>
            </div>

            <!-- IA -->
            <div class="msq-ai-section" id="msq-ai-section">
                <div class="msq-ai-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <strong>Recomendación de IA</strong>
                </div>
                <div id="msq-ai-content" class="msq-ai-content">
                    <div class="msq-ai-loading msq-hidden">
                        <div class="msq-spinner"></div>
                        <p>Generando recomendación personalizada…</p>
                    </div>
                    <div class="msq-ai-result msq-hidden"></div>
                    <div class="msq-ai-error msq-hidden">
                        <p>⚠️ No se pudo generar la recomendación de IA. Puedes continuar de todas formas.</p>
                    </div>
                    <button type="button" class="msq-btn msq-btn-ghost msq-ai-trigger" id="msq-ai-btn">
                        ✨ Generar recomendación con IA
                    </button>
                </div>
            </div>
        </div>
        <div class="msq-panel-footer">
            <button type="button" class="msq-btn msq-btn-ghost msq-prev-btn" data-prev="3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Atrás
            </button>
            <button type="button" class="msq-btn msq-btn-primary msq-next-btn" data-next="5">
                Finalizar cotización <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

    <!-- ──────────────────── PASO 5 – Total + Envío ──────────────────── -->
    <div class="msq-panel msq-hidden" id="msq-panel-5" data-step="5">
        <div class="msq-panel-header">
            <h2>¡Tu cotización está lista! 🎉</h2>
            <p>Estamos enviando los detalles a tu correo y al equipo de MasterStation.</p>
        </div>
        <div class="msq-panel-body">
            <!-- Estado de envío -->
            <div id="msq-submit-loading" class="msq-loading">
                <div class="msq-spinner"></div>
                <p>Procesando tu cotización…</p>
            </div>

            <div id="msq-submit-result" class="msq-hidden">
                <!-- Total -->
                <div class="msq-total-box">
                    <span class="msq-total-label">Total estimado</span>
                    <span class="msq-total-amount" id="msq-final-total">$0.00</span>
                    <span class="msq-total-note">USD · Precio referencial sin impuestos</span>
                </div>

                <!-- Emails -->
                <div class="msq-email-status" id="msq-email-status"></div>

                <!-- WhatsApp -->
                <div class="msq-wa-cta">
                    <p>¿Listo para dar el siguiente paso? Escríbenos directamente por WhatsApp con todos los detalles precargados:</p>
                    <a href="#" id="msq-wa-link" target="_blank" rel="noopener" class="msq-btn msq-btn-wa">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Escribir por WhatsApp
                    </a>
                </div>

                <!-- Despedida -->
                <div class="msq-farewell">
                    <p>Nuestro equipo revisará tu cotización y se pondrá en contacto contigo pronto.</p>
                    <p><strong>¡Gracias por confiar en MasterStation!</strong></p>
                </div>

                <!-- Reiniciar -->
                <div class="msq-restart-row">
                    <button type="button" class="msq-btn msq-btn-ghost" id="msq-restart-btn">
                        ↺ Crear nueva cotización
                    </button>
                </div>
            </div>

            <!-- Error de envío -->
            <div id="msq-submit-error" class="msq-hidden msq-error-box">
                <p>❌ Ocurrió un error al procesar tu cotización. Por favor intenta de nuevo.</p>
                <button type="button" class="msq-btn msq-btn-ghost msq-prev-btn" data-prev="4">Volver</button>
            </div>
        </div>
    </div>

</div><!-- /#msq-wizard -->
