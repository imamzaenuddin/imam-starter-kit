<div id="chat-ai-floating-root">
  <button id="chat-ai-toggle"
          type="button"
          class="btn btn-primary shadow"
          title="{{ __('messages.ai_chat_widget_open') }}"
          style="position:fixed;right:22px;bottom:22px;z-index:1085;display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:30px;font-weight:600;font-size:0.875rem;white-space:nowrap;">
    <i class="bx bx-bot" style="font-size:1.2rem;flex-shrink:0;"></i>
    <span>{{ __('messages.ai_chat_widget_title') }}</span>
    <span id="chat-ai-badge"
          style="display:none;min-width:18px;height:18px;border-radius:9px;background:#ff3e1d;color:#fff;font-size:0.65rem;font-weight:700;line-height:18px;text-align:center;padding:0 4px;margin-left:2px;">
    </span>
  </button>

  <div id="chat-ai-panel"
       class="card shadow"
       style="position:fixed;right:22px;bottom:72px;width:360px;max-width:calc(100vw - 24px);z-index:1085;display:none;border-radius:14px;overflow:hidden;">
    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
      <div class="d-flex align-items-center gap-2">
        <span class="avatar avatar-sm rounded bg-label-primary flex-shrink-0"><i class="bx bx-bot"></i></span>
        <div class="overflow-hidden">
          <h6 class="mb-0 text-truncate">{{ __('messages.ai_chat_widget_title') }}</h6>
          <small class="text-muted d-block text-truncate" style="max-width:180px;">{{ __('messages.ai_chat_widget_greeting') }}</small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-1 flex-shrink-0">
        <button id="chat-ai-clear" type="button"
                class="btn btn-sm btn-icon btn-text-secondary"
                title="{{ __('messages.ai_chat_widget_clear_history') }}"
                style="display:none;">
          <i class="bx bx-trash-alt"></i>
        </button>
        <button id="chat-ai-close" type="button" class="btn btn-sm btn-icon btn-text-secondary" title="{{ __('messages.ai_chat_widget_close') }}">
          <i class="bx bx-x"></i>
        </button>
      </div>
    </div>

    <div id="chat-ai-messages" class="card-body p-3" style="height:300px;overflow-y:auto;background:#f8fafc;"></div>

    <div class="card-footer p-2">
      <form id="chat-ai-form" class="d-flex align-items-end gap-2">
        <textarea id="chat-ai-input"
                  class="form-control form-control-sm"
                  rows="2"
                  maxlength="1000"
                  placeholder="{{ __('messages.ai_chat_widget_placeholder') }}"
                  style="resize:none;line-height:1.5;"
                  required></textarea>
        <button id="chat-ai-send" type="submit" class="btn btn-primary btn-sm flex-shrink-0" style="height:60px;width:42px;padding:0;">
          <i class="bx bx-send" style="font-size:1.1rem;"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    const toggleBtn = document.getElementById('chat-ai-toggle');
    const closeBtn = document.getElementById('chat-ai-close');
    const clearBtn = document.getElementById('chat-ai-clear');
    const panel = document.getElementById('chat-ai-panel');
    const form = document.getElementById('chat-ai-form');
    const input = document.getElementById('chat-ai-input');
    const messages = document.getElementById('chat-ai-messages');
    const badge = document.getElementById('chat-ai-badge');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const askUrl = @json(route('laporan.chat-ai.ask'));
    const historyUrl = @json(route('laporan.chat-ai.history'));
    const deleteUrl = @json(route('laporan.chat-ai.history.delete'));
    const msgClearConfirm = @json(__('messages.ai_chat_widget_clear_confirm'));
    const msgCleared = @json(__('messages.ai_chat_widget_cleared'));
    const msgNewMessage = @json(__('messages.ai_chat_widget_new_message'));

    if (!toggleBtn || !panel || !form || !input || !messages) {
      return;
    }

    let unreadCount = 0;

    const updateBadge = () => {
      if (!badge) return;
      if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        badge.style.display = 'block';
        toggleBtn.setAttribute('aria-label', `${unreadCount} ${msgNewMessage}`);
      } else {
        badge.style.display = 'none';
        toggleBtn.removeAttribute('aria-label');
      }
    };

    const clearBadge = () => {
      unreadCount = 0;
      updateBadge();
    };

    const esc = (text) => {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return String(text || '').replace(/[&<>"']/g, (m) => map[m]);
    };

    const addBubble = (role, text, meta = '') => {
      const isUser = role === 'user';
      const align = isUser ? 'text-end' : 'text-start';
      const bg = isUser ? 'bg-label-primary' : 'bg-white border';
      const label = isUser ? @json(__('messages.user')) : @json(__('messages.ai_chat_widget_title'));
      const labelColor = isUser ? 'text-primary' : 'text-muted';

      const row = document.createElement('div');
      row.className = `mb-3 ${align}`;
      row.innerHTML = `
        <div class="d-inline-block px-3 py-2 rounded-3 shadow-sm ${bg}" style="max-width:85%;white-space:pre-wrap;word-break:break-word;text-align:left;">
          <div class="small fw-semibold mb-0 ${labelColor}" style="font-size:0.7rem;letter-spacing:0.02em;line-height:1.2;">${label}</div>
          <div style="font-size:0.875rem;line-height:1.5;">${esc(text)}</div>
          ${meta ? `<div class="small text-muted mt-1 pt-1 border-top" style="font-size:0.7rem;">${esc(meta)}</div>` : ''}
        </div>
      `;
      messages.appendChild(row);
      messages.scrollTop = messages.scrollHeight;
    };

    const setLoading = (loading) => {
      const sendBtn = document.getElementById('chat-ai-send');
      if (!sendBtn) return;

      sendBtn.disabled = loading;
      sendBtn.innerHTML = loading
        ? '<span class="spinner-border spinner-border-sm"></span>'
        : '<i class="bx bx-send"></i>';
    };

    const loadHistory = async () => {
      if (messages.dataset.historyLoaded === '1') {
        return;
      }

      messages.innerHTML = '';
      addBubble('assistant', @json(__('messages.ai_chat_widget_greeting')));

      try {
        const response = await fetch(historyUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }

        const data = await response.json();
        const list = Array.isArray(data?.riwayat) ? data.riwayat : [];

        // Data dari backend urut terbaru, kita tampilkan dari yang terlama.
        list.slice().reverse().forEach((item) => {
          addBubble('user', item.pertanyaan || '');

          const source = item?.sumber === 'api-ai'
            ? @json(__('messages.ai_model'))
            : @json(__('messages.local_analysis_engine'));

          addBubble('assistant', item.jawaban || '', `${source} • ${item?.waktu || ''}`);
        });

        // Tampilkan tombol hapus hanya jika ada riwayat
        if (clearBtn) {
          clearBtn.style.display = list.length > 0 ? 'inline-flex' : 'none';
        }
      } catch (_error) {
        // Biarkan chat tetap usable meski riwayat gagal dimuat.
      } finally {
        messages.dataset.historyLoaded = '1';
      }
    };

    const openPanel = () => {
      panel.style.display = 'block';
      input.focus();
      clearBadge();
      loadHistory();
    };

    const closePanel = () => {
      panel.style.display = 'none';
    };

    toggleBtn.addEventListener('click', () => {
      if (panel.style.display === 'none' || panel.style.display === '') {
        openPanel();
      } else {
        closePanel();
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', closePanel);
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', async () => {
        const result = await Swal.fire({
          title: @json(__('messages.confirm_delete')),
          text: msgClearConfirm,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: @json(__('messages.yes_delete')),
          cancelButtonText: @json(__('messages.cancel')),
        });

        if (!result.isConfirmed) {
          return;
        }

        try {
          clearBtn.disabled = true;
          const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
          });

          if (!response.ok) throw new Error('HTTP ' + response.status);

          // Reset panel
          messages.innerHTML = '';
          messages.dataset.historyLoaded = '0';
          clearBtn.style.display = 'none';
          addBubble('assistant', msgCleared);
          addBubble('assistant', @json(__('messages.ai_chat_widget_greeting')));
          messages.dataset.historyLoaded = '1';
        } catch (_err) {
          // Diam saja — biarkan user coba lagi
        } finally {
          clearBtn.disabled = false;
        }
      });
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const pertanyaan = input.value.trim();
      if (pertanyaan.length < 3) {
        return;
      }

      addBubble('user', pertanyaan);
      input.value = '';
      setLoading(true);

      try {
        const response = await fetch(askUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ pertanyaan })
        });

        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }

        const data = await response.json();
        const source = data?.sumber === 'api-ai'
          ? @json(__('messages.ai_model'))
          : @json(__('messages.local_analysis_engine'));

        addBubble('assistant', data?.jawaban || @json(__('messages.ai_chat_widget_error')), `${source} • ${data?.waktu || ''}`);

        // Tampilkan tombol hapus setelah ada percakapan
        if (clearBtn) clearBtn.style.display = 'inline-flex';

        // Naikkan badge jika panel sedang tertutup
        if (panel.style.display === 'none' || panel.style.display === '') {
          unreadCount++;
          updateBadge();
        }
      } catch (_error) {
        addBubble('assistant', @json(__('messages.ai_chat_widget_error')));
      } finally {
        setLoading(false);
      }
    });
  })();
</script>
