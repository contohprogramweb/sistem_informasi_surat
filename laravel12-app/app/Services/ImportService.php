<?php

namespace App\Services;

use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ImportService
{
    /**
     * Proses upload file dan preview data
     */
    public function preview(UploadedFile $file, string $type): array
    {
        $data = [];
        $headers = [];
        
        try {
            if ($file->getClientOriginalExtension() === 'csv') {
                $content = file_get_contents($file->getRealPath());
                $lines = str_getcsv($content, "\n");
                
                if (count($lines) > 0) {
                    $headers = str_getcsv($lines[0]);
                    // Ambil 10 baris pertama untuk preview
                    for ($i = 1; $i < min(count($lines), 11); $i++) {
                        $rowData = str_getcsv($lines[$i]);
                        if (count($rowData) === count($headers)) {
                            $data[] = array_combine($headers, $rowData);
                        }
                    }
                }
            } else {
                // Excel file
                Excel::import(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                    public function array(array $array) {}
                }, $file);
                
                $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                    private $data = [];
                    public function array(array $array) { $this->data = $array; }
                    public function getData() { return $this->data; }
                }, $file)[0] ?? [];
                
                if (count($rows) > 0) {
                    $headers = $rows[0];
                    for ($i = 1; $i < min(count($rows), 11); $i++) {
                        if (count($rows[$i]) === count($headers)) {
                            $data[] = array_combine($headers, $rows[$i]);
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'headers' => $headers,
                'preview' => $data,
                'total_rows' => max(0, count($data) > 0 ? count($data) : 0),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Gagal membaca file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validasi dan import data
     */
    public function import(UploadedFile $file, string $type, array $columnMapping = []): ImportBatch
    {
        DB::beginTransaction();
        
        try {
            $batch = ImportBatch::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'filename' => $file->getClientOriginalName(),
                'total_rows' => 0,
                'status' => 'processing',
            ]);

            $errors = [];
            $successCount = 0;
            $failedCount = 0;
            $rowCount = 0;

            // Baca file baris per baris
            if ($file->getClientOriginalExtension() === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                $headers = fgetcsv($handle);
                
                while (($row = fgetcsv($handle)) !== false) {
                    $rowCount++;
                    $rowData = array_combine($headers, $row);
                    
                    $result = $this->validateAndSave($type, $rowData, $columnMapping);
                    
                    if ($result['valid']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                        $errors[] = [
                            'row' => $rowCount + 1,
                            'errors' => $result['errors'],
                            'data' => $rowData,
                        ];
                    }
                }
                fclose($handle);
            } else {
                // Excel processing
                $rows = Excel::toArray([], $file)[0] ?? [];
                $headers = array_shift($rows);
                
                foreach ($rows as $index => $row) {
                    $rowCount++;
                    $rowData = array_combine($headers, $row);
                    
                    $result = $this->validateAndSave($type, $rowData, $columnMapping);
                    
                    if ($result['valid']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                        $errors[] = [
                            'row' => $index + 2, // +2 karena header dan 0-index
                            'errors' => $result['errors'],
                            'data' => $rowData,
                        ];
                    }
                }
            }

            $batch->update([
                'total_rows' => $rowCount,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'status' => $failedCount > 0 ? 'completed' : 'completed',
            ]);

            DB::commit();
            
            return $batch;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validasi dan simpan berdasarkan tipe
     */
    private function validateAndSave(string $type, array $data, array $columnMapping): array
    {
        $rules = [];
        $messages = [];

        switch ($type) {
            case 'surat_masuk':
                $rules = [
                    'nomor_surat' => 'required|string|max:100',
                    'tanggal_surat' => 'required|date',
                    'perihal' => 'required|string|max:500',
                    'pengirim' => 'required|string|max:200',
                ];
                $messages = [
                    'nomor_surat.required' => 'Nomor surat wajib diisi',
                    'tanggal_surat.required' => 'Tanggal surat wajib diisi',
                    'perihal.required' => 'Perihal wajib diisi',
                    'pengirim.required' => 'Pengirim wajib diisi',
                ];
                break;

            case 'surat_keluar':
                $rules = [
                    'nomor_surat' => 'required|string|max:100',
                    'tanggal_surat' => 'required|date',
                    'perihal' => 'required|string|max:500',
                    'tujuan' => 'required|string|max:200',
                ];
                break;

            case 'users':
                $rules = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'role' => 'required|in:pimpinan,kabag,staff_tu,admin',
                ];
                break;

            default:
                return ['valid' => false, 'errors' => ['Tipe import tidak dikenali']];
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        // Jika valid, simpan data (partial mapping)
        try {
            $this->saveData($type, $data);
            return ['valid' => true];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Gagal menyimpan: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Simpan data berdasarkan tipe
     */
    private function saveData(string $type, array $data): void
    {
        switch ($type) {
            case 'surat_masuk':
                \App\Models\SuratMasuk::create([
                    'nomor_surat' => $data['nomor_surat'],
                    'tanggal_surat' => $data['tanggal_surat'],
                    'perihal' => $data['perihal'],
                    'pengirim' => $data['pengirim'],
                    // Add more fields as needed
                ]);
                break;

            case 'surat_keluar':
                \App\Models\SuratKeluar::create([
                    'nomor_surat' => $data['nomor_surat'],
                    'tanggal_surat' => $data['tanggal_surat'],
                    'perihal' => $data['perihal'],
                    'tujuan' => $data['tujuan'],
                ]);
                break;

            case 'users':
                \App\Models\User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('changeme123'), // Default password
                    'role' => $data['role'],
                ]);
                break;
        }
    }
}
