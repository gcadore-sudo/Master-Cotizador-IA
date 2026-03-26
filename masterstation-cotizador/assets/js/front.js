/**
 * MasterStation Cotizador IA – Frontend Wizard JS
 * Vanilla JS, sin dependencias externas.
 * msqData: { restUrl, nonce, currency, aiEnabled }
 */
/* global msqData */
(function () {
  'use strict';

  // ─── Estado ───────────────────────────────────────────────────────────────
  const state = {
    step: 1,
    client: { name: '', email: '', whatsapp: '' },
    services: [],   // { id, title, description, price }
    addons:   [],   // { id, title, description, price }
    selected: { services: [], addons: [] },
    note: '',
    aiRecommendation: '',
    quoteId: null,
    total: 0,
    waLink: '',
  };

  const MAX_STEP = 5;

  // ─── Refs ─────────────────────────────────────────────────────────────────
  const wizard   = document.getElementById('msq-wizard');
  if (!wizard) return;

  // ─── Init ─────────────────────────────────────────────────────────────────
  function init() {
    bindNavButtons();
    bindRestartButton();
    updateProgressBar(1);
    loadServices();
    loadAddons();
  }

  // ─── Navegación ───────────────────────────────────────────────────────────
  function bindNavButtons() {
    wizard.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-next], [data-prev]');
      if (!btn) return;

      if (btn.dataset.next) {
        const target = parseInt(btn.dataset.next, 10);
        if (validateStep(state.step)) {
          goToStep(target);
        }
      }

      if (btn.dataset.prev) {
        goToStep(parseInt(btn.dataset.prev, 10));
      }
    });

    // Trigger IA button
    wizard.addEventListener('click', function (e) {
      if (e.target.closest('#msq-ai-btn')) {
        generateAiRecommendation();
      }
    });
  }

  function bindRestartButton() {
    const btn = document.getElementById('msq-restart-btn');
    if (btn) {
      btn.addEventListener('click', restartWizard);
    }
  }

  function goToStep(step) {
    const current = document.getElementById('msq-panel-' + state.step);
    const next    = document.getElementById('msq-panel-' + step);
    if (!current || !next) return;

    // Acciones especiales al entrar a un paso
    if (step === 4) buildSummary();
    if (step === 5) submitQuote();

    current.classList.add('msq-hidden');
    next.classList.remove('msq-hidden');
    state.step = step;
    updateProgressBar(step);

    // Scroll suave al wizard
    wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function updateProgressBar(active) {
    for (let i = 1; i <= MAX_STEP; i++) {
      const indicator = wizard.querySelector('.msq-step-indicator[data-step="' + i + '"]');
      const line      = wizard.querySelectorAll('.msq-step-line')[i - 1];
      if (!indicator) continue;

      indicator.classList.remove('msq-step-active', 'msq-step-done');
      if (i === active)    indicator.classList.add('msq-step-active');
      else if (i < active) indicator.classList.add('msq-step-done');

      if (line) {
        line.classList.toggle('msq-line-done', i < active);
      }
    }
  }

  // ─── Validaciones (paso 1) ────────────────────────────────────────────────
  function validateStep(step) {
    if (step === 1) return validateClientStep();
    return true;
  }

  function validateClientStep() {
    let valid = true;

    const nameInput = document.getElementById('msq-name');
    const emailInput = document.getElementById('msq-email');
    const waInput = document.getElementById('msq-whatsapp');

    // Nombre
    if (!nameInput.value.trim()) {
      showError('msq-error-name', 'El nombre es obligatorio.');
      nameInput.classList.add('msq-invalid');
      valid = false;
    } else {
      clearError('msq-error-name');
      nameInput.classList.remove('msq-invalid');
    }

    // Email
    if (!isValidEmail(emailInput.value.trim())) {
      showError('msq-error-email', 'Ingresa un email válido.');
      emailInput.classList.add('msq-invalid');
      valid = false;
    } else {
      clearError('msq-error-email');
      emailInput.classList.remove('msq-invalid');
    }

    // WhatsApp
    const waNorm = normalizeWhatsapp(waInput.value.trim());
    if (!isValidWhatsapp(waNorm)) {
      showError('msq-error-whatsapp', 'Ingresa un número WhatsApp válido (mínimo 7 dígitos).');
      waInput.classList.add('msq-invalid');
      valid = false;
    } else {
      clearError('msq-error-whatsapp');
      waInput.classList.remove('msq-invalid');
      waInput.value = waNorm; // Normalizar en el input
    }

    if (valid) {
      state.client = {
        name:      nameInput.value.trim(),
        email:     emailInput.value.trim(),
        whatsapp:  waNorm,
      };
    }

    return valid;
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function normalizeWhatsapp(raw) {
    // Quitar todo excepto dígitos y +
    let clean = raw.replace(/[^\d+]/g, '');
    if (!clean) return '';
    if (clean.startsWith('+')) return clean;
    if (clean.startsWith('00')) return '+' + clean.slice(2);
    // Preponer código de país configurado en admin (pasado via wp_localize_script)
    const countryCode = (msqData.countryCode || '+58').replace(/^\+/, '');
    return '+' + countryCode + clean;
  }

  function isValidWhatsapp(normalized) {
    const digits = normalized.replace(/\D/g, '');
    return digits.length >= 7 && digits.length <= 15;
  }

  function showError(id, msg) {
    const el = document.getElementById(id);
    if (el) el.textContent = msg;
  }

  function clearError(id) {
    const el = document.getElementById(id);
    if (el) el.textContent = '';
  }

  // ─── Cargar servicios (Paso 2) ────────────────────────────────────────────
  function loadServices() {
    fetch(msqData.restUrl + 'services', {
      headers: { 'X-WP-Nonce': msqData.nonce },
    })
      .then(r => r.json())
      .then(data => {
        state.services = data;
        renderItems('msq-services-list', data, 'service');
      })
      .catch(() => {
        document.getElementById('msq-services-list').innerHTML =
          '<p style="color:#dc2626">Error al cargar servicios. Recarga la página.</p>';
      });
  }

  // ─── Cargar adicionales (Paso 3) ──────────────────────────────────────────
  function loadAddons() {
    fetch(msqData.restUrl + 'addons', {
      headers: { 'X-WP-Nonce': msqData.nonce },
    })
      .then(r => r.json())
      .then(data => {
        state.addons = data;
        renderItems('msq-addons-list', data, 'addon');
      })
      .catch(() => {
        document.getElementById('msq-addons-list').innerHTML =
          '<p style="color:#dc2626">Error al cargar adicionales. Recarga la página.</p>';
      });
  }

  // ─── Renderizar tarjetas de items ─────────────────────────────────────────
  function renderItems(containerId, items, type) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!items || items.length === 0) {
      container.innerHTML = '<p style="color:#6b7280">No hay ' + (type === 'service' ? 'servicios' : 'adicionales') + ' disponibles.</p>';
      return;
    }

    container.innerHTML = items.map(item => `
      <div class="msq-item-card" data-id="${item.id}" data-type="${type}" tabindex="0" role="checkbox" aria-checked="false">
        <div class="msq-item-check"></div>
        <div class="msq-item-info">
          <div class="msq-item-title">${escHtml(item.title)}</div>
          ${item.description ? `<div class="msq-item-desc">${escHtml(truncate(item.description, 80))}</div>` : ''}
          <div class="msq-item-price">${msqData.currency}${formatPrice(item.price)}</div>
        </div>
      </div>
    `).join('');

    // Eventos de selección
    container.addEventListener('click', function (e) {
      const card = e.target.closest('.msq-item-card');
      if (!card) return;
      toggleItem(card, type);
    });

    container.addEventListener('keydown', function (e) {
      if (e.key === ' ' || e.key === 'Enter') {
        const card = e.target.closest('.msq-item-card');
        if (card) {
          e.preventDefault();
          toggleItem(card, type);
        }
      }
    });
  }

  function toggleItem(card, type) {
    const id    = parseInt(card.dataset.id, 10);
    const arr   = type === 'service' ? state.selected.services : state.selected.addons;
    const items = type === 'service' ? state.services : state.addons;
    const idx   = arr.findIndex(x => x.id === id);

    if (idx > -1) {
      arr.splice(idx, 1);
      card.classList.remove('msq-selected');
      card.setAttribute('aria-checked', 'false');
    } else {
      const item = items.find(x => x.id === id);
      if (item) {
        arr.push(item);
        card.classList.add('msq-selected');
        card.setAttribute('aria-checked', 'true');
      }
    }
  }

  // ─── Construir resumen (Paso 4) ───────────────────────────────────────────
  function buildSummary() {
    // Leer nota
    state.note = (document.getElementById('msq-note') || {}).value || '';

    // Cliente
    const clientEl = document.getElementById('msq-summary-client');
    if (clientEl) {
      clientEl.innerHTML = `
        <div class="msq-summary-label">Cliente</div>
        <div class="msq-summary-value">
          ${escHtml(state.client.name)} · ${escHtml(state.client.email)} · ${escHtml(state.client.whatsapp)}
        </div>`;
    }

    // Servicios
    const servicesEl = document.getElementById('msq-summary-services');
    if (servicesEl) {
      if (state.selected.services.length) {
        const tags = state.selected.services.map(s =>
          `<span class="msq-tag">${escHtml(s.title)} · ${msqData.currency}${formatPrice(s.price)}</span>`
        ).join('');
        servicesEl.innerHTML = `<div class="msq-summary-label">Servicios</div><div class="msq-summary-tags">${tags}</div>`;
      } else {
        servicesEl.innerHTML = '<div class="msq-summary-label">Servicios</div><div class="msq-summary-value"><em>Ninguno</em></div>';
      }
    }

    // Adicionales
    const addonsEl = document.getElementById('msq-summary-addons');
    if (addonsEl) {
      if (state.selected.addons.length) {
        const tags = state.selected.addons.map(a =>
          `<span class="msq-tag">${escHtml(a.title)} · ${msqData.currency}${formatPrice(a.price)}</span>`
        ).join('');
        addonsEl.innerHTML = `<div class="msq-summary-label">Adicionales</div><div class="msq-summary-tags">${tags}</div>`;
      } else {
        addonsEl.innerHTML = '<div class="msq-summary-label">Adicionales</div><div class="msq-summary-value"><em>Ninguno</em></div>';
      }
    }

    // Nota
    const noteEl = document.getElementById('msq-summary-note');
    if (noteEl && state.note) {
      noteEl.innerHTML = `<div class="msq-summary-label">Nota</div><div class="msq-summary-value">${escHtml(state.note)}</div>`;
    }

    // Mostrar/ocultar sección IA
    const aiSection = document.getElementById('msq-ai-section');
    if (aiSection) {
      aiSection.style.display = msqData.aiEnabled ? '' : 'none';
    }
  }

  // ─── Recomendación IA ─────────────────────────────────────────────────────
  function generateAiRecommendation() {
    const btn      = document.getElementById('msq-ai-btn');
    const loading  = wizard.querySelector('.msq-ai-loading');
    const result   = wizard.querySelector('.msq-ai-result');
    const errorEl  = wizard.querySelector('.msq-ai-error');

    if (btn)     btn.classList.add('msq-hidden');
    if (loading) loading.classList.remove('msq-hidden');
    if (result)  result.classList.add('msq-hidden');
    if (errorEl) errorEl.classList.add('msq-hidden');

    fetch(msqData.restUrl + 'ai-recommendation', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':   msqData.nonce,
      },
      body: JSON.stringify({
        services: state.selected.services,
        addons:   state.selected.addons,
        note:     state.note,
      }),
    })
      .then(r => r.json())
      .then(data => {
        if (loading) loading.classList.add('msq-hidden');
        if (data.success && data.text) {
          state.aiRecommendation = data.text;
          if (result) {
            result.textContent = data.text;
            result.classList.remove('msq-hidden');
          }
        } else {
          if (errorEl) errorEl.classList.remove('msq-hidden');
          if (btn) btn.classList.remove('msq-hidden');
        }
      })
      .catch(() => {
        if (loading) loading.classList.add('msq-hidden');
        if (errorEl) errorEl.classList.remove('msq-hidden');
        if (btn) btn.classList.remove('msq-hidden');
      });
  }

  // ─── Enviar cotización (Paso 5) ───────────────────────────────────────────
  function submitQuote() {
    const loadingEl = document.getElementById('msq-submit-loading');
    const resultEl  = document.getElementById('msq-submit-result');
    const errorEl   = document.getElementById('msq-submit-error');

    if (loadingEl) loadingEl.classList.remove('msq-hidden');
    if (resultEl)  resultEl.classList.add('msq-hidden');
    if (errorEl)   errorEl.classList.add('msq-hidden');

    fetch(msqData.restUrl + 'submit', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':   msqData.nonce,
      },
      body: JSON.stringify({
        client_name:       state.client.name,
        client_email:      state.client.email,
        client_whatsapp:   state.client.whatsapp,
        services:          state.selected.services,
        addons:            state.selected.addons,
        note:              state.note,
        ai_recommendation: state.aiRecommendation,
      }),
    })
      .then(r => r.json())
      .then(data => {
        if (loadingEl) loadingEl.classList.add('msq-hidden');

        if (data.success) {
          state.quoteId = data.quote_id;
          state.total   = data.total;
          state.waLink  = data.wa_link;

          // Total
          const totalEl = document.getElementById('msq-final-total');
          if (totalEl) totalEl.textContent = msqData.currency + formatPrice(data.total);

          // Estado de emails
          const emailStatusEl = document.getElementById('msq-email-status');
          if (emailStatusEl) {
            const parts = [];
            if (data.email_admin)  parts.push('✅ Email enviado al equipo de MasterStation');
            if (data.email_client) parts.push('✅ Confirmación enviada a ' + escHtml(state.client.email));
            if (!data.email_admin && !data.email_client) parts.push('⚠️ No se pudieron enviar los emails.');
            emailStatusEl.innerHTML = parts.map(p => `<p>${p}</p>`).join('');
          }

          // WhatsApp link
          const waLinkEl = document.getElementById('msq-wa-link');
          if (waLinkEl && data.wa_link) {
            waLinkEl.href = data.wa_link;
          }

          if (resultEl) resultEl.classList.remove('msq-hidden');
        } else {
          if (errorEl) errorEl.classList.remove('msq-hidden');
        }
      })
      .catch(() => {
        if (loadingEl) loadingEl.classList.add('msq-hidden');
        if (errorEl)   errorEl.classList.remove('msq-hidden');
      });
  }

  // ─── Reiniciar ────────────────────────────────────────────────────────────
  function restartWizard() {
    // Reset estado
    state.step = 1;
    state.client = { name: '', email: '', whatsapp: '' };
    state.selected = { services: [], addons: [] };
    state.note = '';
    state.aiRecommendation = '';
    state.quoteId = null;
    state.total = 0;
    state.waLink = '';

    // Limpiar campos
    const nameInput  = document.getElementById('msq-name');
    const emailInput = document.getElementById('msq-email');
    const waInput    = document.getElementById('msq-whatsapp');
    const noteInput  = document.getElementById('msq-note');
    if (nameInput)  nameInput.value = '';
    if (emailInput) emailInput.value = '';
    if (waInput)    waInput.value = '';
    if (noteInput)  noteInput.value = '';

    // Des-seleccionar cards
    wizard.querySelectorAll('.msq-item-card.msq-selected').forEach(card => {
      card.classList.remove('msq-selected');
      card.setAttribute('aria-checked', 'false');
    });

    // Reset IA
    const aiBtn    = document.getElementById('msq-ai-btn');
    const aiResult = wizard.querySelector('.msq-ai-result');
    const aiLoad   = wizard.querySelector('.msq-ai-loading');
    const aiError  = wizard.querySelector('.msq-ai-error');
    if (aiBtn)    aiBtn.classList.remove('msq-hidden');
    if (aiResult) { aiResult.classList.add('msq-hidden'); aiResult.textContent = ''; }
    if (aiLoad)   aiLoad.classList.add('msq-hidden');
    if (aiError)  aiError.classList.add('msq-hidden');

    // Ocultar todos los panels y mostrar paso 1
    for (let i = 1; i <= MAX_STEP; i++) {
      const panel = document.getElementById('msq-panel-' + i);
      if (panel) panel.classList.toggle('msq-hidden', i !== 1);
    }

    updateProgressBar(1);
    wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ─── Utilidades ───────────────────────────────────────────────────────────
  function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
  }

  function formatPrice(n) {
    return parseFloat(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function truncate(str, len) {
    return str.length > len ? str.slice(0, len) + '…' : str;
  }

  // ─── Arrancar ─────────────────────────────────────────────────────────────
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
