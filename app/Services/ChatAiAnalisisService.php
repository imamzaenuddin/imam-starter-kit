<?php

namespace App\Services;

use App\Models\Bahasa;
use App\Models\DashboardWidget;
use App\Models\Level;
use App\Models\LogAktivitas;
use App\Models\Menu;
use App\Models\User;
use App\Models\ChatAiRiwayat;
use Illuminate\Support\Facades\Http;

class ChatAiAnalisisService
{
    public function analisa(string $pertanyaan): array
    {
        $konteks = $this->konteksData();

        if ($this->bisaPakaiApiAi()) {
            $jawaban = $this->jawabanDariApi($pertanyaan, $konteks);

            if ($jawaban !== null) {
                return [
                    'jawaban' => $jawaban,
                    'sumber' => 'api-ai',
                    'konteks' => $konteks,
                ];
            }
        }

        return [
            'jawaban' => $this->jawabanLokal($pertanyaan, $konteks),
            'sumber' => 'lokal',
            'konteks' => $konteks,
        ];
    }

    private function konteksData(): array
    {
        $modulTeratas = LogAktivitas::query()
            ->selectRaw('modul, COUNT(*) as total')
            ->whereNotNull('modul')
            ->groupBy('modul')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'modul' => $row->modul,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        return [
            'total_pengguna' => User::count(),
            'total_level' => Level::count(),
            'level_aktif' => Level::query()->where('is_active', true)->count(),
            'total_menu' => Menu::count(),
            'menu_aktif' => Menu::query()->where('is_active', true)->count(),
            'bahasa_aktif' => Bahasa::query()->where('is_active', true)->count(),
            'widget_dashboard_aktif' => DashboardWidget::query()->where('is_active', true)->count(),
            'aktivitas_7_hari' => LogAktivitas::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'modul_teratas' => $modulTeratas,
            'waktu_analisa' => now()->toDateTimeString(),
        ];
    }

    private function bisaPakaiApiAi(): bool
    {
        return (bool) env('AI_CHAT_ENABLED', false)
            && filled((string) env('OPENAI_API_KEY', ''));
    }

    private function jawabanDariApi(string $pertanyaan, array $konteks): ?string
    {
        $apiKey = (string) env('OPENAI_API_KEY', '');
        $baseUrl = (string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
        $model = (string) env('OPENAI_MODEL', 'gpt-4o-mini');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(25)
                ->post(rtrim($baseUrl, '/') . '/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah analis data sistem organisasi. Jawab singkat, jelas, Bahasa Indonesia, dan hanya berdasar konteks data JSON yang diberikan.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Konteks data (JSON):\n" . json_encode($konteks, JSON_PRETTY_PRINT) . "\n\nPertanyaan: {$pertanyaan}",
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                return null;
            }

            return (string) data_get($response->json(), 'choices.0.message.content');
        } catch (\Throwable) {
            return null;
        }
    }

    private function jawabanLokal(string $pertanyaan, array $konteks): string
    {
        $q = mb_strtolower($pertanyaan);

        if (str_contains($q, 'pengguna') || str_contains($q, 'user')) {
            return "Total pengguna saat ini adalah {$konteks['total_pengguna']}. Dalam 7 hari terakhir tercatat {$konteks['aktivitas_7_hari']} aktivitas.";
        }

        if (str_contains($q, 'menu')) {
            return "Total menu adalah {$konteks['total_menu']} dengan {$konteks['menu_aktif']} menu aktif. Anda bisa cek mapping hak akses untuk distribusi per level.";
        }

        if (str_contains($q, 'level')) {
            return "Total level adalah {$konteks['total_level']} dan {$konteks['level_aktif']} level dalam status aktif.";
        }

        if (str_contains($q, 'bahasa') || str_contains($q, 'translate')) {
            return "Jumlah bahasa aktif saat ini adalah {$konteks['bahasa_aktif']}. Pastikan semua key ada di file lang/id/messages.php dan lang/en/messages.php.";
        }

        $modulTop = collect($konteks['modul_teratas'])
            ->map(fn($row) => "{$row['modul']} ({$row['total']})")
            ->implode(', ');

        return "Ringkasan cepat: pengguna {$konteks['total_pengguna']}, level aktif {$konteks['level_aktif']}, menu aktif {$konteks['menu_aktif']}, aktivitas 7 hari {$konteks['aktivitas_7_hari']}. Modul paling aktif: {$modulTop}.";
    }

    public function simpanRiwayat(User $user, string $pertanyaan, string $jawaban, string $sumber): void
    {
        try {
            ChatAiRiwayat::query()->create([
                'user_id' => $user->id,
                'pertanyaan' => $pertanyaan,
                'jawaban' => $jawaban,
                'sumber' => $sumber,
            ]);
        } catch (\Throwable) {
            // No-op agar chat tetap berjalan meski tabel riwayat belum tersedia.
        }
    }

    public function riwayatUser(User $user, int $limit = 20): array
    {
        try {
            return ChatAiRiwayat::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit($limit)
                ->get(['pertanyaan', 'jawaban', 'sumber', 'created_at'])
                ->map(fn($item) => [
                    'pertanyaan' => $item->pertanyaan,
                    'jawaban' => $item->jawaban,
                    'sumber' => $item->sumber,
                    'waktu' => optional($item->created_at)->format('d/m/Y H:i:s'),
                ])
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function hapusRiwayat(User $user): int
    {
        try {
            return ChatAiRiwayat::query()
                ->where('user_id', $user->id)
                ->delete();
        } catch (\Throwable) {
            return 0;
        }
    }
}
