<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Disposisi;
use App\Models\User;
use App\Notifications\SiapSmkNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckDisposisiDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'disposisi:check-deadlines';

    /**
     * The console command description.
     */
    protected $description = 'Check disposisi deadlines and send notifications for H-1 and overdue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('Starting disposisi deadline check...');
        
        $now = Carbon::now();
        $tomorrow = $now->copy()->addDay();
        
        // Get today's date at end of day for comparison
        $todayEnd = $now->copy()->endOfDay();
        
        $countH1 = 0;
        $countOverdue = 0;
        
        // Check for H-1 (batas_waktu = tomorrow)
        $h1Disposisis = Disposisi::whereNotNull('batas_waktu')
            ->whereDate('batas_waktu', $tomorrow->toDateString())
            ->whereNotIn('status', ['Selesai', 'Diteruskan'])
            ->with(['penerima', 'suratMasuk', 'suratKeluar'])
            ->get();
        
        foreach ($h1Disposisis as $disposisi) {
            $user = $disposisi->penerima;
            
            if (!$user) continue;
            
            $suratInfo = $disposisi->suratMasuk ?? $disposisi->suratKeluar;
            $suratType = $disposisi->suratMasuk ? 'Surat Masuk' : 'Surat Keluar';
            
            $notificationData = [
                'type' => 'batas_waktu_h1',
                'message' => "Disposisi untuk {$suratType} No. {$suratInfo->no_surut ?? 'N/A'} jatuh tempo besok ({$disposisi->batas_waktu->format('d M Y')}).",
                'action_url' => route('disposisi.show', $disposisi->id),
                'disposisi_id' => $disposisi->id,
            ];
            
            try {
                $user->notify(new SiapSmkNotification('batas_waktu_h1', $notificationData));
                $countH1++;
                
                Log::info("Sent H-1 notification to user {$user->id} for disposisi {$disposisi->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send H-1 notification: {$e->getMessage()}");
            }
        }
        
        // Check for overdue (batas_waktu < now AND status != Selesai)
        $overdueDisposisis = Disposisi::whereNotNull('batas_waktu')
            ->where('batas_waktu', '<', $now)
            ->whereNotIn('status', ['Selesai', 'Diteruskan'])
            ->with(['penerima', 'suratMasuk', 'suratKeluar'])
            ->get();
        
        foreach ($overdueDisposisis as $disposisi) {
            $user = $disposisi->penerima;
            
            if (!$user) continue;
            
            $suratInfo = $disposisi->suratMasuk ?? $disposisi->suratKeluar;
            $suratType = $disposisi->suratMasuk ? 'Surat Masuk' : 'Surat Keluar';
            
            $daysOverdue = $now->diffInDays($disposisi->batas_waktu, false);
            
            $notificationData = [
                'type' => 'overdue',
                'message' => "URGENT: Disposisi untuk {$suratType} No. {$suratInfo->no_surut ?? 'N/A'} sudah melewati batas waktu sejak {$daysOverdue} hari yang lalu.",
                'action_url' => route('disposisi.show', $disposisi->id),
                'disposisi_id' => $disposisi->id,
            ];
            
            try {
                $user->notify(new SiapSmkNotification('overdue', $notificationData));
                $countOverdue++;
                
                Log::info("Sent overdue notification to user {$user->id} for disposisi {$disposisi->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send overdue notification: {$e->getMessage()}");
            }
        }
        
        $this->info("Deadline check completed.");
        $this->info("H-1 notifications sent: {$countH1}");
        $this->info("Overdue notifications sent: {$countOverdue}");
        
        Log::info("Deadline check completed. H-1: {$countH1}, Overdue: {$countOverdue}");
        
        return Command::SUCCESS;
    }
}
