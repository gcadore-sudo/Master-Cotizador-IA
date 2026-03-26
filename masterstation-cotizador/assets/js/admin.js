/**
 * MasterStation Cotizador IA – Admin JS
 * Acciones: reenviar email y abrir WhatsApp desde el listado de cotizaciones.
 * msqAdmin: { restUrl, nonce }
 */
/* global msqAdmin */
(function ($) {
  'use strict';

  $(document).ready(function () {
    // Reenviar email al cliente
    $(document).on('click', '.msq-resend-btn', function (e) {
      e.preventDefault();
      const btn   = $(this);
      const id    = btn.data('id');

      if (!id) return;

      if (!window.confirm('¿Reenviar el email de cotización al cliente?')) return;

      btn.prop('disabled', true).text('Enviando…');

      $.ajax({
        url:    msqAdmin.restUrl + 'resend-email/' + id,
        method: 'POST',
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', msqAdmin.nonce);
        },
        success: function (data) {
          if (data.success) {
            btn.text('✅ Enviado');
            showAdminNotice('Email enviado correctamente al cliente.', 'success');
          } else {
            btn.prop('disabled', false).text('📧 Reenviar');
            showAdminNotice('Error al enviar el email.', 'error');
          }
        },
        error: function () {
          btn.prop('disabled', false).text('📧 Reenviar');
          showAdminNotice('Error de conexión al intentar reenviar el email.', 'error');
        },
      });
    });

    // Notificación flotante
    function showAdminNotice(msg, type) {
      const cls   = type === 'success' ? 'notice-success' : 'notice-error';
      const el    = $('<div class="notice ' + cls + ' is-dismissible"><p>' + msg + '</p></div>');
      $('.wrap.msq-admin-wrap h1').after(el);
      setTimeout(() => el.fadeOut(() => el.remove()), 4000);
    }
  });

}(typeof jQuery !== 'undefined' ? jQuery : { fn: {}, ajax: function () {}, ready: function (cb) { cb(); } }));
