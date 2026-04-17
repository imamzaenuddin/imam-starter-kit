<?php

use App\Services\BackupRestoreService;
use App\Services\LogAktivitasService;
use App\Services\NotifikasiService;
use App\Services\PengaturanBackupService;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public ?TemporaryUploadedFile $fileRestore = null;
    public string $passwordRestore = '';
    public string $passwordKonfirmasi = '';
    public ?string $namaFileHapus = null;
    public bool $showModalHapus = false;

    public string $jadwalHarianTipe = 'transaksi';
    public string $jadwalHarianJam = '01:00';
    public string $jadwalMingguanTipe = 'full';
    public string $jadwalMingguanHari = 'sunday';
    public string $jadwalMingguanJam = '02:00';
    public int $retensiHari = 30;
    public bool $restoreAutoBackup = true;
    public string $restoreAutoBackupTipe = 'full';
    public int $restoreLockTimeoutDetik = 900;

    public string $setupTipeBackup = 'full';
    public string $setupPrefixNama = 'backup_manual';
    public int $setupRetensiHari = 30;
    public string $setupModeHasil = 'download';
    public string $setupTemplate = '';

    public function mount(): void
    {
        if (! auth()->user()?->bisaMenu('/admin/backup-restore', 'dapat_lihat')) {
            app(LogAktivitasService::class)->catatManual(
                __('messages.backup_restore_module_name'),
                __('messages.backup_restore_log_access_denied_open'),
                '/admin/backup-restore',
                ['aksi' => 'lihat_halaman', 'status' => 'ditolak']
            );
            abort(403);
        }

        $konfigurasi = app(PengaturanBackupService::class)->konfigurasiScheduler();
        $this->jadwalHarianTipe = $konfigurasi['jadwal_harian_tipe'];
        $this->jadwalHarianJam = $konfigurasi['jadwal_harian_jam'];
        $this->jadwalMingguanTipe = $konfigurasi['jadwal_mingguan_tipe'];
        $this->jadwalMingguanHari = $konfigurasi['jadwal_mingguan_hari'];
        $this->jadwalMingguanJam = $konfigurasi['jadwal_mingguan_jam'];
        $this->retensiHari = (int) $konfigurasi['retensi_hari'];
        $this->restoreAutoBackup = (bool) $konfigurasi['restore_auto_backup'];
        $this->restoreAutoBackupTipe = $konfigurasi['restore_auto_backup_tipe'];
        $this->restoreLockTimeoutDetik = (int) $konfigurasi['restore_lock_timeout_detik'];
    }

    public function simpanPengaturanScheduler(): void
    {
        $this->pastikanIzinAksiSensitif('restore', 'simpan_scheduler');

        $data = $this->validate([
            'jadwalHarianTipe' => 'required|string|in:full,transaksi,master',
            'jadwalHarianJam' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'jadwalMingguanTipe' => 'required|string|in:full,transaksi,master',
            'jadwalMingguanHari' => 'required|string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'jadwalMingguanJam' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'retensiHari' => 'required|integer|min:1|max:365',
            'restoreAutoBackup' => 'boolean',
            'restoreAutoBackupTipe' => 'required|string|in:full,transaksi,master',
            'restoreLockTimeoutDetik' => 'required|integer|min:60|max:86400',
        ]);

        app(PengaturanBackupService::class)->simpan([
            'jadwal_harian_tipe' => $data['jadwalHarianTipe'],
            'jadwal_harian_jam' => $data['jadwalHarianJam'],
            'jadwal_mingguan_tipe' => $data['jadwalMingguanTipe'],
            'jadwal_mingguan_hari' => $data['jadwalMingguanHari'],
            'jadwal_mingguan_jam' => $data['jadwalMingguanJam'],
            'retensi_hari' => $data['retensiHari'],
            'restore_auto_backup' => (bool) $data['restoreAutoBackup'],
            'restore_auto_backup_tipe' => $data['restoreAutoBackupTipe'],
            'restore_lock_timeout_detik' => $data['restoreLockTimeoutDetik'],
        ]);

        app(LogAktivitasService::class)->catatManual(
            __('messages.backup_restore_module_name'),
            __('messages.backup_restore_log_save_scheduler'),
            '/admin/backup-restore',
            [
                'jadwal_harian' => $data['jadwalHarianTipe'] . ' @ ' . $data['jadwalHarianJam'],
                'jadwal_mingguan' => $data['jadwalMingguanTipe'] . ' @ ' . $data['jadwalMingguanHari'] . ' ' . $data['jadwalMingguanJam'],
                'retensi_hari' => $data['retensiHari'],
            ]
        );

        session()->flash('sukses', __('messages.backup_scheduler_saved'));
    }

    public function backupFull()
    {
        return $this->prosesBackup('full');
    }

    public function backupTransaksi()
    {
        return $this->prosesBackup('transaksi');
    }

    public function backupMaster()
    {
        return $this->prosesBackup('master');
    }

    public function jalankanSetupBackup()
    {
        $this->pastikanIzinAksiSensitif('backup', 'setup_backup_manual');

        $data = $this->validate([
            'setupTipeBackup' => 'required|string|in:full,transaksi,master',
            'setupPrefixNama' => 'required|string|max:80',
            'setupRetensiHari' => 'required|integer|min:1|max:365',
            'setupModeHasil' => 'required|string|in:download,simpan',
        ]);

        $backup = app(BackupRestoreService::class)->buatBackupKustom($data['setupTipeBackup'], $data['setupPrefixNama']);
        $jumlahDihapus = app(BackupRestoreService::class)->hapusBackupKadaluarsa((int) $data['setupRetensiHari']);

        app(LogAktivitasService::class)->catatManual(
            __('messages.backup_restore_module_name'),
            __('messages.backup_restore_log_run_manual_setup', ['tipe' => strtoupper($data['setupTipeBackup'])]),
            '/admin/backup-restore',
            [
                'tipe' => strtoupper($data['setupTipeBackup']),
                'file' => $backup['nama_file'],
                'retensi_hari' => (int) $data['setupRetensiHari'],
                'dihapus_retensi' => $jumlahDihapus,
                'mode_hasil' => $data['setupModeHasil'],
            ]
        );

        $this->kirimNotifikasiBackupSelesai(strtoupper($data['setupTipeBackup']), $backup['nama_file']);

        if ($data['setupModeHasil'] === 'download') {
            return response()->download($backup['path']);
        }

        session()->flash('sukses', __('messages.backup_setup_success', [
            'file' => $backup['nama_file'],
            'jumlah' => $jumlahDihapus,
        ]));
    }

    public function terapkanTemplateSetupBackup(): void
    {
        $template = strtolower(trim($this->setupTemplate));

        if ($template === '') {
            return;
        }

        if ($template === 'cepat_harian') {
            $this->setupTipeBackup = 'transaksi';
            $this->setupPrefixNama = 'backup_harian';
            $this->setupRetensiHari = 14;
            $this->setupModeHasil = 'simpan';

            return;
        }

        if ($template === 'arsip_mingguan') {
            $this->setupTipeBackup = 'master';
            $this->setupPrefixNama = 'backup_mingguan';
            $this->setupRetensiHari = 60;
            $this->setupModeHasil = 'simpan';

            return;
        }

        if ($template === 'full_bulanan') {
            $this->setupTipeBackup = 'full';
            $this->setupPrefixNama = 'backup_bulanan';
            $this->setupRetensiHari = 180;
            $this->setupModeHasil = 'download';
        }
    }

    public function restoreDatabase(): void
    {
        $this->pastikanIzinAksiSensitif('restore', 'restore_database');

        $this->validate([
            'fileRestore' => 'required|file|mimes:zip,gz|max:' . ((int) config('app_runtime.batas_upload_kb', 102400)),
            'passwordRestore' => 'required|string|current_password',
        ]);

        try {
            $hasil = app(BackupRestoreService::class)->restoreAman($this->fileRestore);
            $jumlah = (int) ($hasil['jumlah_statement'] ?? 0);
            $backupSebelum = data_get($hasil, 'backup_sebelum.nama_file');

            app(LogAktivitasService::class)->catatManual(
                __('messages.backup_restore_module_name'),
                __('messages.backup_restore_log_run_restore'),
                '/admin/backup-restore',
                [
                    'file' => $this->fileRestore?->getClientOriginalName(),
                    'statement' => $jumlah,
                ]
            );

            $this->kirimNotifikasiRestoreSelesai($this->fileRestore?->getClientOriginalName() ?? '-');

            $this->reset(['fileRestore', 'passwordRestore']);

            $pesan = __('messages.backup_restore_success', ['jumlah' => $jumlah]);
            if ($backupSebelum) {
                $pesan .= ' ' . __('messages.backup_restore_safeguard', ['file' => $backupSebelum]);
            }

            session()->flash('sukses', $pesan);
        } catch (\Throwable $e) {
            $this->kirimNotifikasiRestoreGagal($e->getMessage());
            throw $e;
        }
    }

    public function with(): array
    {
        $izinAksi = [
            'backup' => $this->punyaIzinAksiSensitif('backup'),
            'restore' => $this->punyaIzinAksiSensitif('restore'),
            'hapus_backup' => $this->punyaIzinAksiSensitif('hapus_backup'),
        ];

        return [
            'riwayatBackup' => app(BackupRestoreService::class)->riwayatBackup(),
            'izinAksi' => $izinAksi,
        ];
    }

    public function bukaHapus(string $namaFile): void
    {
        $this->pastikanIzinAksiSensitif('hapus_backup', 'buka_hapus_backup');

        $this->namaFileHapus = $namaFile;
        $this->passwordKonfirmasi = '';
        $this->resetErrorBag();
        $this->showModalHapus = true;
    }

    public function hapusBackupTerpilih(): void
    {
        $this->pastikanIzinAksiSensitif('hapus_backup', 'hapus_backup');

        $this->validate([
            'passwordKonfirmasi' => 'required|string|current_password',
        ]);

        if (! $this->namaFileHapus) {
            throw new \RuntimeException(__('messages.backup_file_not_selected'));
        }

        app(BackupRestoreService::class)->hapusBackup($this->namaFileHapus);

        app(LogAktivitasService::class)->catatManual(
            __('messages.backup_restore_module_name'),
            __('messages.backup_restore_log_delete_file', ['file' => $this->namaFileHapus]),
            '/admin/backup-restore',
            ['file' => $this->namaFileHapus]
        );

        $this->showModalHapus = false;
        $this->namaFileHapus = null;
        $this->passwordKonfirmasi = '';
        session()->flash('sukses', __('messages.backup_delete_success'));
    }

    private function prosesBackup(string $tipe)
    {
        $this->pastikanIzinAksiSensitif('backup', 'backup_' . $tipe);

        $backup = app(BackupRestoreService::class)->buatBackup($tipe);

        app(LogAktivitasService::class)->catatManual(
            __('messages.backup_restore_module_name'),
            __('messages.backup_restore_log_create_backup_type', ['tipe' => strtoupper($tipe)]),
            '/admin/backup-restore',
            [
                'tipe' => strtoupper($tipe),
                'file' => $backup['nama_file'],
            ]
        );

        $this->kirimNotifikasiBackupSelesai(strtoupper($tipe), $backup['nama_file']);

        return response()->download($backup['path']);
    }

    private function daftarUserPenerimaNotifikasi(): array
    {
        $userId = auth()->id();

        return $userId ? [(int) $userId] : [];
    }

    private function kirimNotifikasiBackupSelesai(string $tipe, string $namaFile): void
    {
        $userIds = $this->daftarUserPenerimaNotifikasi();
        if (empty($userIds)) {
            return;
        }

        app(NotifikasiService::class)->backupSelesai($userIds, [
            'tipe' => $tipe,
            'ukuran' => $namaFile,
            'waktu' => now()->format('d/m/Y H:i'),
        ]);

        $this->dispatch('notifikasi:baru');
    }

    private function kirimNotifikasiRestoreSelesai(string $namaFile): void
    {
        $userIds = $this->daftarUserPenerimaNotifikasi();
        if (empty($userIds)) {
            return;
        }

        app(NotifikasiService::class)->restoreSelesai($userIds, [
            'berkas' => $namaFile,
        ]);

        $this->dispatch('notifikasi:baru');
    }

    private function kirimNotifikasiRestoreGagal(string $alasan): void
    {
        $userIds = $this->daftarUserPenerimaNotifikasi();
        if (empty($userIds)) {
            return;
        }

        app(NotifikasiService::class)->restoreGagal($userIds, [
            'alasan' => $alasan,
        ]);

        $this->dispatch('notifikasi:baru');
    }

    private function punyaIzinAksiSensitif(string $aksi): bool
    {
        return (bool) auth()->user()?->bisaAksiSensitif('/admin/backup-restore', $aksi);
    }

    private function pastikanIzinAksiSensitif(string $aksi, string $aktivitas): void
    {
        if ($this->punyaIzinAksiSensitif($aksi)) {
            return;
        }

        app(LogAktivitasService::class)->catatManual(
            __('messages.backup_restore_module_name'),
            __('messages.backup_restore_log_access_denied_sensitive_action', ['aktivitas' => $aktivitas]),
            '/admin/backup-restore',
            [
                'aksi' => $aksi,
                'aktivitas' => $aktivitas,
                'status' => 'ditolak',
            ]
        );

        abort(403);
    }
};
?>

