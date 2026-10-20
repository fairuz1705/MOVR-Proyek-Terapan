<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\TryOnSesi;
use App\Services\TryOn\Providers\MockTryOnProvider;
use App\Jobs\ProcessTryOnJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TryOnTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'db_apk_main');
        Config::set('database.connections.mysql.host', '127.0.0.1');
        Config::set('database.connections.mysql.username', 'root');
        Config::set('database.connections.mysql.password', '');
    }

    protected function getOrCreateSupplierId(): int
    {
        $supplier = Supplier::first();
        if (!$supplier) {
            $supplier = Supplier::create([
                'nama_supplier' => 'Supplier Test',
                'kontak' => '08123456789',
            ]);
        }
        return $supplier->supplier_id;
    }

    protected function getOrCreateKategoriId(): int
    {
        $kategori = Kategori::first();
        if (!$kategori) {
            $kategori = Kategori::create([
                'nama_kategori' => 'Pakaian Olahraga',
                'slug' => 'pakaian-olahraga-' . rand(1000, 9999),
                'is_active' => true,
            ]);
        }
        return $kategori->kategori_id;
    }

    public function test_guest_cannot_access_tryon_upload()
    {
        $response = $this->postJson(route('tryon.upload'), [
            'foto' => UploadedFile::fake()->create('human.jpg', 100, 'image/jpeg'),
            'consent' => true,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_upload_photo_with_consent()
    {
        Storage::fake('private');
        $user = Pengguna::create([
            'nama_pengguna' => 'Buyer Test',
            'username' => 'buyertest_' . rand(10000, 99999),
            'email' => 'buyer' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('tryon.upload'), [
            'foto' => UploadedFile::fake()->create('human.jpg', 100, 'image/jpeg'),
            'consent' => true,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['sesi_id']);
        $this->assertDatabaseHas('tryon_sesi', ['pengguna_id' => $user->pengguna_id]);
    }

    public function test_upload_without_consent_is_rejected()
    {
        Storage::fake('private');
        $user = Pengguna::create([
            'nama_pengguna' => 'Buyer Test 2',
            'username' => 'buyertest2_' . rand(10000, 99999),
            'email' => 'buyer2' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('tryon.upload'), [
            'foto' => UploadedFile::fake()->create('human.jpg', 100, 'image/jpeg'),
            'consent' => false,
        ]);

        $response->assertStatus(422);
    }

    public function test_search_products_only_returns_active_published_clothing()
    {
        $user = Pengguna::create([
            'nama_pengguna' => 'Buyer Test 3',
            'username' => 'buyertest3_' . rand(10000, 99999),
            'email' => 'buyer3' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $supplierId = $this->getOrCreateSupplierId();
        $kategoriId = $this->getOrCreateKategoriId();

        $top = Produk::create([
            'supplier_id' => $supplierId,
            'kategori_id' => $kategoriId,
            'nama_produk' => 'Baju Polo Olahraga Test ' . rand(100, 999),
            'slug' => 'baju-polo-olahraga-test-' . rand(10000, 99999),
            'deskripsi' => 'Deskripsi baju polo',
            'harga_dasar' => 150000,
            'is_active' => true,
            'status_publish' => 'publish',
            'tryon_tipe' => 'top',
        ]);

        $unsupported = Produk::create([
            'supplier_id' => $supplierId,
            'kategori_id' => $kategoriId,
            'nama_produk' => 'Aksesoris Topi Test ' . rand(100, 999),
            'slug' => 'aksesoris-topi-test-' . rand(10000, 99999),
            'deskripsi' => 'Deskripsi topi',
            'harga_dasar' => 50000,
            'is_active' => true,
            'status_publish' => 'publish',
            'tryon_tipe' => 'unsupported',
        ]);

        $response = $this->actingAs($user)->getJson(route('tryon.products', ['q' => 'Baju Polo Olahraga Test']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $ids = collect($data)->pluck('produk_id')->all();

        $this->assertContains($top->produk_id, $ids);
        $this->assertNotContains($unsupported->produk_id, $ids);
    }

    public function test_user_cannot_access_other_user_tryon_result()
    {
        $user1 = Pengguna::create([
            'nama_pengguna' => 'User One',
            'username' => 'userone_' . rand(10000, 99999),
            'email' => 'userone' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $user2 = Pengguna::create([
            'nama_pengguna' => 'User Two',
            'username' => 'usertwo_' . rand(10000, 99999),
            'email' => 'usertwo' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $sesi = TryOnSesi::create([
            'pengguna_id' => $user1->pengguna_id,
            'foto_user_path' => 'tryon/photos/test.webp',
            'produk_id' => 1,
            'status' => 'done',
            'foto_hasil_path' => 'tryon/results/result.webp',
            'consent_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($user2)->get(route('tryon.result', $sesi->id));
        $response->assertStatus(404);
    }

    public function test_process_tryon_job_executes_with_mock_driver()
    {
        Storage::fake('private');
        $user = Pengguna::create([
            'nama_pengguna' => 'Job Tester',
            'username' => 'jobtester_' . rand(10000, 99999),
            'email' => 'jobtester' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $supplierId = $this->getOrCreateSupplierId();
        $kategoriId = $this->getOrCreateKategoriId();

        $produk = Produk::create([
            'supplier_id' => $supplierId,
            'kategori_id' => $kategoriId,
            'nama_produk' => 'Jersey Lari MOVR Test ' . rand(100, 999),
            'slug' => 'jersey-lari-movr-test-' . rand(10000, 99999),
            'harga_dasar' => 200000,
            'is_active' => true,
            'status_publish' => 'publish',
            'tryon_tipe' => 'top',
        ]);

        Storage::disk('private')->put('tryon/photos/user.jpg', 'dummy image content');

        $sesi = TryOnSesi::create([
            'pengguna_id' => $user->pengguna_id,
            'foto_user_path' => 'tryon/photos/user.jpg',
            'produk_id' => $produk->produk_id,
            'status' => 'pending',
            'consent_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        $job = new ProcessTryOnJob($sesi->id);
        $provider = new MockTryOnProvider();
        $job->handle($provider);

        $sesi->refresh();
        $this->assertEquals('done', $sesi->status);
        $this->assertNotNull($sesi->foto_hasil_path);
    }

    public function test_cleanup_command_deletes_expired_sessions()
    {
        Storage::fake('private');
        $user = Pengguna::create([
            'nama_pengguna' => 'Cleanup Tester',
            'username' => 'cleanuptester_' . rand(10000, 99999),
            'email' => 'cleanuptester' . rand(10000, 99999) . '@example.com',
            'no_telepon' => '08123456' . rand(1000, 9999),
            'sandi' => bcrypt('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $expired = TryOnSesi::create([
            'pengguna_id' => $user->pengguna_id,
            'foto_user_path' => 'tryon/photos/old.webp',
            'produk_id' => 1,
            'status' => 'done',
            'consent_at' => now()->subDays(2),
            'expires_at' => now()->subHours(1),
        ]);

        Artisan::call('tryon:cleanup');

        $this->assertDatabaseMissing('tryon_sesi', ['id' => $expired->id]);
    }
}
