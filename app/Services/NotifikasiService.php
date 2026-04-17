<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class NotifikasiService
{
    /**
     * Buat notifikasi baru
     */
    public function buat(
        int|array $userIds,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $pathTerkait = null
    ): Collection {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $notifikasiList = [];

        foreach ($userIds as $userId) {
            $notifikasiList[] = [
                'user_id' => $userId,
                'judul' => $judul,
                'pesan' => $pesan,
                'tipe' => $tipe,
                'path_terkait' => $pathTerkait,
                'dibaca' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Notifikasi::insert($notifikasiList);

        return Notifikasi::whereIn('user_id', $userIds)
            ->latest()
            ->limit(count($notifikasiList))
            ->get();
    }

    /**
     * Ambil notifikasi belum dibaca untuk user
     */
    public function ambilBelumDibaca(int $userId, int $limit = 10): Collection
    {
        return Notifikasi::untukUser($userId)
            ->belumDibaca()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil semua notifikasi user (dengan pagination)
     */
    public function ambilSemuaUser(int $userId, int $perPage = 15): Paginator
    {
        return Notifikasi::untukUser($userId)
            ->latest('created_at')
            ->simplePaginate($perPage);
    }

    /**
     * Hitung notifikasi belum dibaca
     */
    public function hitungBelumDibaca(int $userId): int
    {
        return Notifikasi::untukUser($userId)
            ->belumDibaca()
            ->count();
    }

    /**
     * Tandai notifikasi sebagai dibaca
     */
    public function tandaiDibaca(int $notifikasiId): bool
    {
        $notifikasi = Notifikasi::find($notifikasiId);

        if ($notifikasi) {
            $notifikasi->tandaiBaca();

            return true;
        }

        return false;
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca
     */
    public function tandaiSemuaDibaca(int $userId): void
    {
        Notifikasi::tandaiSemuaBaca($userId);
    }

    /**
     * Hapus notifikasi
     */
    public function hapus(int $notifikasiId): bool
    {
        return Notifikasi::destroy($notifikasiId) > 0;
    }

    /**
     * Hapus notifikasi lama (lebih dari X hari)
     */
    public function hapusLama(int $hariSebelum = 30): int
    {
        return Notifikasi::where('created_at', '<', now()->subDays($hariSebelum))
            ->where('dibaca', true)
            ->delete();
    }

    /**
     * Notifikasi backup selesai
     */
    public function backupSelesai(array $userIds, array $data): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_backup_complete_title'),
            __('messages.notification_backup_complete_message', [
                'tipe' => $data['tipe'] ?? 'FULL',
                'ukuran' => $data['ukuran'] ?? '0 MB',
                'waktu' => $data['waktu'] ?? '0s',
            ]),
            'backup_selesai',
            '/admin/backup-restore'
        );
    }

    /**
     * Notifikasi restore selesai
     */
    public function restoreSelesai(array $userIds, array $data): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_restore_complete_title'),
            __('messages.notification_restore_complete_message', [
                'berkas' => $data['berkas'] ?? 'unknown',
            ]),
            'restore_selesai',
            '/admin/backup-restore'
        );
    }

    /**
     * Notifikasi restore gagal
     */
    public function restoreGagal(array $userIds, array $data): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_restore_failed_title'),
            __('messages.notification_restore_failed_message', [
                'alasan' => $data['alasan'] ?? 'Unknown error',
            ]),
            'restore_gagal',
            '/admin/backup-restore'
        );
    }

    /**
     * Notifikasi perubahan data penting
     */
    public function perubahanData(array $userIds, string $modul, string $aksi): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_data_change_title'),
            __('messages.notification_data_change_message', [
                'modul' => $modul,
                'aksi' => $aksi,
            ]),
            'perubahan_data'
        );
    }

    /**
     * Notifikasi aktivitas penting
     */
    public function aktivitasPenting(array $userIds, string $pesan, ?string $path = null): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_activity_title'),
            $pesan,
            'aktivitas_penting',
            $path
        );
    }

    /**
     * Notifikasi peringatan
     */
    public function peringatan(array $userIds, string $pesan, ?string $path = null): Collection
    {
        return $this->buat(
            $userIds,
            __('messages.notification_warning_title'),
            $pesan,
            'peringatan',
            $path
        );
    }
}
