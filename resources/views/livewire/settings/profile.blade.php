<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public array $opsiAgama = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
    ];

    public array $opsiStatusPerkawinan = [
        'Belum Kawin',
        'Kawin',
        'Cerai Hidup',
        'Cerai Mati',
    ];

    public array $opsiPekerjaan = [
        'Belum/Tidak Bekerja',
        'Mengurus Rumah Tangga',
        'Pelajar/Mahasiswa',
        'Pensiunan',
        'Pegawai Negeri Sipil',
        'Tentara Nasional Indonesia',
        'Kepolisian RI',
        'Perdagangan',
        'Petani/Pekebun',
        'Peternak',
        'Nelayan/Perikanan',
        'Industri',
        'Konstruksi',
        'Transportasi',
        'Karyawan Swasta',
        'Karyawan BUMN',
        'Karyawan BUMD',
        'Karyawan Honorer',
        'Buruh Harian Lepas',
        'Buruh Tani/Perkebunan',
        'Buruh Nelayan/Perikanan',
        'Buruh Peternakan',
        'Pembantu Rumah Tangga',
        'Tukang Cukur',
        'Tukang Listrik',
        'Tukang Batu',
        'Tukang Kayu',
        'Tukang Sol Sepatu',
        'Tukang Las/Pandai Besi',
        'Tukang Jahit',
        'Penata Rias',
        'Penata Busana',
        'Penata Rambut',
        'Mekanik',
        'Seniman',
        'Tabib',
        'Paraji',
        'Perancang Busana',
        'Penterjemah',
        'Imam Masjid',
        'Pendeta',
        'Pastor',
        'Wartawan',
        'Ustadz/Mubaligh',
        'Juru Masak',
        'Promotor Acara',
        'Anggota DPR-RI',
        'Anggota DPD',
        'Anggota BPK',
        'Presiden',
        'Wakil Presiden',
        'Anggota Mahkamah Konstitusi',
        'Anggota Kabinet/Kementerian',
        'Duta Besar',
        'Gubernur',
        'Wakil Gubernur',
        'Bupati',
        'Wakil Bupati',
        'Walikota',
        'Wakil Walikota',
        'Konsultan',
        'Dokter',
        'Bidan',
        'Perawat',
        'Apoteker',
        'Psikiater/Psikolog',
        'Penyiar Televisi',
        'Penyiar Radio',
        'Pilot',
        'Pengacara',
        'Notaris',
        'Arsitek',
        'Akuntan',
        'Dosen',
        'Guru',
        'Manajer',
        'Wiraswasta',
        'Lainnya',
    ];

    public string $name = '';
    public string $email = '';
    public ?string $nama_panggilan = null;
    public ?string $nomor_ktp = null;
    public ?string $tempat_lahir = null;
    public ?string $tanggal_lahir = null;
    public ?string $jenis_kelamin = null;
    public ?string $alamat_ktp = null;
    public ?string $rt = null;
    public ?string $rw = null;
    public ?string $kelurahan = null;
    public ?string $kecamatan = null;
    public ?string $kabupaten_kota = null;
    public ?string $provinsi = null;
    public ?string $agama = null;
    public ?string $status_perkawinan = null;
    public ?string $pekerjaan = null;
    public ?string $kewarganegaraan = null;
    public ?string $foto_profil_url = null;
    public ?string $foto_kamera_base64 = null;
    public $foto_upload = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->nama_panggilan = $user->nama_panggilan;
        $this->nomor_ktp = $user->nomor_ktp;
        $this->tempat_lahir = $user->tempat_lahir;
        $this->tanggal_lahir = $user->tanggal_lahir?->format('Y-m-d');
        $this->jenis_kelamin = $user->jenis_kelamin;
        $this->alamat_ktp = $user->alamat_ktp;
        $this->rt = $user->rt;
        $this->rw = $user->rw;
        $this->kelurahan = $user->kelurahan;
        $this->kecamatan = $user->kecamatan;
        $this->kabupaten_kota = $user->kabupaten_kota;
        $this->provinsi = $user->provinsi;
        $this->agama = $user->agama;
        $this->status_perkawinan = $user->status_perkawinan;
        $this->pekerjaan = $user->pekerjaan;
        $this->kewarganegaraan = $user->kewarganegaraan;

        if ($this->agama && ! in_array($this->agama, $this->opsiAgama, true)) {
            $this->opsiAgama[] = $this->agama;
        }

        if ($this->status_perkawinan && ! in_array($this->status_perkawinan, $this->opsiStatusPerkawinan, true)) {
            $this->opsiStatusPerkawinan[] = $this->status_perkawinan;
        }

        if ($this->pekerjaan && ! in_array($this->pekerjaan, $this->opsiPekerjaan, true)) {
            $this->opsiPekerjaan[] = $this->pekerjaan;
        }

        $this->sinkronFotoProfilPreview();
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'nama_panggilan' => ['nullable', 'string', 'max:100'],
            'nomor_ktp' => ['nullable', 'regex:/^[0-9]{8,30}$/', Rule::unique(User::class)->ignore($user->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat_ktp' => ['nullable', 'string', 'max:255'],
            'rt' => ['nullable', 'regex:/^[0-9]{1,3}$/'],
            'rw' => ['nullable', 'regex:/^[0-9]{1,3}$/'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'agama' => ['nullable', Rule::in($this->opsiAgama)],
            'status_perkawinan' => ['nullable', Rule::in($this->opsiStatusPerkawinan)],
            'pekerjaan' => ['nullable', Rule::in($this->opsiPekerjaan)],
            'kewarganegaraan' => ['nullable', 'string', 'max:30'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profil-detail-disimpan');
    }

    public function simpanFotoUpload(): void
    {
        $this->validate([
            'foto_upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $path = $this->foto_upload->store('profil', 'public');

        $this->simpanFotoProfilPath($path);
        $this->reset('foto_upload');
        $this->sinkronFotoProfilPreview();
        $this->dispatch('foto-upload-disimpan');
    }

    public function simpanFotoKamera(): void
    {
        $this->validate([
            'foto_kamera_base64' => ['required', 'string', 'starts_with:data:image/'],
        ]);

        $path = $this->simpanDariBase64($this->foto_kamera_base64);

        $this->simpanFotoProfilPath($path);
        $this->sinkronFotoProfilPreview();
        $this->dispatch('foto-kamera-disimpan');
    }

    private function simpanDariBase64(string $data): string
    {
        if (! preg_match('/^data:image\/(png|jpg|jpeg|webp);base64,/', $data, $match)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'foto_kamera_base64' => __('messages.profile_invalid_camera_photo'),
            ]);
        }

        $ekstensi = $match[1] === 'jpeg' ? 'jpg' : $match[1];
        $raw = preg_replace('/^data:image\/(png|jpg|jpeg|webp);base64,/', '', $data);
        $binary = base64_decode((string) $raw, true);

        if ($binary === false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'foto_kamera_base64' => __('messages.profile_invalid_camera_photo'),
            ]);
        }

        $path = 'profil/' . Auth::id() . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(8)) . '.' . $ekstensi;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function simpanFotoProfilPath(string $path): void
    {
        $user = Auth::user();
        $fotoLama = $user->foto_profil;

        $user->foto_profil = $path;
        $user->save();

        if (! empty($fotoLama) && $fotoLama !== $path && Storage::disk('public')->exists($fotoLama)) {
            Storage::disk('public')->delete($fotoLama);
        }
    }

    private function sinkronFotoProfilPreview(): void
    {
        $this->foto_profil_url = Auth::user()->fresh()->profile_photo_url;
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

@section('title', __('messages.profile'))

<section>
    @include('partials.settings-heading')

    <x-settings.layout :subheading="__('messages.settings_profile_subheading')">
        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.profile_detail_card_title') }}</h5>
                        <small class="text-muted">{{ __('messages.profile_detail_card_subtitle') }}</small>
                    </div>
                    <div class="card-body">
                        <form wire:submit="updateProfileInformation">
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('messages.name') }}</label>
                                <input type="text" id="name" wire:model="name" class="form-control" placeholder="{{ __('messages.enter_your_name') }}" required autofocus autocomplete="name">
                            </div>

                            <div class="mb-3">
                                <label for="nama_panggilan" class="form-label">{{ __('messages.nickname') }}</label>
                                <input type="text" id="nama_panggilan" wire:model="nama_panggilan" class="form-control" placeholder="{{ __('messages.nickname_placeholder') }}" maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="nomor_ktp" class="form-label">{{ __('messages.id_card_number') }}</label>
                                <input type="text" id="nomor_ktp" wire:model="nomor_ktp" class="form-control" placeholder="{{ __('messages.id_card_placeholder') }}" inputmode="numeric" maxlength="30" autocomplete="off">
                                <small class="text-muted">{{ __('messages.id_card_hint') }}</small>
                            </div>

                            <div class="row g-3 mb-3" x-data="wilayahBiodata($wire, {
                                provinsi: @js(route('api.wilayah.provinsi')),
                                kabupaten: @js(route('api.wilayah.kabupaten')),
                                kecamatan: @js(route('api.wilayah.kecamatan')),
                                kelurahan: @js(route('api.wilayah.kelurahan')),
                            }, {
                                provinsi: @js($provinsi),
                                kabupaten: @js($kabupaten_kota),
                                kecamatan: @js($kecamatan),
                                kelurahan: @js($kelurahan),
                            })" x-init="init()">
                                <div class="col-12 col-md-6">
                                    <label for="tempat_lahir" class="form-label">{{ __('messages.profile_birth_place') }}</label>
                                    <input type="text" id="tempat_lahir" wire:model="tempat_lahir" class="form-control" maxlength="100">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="tanggal_lahir" class="form-label">{{ __('messages.profile_birth_date') }}</label>
                                    <input type="date" id="tanggal_lahir" wire:model="tanggal_lahir" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="jenis_kelamin" class="form-label">{{ __('messages.profile_gender') }}</label>
                                    <select id="jenis_kelamin" wire:model="jenis_kelamin" class="form-select">
                                        <option value="">{{ __('messages.profile_select_gender') }}</option>
                                        <option value="L">{{ __('messages.profile_gender_male') }}</option>
                                        <option value="P">{{ __('messages.profile_gender_female') }}</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="agama" class="form-label">{{ __('messages.profile_religion') }}</label>
                                    <select id="agama" wire:model="agama" class="form-select">
                                        <option value="">Pilih agama</option>
                                        @foreach ($opsiAgama as $opsi)
                                            <option value="{{ $opsi }}">{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="alamat_ktp" class="form-label">{{ __('messages.profile_id_address') }}</label>
                                    <textarea id="alamat_ktp" wire:model="alamat_ktp" class="form-control" rows="2" maxlength="255"></textarea>
                                </div>

                                <div class="col-6 col-md-3">
                                    <label for="rt" class="form-label">{{ __('messages.profile_rt') }}</label>
                                    <input type="text" id="rt" wire:model="rt" class="form-control" inputmode="numeric" maxlength="3" autocomplete="off">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="rw" class="form-label">{{ __('messages.profile_rw') }}</label>
                                    <input type="text" id="rw" wire:model="rw" class="form-control" inputmode="numeric" maxlength="3" autocomplete="off">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="provinsi" class="form-label">{{ __('messages.profile_province') }}</label>
                                    <select id="provinsi" class="form-select" x-model="selectedProvinsiId" @change="onProvinsiChange()">
                                        <option value="">Pilih provinsi</option>
                                        <template x-for="item in provinsiList" :key="item.id">
                                            <option :value="item.id" x-text="item.nama"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="kabupaten_kota" class="form-label">{{ __('messages.profile_city_regency') }}</label>
                                    <select id="kabupaten_kota" class="form-select" x-model="selectedKabupatenId" @change="onKabupatenChange()" :disabled="!selectedProvinsiId || loadingKabupaten">
                                        <option value="">Pilih kabupaten / kota</option>
                                        <template x-for="item in kabupatenList" :key="item.id">
                                            <option :value="item.id" x-text="item.nama"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="kecamatan" class="form-label">{{ __('messages.profile_kecamatan') }}</label>
                                    <select id="kecamatan" class="form-select" x-model="selectedKecamatanId" @change="onKecamatanChange()" :disabled="!selectedKabupatenId || loadingKecamatan">
                                        <option value="">Pilih kecamatan</option>
                                        <template x-for="item in kecamatanList" :key="item.id">
                                            <option :value="item.id" x-text="item.nama"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="kelurahan" class="form-label">{{ __('messages.profile_kelurahan') }}</label>
                                    <select id="kelurahan" class="form-select" x-model="selectedKelurahanId" @change="onKelurahanChange()" :disabled="!selectedKecamatanId || loadingKelurahan">
                                        <option value="">Pilih kelurahan / desa</option>
                                        <template x-for="item in kelurahanList" :key="item.id">
                                            <option :value="item.id" x-text="item.nama"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="status_perkawinan" class="form-label">{{ __('messages.profile_marital_status') }}</label>
                                    <select id="status_perkawinan" wire:model="status_perkawinan" class="form-select">
                                        <option value="">Pilih status</option>
                                        @foreach ($opsiStatusPerkawinan as $opsi)
                                            <option value="{{ $opsi }}">{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="pekerjaan" class="form-label">{{ __('messages.profile_job') }}</label>
                                    <select id="pekerjaan" wire:model="pekerjaan" class="form-select">
                                        <option value="">Pilih pekerjaan</option>
                                        @foreach ($opsiPekerjaan as $opsi)
                                            <option value="{{ $opsi }}">{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="kewarganegaraan" class="form-label">{{ __('messages.profile_nationality') }}</label>
                                    <input type="text" id="kewarganegaraan" wire:model="kewarganegaraan" class="form-control text-uppercase" maxlength="30" placeholder="WNI / WNA" autocomplete="off">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('messages.email') }}</label>
                                <div class="input-group">
                                    <input type="email" id="email" wire:model="email" class="form-control" placeholder="{{ __('messages.enter_your_email') }}" required autocomplete="email">
                                    <span class="input-group-text">@example.com</span>
                                </div>

                                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                                    <div class="mt-3">
                                        <p class="text-warning mb-2">
                                            {{ __('messages.email_unverified') }}
                                            <a href="#" wire:click.prevent="resendVerificationNotification" class="text-info">{{ __('messages.click_resend_verification') }}</a>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="text-success mb-0">{{ __('messages.verification_link_sent_profile') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updateProfileInformation">
                                    <span wire:loading.remove wire:target="updateProfileInformation">{{ __('messages.save_changes') }}</span>
                                    <span wire:loading wire:target="updateProfileInformation" style="display:none">{{ __('messages.saving') }}</span>
                                </button>
                                <x-action-message on="profil-detail-disimpan">{{ __('messages.saved') }}</x-action-message>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.profile_photo_upload_title') }}</h5>
                        <small class="text-muted">{{ __('messages.profile_photo_upload_subtitle') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="{{ $foto_upload ? $foto_upload->temporaryUrl() : $foto_profil_url }}" alt="Foto Profil" class="rounded" style="width:88px;height:110px;object-fit:cover;border:1px solid #d9dee3;">
                            <div>
                                <div class="fw-semibold">{{ __('messages.profile_current_photo') }}</div>
                                <small class="text-muted">{{ __('messages.profile_photo_size_hint') }}</small>
                            </div>
                        </div>

                        <form wire:submit="simpanFotoUpload">
                            <div class="mb-3">
                                <input type="file" wire:model="foto_upload" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
                                @error('foto_upload') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpanFotoUpload">
                                    <span wire:loading.remove wire:target="simpanFotoUpload">{{ __('messages.profile_save_photo') }}</span>
                                    <span wire:loading wire:target="simpanFotoUpload" style="display:none">{{ __('messages.saving') }}</span>
                                </button>
                                <x-action-message on="foto-upload-disimpan">{{ __('messages.saved') }}</x-action-message>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card" x-data="kameraPasFoto($wire)">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('messages.profile_photo_camera_title') }}</h5>
                        <small class="text-muted">{{ __('messages.profile_photo_camera_subtitle') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="position-relative rounded overflow-hidden border mb-3" style="height:260px;background:#0f172a;">
                            <video x-ref="video" autoplay playsinline class="w-100 h-100" style="object-fit:cover;"></video>
                            <div class="position-absolute top-50 start-50 translate-middle" style="width:62%;height:78%;border:3px dashed #ffffff;box-shadow:0 0 0 9999px rgba(15,23,42,.35);border-radius:12px;"></div>
                            <div class="position-absolute" style="left:19%;right:19%;top:72%;border-top:2px solid rgba(255,255,255,.8);"></div>
                            <small class="position-absolute bottom-0 start-0 end-0 text-center text-white py-1" style="background:rgba(15,23,42,.65);font-size:.75rem;">{{ __('messages.profile_camera_frame_hint') }}</small>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary" @click="mulaiKamera()">{{ __('messages.profile_start_camera') }}</button>
                            <button type="button" class="btn btn-outline-secondary" @click="ambilFoto()">{{ __('messages.profile_take_photo') }}</button>
                            <button type="button" class="btn btn-outline-danger" @click="hentikanKamera()">{{ __('messages.profile_stop_camera') }}</button>
                        </div>

                        <template x-if="fotoData">
                            <div class="mb-3">
                                <div class="fw-semibold mb-2">{{ __('messages.profile_camera_preview') }}</div>
                                <img :src="fotoData" alt="Preview Kamera" class="rounded" style="width:88px;height:110px;object-fit:cover;border:1px solid #d9dee3;">
                            </div>
                        </template>

                        <canvas x-ref="canvas" class="d-none"></canvas>

                        <form wire:submit="simpanFotoKamera">
                            <input type="hidden" wire:model="foto_kamera_base64">
                            @error('foto_kamera_base64') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpanFotoKamera">
                                    <span wire:loading.remove wire:target="simpanFotoKamera">{{ __('messages.profile_use_camera_photo') }}</span>
                                    <span wire:loading wire:target="simpanFotoKamera" style="display:none">{{ __('messages.saving') }}</span>
                                </button>
                                <x-action-message on="foto-kamera-disimpan">{{ __('messages.saved') }}</x-action-message>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

<script>
  function kameraPasFoto(wire) {
    return {
      stream: null,
      fotoData: '',

      async mulaiKamera() {
        try {
          this.hentikanKamera();
          this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
          this.$refs.video.srcObject = this.stream;
          await this.$refs.video.play();
        } catch (_e) {
          // Abaikan agar UI tetap berjalan jika kamera ditolak browser.
        }
      },

      hentikanKamera() {
        if (!this.stream) return;
        this.stream.getTracks().forEach((track) => track.stop());
        this.stream = null;
      },

      ambilFoto() {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;

        if (!video || video.videoWidth === 0 || video.videoHeight === 0) {
          return;
        }

        const sumberLebar = video.videoWidth;
        const sumberTinggi = video.videoHeight;

        const cropLebar = sumberLebar * 0.62;
        const cropTinggi = sumberTinggi * 0.78;
        const cropX = (sumberLebar - cropLebar) / 2;
        const cropY = sumberTinggi * 0.10;

        canvas.width = 480;
        canvas.height = 600;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, cropX, cropY, cropLebar, cropTinggi, 0, 0, canvas.width, canvas.height);

        this.fotoData = canvas.toDataURL('image/jpeg', 0.92);
        wire.set('foto_kamera_base64', this.fotoData);
      },
    }
  }

    function initProfilBiodataHelpers() {
        const hanyaAngka = (selector, maxLength) => {
            const elemen = document.getElementById(selector);
            if (!elemen || elemen.dataset.helperAktif === '1') return;

            elemen.addEventListener('input', () => {
                elemen.value = (elemen.value || '').replace(/\D/g, '').slice(0, maxLength);
            });

            elemen.dataset.helperAktif = '1';
        };

        hanyaAngka('nomor_ktp', 30);
        hanyaAngka('rt', 3);
        hanyaAngka('rw', 3);

        ['rt', 'rw'].forEach((id) => {
            const elemen = document.getElementById(id);
            if (!elemen || elemen.dataset.paddingAktif === '1') return;

            elemen.addEventListener('blur', () => {
                if (elemen.value === '') return;
                elemen.value = elemen.value.padStart(3, '0').slice(-3);
            });

            elemen.dataset.paddingAktif = '1';
        });

        const kewarganegaraan = document.getElementById('kewarganegaraan');
        if (kewarganegaraan && kewarganegaraan.dataset.helperAktif !== '1') {
            kewarganegaraan.addEventListener('input', () => {
                kewarganegaraan.value = (kewarganegaraan.value || '')
                    .toUpperCase()
                    .replace(/[^A-Z/\s]/g, '')
                    .slice(0, 30);
            });

            kewarganegaraan.dataset.helperAktif = '1';
        }
    }

    function wilayahBiodata(wire, endpoint, initial) {
        return {
            provinsiList: [],
            kabupatenList: [],
            kecamatanList: [],
            kelurahanList: [],
            selectedProvinsiId: '',
            selectedKabupatenId: '',
            selectedKecamatanId: '',
            selectedKelurahanId: '',
            loadingKabupaten: false,
            loadingKecamatan: false,
            loadingKelurahan: false,

            async init() {
                await this.loadProvinsi();

                if (initial?.provinsi) {
                    const prov = this.provinsiList.find((x) => (x.nama || '').toLowerCase() === String(initial.provinsi).toLowerCase());
                    if (prov) {
                        this.selectedProvinsiId = prov.id;
                        wire.set('provinsi', prov.nama);
                        await this.loadKabupaten();
                    }
                }

                if (initial?.kabupaten) {
                    const kab = this.kabupatenList.find((x) => (x.nama || '').toLowerCase() === String(initial.kabupaten).toLowerCase());
                    if (kab) {
                        this.selectedKabupatenId = kab.id;
                        wire.set('kabupaten_kota', kab.nama);
                        await this.loadKecamatan();
                    }
                }

                if (initial?.kecamatan) {
                    const kec = this.kecamatanList.find((x) => (x.nama || '').toLowerCase() === String(initial.kecamatan).toLowerCase());
                    if (kec) {
                        this.selectedKecamatanId = kec.id;
                        wire.set('kecamatan', kec.nama);
                        await this.loadKelurahan();
                    }
                }

                if (initial?.kelurahan) {
                    const kel = this.kelurahanList.find((x) => (x.nama || '').toLowerCase() === String(initial.kelurahan).toLowerCase());
                    if (kel) {
                        this.selectedKelurahanId = kel.id;
                        wire.set('kelurahan', kel.nama);
                    }
                }
            },

            async loadProvinsi() {
                try {
                    const response = await fetch(endpoint.provinsi, { headers: { Accept: 'application/json' } });
                    const json = await response.json();
                    this.provinsiList = Array.isArray(json?.data) ? json.data : [];
                } catch (_e) {
                    this.provinsiList = [];
                }
            },

            async loadKabupaten() {
                if (!this.selectedProvinsiId) {
                    this.kabupatenList = [];
                    return;
                }

                this.loadingKabupaten = true;

                try {
                    const url = `${endpoint.kabupaten}?provinsi_id=${encodeURIComponent(this.selectedProvinsiId)}`;
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const json = await response.json();
                    this.kabupatenList = Array.isArray(json?.data) ? json.data : [];
                } catch (_e) {
                    this.kabupatenList = [];
                } finally {
                    this.loadingKabupaten = false;
                }
            },

            async loadKecamatan() {
                if (!this.selectedKabupatenId) {
                    this.kecamatanList = [];
                    return;
                }

                this.loadingKecamatan = true;

                try {
                    const url = `${endpoint.kecamatan}?kabupaten_id=${encodeURIComponent(this.selectedKabupatenId)}`;
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const json = await response.json();
                    this.kecamatanList = Array.isArray(json?.data) ? json.data : [];
                } catch (_e) {
                    this.kecamatanList = [];
                } finally {
                    this.loadingKecamatan = false;
                }
            },

            async loadKelurahan() {
                if (!this.selectedKecamatanId) {
                    this.kelurahanList = [];
                    return;
                }

                this.loadingKelurahan = true;

                try {
                    const url = `${endpoint.kelurahan}?kecamatan_id=${encodeURIComponent(this.selectedKecamatanId)}`;
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const json = await response.json();
                    this.kelurahanList = Array.isArray(json?.data) ? json.data : [];
                } catch (_e) {
                    this.kelurahanList = [];
                } finally {
                    this.loadingKelurahan = false;
                }
            },

            async onProvinsiChange() {
                const prov = this.provinsiList.find((x) => x.id === this.selectedProvinsiId);
                wire.set('provinsi', prov?.nama || null, false);

                this.selectedKabupatenId = '';
                this.selectedKecamatanId = '';
                this.selectedKelurahanId = '';
                this.kecamatanList = [];
                this.kelurahanList = [];
                wire.set('kabupaten_kota', null, false);
                wire.set('kecamatan', null, false);
                wire.set('kelurahan', null, false);

                await this.loadKabupaten();
            },

            async onKabupatenChange() {
                const kab = this.kabupatenList.find((x) => x.id === this.selectedKabupatenId);
                wire.set('kabupaten_kota', kab?.nama || null, false);

                this.selectedKecamatanId = '';
                this.selectedKelurahanId = '';
                this.kelurahanList = [];
                wire.set('kecamatan', null, false);
                wire.set('kelurahan', null, false);

                await this.loadKecamatan();
            },

            async onKecamatanChange() {
                const kec = this.kecamatanList.find((x) => x.id === this.selectedKecamatanId);
                wire.set('kecamatan', kec?.nama || null, false);

                this.selectedKelurahanId = '';
                wire.set('kelurahan', null, false);

                await this.loadKelurahan();
            },

            onKelurahanChange() {
                const kel = this.kelurahanList.find((x) => x.id === this.selectedKelurahanId);
                wire.set('kelurahan', kel?.nama || null, false);
            },
        }
    }

    document.addEventListener('DOMContentLoaded', initProfilBiodataHelpers);
    document.addEventListener('livewire:navigated', initProfilBiodataHelpers);
</script>