@section('title', __('messages.backup_restore_page_title'))

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('messages.backup_restore_page_heading') }}</h4>
            <p class="text-muted mb-0">{{ __('messages.backup_restore_page_subheading') }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small me-1">{{ __('messages.backup_sensitive_permission_status') }}</span>
                <span class="badge {{ $izinAksi['backup'] ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ __('messages.backup_permission_backup') }}: {{ $izinAksi['backup'] ? __('messages.active') : __('messages.inactive') }}
                </span>
                <span class="badge {{ $izinAksi['restore'] ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ __('messages.backup_permission_restore') }}: {{ $izinAksi['restore'] ? __('messages.active') : __('messages.inactive') }}
                </span>
                <span class="badge {{ $izinAksi['hapus_backup'] ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ __('messages.backup_permission_delete') }}: {{ $izinAksi['hapus_backup'] ? __('messages.active') : __('messages.inactive') }}
                </span>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $izinAksi['backup'] && ! $izinAksi['restore'] && ! $izinAksi['hapus_backup'])
        <div class="alert alert-warning mb-4">
            {{ __('messages.backup_sensitive_permission_warning') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2"><i class="bx bx-data me-1"></i>{{ __('messages.backup_full_title') }}</h5>
                    <p class="text-muted mb-3">{{ __('messages.backup_full_desc') }}</p>
                    @if ($izinAksi['backup'])
                        <button class="btn btn-primary w-100" wire:click="backupFull" wire:loading.attr="disabled" wire:target="backupFull">
                            <span wire:loading.remove wire:target="backupFull">{{ __('messages.backup_full_download') }}</span>
                            <span wire:loading wire:target="backupFull" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    @else
                        <span class="text-muted small">{{ __('messages.backup_button_hidden_no_permission') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2"><i class="bx bx-transfer-alt me-1"></i>{{ __('messages.backup_transaksi_title') }}</h5>
                    <p class="text-muted mb-3">{{ __('messages.backup_transaksi_desc') }}</p>
                    @if ($izinAksi['backup'])
                        <button class="btn btn-outline-primary w-100" wire:click="backupTransaksi" wire:loading.attr="disabled" wire:target="backupTransaksi">
                            <span wire:loading.remove wire:target="backupTransaksi">{{ __('messages.backup_transaksi_download') }}</span>
                            <span wire:loading wire:target="backupTransaksi" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    @else
                        <span class="text-muted small">{{ __('messages.backup_button_hidden_no_permission') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2"><i class="bx bx-collection me-1"></i>{{ __('messages.backup_master_title') }}</h5>
                    <p class="text-muted mb-3">{{ __('messages.backup_master_desc') }}</p>
                    @if ($izinAksi['backup'])
                        <button class="btn btn-outline-secondary w-100" wire:click="backupMaster" wire:loading.attr="disabled" wire:target="backupMaster">
                            <span wire:loading.remove wire:target="backupMaster">{{ __('messages.backup_master_download') }}</span>
                            <span wire:loading wire:target="backupMaster" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    @else
                        <span class="text-muted small">{{ __('messages.backup_button_hidden_no_permission') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($izinAksi['backup'])
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.backup_manual_setup_title') }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit="jalankanSetupBackup">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.backup_quick_template') }}</label>
                        <select wire:model="setupTemplate" wire:change="terapkanTemplateSetupBackup" class="form-select">
                            <option value="">{{ __('messages.backup_template_custom') }}</option>
                            <option value="cepat_harian">{{ __('messages.backup_template_cepat_harian') }}</option>
                            <option value="arsip_mingguan">{{ __('messages.backup_template_arsip_mingguan') }}</option>
                            <option value="full_bulanan">{{ __('messages.backup_template_full_bulanan') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_type') }}</label>
                        <select wire:model="setupTipeBackup" class="form-select">
                            <option value="full">{{ __('messages.backup_type_label_full') }}</option>
                            <option value="transaksi">{{ __('messages.backup_type_label_transaksi') }}</option>
                            <option value="master">{{ __('messages.backup_type_label_master') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_filename_prefix') }}</label>
                        <input type="text" wire:model="setupPrefixNama" class="form-control" placeholder="{{ __('messages.backup_filename_prefix_placeholder') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.backup_retention_days') }}</label>
                        <input type="number" min="1" max="365" wire:model="setupRetensiHari" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.backup_result_mode') }}</label>
                        <select wire:model="setupModeHasil" class="form-select">
                            <option value="download">{{ __('messages.backup_result_download') }}</option>
                            <option value="simpan">{{ __('messages.backup_result_save_only') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled" wire:target="jalankanSetupBackup">
                            <span wire:loading.remove wire:target="jalankanSetupBackup">{{ __('messages.backup_run_setup') }}</span>
                            <span wire:loading wire:target="jalankanSetupBackup" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">{{ __('messages.backup_manual_setup_hint') }}</small>
            </form>
        </div>
    </div>
    @endif

    @if ($izinAksi['restore'])
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.backup_scheduler_settings_title') }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit="simpanPengaturanScheduler">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_daily_type') }}</label>
                        <select wire:model="jadwalHarianTipe" class="form-select">
                            <option value="transaksi">{{ __('messages.backup_type_label_transaksi') }}</option>
                            <option value="master">{{ __('messages.backup_type_label_master') }}</option>
                            <option value="full">{{ __('messages.backup_type_label_full') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_daily_time') }}</label>
                        <input type="time" wire:model="jadwalHarianJam" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_weekly_type') }}</label>
                        <select wire:model="jadwalMingguanTipe" class="form-select">
                            <option value="full">{{ __('messages.backup_type_label_full') }}</option>
                            <option value="transaksi">{{ __('messages.backup_type_label_transaksi') }}</option>
                            <option value="master">{{ __('messages.backup_type_label_master') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_weekly_day') }}</label>
                        <select wire:model="jadwalMingguanHari" class="form-select">
                            <option value="sunday">{{ __('messages.day_sunday') }}</option>
                            <option value="monday">{{ __('messages.day_monday') }}</option>
                            <option value="tuesday">{{ __('messages.day_tuesday') }}</option>
                            <option value="wednesday">{{ __('messages.day_wednesday') }}</option>
                            <option value="thursday">{{ __('messages.day_thursday') }}</option>
                            <option value="friday">{{ __('messages.day_friday') }}</option>
                            <option value="saturday">{{ __('messages.day_saturday') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_weekly_time') }}</label>
                        <input type="time" wire:model="jadwalMingguanJam" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_retention_days') }}</label>
                        <input type="number" min="1" max="365" wire:model="retensiHari" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_auto_before_restore') }}</label>
                        <select wire:model="restoreAutoBackup" class="form-select">
                            <option value="1">{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_auto_restore_type') }}</label>
                        <select wire:model="restoreAutoBackupTipe" class="form-select">
                            <option value="full">{{ __('messages.backup_type_label_full') }}</option>
                            <option value="transaksi">{{ __('messages.backup_type_label_transaksi') }}</option>
                            <option value="master">{{ __('messages.backup_type_label_master') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.backup_restore_lock_timeout_seconds') }}</label>
                        <input type="number" min="60" max="86400" wire:model="restoreLockTimeoutDetik" class="form-control">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpanPengaturanScheduler">
                            <span wire:loading.remove wire:target="simpanPengaturanScheduler">{{ __('messages.backup_save_scheduler_settings') }}</span>
                            <span wire:loading wire:target="simpanPengaturanScheduler" style="display:none">{{ __('messages.saving') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($izinAksi['restore'])
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.backup_restore_section_title') }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit="restoreDatabase">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('messages.backup_restore_file_label') }}</label>
                        <input type="file" wire:model="fileRestore" class="form-control" accept=".zip,.gz,.sql.gz">
                        <small class="text-muted">{{ __('messages.backup_restore_file_hint') }}</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ __('messages.backup_restore_password_label') }}</label>
                        <input type="password"
                               wire:model="passwordRestore"
                               class="form-control @error('passwordRestore') is-invalid @enderror"
                               autocomplete="current-password"
                               placeholder="{{ __('messages.backup_restore_password_placeholder') }}">
                        @error('passwordRestore') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-danger w-100" wire:loading.attr="disabled" wire:target="restoreDatabase,fileRestore">
                            <span wire:loading.remove wire:target="restoreDatabase">{{ __('messages.backup_restore_now') }}</span>
                            <span wire:loading wire:target="restoreDatabase" style="display:none">{{ __('messages.processing') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('messages.backup_history_latest') }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.backup_file_name') }}</th>
                        <th>{{ __('messages.backup_file_size') }}</th>
                        <th>{{ __('messages.time') }}</th>
                        <th class="text-center">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatBackup as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item['nama'] }}</td>
                            <td>{{ $item['ukuran'] }}</td>
                            <td>{{ $item['waktu'] }}</td>
                            <td class="text-center">
                                @if ($izinAksi['hapus_backup'])
                                    <button class="btn btn-sm btn-icon btn-text-danger"
                                            wire:click="bukaHapus('{{ $item['nama'] }}')"
                                            title="{{ __('messages.backup_delete_file_title') }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">{{ __('messages.backup_no_history') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModalHapus && $izinAksi['hapus_backup'])
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('messages.backup_delete_confirm_title') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModalHapus', false)"></button>
                    </div>
                    <form @submit.prevent="Swal.fire({
                                title: '{{ __('messages.confirm_delete') }}',
                                text: '{{ __('messages.backup_delete_confirm_prompt') }}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: '{{ __('messages.yes_delete') }}',
                                cancelButtonText: '{{ __('messages.cancel') }}',
                            }).then(r => r.isConfirmed && $wire.hapusBackupTerpilih())">
                        <div class="modal-body">
                            <p class="mb-3">{{ __('messages.backup_delete_confirm_text') }}: <strong>{{ $namaFileHapus }}</strong></p>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">{{ __('messages.backup_delete_password_label') }}</label>
                                <input type="password"
                                       wire:model="passwordKonfirmasi"
                                       class="form-control @error('passwordKonfirmasi') is-invalid @enderror"
                                       autocomplete="current-password"
                                       placeholder="{{ __('messages.backup_delete_password_placeholder') }}">
                                @error('passwordKonfirmasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModalHapus', false)">{{ __('messages.cancel') }}</button>
                            <button type="submit"
                                    class="btn btn-danger"
                                    wire:loading.attr="disabled"
                                    wire:target="hapusBackupTerpilih">
                                <span wire:loading.remove wire:target="hapusBackupTerpilih">{{ __('messages.backup_delete_now') }}</span>
                                <span wire:loading wire:target="hapusBackupTerpilih" style="display:none">{{ __('messages.processing') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
