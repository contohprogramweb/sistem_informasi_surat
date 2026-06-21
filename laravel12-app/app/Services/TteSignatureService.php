<?php

namespace App\Services;

use App\Models\TteSignature;
use App\Models\TteLog;
use App\Models\SuratKeluar;
use App\Models\Lampiran;
use App\Enums\SuratKeluarStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class TteSignatureService
{
    /**
     * Upload tanda tangan gambar (PNG transparan) untuk user
     * File akan dienkripsi sebelum disimpan
     */
    public function uploadSignature($file, $userId = null): TteSignature
    {
        $userId = $userId ?? Auth::id();
        
        // Validasi file
        if ($file->getClientOriginalExtension() !== 'png') {
            throw new Exception('File tanda tangan harus berformat PNG.');
        }

        // Baca konten file
        $fileContent = file_get_contents($file->getRealPath());
        
        // Encode ke base64 untuk enkripsi
        $encodedContent = base64_encode($fileContent);
        
        // Enkripsi menggunakan AES-256 dengan APP_KEY
        $encryptedContent = Crypt::encryptString($encodedContent);
        
        // Generate nama file unik
        $filename = 'signature_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(16)) . '.enc';
        $path = storage_path('app/tte_signatures/' . $filename);
        
        // Pastikan direktori ada
        if (!Storage::disk('local')->exists('tte_signatures')) {
            Storage::disk('local')->makeDirectory('tte_signatures');
        }
        
        // Simpan file terenkripsi
        file_put_contents($path, $encryptedContent);
        
        // Nonaktifkan signature lama
        TteSignature::where('user_id', $userId)->update(['is_active' => false]);
        
        // Buat record baru
        return TteSignature::create([
            'user_id' => $userId,
            'encrypted_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'is_active' => true,
        ]);
    }

    /**
     * Dapatkan signature aktif user
     */
    public function getActiveSignature($userId = null): ?TteSignature
    {
        $userId = $userId ?? Auth::id();
        
        return TteSignature::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Dekripsi dan dapatkan path file tanda tangan sementara
     */
    public function decryptSignature(TteSignature $signature): string
    {
        if (!file_exists($signature->encrypted_path)) {
            throw new Exception('File tanda tangan tidak ditemukan.');
        }

        $encryptedContent = file_get_contents($signature->encrypted_path);
        $decrypted = Crypt::decryptString($encryptedContent);
        
        // Decode base64
        $decodedContent = base64_decode($decrypted);
        
        // Buat file temporary
        $tempDir = storage_path('app/temp/tte');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $tempPath = $tempDir . '/' . uniqid() . '_' . $signature->original_filename;
        file_put_contents($tempPath, $decodedContent);
        
        return $tempPath;
    }

    /**
     * Proses Tanda Tangan Elektronik pada PDF
     * 
     * @param SuratKeluar $suratKeluar
     * @param int $positionX Posisi X dalam pixel dari kiri
     * @param int $positionY Posisi Y dalam pixel dari atas
     * @param float $scale Faktor skala gambar tanda tangan
     * @return array Result dengan hash dan path PDF final
     */
    public function signDocument(
        SuratKeluar $suratKeluar, 
        int $positionX, 
        int $positionY, 
        float $scale = 1.0
    ): array {
        return DB::transaction(function () use ($suratKeluar, $positionX, $positionY, $scale) {
            try {
                // Simpan status lama untuk rollback jika gagal
                $oldStatus = $suratKeluar->status;
                
                // Dapatkan signature aktif pimpinan
                $signature = $this->getActiveSignature($suratKeluar->signed_by ?? Auth::id());
                
                if (!$signature) {
                    throw new Exception('Tanda tangan digital tidak ditemukan. Silakan upload tanda tangan terlebih dahulu.');
                }

                if (!$signature->isValid()) {
                    throw new Exception('Tanda tangan digital tidak valid atau sudah kadaluarsa.');
                }

                // Dekripsi gambar tanda tangan
                $signaturePath = $this->decryptSignature($signature);
                
                // Cari PDF asli dari lampiran
                $pdfLampiran = Lampiran::where('attachable_type', SuratKeluar::class)
                    ->where('attachable_id', $suratKeluar->id)
                    ->where('mime_type', 'application/pdf')
                    ->latest()
                    ->first();

                if (!$pdfLampiran || !file_exists($pdfLampiran->full_path)) {
                    throw new Exception('PDF surat tidak ditemukan.');
                }

                // Load library FPDI/FPDF untuk manipulasi PDF
                $pdf = $this->applySignatureToPdf(
                    $pdfLampiran->full_path,
                    $signaturePath,
                    $positionX,
                    $positionY,
                    $scale
                );

                // Simpan PDF hasil tanda tangan
                $finalFilename = 'signed_' . $suratKeluar->nomor_surat_final . '_' . time() . '.pdf';
                $finalPath = storage_path('app/surats_keluar/signed/' . $finalFilename);
                
                if (!Storage::disk('local')->exists('surats_keluar/signed')) {
                    Storage::disk('local')->makeDirectory('surats_keluar/signed');
                }
                
                file_put_contents($finalPath, $pdf);

                // Hitung SHA-256 hash
                $hash = hash_file('sha256', $finalPath);

                // Set password owner untuk mencegah modifikasi
                $this->setPdfOwnerPassword($finalPath);

                // Buat lampiran baru untuk PDF yang sudah ditandatangani
                $newLampiran = Lampiran::create([
                    'attachable_type' => SuratKeluar::class,
                    'attachable_id' => $suratKeluar->id,
                    'filename' => 'surats_keluar/signed/' . $finalFilename,
                    'original_name' => 'Surat_Tertandatangani_' . $suratKeluar->nomor_surat_final . '.pdf',
                    'hash' => $hash,
                    'mime_type' => 'application/pdf',
                    'file_size' => filesize($finalPath),
                ]);

                // Update status surat
                $suratKeluar->status = SuratKeluarStatus::Tertandatangani;
                $suratKeluar->save();

                // Catat log TTE
                $tteLog = TteLog::create([
                    'user_id' => Auth::id(),
                    'surat_keluar_id' => $suratKeluar->id,
                    'hash_file' => $hash,
                    'pdf_path' => $newLampiran->filename,
                    'position_x' => $positionX,
                    'position_y' => $positionY,
                    'scale' => $scale,
                    'ip_address' => request()->ip(),
                    'success' => true,
                ]);

                // Hapus file temporary signature
                if (file_exists($signaturePath)) {
                    unlink($signaturePath);
                }

                Log::info('TTE berhasil', [
                    'surat_keluar_id' => $suratKeluar->id,
                    'user_id' => Auth::id(),
                    'hash' => $hash,
                ]);

                return [
                    'success' => true,
                    'hash' => $hash,
                    'pdf_path' => $newLampiran->filename,
                    'log_id' => $tteLog->id,
                ];

            } catch (Exception $e) {
                // Rollback status ke Siap Tandatangan jika gagal
                $suratKeluar->status = SuratKeluarStatus::SiapTandatangan;
                $suratKeluar->save();

                // Catat log error
                TteLog::create([
                    'user_id' => Auth::id(),
                    'surat_keluar_id' => $suratKeluar->id,
                    'hash_file' => '',
                    'pdf_path' => '',
                    'error_message' => $e->getMessage(),
                    'success' => false,
                ]);

                Log::error('TTE gagal', [
                    'surat_keluar_id' => $suratKeluar->id,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Apply signature image to PDF using FPDI
     */
    private function applySignatureToPdf(string $pdfPath, string $signaturePath, int $x, int $y, float $scale): string
    {
        // Cek apakah setasign/FPDI tersedia
        if (!class_exists('\setasign\Fpdi\Fpdi')) {
            // Fallback: Gunakan approach sederhana dengan TCPDF atau library lain
            // Untuk sekarang, throw exception jika library tidak ada
            throw new Exception('Library FPDI tidak terinstall. Silakan install dengan: composer require setasign/fpdi');
        }

        $pdf = new \setasign\Fpdi\Fpdi();
        
        // Load PDF sumber
        $pageCount = $pdf->setSourceFile($pdfPath);
        
        // Import semua halaman
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // Tambahkan tanda tangan hanya di halaman terakhir
            if ($pageNo === $pageCount) {
                // Konversi pixel ke mm (asumsi 96 DPI)
                $xMm = $x / 3.78;
                $yMm = $y / 3.78;
                
                // Load gambar signature
                $imgSize = getimagesize($signaturePath);
                $imgWidthMm = ($imgSize[0] / 3.78) * $scale;
                $imgHeightMm = ($imgSize[1] / 3.78) * $scale;
                
                $pdf->Image($signaturePath, $xMm, $yMm, $imgWidthMm, $imgHeightMm, 'PNG');
            }
        }
        
        return $pdf->Output('S');
    }

    /**
     * Set owner password pada PDF untuk mencegah modifikasi
     */
    private function setPdfOwnerPassword(string $pdfPath): void
    {
        // Jika menggunakan library yang mendukung proteksi PDF
        // Implementasi tergantung library yang digunakan
        // Contoh dengan setasign\Fpdi tidak support proteksi langsung
        // Perlu library tambahan seperti setasign\FpdiProtection
        
        if (class_exists('\setasign\FpdiProtection\FpdiProtection')) {
            $pdf = new \setasign\FpdiProtection\FpdiProtection();
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                
                $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
            
            // Set permissions - hanya boleh print, tidak boleh modify
            $pdf->setRights(array_merge(
                \setasign\FpdiProtection\FpdiProtection::PERM_PRINT,
                \setasign\FpdiProtection\FpdiProtection::PERM_COPY
            ));
            
            // Set owner password (gunakan kombinasi APP_KEY + timestamp)
            $ownerPassword = substr(hash('sha256', config('app.key') . time()), 0, 16);
            
            $pdf->setProtection(['print'], null, $ownerPassword);
            
            $protectedContent = $pdf->Output('S');
            file_put_contents($pdfPath, $protectedContent);
        }
    }

    /**
     * Verifikasi hash file PDF
     */
    public function verifyHash(string $pdfPath, string $expectedHash): bool
    {
        if (!file_exists($pdfPath)) {
            return false;
        }
        
        $actualHash = hash_file('sha256', $pdfPath);
        return $actualHash === $expectedHash;
    }

    /**
     * Cleanup file temporary secara berkala
     */
    public function cleanupTempFiles(): void
    {
        $tempDir = storage_path('app/temp/tte');
        if (file_exists($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                // Hapus file yang lebih tua dari 1 jam
                if (filemtime($file) < time() - 3600) {
                    unlink($file);
                }
            }
        }
    }
}
