<?php

namespace App\Services;

use App\Models\Bahasa;
use App\Models\ChatAiRiwayat;
use App\Models\ChatAiSumber;
use App\Models\DashboardWidget;
use App\Models\Level;
use App\Models\LogAktivitas;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ChatAiAnalisisService
{
    public function analisa(string $pertanyaan, ?User $user = null): array
    {
        $konteks = $this->konteksData($user);
        $ringkasanRedaksi = $this->ringkasanRedaksi($konteks);

        if ($this->bisaPakaiApiAi()) {
            $jawaban = $this->jawabanDariApi($pertanyaan, $konteks);

            if ($jawaban !== null) {
                return [
                    'jawaban' => $jawaban,
                    'sumber' => 'api-ai',
                    'konteks' => $konteks,
                    'ringkasan_redaksi' => $ringkasanRedaksi,
                ];
            }
        }

        return [
            'jawaban' => $this->jawabanLokal($pertanyaan, $konteks),
            'sumber' => 'lokal',
            'konteks' => $konteks,
            'ringkasan_redaksi' => $ringkasanRedaksi,
        ];
    }

    private function konteksData(?User $user = null): array
    {
        $aktif = $this->konteksDiaktifkan();

        $semua = [
            'total_pengguna' => fn () => User::count(),
            'total_level' => fn () => Level::count(),
            'level_aktif' => fn () => Level::query()->where('is_active', true)->count(),
            'total_menu' => fn () => Menu::count(),
            'menu_aktif' => fn () => Menu::query()->where('is_active', true)->count(),
            'bahasa_aktif' => fn () => Bahasa::query()->where('is_active', true)->count(),
            'widget_dashboard_aktif' => fn () => DashboardWidget::query()->where('is_active', true)->count(),
            'aktivitas_7_hari' => fn () => LogAktivitas::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'modul_teratas' => fn () => LogAktivitas::query()
                ->selectRaw('modul, COUNT(*) as total')
                ->whereNotNull('modul')
                ->groupBy('modul')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => ['modul' => $row->modul, 'total' => (int) $row->total])
                ->values()
                ->all(),
        ];

        $hasil = ['waktu_analisa' => now()->toDateTimeString()];

        foreach ($semua as $key => $resolver) {
            if (in_array($key, $aktif, true)) {
                $hasil[$key] = $resolver();
            }
        }

        $hasil['konteks_dinamis'] = $this->konteksDinamisAktif($user);

        return $hasil;
    }

    /**
     * Daftar semua konteks yang tersedia beserta label tampilan (untuk UI admin).
     */
    public function konteksAktifTersedia(): array
    {
        return [
            'total_pengguna' => 'Total Pengguna',
            'total_level' => 'Total Level',
            'level_aktif' => 'Level Aktif',
            'total_menu' => 'Total Menu',
            'menu_aktif' => 'Menu Aktif',
            'bahasa_aktif' => 'Bahasa Aktif',
            'widget_dashboard_aktif' => 'Widget Dashboard Aktif',
            'aktivitas_7_hari' => 'Aktivitas 7 Hari Terakhir',
            'modul_teratas' => 'Modul Paling Aktif (Top 5)',
        ];
    }

    /**
     * Kunci konteks yang saat ini diaktifkan berdasarkan konfigurasi.
     */
    public function konteksDiaktifkan(): array
    {
        $pengaturan = app(PengaturanAplikasiService::class)->konfigurasiAktif();
        $diaktifkan = $pengaturan['chat_ai_konteks'] ?? [];

        $whitelist = array_keys($this->konteksAktifTersedia());

        return array_values(array_intersect($diaktifkan, $whitelist));
    }

    public function sumberDataTersedia(): array
    {
        return collect($this->konfigurasiSumberSemua())
            ->mapWithKeys(fn (array $item, string $key) => [$key => $item['label']])
            ->all();
    }

    public function tipeDataTersedia(): array
    {
        return [
            'statistik' => 'Statistik',
            'daftar' => 'Daftar Data',
        ];
    }

    public function tipeQueryTersedia(): array
    {
        return [
            'count' => 'Jumlah Data',
            'sum' => 'Total / Sum',
            'avg' => 'Rata-rata',
            'min' => 'Nilai Minimum',
            'max' => 'Nilai Maksimum',
        ];
    }

    public function operatorFilterTersedia(): array
    {
        return [
            '=' => 'Sama dengan',
            '!=' => 'Tidak sama dengan',
            '>' => 'Lebih besar dari',
            '>=' => 'Lebih besar / sama dengan',
            '<' => 'Lebih kecil dari',
            '<=' => 'Lebih kecil / sama dengan',
            'like' => 'Mengandung teks',
        ];
    }

    public function kolomSumberTersedia(?string $sumberData): array
    {
        $konfigurasi = $sumberData ? $this->konfigurasiSumber($sumberData) : null;

        return $konfigurasi ? $konfigurasi['kolom'] : [];
    }

    public function kolomNumerikSumberTersedia(?string $sumberData): array
    {
        $konfigurasi = $sumberData ? $this->konfigurasiSumber($sumberData) : null;

        return $konfigurasi ? $konfigurasi['kolom_numerik'] : [];
    }

    private function konteksDinamisAktif(?User $user = null): array
    {
        if (! Schema::hasTable('m_chat_ai_sumber')) {
            return [];
        }

        $hasMappingLevel = Schema::hasTable('m_chat_ai_sumber_level');

        $query = ChatAiSumber::query();

        if ($hasMappingLevel) {
            $query->with('levels:id,nama_level');
        }

        return $query
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->filter(fn (ChatAiSumber $sumber) => $this->bisaAksesSumber($sumber, $user, $hasMappingLevel))
            ->map(function (ChatAiSumber $sumber) use ($user) {
                try {
                    return $this->eksekusiSumberDinamis($sumber, $user);
                } catch (\Throwable) {
                    return [
                        'nama' => $sumber->nama,
                        'sumber_data' => $sumber->sumber_data,
                        'tipe_data' => $sumber->tipe_data,
                        'hasil' => null,
                        'catatan' => 'Gagal membaca sumber data.',
                    ];
                }
            })
            ->values()
            ->all();
    }

    private function eksekusiSumberDinamis(ChatAiSumber $sumber, ?User $user = null): array
    {
        $konfigurasi = $this->konfigurasiSumber($sumber->sumber_data);

        if (! $konfigurasi) {
            return [
                'nama' => $sumber->nama,
                'sumber_data' => $sumber->sumber_data,
                'tipe_data' => $sumber->tipe_data,
                'hasil' => null,
                'catatan' => 'Sumber data tidak dikenali.',
            ];
        }

        $query = $this->builderUntuk($sumber->sumber_data);
        $this->terapkanFilter($query, $sumber, $konfigurasi);

        if ($sumber->tipe_data === 'daftar') {
            $kolomTampil = collect($sumber->kolom_tampil ?? [])
                ->filter(fn ($kolom) => is_string($kolom) && array_key_exists($kolom, $konfigurasi['kolom']))
                ->filter(fn ($kolom) => ! $this->isKolomSensitif($kolom))
                ->values()
                ->all();

            $kolomSebelumProteksi = $kolomTampil;

            if ($sumber->is_data_personal && ! $user?->isSuperadmin()) {
                $kolomTampil = array_values(array_diff($kolomTampil, ['name', 'email']));
            }

            $kolomTampil = $this->kolomAmanUntukUser($kolomTampil, $sumber->sumber_data, $user);

            $this->catatAuditRedaksiKolomPersonal($user, $sumber, $kolomSebelumProteksi, $kolomTampil);
            $kolomDisensor = $this->hitungKolomDisensor($kolomSebelumProteksi, $kolomTampil);

            if (empty($kolomTampil)) {
                $kolomTampil = array_slice(array_keys($konfigurasi['kolom']), 0, 3);
                $kolomTampil = array_values(array_filter($kolomTampil, fn ($kolom) => ! $this->isKolomSensitif($kolom)));

                $kolomSebelumProteksiFallback = $kolomTampil;

                $kolomTampil = $this->kolomAmanUntukUser($kolomTampil, $sumber->sumber_data, $user);

                $this->catatAuditRedaksiKolomPersonal($user, $sumber, $kolomSebelumProteksiFallback, $kolomTampil);
                $kolomDisensor = array_values(array_unique(array_merge(
                    $kolomDisensor,
                    $this->hitungKolomDisensor($kolomSebelumProteksiFallback, $kolomTampil)
                )));
            }

            $batas = min(max((int) $sumber->batas_data, 1), 50);
            $kolomOrder = array_key_exists('created_at', $konfigurasi['kolom']) ? 'created_at' : 'id';

            $rows = $query->orderByDesc($kolomOrder)
                ->limit($batas)
                ->get($kolomTampil)
                ->map(fn ($row) => collect($kolomTampil)->mapWithKeys(fn ($kolom) => [$kolom => data_get($row, $kolom)])->all())
                ->values()
                ->all();

            return [
                'nama' => $sumber->nama,
                'sumber_data' => $sumber->sumber_data,
                'tipe_data' => 'daftar',
                'kolom' => $kolomTampil,
                'redaksi_otomatis' => ! empty($kolomDisensor),
                'kolom_disensor' => $kolomDisensor,
                'hasil' => $rows,
            ];
        }

        $tipeQuery = $sumber->tipe_query ?: 'count';
        $kolomAgregasi = $sumber->kolom_agregasi;

        $nilai = match ($tipeQuery) {
            'sum' => $kolomAgregasi && array_key_exists($kolomAgregasi, $konfigurasi['kolom_numerik']) ? (float) $query->sum($kolomAgregasi) : 0,
            'avg' => $kolomAgregasi && array_key_exists($kolomAgregasi, $konfigurasi['kolom_numerik']) ? round((float) $query->avg($kolomAgregasi), 2) : 0,
            'min' => $kolomAgregasi && array_key_exists($kolomAgregasi, $konfigurasi['kolom_numerik']) ? (float) $query->min($kolomAgregasi) : 0,
            'max' => $kolomAgregasi && array_key_exists($kolomAgregasi, $konfigurasi['kolom_numerik']) ? (float) $query->max($kolomAgregasi) : 0,
            default => $query->count(),
        };

        return [
            'nama' => $sumber->nama,
            'sumber_data' => $sumber->sumber_data,
            'tipe_data' => 'statistik',
            'tipe_query' => $tipeQuery,
            'kolom_agregasi' => $kolomAgregasi,
            'redaksi_otomatis' => false,
            'kolom_disensor' => [],
            'hasil' => $nilai,
        ];
    }

    private function ringkasanRedaksi(array $konteks): array
    {
        $konteksDinamis = collect(data_get($konteks, 'konteks_dinamis', []));

        $sumberTeredaksi = $konteksDinamis
            ->filter(fn (array $item) => (bool) data_get($item, 'redaksi_otomatis', false));

        $kolomDisensor = $sumberTeredaksi
            ->flatMap(fn (array $item) => (array) data_get($item, 'kolom_disensor', []))
            ->map(fn ($kolom) => (string) $kolom)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'ada_redaksi' => $sumberTeredaksi->isNotEmpty(),
            'jumlah_sumber_teredaksi' => $sumberTeredaksi->count(),
            'kolom_disensor' => $kolomDisensor,
        ];
    }

    private function builderUntuk(string $sumberData): Builder
    {
        $modelClass = $this->konfigurasiSumber($sumberData)['model'];

        return $modelClass::query();
    }

    private function bisaAksesSumber(ChatAiSumber $sumber, ?User $user = null, bool $hasMappingLevel = false): bool
    {
        if ($sumber->is_data_personal && ! $user?->isSuperadmin()) {
            return false;
        }

        if (! $hasMappingLevel) {
            return true;
        }

        $levelIds = $sumber->levels->pluck('id')->all();

        if (empty($levelIds)) {
            return true;
        }

        if (! $user?->level_id) {
            return false;
        }

        return in_array((int) $user->level_id, array_map('intval', $levelIds), true);
    }

    private function isKolomSensitif(string $kolom): bool
    {
        $kolomLower = strtolower($kolom);

        if (in_array($kolomLower, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'], true)) {
            return true;
        }

        return str_contains($kolomLower, 'token')
          || str_contains($kolomLower, 'secret')
          || str_contains($kolomLower, 'password')
          || str_contains($kolomLower, 'key');
    }

    private function kolomAmanUntukUser(array $kolomTampil, string $sumberData, ?User $user = null): array
    {
        if ($sumberData !== 'users') {
            return $kolomTampil;
        }

        if ($user?->isSuperadmin()) {
            return $kolomTampil;
        }

        return array_values(array_filter($kolomTampil, fn ($kolom) => strtolower((string) $kolom) !== 'email'));
    }

    private function catatAuditRedaksiKolomPersonal(?User $user, ChatAiSumber $sumber, array $kolomAwal, array $kolomFinal): void
    {
        if (! $user) {
            return;
        }

        $kolomDisensor = array_values(array_diff($kolomAwal, $kolomFinal));

        if (empty($kolomDisensor)) {
            return;
        }

        try {
            LogAktivitas::query()->create([
                'user_id' => $user->id,
                'modul' => 'Chat AI Keamanan',
                'aktivitas' => 'Redaksi otomatis kolom personal pada sumber AI: '.$sumber->nama,
                'url' => '/laporan/chat-ai',
                'metode' => 'POST',
                'ip_address' => request()->ip() ?: 'system',
                'user_agent' => request()->userAgent() ?: 'system',
                'metadata' => [
                    'chat_ai_sumber_id' => $sumber->id,
                    'sumber_data' => $sumber->sumber_data,
                    'kolom_awal' => array_values($kolomAwal),
                    'kolom_final' => array_values($kolomFinal),
                    'kolom_disensor' => $kolomDisensor,
                    'alasan' => 'Proteksi data personal untuk non-Superadmin',
                ],
            ]);
        } catch (\Throwable) {
            // No-op agar proses analisa tidak terganggu jika audit log gagal ditulis.
        }
    }

    private function hitungKolomDisensor(array $kolomAwal, array $kolomFinal): array
    {
        return array_values(array_diff($kolomAwal, $kolomFinal));
    }

    private function terapkanFilter(Builder $query, ChatAiSumber $sumber, array $konfigurasi): void
    {
        if (! $sumber->filter_kolom || ! array_key_exists($sumber->filter_kolom, $konfigurasi['kolom'])) {
            return;
        }

        if ($sumber->filter_nilai === null || $sumber->filter_nilai === '') {
            return;
        }

        $operator = $sumber->filter_operator ?: '=';
        $nilai = $this->normalisasiNilaiFilter($sumber->filter_kolom, $sumber->filter_nilai, $konfigurasi);

        if ($operator === 'like') {
            $query->where($sumber->filter_kolom, 'like', '%'.$sumber->filter_nilai.'%');

            return;
        }

        if (! array_key_exists($operator, $this->operatorFilterTersedia())) {
            $operator = '=';
        }

        $query->where($sumber->filter_kolom, $operator, $nilai);
    }

    private function normalisasiNilaiFilter(string $kolom, string $nilai, array $konfigurasi): mixed
    {
        if ($nilai === 'hari_ini') {
            return now()->toDateString();
        }

        if ($nilai === '7_hari') {
            return now()->subDays(7)->toDateTimeString();
        }

        if ($nilai === '30_hari') {
            return now()->subDays(30)->toDateTimeString();
        }

        if (array_key_exists($kolom, $konfigurasi['kolom_boolean'])) {
            return filter_var($nilai, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (int) $nilai;
        }

        if (array_key_exists($kolom, $konfigurasi['kolom_numerik'])) {
            return is_numeric($nilai) ? $nilai + 0 : 0;
        }

        return $nilai;
    }

    private function konfigurasiSumber(?string $sumberData): ?array
    {
        if (! $sumberData) {
            return null;
        }

        return $this->konfigurasiSumberSemua()[$sumberData] ?? null;
    }

    private function konfigurasiSumberSemua(): array
    {
        return [
            'users' => [
                'label' => 'Pengguna',
                'model' => User::class,
                'kolom' => [
                    'id' => 'ID',
                    'name' => 'Nama',
                    'email' => 'Email',
                    'level_id' => 'Level',
                    'created_at' => 'Tanggal Dibuat',
                ],
                'kolom_numerik' => [
                    'id' => 'ID',
                    'level_id' => 'Level',
                ],
                'kolom_boolean' => [],
            ],
            'levels' => [
                'label' => 'Level User',
                'model' => Level::class,
                'kolom' => [
                    'id' => 'ID',
                    'nama_level' => 'Nama Level',
                    'deskripsi' => 'Deskripsi',
                    'is_active' => 'Status Aktif',
                    'created_at' => 'Tanggal Dibuat',
                ],
                'kolom_numerik' => ['id' => 'ID'],
                'kolom_boolean' => ['is_active' => 'Status Aktif'],
            ],
            'menus' => [
                'label' => 'Menu Sistem',
                'model' => Menu::class,
                'kolom' => [
                    'id' => 'ID',
                    'nama' => 'Nama Menu',
                    'url' => 'URL',
                    'parent_id' => 'Parent',
                    'urutan' => 'Urutan',
                    'is_active' => 'Status Aktif',
                    'created_at' => 'Tanggal Dibuat',
                ],
                'kolom_numerik' => [
                    'id' => 'ID',
                    'parent_id' => 'Parent',
                    'urutan' => 'Urutan',
                ],
                'kolom_boolean' => ['is_active' => 'Status Aktif'],
            ],
            'log_aktivitas' => [
                'label' => 'Log Aktivitas',
                'model' => LogAktivitas::class,
                'kolom' => [
                    'id' => 'ID',
                    'user_id' => 'User',
                    'modul' => 'Modul',
                    'aktivitas' => 'Aktivitas',
                    'metode' => 'Metode',
                    'created_at' => 'Tanggal Dibuat',
                ],
                'kolom_numerik' => [
                    'id' => 'ID',
                    'user_id' => 'User',
                ],
                'kolom_boolean' => [],
            ],
            'identitas' => [
                'label' => 'Identitas Sistem',
                'model' => \App\Models\Identitas::class,
                'kolom' => [
                    'id' => 'ID',
                    'nama_aplikasi' => 'Nama Aplikasi',
                    'versi' => 'Versi',
                    'email' => 'Email',
                    'website' => 'Website',
                    'is_active' => 'Status Aktif',
                    'created_at' => 'Tanggal Dibuat',
                ],
                'kolom_numerik' => ['id' => 'ID'],
                'kolom_boolean' => ['is_active' => 'Status Aktif'],
            ],
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
                ->post(rtrim($baseUrl, '/').'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah analis data sistem organisasi. Jawab singkat, jelas, Bahasa Indonesia, dan hanya berdasar konteks data JSON yang diberikan.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Konteks data (JSON):\n".json_encode($konteks, JSON_PRETTY_PRINT)."\n\nPertanyaan: {$pertanyaan}",
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

        $totalPengguna = (int) data_get($konteks, 'total_pengguna', 0);
        $aktivitas7Hari = (int) data_get($konteks, 'aktivitas_7_hari', 0);
        $totalMenu = (int) data_get($konteks, 'total_menu', 0);
        $menuAktif = (int) data_get($konteks, 'menu_aktif', 0);
        $totalLevel = (int) data_get($konteks, 'total_level', 0);
        $levelAktif = (int) data_get($konteks, 'level_aktif', 0);
        $bahasaAktif = (int) data_get($konteks, 'bahasa_aktif', 0);
        $modulTop = collect(data_get($konteks, 'modul_teratas', []))
            ->map(fn ($row) => data_get($row, 'modul', '-').' ('.(int) data_get($row, 'total', 0).')')
            ->implode(', ');
        $jumlahKonteksDinamis = count(data_get($konteks, 'konteks_dinamis', []));

        if (str_contains($q, 'pengguna') || str_contains($q, 'user')) {
            return "Total pengguna saat ini adalah {$totalPengguna}. Dalam 7 hari terakhir tercatat {$aktivitas7Hari} aktivitas.";
        }

        if (str_contains($q, 'menu')) {
            return "Total menu adalah {$totalMenu} dengan {$menuAktif} menu aktif. Anda bisa cek mapping hak akses untuk distribusi per level.";
        }

        if (str_contains($q, 'level')) {
            return "Total level adalah {$totalLevel} dan {$levelAktif} level dalam status aktif.";
        }

        if (str_contains($q, 'bahasa') || str_contains($q, 'translate')) {
            return "Jumlah bahasa aktif saat ini adalah {$bahasaAktif}. Pastikan semua key ada di file lang/id/messages.php dan lang/en/messages.php.";
        }

        return "Ringkasan cepat: pengguna {$totalPengguna}, level aktif {$levelAktif}, menu aktif {$menuAktif}, aktivitas 7 hari {$aktivitas7Hari}. Modul paling aktif: {$modulTop}. Sumber konteks dinamis aktif: {$jumlahKonteksDinamis}.";
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
                ->map(fn ($item) => [
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
