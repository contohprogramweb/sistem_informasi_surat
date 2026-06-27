<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Unit;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'unit_id' => Unit::factory()->create(['nama_unit' => 'Unit Admin'])->id,
        ]);
        
        $this->unitTujuan = Unit::factory()->create(['nama_unit' => 'Unit Tujuan']);
        $this->suratMasuk = SuratMasuk::factory()->create();
    }

    public function test_admin_can_create_disposisi(): void
    {
        $data = [
            'surat_masuk_id' => $this->suratMasuk->id,
            'unit_tujuan_id' => $this->unitTujuan->id,
            'instruksi' => 'Mohon segera diproses',
            'sifat' => 'Segera',
            'catatan' => 'Prioritas tinggi',
        ];

        $response = $this->actingAs($this->admin)->post(route('disposisi.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('disposisis', [
            'surat_masuk_id' => $this->suratMasuk->id,
            'unit_tujuan_id' => $this->unitTujuan->id,
            'sifat' => 'Segera',
        ]);
    }

    public function test_validation_required_for_disposisi(): void
    {
        $response = $this->actingAs($this->admin)->post(route('disposisi.store'), []);

        $response->assertSessionHasErrors(['surat_masuk_id', 'unit_tujuan_id', 'instruksi']);
    }

    public function test_only_admin_can_create_disposisi(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        
        $data = [
            'surat_masuk_id' => $this->suratMasuk->id,
            'unit_tujuan_id' => $this->unitTujuan->id,
            'instruksi' => 'Test disposisi',
            'sifat' => 'Biasa',
        ];

        $response = $this->actingAs($staff)->post(route('disposisi.store'), $data);

        $response->assertStatus(403);
    }

    public function test_disposisi_has_correct_relationships(): void
    {
        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->suratMasuk->id,
            'unit_tujuan_id' => $this->unitTujuan->id,
        ]);

        $this->assertEquals($this->suratMasuk->id, $disposisi->suratMasuk->id);
        $this->assertEquals($this->unitTujuan->id, $disposisi->unitTujuan->id);
    }
}
