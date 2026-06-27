<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\SifatSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratMasukTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->unit = Unit::factory()->create();
        $this->user = User::factory()->create(['unit_id' => $this->unit->id]);
    }

    public function test_user_can_view_surat_masuk_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('surat-masuk.index'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.index');
    }

    public function test_user_can_create_surat_masuk(): void
    {
        $klasifikasi = KlasifikasiArsip::factory()->create();
        $sifat = SifatSurat::factory()->create();
        $unitTujuan = Unit::factory()->create();

        $data = [
            'tanggal_terima' => now()->format('Y-m-d'),
            'cara_terima' => 'Langsung',
            'penerima_fisik' => 'John Doe',
            'nomor_surat' => '123/TEST/2024',
            'tanggal_surat' => now()->format('Y-m-d'),
            'pengirim' => 'Instansi Pengirim',
            'perihal' => 'Test Perihal Surat',
            'ringkasan' => 'Test Ringkasan',
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
            'prioritas' => 'Normal',
            'unit_tujuan' => [$unitTujuan->id],
            'tidak_perlu_disposisi' => false,
        ];

        $response = $this->actingAs($this->user)->post(route('surat-masuk.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => '123/TEST/2024',
            'pengirim' => 'Instansi Pengirim',
        ]);
    }

    public function test_user_can_view_surat_masuk_detail(): void
    {
        $klasifikasi = KlasifikasiArsip::factory()->create();
        $sifat = SifatSurat::factory()->create();
        $unitTujuan = Unit::factory()->create();

        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
        ]);

        $suratMasuk->unitTujuan()->attach([$unitTujuan->id]);

        $response = $this->actingAs($this->user)->get(route('surat-masuk.show', $suratMasuk->id));

        $response->assertStatus(200);
        $response->assertViewIs('surat-masuk.show');
        $response->assertViewHas('suratMasuk', $suratMasuk);
    }

    public function test_user_can_update_surat_masuk(): void
    {
        $klasifikasi = KlasifikasiArsip::factory()->create();
        $sifat = SifatSurat::factory()->create();
        $unitTujuan = Unit::factory()->create();

        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
            'perihal' => 'Perihal Lama',
        ]);

        $suratMasuk->unitTujuan()->attach([$unitTujuan->id]);

        $data = [
            'tanggal_terima' => now()->format('Y-m-d'),
            'cara_terima' => 'Email',
            'penerima_fisik' => 'Jane Doe',
            'nomor_surat' => $suratMasuk->nomor_surat,
            'tanggal_surat' => now()->format('Y-m-d'),
            'pengirim' => $suratMasuk->pengirim,
            'perihal' => 'Perihal Baru',
            'ringkasan' => $suratMasuk->ringkasan,
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
            'prioritas' => $suratMasuk->prioritas,
            'unit_tujuan' => [$unitTujuan->id],
        ];

        $response = $this->actingAs($this->user)->put(route('surat-masuk.update', $suratMasuk->id), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('surat_masuk', [
            'id' => $suratMasuk->id,
            'perihal' => 'Perihal Baru',
            'cara_terima' => 'Email',
        ]);
    }

    public function test_user_can_delete_surat_masuk(): void
    {
        $klasifikasi = KlasifikasiArsip::factory()->create();
        $sifat = SifatSurat::factory()->create();

        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->user)->delete(route('surat-masuk.destroy', $suratMasuk->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertSoftDeleted('surat_masuk', ['id' => $suratMasuk->id]);
    }

    public function test_user_can_archive_surat_masuk(): void
    {
        $klasifikasi = KlasifikasiArsip::factory()->create();
        $sifat = SifatSurat::factory()->create();

        $suratMasuk = SuratMasuk::factory()->create([
            'klasifikasi_id' => $klasifikasi->id,
            'sifat_id' => $sifat->id,
            'status' => 'Aktif',
            'tidak_perlu_disposisi' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('surat-masuk.archive', $suratMasuk->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('surat_masuk', [
            'id' => $suratMasuk->id,
            'status' => 'Diarsipkan',
        ]);
    }

    public function test_cannot_archive_surat_with_pending_disposisi(): void
    {
        // This test requires Disposisi factory and model
        $this->markTestIncomplete('Requires Disposisi factory setup');
    }
}
