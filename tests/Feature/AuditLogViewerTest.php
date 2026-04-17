<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create sample logs
        LogAktivitas::create([
            'user_id' => $this->user->id,
            'modul' => 'user',
            'aktivitas' => 'Membuat user baru',
            'url' => '/admin/users',
            'metode' => 'POST',
            'ip_address' => '192.168.1.1',
            'metadata' => ['status_code' => 200],
        ]);

        LogAktivitas::create([
            'user_id' => $this->user->id,
            'modul' => 'media',
            'aktivitas' => 'Menghapus file',
            'url' => '/admin/media/1',
            'metode' => 'DELETE',
            'ip_address' => '192.168.1.1',
            'metadata' => ['status_code' => 204],
        ]);

        LogAktivitas::create([
            'user_id' => $this->user->id,
            'modul' => 'dashboard',
            'aktivitas' => 'Melihat dashboard',
            'url' => '/dashboard',
            'metode' => 'GET',
            'ip_address' => '10.0.0.5',
            'metadata' => ['status_code' => 200],
        ]);
    }

    #[Test]
    public function can_filter_logs(): void
    {
        $this->actingAs($this->user);

        // Basic assertion that filtering works
        $logsCount = LogAktivitas::byModul('user')->count();
        $this->assertGreaterThanOrEqual(1, $logsCount);
    }

    #[Test]
    public function filter_by_modul(): void
    {
        $logs = LogAktivitas::byModul('user')->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
        $this->assertTrue($logs->every(fn ($log) => $log->modul === 'user'));
    }

    #[Test]
    public function filter_by_user(): void
    {
        $logs = LogAktivitas::byUser($this->user->id)->get();

        $this->assertGreaterThanOrEqual(3, $logs->count());
        $this->assertTrue($logs->every(fn ($log) => $log->user_id === $this->user->id));
    }

    #[Test]
    public function filter_by_metode(): void
    {
        $logs = LogAktivitas::byMetode('POST')->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
        $this->assertTrue($logs->every(fn ($log) => $log->metode === 'POST'));
    }

    #[Test]
    public function filter_by_ip(): void
    {
        $logs = LogAktivitas::byIp('192.168.1.1')->get();

        $this->assertGreaterThanOrEqual(2, $logs->count());
        $this->assertTrue($logs->every(fn ($log) => str_contains($log->ip_address, '192.168.1.1')));
    }

    #[Test]
    public function filter_by_status_code(): void
    {
        $logs = LogAktivitas::byStatusCode(200)->get();

        $this->assertGreaterThanOrEqual(2, $logs->count());
    }

    #[Test]
    public function filter_by_date_range(): void
    {
        $fromDate = now()->subDays(1)->format('Y-m-d');
        $toDate = now()->format('Y-m-d');

        $logs = LogAktivitas::dateBetween($fromDate, $toDate)->get();

        $this->assertGreaterThanOrEqual(3, $logs->count());
    }

    #[Test]
    public function search_by_keyword(): void
    {
        $logs = LogAktivitas::search('user')->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
    }

    #[Test]
    public function search_by_aktivitas(): void
    {
        $logs = LogAktivitas::search('Membuat')->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
    }

    #[Test]
    public function get_modul_list(): void
    {
        $moduls = LogAktivitas::getModulList()->pluck('modul')->toArray();

        $this->assertContains('user', $moduls);
        $this->assertContains('media', $moduls);
        $this->assertContains('dashboard', $moduls);
    }

    #[Test]
    public function chaining_multiple_filters(): void
    {
        $logs = LogAktivitas::byModul('user')
            ->byUser($this->user->id)
            ->byMetode('POST')
            ->get();

        $this->assertGreaterThanOrEqual(1, $logs->count());
        $this->assertTrue($logs->every(function ($log) {
            return $log->modul === 'user' && $log->metode === 'POST';
        }));
    }

    #[Test]
    public function can_get_logs_ordered_by_latest(): void
    {
        $logs = LogAktivitas::latest()->get();

        $this->assertGreaterThanOrEqual(3, $logs->count());

        // Check that logs are in descending order by created_at
        $times = $logs->pluck('created_at')->toArray();
        $sortedTimes = collect($times)->sort(function ($a, $b) {
            return $b <=> $a;
        })->toArray();

        $this->assertEquals($times, $sortedTimes);
    }
}
