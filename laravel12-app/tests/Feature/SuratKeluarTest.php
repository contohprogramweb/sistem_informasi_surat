<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Unit;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiArsip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'role' => 'admin',
            'unit_id' => Unit::factory()->create()->id,
        ]);
        
        $this->klasifikasi = KlasifikasiArsip::factory()->create();
    }

    public function test_admin_can_view_surat_keluar_list(): void
    {
        $surat = SuratKeluar::factory()->create([
            'unit_id' => $this->user->unit_id,
            'klasifikasi_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('surat-keluar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.index');
        $response->assertSee($surat->nomor_surat);
    }

    public function test_admin_can_create_surat_keluar(): void
    {
        $unit = Unit::factory()->create();
        $klasifikasi = KlasifikasiArsip::factory()->create();

        $data = [
            'nomor_surat' => 'SK/001/2024',
            'tanggal_surat' => '2024-01-15',
            'tujuan' => 'Kepala Dinas Pendidikan',
            'perihal' => 'Undangan Rapat Koordinasi',
            'unit_id' => $unit->id,
            'klasifikasi_id' => $klasifikasi->id,
        ];

        $response = $this->actingAs($this->user)->post(route('surat-keluar.store'), $data);

        $response->assertRedirect(route('surat-keluar.index'));
        $this->assertDatabaseHas('surat_keluar', [
            'nomor_surat' => 'SK/001/2024',
            'tujuan' => 'Kepala Dinas Pendidikan',
        ]);
    }

    public function test_validation_required_for_surat_keluar_creation(): void
    {
        $response = $this->actingAs($this->user)->post(route('surat-keluar.store'), []);

        $response->assertSessionHasErrors(['nomor_surat', 'tanggal_surat', 'tujuan', 'perihal', 'unit_id', 'klasifikasi_id']);
    }

    public function test_admin_can_view_surat_keluar_detail(): void
    {
        $surat = SuratKeluar::factory()->create([
            'unit_id' => $this->user->unit_id,
            'klasifikasi_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('surat-keluar.show', $surat->id));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.show');
        $response->assertSee($surat->nomor_surat);
        $response->assertSee($surat->tujuan);
    }

    public function test_admin_can_edit_surat_keluar(): void
    {
        $surat = SuratKeluar::factory()->create([
            'unit_id' => $this->user->unit_id,
            'klasifikasi_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('surat-keluar.edit', $surat->id));

        $response->assertStatus(200);
        $response->assertViewIs('surat-keluar.edit');
        $response->assertSee($surat->nomor_surat);
    }

    public function test_admin_can_update_surat_keluar(): void
    {
        $surat = SuratKeluar::factory()->create([
            'unit_id' => $this->user->unit_id,
            'klasifikasi_id' => $this->klasifikasi->id,
        ]);

        $data = [
            'nomor_surat' => 'SK/002/2024',
            'tanggal_surat' => '2024-01-20',
            'tujuan' => 'Kepala Badan Kepegawaian',
            'perihal' => 'Permohonan Data Pegawai',
            'unit_id' => $surat->unit_id,
            'klasifikasi_id' => $surat->klasifikasi_id,
        ];

        $response = $this->actingAs($this->user)->put(route('surat-keluar.update', $surat->id), $data);

        $response->assertRedirect(route('surat-keluar.show', $surat->id));
        $this->assertDatabaseHas('surat_keluar', [
            'id' => $surat->id,
            'nomor_surat' => 'SK/002/2024',
            'tujuan' => 'Kepala Badan Kepegawaian',
        ]);
    }

    public function test_admin_can_delete_surat_keluar(): void
    {
        $surat = SuratKeluar::factory()->create([
            'unit_id' => $this->user->unit_id,
            'klasifikasi_id' => $this->klasifikasi->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('surat-keluar.destroy', $surat->id));

        $response->assertRedirect(route('surat-keluar.index'));
        $this->assertDatabaseMissing('surat_keluar', ['id' => $surat->id]);
    }

    public function test_unauthorized_user_cannot_delete_surat_keluar(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $surat = SuratKeluar::factory()->create();

        $response = $this->actingAs($user)->delete(route('surat-keluar.destroy', $surat->id));

        $response->assertStatus(403);
    }
}
