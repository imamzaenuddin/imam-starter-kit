<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    protected string $basePath = 'uploads';

    /**
     * Upload file dengan validasi path traversal
     */
    public function upload(
        UploadedFile $file,
        int $userId,
        string $kategori = 'lainnya',
        ?string $deskripsi = null
    ): Media {
        // Validasi kategori
        if (! in_array($kategori, ['logo', 'profil', 'dokumen', 'lainnya'])) {
            $kategori = 'lainnya';
        }

        // Generate nama file aman (prevent path traversal)
        $namaAsli = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $namaFile = $this->generateNamaFile($extension);

        // Path relatif aman
        $pathRelatif = $this->buildSafePath($userId, $kategori, $namaFile);

        // Store file
        $disk = 'public';
        Storage::disk($disk)->putFileAs(
            dirname($pathRelatif),
            $file,
            basename($pathRelatif)
        );

        // Create DB record
        $media = Media::create([
            'user_id' => $userId,
            'nama_asli' => $namaAsli,
            'nama_file' => $namaFile,
            'mime_type' => $file->getClientMimeType(),
            'ukuran_byte' => $file->getSize(),
            'kategori' => $kategori,
            'path_relatif' => $pathRelatif,
            'disk' => $disk,
            'deskripsi' => $deskripsi,
        ]);

        return $media;
    }

    /**
     * Download file dengan verifikasi izin
     */
    public function download(Media $media, int $userId)
    {
        // Verifikasi ownership
        if ($media->user_id !== $userId) {
            throw new \Exception(__('messages.media_permission_error'));
        }

        // Verifikasi file masih ada
        if (! $media->fileExists()) {
            throw new \Exception(__('messages.media_file_not_found'));
        }

        $filePath = $media->path_relatif;
        $fileName = $media->nama_asli;

        return response()->download(
            Storage::disk($media->disk)->path($filePath),
            $fileName
        );
    }

    /**
     * Delete file dari disk dan database
     */
    public function delete(Media $media, int $userId): bool
    {
        // Verifikasi ownership
        if ($media->user_id !== $userId) {
            throw new \Exception(__('messages.media_permission_error'));
        }

        // Delete dari disk
        if ($media->fileExists()) {
            Storage::disk($media->disk)->delete($media->path_relatif);
        }

        // Delete dari database
        return $media->delete();
    }

    /**
     * List file untuk user
     */
    public function ambilUntukUser(int $userId, ?string $kategori = null, int $perPage = 20)
    {
        $query = Media::untukUser($userId)->terbaru();

        if ($kategori && in_array($kategori, ['logo', 'profil', 'dokumen', 'lainnya'])) {
            $query->kategori($kategori);
        }

        return $query->paginate($perPage);
    }

    /**
     * List semua file by kategori
     */
    public function ambilBerdasarkanKategori(string $kategori, int $perPage = 20)
    {
        return Media::kategori($kategori)->terbaru()->paginate($perPage);
    }

    /**
     * List file terbaru untuk dashboard
     */
    public function ambilTerbaru(int $limit = 10)
    {
        return Media::terbaru()->limit($limit)->get();
    }

    /**
     * Hitung total ukuran file user
     */
    public function totalUkuranUser(int $userId): int
    {
        return Media::untukUser($userId)->sum('ukuran_byte') ?? 0;
    }

    /**
     * Cek quota file user (optional constraint)
     */
    public function tersediaQuota(int $userId, int $maxBytes = 104857600): bool
    {
        $maxBytes = $maxBytes ?? (100 * 1024 * 1024); // Default 100MB

        return $this->totalUkuranUser($userId) < $maxBytes;
    }

    /**
     * Delete old unused files (cleanup task)
     */
    public function hapusFileLama(int $hariSejak = 90): int
    {
        $tanggalCutoff = now()->subDays($hariSejak);
        $mediaLama = Media::where('created_at', '<', $tanggalCutoff)->get();

        $count = 0;
        foreach ($mediaLama as $media) {
            if ($media->fileExists()) {
                Storage::disk($media->disk)->delete($media->path_relatif);
            }
            $media->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Generate nama file unik dengan hash
     */
    protected function generateNamaFile(string $extension): string
    {
        return Str::random(32).'.'.$extension;
    }

    /**
     * Build path relatif yang aman (prevent path traversal)
     */
    protected function buildSafePath(int $userId, string $kategori, string $namaFile): string
    {
        // Sanitize inputs - prevent ../ dan ./ traversal
        $userId = (int) $userId; // Ensure integer
        $kategori = preg_replace('/[^a-z0-9_-]/', '', strtolower($kategori));
        $namaFile = basename($namaFile); // Strip any path components

        // Build safe path: uploads/user_{id}/{kategori}/namaFile
        $path = "{$this->basePath}/user_{$userId}/{$kategori}/{$namaFile}";

        // Verify path is within base directory (extra security)
        $this->verifyPathTraversal($path);

        return $path;
    }

    /**
     * Verifikasi path tidak ada traversal attempt
     */
    protected function verifyPathTraversal(string $path): void
    {
        // Ensure no ./ atau ../ attempts
        if (str_contains($path, '..') || str_contains($path, './')) {
            throw new \Exception(__('messages.media_traversal_error'));
        }

        // Additional check: realpath simulation
        $normalized = str_replace('\\', '/', $path);
        if (
            str_contains($normalized, '../') ||
            str_contains($normalized, '/..') ||
            Str::startsWith($normalized, '/')
        ) {
            throw new \Exception(__('messages.media_traversal_error'));
        }
    }

    /**
     * Get kategori tersedia
     */
    public static function kategoriTersedia(): array
    {
        return ['logo', 'profil', 'dokumen', 'lainnya'];
    }
}
