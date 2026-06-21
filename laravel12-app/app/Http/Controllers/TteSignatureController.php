<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\TteSignature;
use App\Models\TteLog;
use App\Services\TteSignatureService;
use App\Enums\SuratKeluarStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TteSignatureController extends Controller
{
    public function __construct(
        private TteSignatureService $tteService
    ) {}

    /**
     * Halaman upload tanda tangan untuk Admin TU
     */
    public function uploadSignaturePage()
    {
        Gate::authorize('tte.upload');

        $signature = $this->tteService->getActiveSignature(Auth::id());

        return view('tte.upload-signature', compact('signature'));
    }

    /**
     * Proses upload tanda tangan
     */
    public function uploadSignature(Request $request)
    {
        Gate::authorize('tte.upload');

        $request->validate([
            'signature_image' => 'required|image|mimes:png|max:2048', // Max 2MB
        ], [
            'signature_image.mimes' => 'File tanda tangan harus berformat PNG.',
            'signature_image.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $signature = $this->tteService->uploadSignature(
                $request->file('signature_image'),
                Auth::id()
            );

            return redirect()->route('tte.upload-signature')
                ->with('success', 'Tanda tangan berhasil diupload dan dienkripsi.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupload tanda tangan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman TTE untuk Pimpinan (pratinjau PDF dan tanda tangan)
     */
    public function signPage(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.ttd');

        // Pastikan surat dalam status Siap Tandatangan
        if ($suratKeluar->status !== SuratKeluarStatus::SiapTandatangan) {
            abort(403, 'Surat ini tidak dapat ditandatangani. Status saat ini: ' . $suratKeluar->status->label());
        }

        // Cek apakah pimpinan sudah punya signature
        $hasSignature = $this->tteService->getActiveSignature($suratKeluar->signed_by ?? Auth::id()) !== null;

        // Cari PDF lampiran
        $pdfLampiran = $suratKeluar->lampiran()
            ->where('mime_type', 'application/pdf')
            ->latest()
            ->first();

        if (!$pdfLampiran) {
            abort(404, 'PDF surat tidak ditemukan.');
        }

        return view('tte.sign', compact('suratKeluar', 'pdfLampiran', 'hasSignature'));
    }

    /**
     * Proses tanda tangan elektronik
     */
    public function signDocument(Request $request, SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.ttd');

        // Validasi
        $request->validate([
            'position_x' => 'required|integer|min:0',
            'position_y' => 'required|integer|min:0',
            'scale' => 'required|numeric|min:0.1|max:5|step:0.1',
        ], [
            'position_x.required' => 'Posisi horizontal (X) wajib diisi.',
            'position_y.required' => 'Posisi vertikal (Y) wajib diisi.',
            'scale.required' => 'Skala gambar wajib diisi.',
            'scale.min' => 'Skala minimal 0.1.',
            'scale.max' => 'Skala maksimal 5.',
        ]);

        // Pastikan surat dalam status Siap Tandatangan
        if ($suratKeluar->status !== SuratKeluarStatus::SiapTandatangan) {
            return response()->json([
                'success' => false,
                'message' => 'Surat ini tidak dapat ditandatangani. Status saat ini: ' . $suratKeluar->status->label(),
            ], 403);
        }

        try {
            $result = $this->tteService->signDocument(
                $suratKeluar,
                (int) $request->position_x,
                (int) $request->position_y,
                (float) $request->scale
            );

            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil ditandatangani.',
                'hash' => $result['hash'],
                'redirect' => route('surat-keluar.show', $suratKeluar),
            ]);

        } catch (\Exception $e) {
            // Notifikasi error ke Pimpinan dan TU akan dikirim via event/notification
            \Log::error('TTE Sign Failed', [
                'surat_keluar_id' => $suratKeluar->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandatangani surat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan preview URL untuk PDF
     */
    public function getPdfPreview(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.ttd');

        $pdfLampiran = $suratKeluar->lampiran()
            ->where('mime_type', 'application/pdf')
            ->latest()
            ->first();

        if (!$pdfLampiran || !file_exists($pdfLampiran->full_path)) {
            abort(404, 'PDF tidak ditemukan.');
        }

        return response()->file($pdfLampiran->full_path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfLampiran->original_name . '"',
        ]);
    }

    /**
     * Lihat log TTE untuk surat tertentu
     */
    public function viewLogs(SuratKeluar $suratKeluar)
    {
        Gate::authorize('surat_keluar.view.all');

        $logs = TteLog::where('surat_keluar_id', $suratKeluar->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('tte.logs', compact('suratKeluar', 'logs'));
    }

    /**
     * Verifikasi hash file PDF
     */
    public function verifyHash(Request $request)
    {
        Gate::authorize('tte.verify');

        $request->validate([
            'pdf_path' => 'required|string',
            'expected_hash' => 'required|string|size:64',
        ]);

        $fullPath = storage_path('app/' . $request->pdf_path);
        $isValid = $this->tteService->verifyHash($fullPath, $request->expected_hash);

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'message' => $isValid ? 'Hash valid. File tidak dimodifikasi.' : 'Hash tidak cocok. File mungkin telah dimodifikasi.',
        ]);
    }

    /**
     * Hapus tanda tangan user (hanya untuk admin)
     */
    public function deleteSignature($signatureId)
    {
        Gate::authorize('tte.manage');

        $signature = TteSignature::findOrFail($signatureId);
        
        // Hapus file encrypted
        if (file_exists($signature->encrypted_path)) {
            unlink($signature->encrypted_path);
        }
        
        $signature->delete();

        return redirect()->back()
            ->with('success', 'Tanda tangan berhasil dihapus.');
    }
}
