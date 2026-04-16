<?php

namespace Database\Seeders;

use App\Models\ChatAiSumber;
use App\Models\Level;
use App\Models\PengaturanAplikasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ChatAiSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedKonteksPilihan();
        $this->seedSumberDinamis();
    }

    private function seedKonteksPilihan(): void
    {
        if (! Schema::hasTable('m_pengaturan_aplikasi')) {
            return;
        }

        $konteksDefault = [
            'total_pengguna',
            'total_level',
            'level_aktif',
            'total_menu',
            'menu_aktif',
            'bahasa_aktif',
            'widget_dashboard_aktif',
            'aktivitas_7_hari',
            'modul_teratas',
        ];

        $aktif = PengaturanAplikasi::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($aktif) {
            $aktif->update([
                'chat_ai_konteks' => $konteksDefault,
            ]);

            return;
        }

        PengaturanAplikasi::query()->create([
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'locale_default' => config('app.locale', 'id'),
            'batas_upload_kb' => 10240,
            'pagination_default' => 10,
            'otp_mode' => 'always',
            'otp_inactive_days' => 30,
            'otp_failed_attempts' => 3,
            'otp_failed_window_minutes' => 15,
            'chat_ai_konteks' => $konteksDefault,
            'is_active' => true,
        ]);
    }

    private function seedSumberDinamis(): void
    {
        if (! Schema::hasTable('m_chat_ai_sumber')) {
            return;
        }

        $sumber = [
            [
                'nama' => 'Statistik Pengguna Aktif',
                'sumber_data' => 'users',
                'tipe_data' => 'statistik',
                'tipe_query' => 'count',
                'kolom_agregasi' => null,
                'kolom_tampil' => null,
                'filter_kolom' => 'is_active',
                'filter_operator' => '=',
                'filter_nilai' => '1',
                'batas_data' => 10,
                'is_data_personal' => false,
                'urutan' => 1,
                'is_active' => true,
                'level' => ['Superadmin', 'Admin'],
            ],
            [
                'nama' => 'Ringkasan Aktivitas 7 Hari',
                'sumber_data' => 'log_aktivitas',
                'tipe_data' => 'statistik',
                'tipe_query' => 'count',
                'kolom_agregasi' => null,
                'kolom_tampil' => null,
                'filter_kolom' => 'created_at',
                'filter_operator' => '>=',
                'filter_nilai' => '7_hari',
                'batas_data' => 10,
                'is_data_personal' => false,
                'urutan' => 2,
                'is_active' => true,
                'level' => ['Superadmin', 'Admin', 'Anggota'],
            ],
            [
                'nama' => 'Daftar Aktivitas Terbaru',
                'sumber_data' => 'log_aktivitas',
                'tipe_data' => 'daftar',
                'tipe_query' => 'count',
                'kolom_agregasi' => null,
                'kolom_tampil' => ['aktivitas', 'modul', 'created_at'],
                'filter_kolom' => null,
                'filter_operator' => null,
                'filter_nilai' => null,
                'batas_data' => 10,
                'is_data_personal' => false,
                'urutan' => 3,
                'is_active' => true,
                'level' => ['Superadmin', 'Admin', 'Anggota'],
            ],
            [
                'nama' => 'Daftar Pengguna Terbaru (Personal)',
                'sumber_data' => 'users',
                'tipe_data' => 'daftar',
                'tipe_query' => 'count',
                'kolom_agregasi' => null,
                'kolom_tampil' => ['name', 'email', 'created_at'],
                'filter_kolom' => null,
                'filter_operator' => null,
                'filter_nilai' => null,
                'batas_data' => 10,
                'is_data_personal' => true,
                'urutan' => 4,
                'is_active' => true,
                'level' => ['Superadmin'],
            ],
        ];

        foreach ($sumber as $item) {
            $levelNama = $item['level'];
            unset($item['level']);

            $row = ChatAiSumber::query()->updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );

            if (Schema::hasTable('m_chat_ai_sumber_level')) {
                $levelIds = Level::query()
                    ->whereIn('nama_level', $levelNama)
                    ->pluck('id')
                    ->all();

                $row->levels()->sync($levelIds);
            }
        }
    }
}
