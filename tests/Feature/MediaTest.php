<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->service = app(MediaService::class);
    }

    #[Test]
    public function user_can_upload_file(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $media = $this->service->upload($file, $this->user->id, 'profil', 'Foto profil');

        $this->assertEquals($this->user->id, $media->user_id);
        $this->assertEquals('profil', $media->kategori);
        $this->assertEquals('Foto profil', $media->deskripsi);
        $this->assertDatabaseHas('m_media', [
            'user_id' => $this->user->id,
            'kategori' => 'profil',
        ]);
    }

    #[Test]
    public function upload_file_requires_valid_category(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->service->upload($file, $this->user->id, 'invalid_category');

        $this->assertEquals('lainnya', $media->kategori);
    }

    #[Test]
    public function user_can_download_own_file(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $this->service->upload($file, $this->user->id, 'dokumen');

        $response = $this->service->download($media, $this->user->id);

        $this->assertNotNull($response);
    }

    #[Test]
    public function user_cannot_download_others_file(): void
    {
        $otherUser = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $this->service->upload($file, $otherUser->id, 'dokumen');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.media_permission_error'));

        $this->service->download($media, $this->user->id);
    }

    #[Test]
    public function user_can_delete_own_file(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $this->service->upload($file, $this->user->id, 'dokumen');

        $mediaId = $media->id;

        $result = $this->service->delete($media, $this->user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('m_media', ['id' => $mediaId]);
    }

    #[Test]
    public function user_cannot_delete_others_file(): void
    {
        $otherUser = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $this->service->upload($file, $otherUser->id, 'dokumen');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.media_permission_error'));

        $this->service->delete($media, $this->user->id);
    }

    #[Test]
    public function path_traversal_attempt_blocked(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.media_traversal_error'));

        // Try to access parent directory via path traversal
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('verifyPathTraversal');
        $method->setAccessible(true);

        $method->invoke($this->service, 'uploads/user_1/../../../etc/passwd');
    }

    #[Test]
    public function user_can_list_own_files(): void
    {
        $this->actingAs($this->user);

        UploadedFile::fake()->image('test1.jpg')->store('uploads/user_' . $this->user->id . '/profil', 'public');
        UploadedFile::fake()->image('test2.jpg')->store('uploads/user_' . $this->user->id . '/profil', 'public');

        $this->service->upload(UploadedFile::fake()->image('test1.jpg'), $this->user->id, 'profil');
        $this->service->upload(UploadedFile::fake()->image('test2.jpg'), $this->user->id, 'profil');

        $media = $this->service->ambilUntukUser($this->user->id);

        $this->assertGreaterThanOrEqual(2, $media->total());
    }

    #[Test]
    public function file_category_filter_works(): void
    {
        $this->actingAs($this->user);

        $this->service->upload(UploadedFile::fake()->image('logo.png'), $this->user->id, 'logo');
        $this->service->upload(UploadedFile::fake()->create('doc.pdf'), $this->user->id, 'dokumen');

        $logoFiles = $this->service->ambilBerdasarkanKategori('logo');
        $allFiles = Media::all();

        $this->assertTrue($logoFiles->total() >= 1);
    }

    #[Test]
    public function total_user_file_size_calculated_correctly(): void
    {
        $this->actingAs($this->user);

        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->create('doc2.pdf', 200);

        $this->service->upload($file1, $this->user->id, 'dokumen');
        $this->service->upload($file2, $this->user->id, 'dokumen');

        $totalSize = $this->service->totalUkuranUser($this->user->id);

        $this->assertGreaterThanOrEqual(300, $totalSize);
    }

    #[Test]
    public function image_detection_works(): void
    {
        $this->actingAs($this->user);

        $imageFile = UploadedFile::fake()->image('test.jpg');
        $media = $this->service->upload($imageFile, $this->user->id, 'profil');

        $this->assertTrue($media->isImage());
    }

    #[Test]
    public function document_detection_works(): void
    {
        $this->actingAs($this->user);

        $docFile = UploadedFile::fake()->create('document.pdf', 100);
        $media = $this->service->upload($docFile, $this->user->id, 'dokumen');

        $this->assertTrue($media->isDocument());
    }
}
