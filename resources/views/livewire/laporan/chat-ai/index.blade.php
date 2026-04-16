<?php

use App\Services\ChatAiAnalisisService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $pertanyaan = '';
    public array $riwayat = [];

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
            abort(403);
        }
    }

    public function kirim(): void
    {
        if (! auth()->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat')) {
            abort(403);
        }

        $data = $this->validate([
            'pertanyaan' => 'required|string|min:3|max:1000',
        ]);

        $hasil = app(ChatAiAnalisisService::class)->analisa($data['pertanyaan'], auth()->user());

        $this->riwayat[] = [
            'pertanyaan' => $data['pertanyaan'],
            'jawaban' => $hasil['jawaban'],
            'sumber' => $hasil['sumber'],
            'ringkasan_redaksi' => $hasil['ringkasan_redaksi'] ?? [
                'ada_redaksi' => false,
                'jumlah_sumber_teredaksi' => 0,
                'kolom_disensor' => [],
            ],
            'waktu' => now()->format('d/m/Y H:i:s'),
        ];

        $this->pertanyaan = '';
    }

    public function resetRiwayat(): void
    {
        $this->riwayat = [];
    }
};
?>
@section('title', __('messages.ai_chat_analyst_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.ai_chat_analyst_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.ai_chat_analyst_subheading') }}</p>
        </div>
        <button type="button" class="btn btn-outline-secondary" wire:click="resetRiwayat">
            <i class="bx bx-reset me-1"></i>{{ __('messages.reset_chat') }}
        </button>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit="kirim">
                <label class="form-label fw-semibold">{{ __('messages.ask_data_question') }}</label>
                <textarea
                    wire:model="pertanyaan"
                    rows="3"
                    class="form-control @error('pertanyaan') is-invalid @enderror"
                    placeholder="{{ __('messages.ask_data_question_placeholder') }}"
                ></textarea>
                @error('pertanyaan') <div class="invalid-feedback">{{ $message }}</div> @enderror

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="kirim">
                        <span wire:loading.remove wire:target="kirim">
                            <i class="bx bx-send me-1"></i>{{ __('messages.analyze_now') }}
                        </span>
                        <span wire:loading wire:target="kirim" style="display:none">
                            <span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.processing') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.analysis_result_history') }}</h5>
        </div>
        <div class="card-body">
            @forelse ($riwayat as $item)
                <div class="border rounded p-3 mb-3">
                    <div class="mb-2">
                        <span class="badge bg-label-primary me-1">{{ __('messages.question_short') }}</span>
                        <span class="fw-semibold">{{ $item['pertanyaan'] }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-label-success me-1">{{ __('messages.answer_short') }}</span>
                        <span>{{ $item['jawaban'] }}</span>
                    </div>
                    <small class="text-muted">
                        {{ __('messages.source') }}: {{ $item['sumber'] === 'api-ai' ? __('messages.ai_model') : __('messages.local_analysis_engine') }}
                        • {{ $item['waktu'] }}
                    </small>
                    @if (data_get($item, 'ringkasan_redaksi.ada_redaksi'))
                        <div class="mt-2">
                            <span class="badge bg-label-warning me-1">{{ __('messages.chat_ai_redaksi_badge') }}</span>
                            <small class="text-muted">
                                {{ __('messages.chat_ai_redaksi_info', [
                                    'jumlah' => data_get($item, 'ringkasan_redaksi.jumlah_sumber_teredaksi', 0),
                                    'kolom' => collect(data_get($item, 'ringkasan_redaksi.kolom_disensor', []))->implode(', '),
                                ]) }}
                            </small>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bx bx-bot" style="font-size:2rem;opacity:.5"></i>
                    <p class="mb-0 mt-2">{{ __('messages.no_analysis_result_yet') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
