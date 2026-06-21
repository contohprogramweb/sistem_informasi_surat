<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArsipRetensiService;
use Illuminate\Support\Facades\Log;

class ArsipRetentionScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arsip:retention-scheduler
                            {--permanent-delete : Execute permanent deletion of expired soft-deleted arsip}
                            {--send-notifications : Send retention notifications}
                            {--months-ahead=3 : Number of months ahead for notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scheduler untuk mengelola retensi arsip: hapus permanen dan kirim notifikasi';

    protected ArsipRetensiService $service;

    /**
     * Execute the console command.
     */
    public function handle(ArsipRetensiService $service): int
    {
        $this->service = $service;

        Log::channel('audit')->info('Arsip Retention Scheduler Started');

        if ($this->option('permanent-delete')) {
            $this->executePermanentDelete();
        }

        if ($this->option('send-notifications')) {
            $monthsAhead = (int) $this->option('months-ahead');
            $this->executeSendNotifications($monthsAhead);
        }

        // If no option specified, run both
        if (!$this->option('permanent-delete') && !$this->option('send-notifications')) {
            $this->executePermanentDelete();
            $this->executeSendNotifications(3);
        }

        Log::channel('audit')->info('Arsip Retention Scheduler Completed');

        return self::SUCCESS;
    }

    /**
     * Execute permanent deletion of expired soft-deleted arsip
     */
    private function executePermanentDelete(): void
    {
        $this->info('Executing permanent deletion of expired soft-deleted arsip...');
        
        try {
            $deletedCount = $this->service->permanentDeleteExpiredSurat();
            
            $this->info("Successfully permanently deleted {$deletedCount} arsip.");
            
            Log::channel('audit')->info('Permanent Delete Executed', [
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            $this->error('Error executing permanent delete: ' . $e->getMessage());
            
            Log::error('Permanent Delete Failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute sending retention notifications
     */
    private function executeSendNotifications(int $monthsAhead): void
    {
        $this->info("Sending retention notifications for arsip falling due within {$monthsAhead} months...");
        
        try {
            $notificationCount = $this->service->sendRetentionNotifications($monthsAhead);
            
            $this->info("Successfully sent {$notificationCount} notifications.");
            
            Log::channel('audit')->info('Retention Notifications Sent', [
                'notification_count' => $notificationCount,
                'months_ahead' => $monthsAhead,
            ]);
        } catch (\Exception $e) {
            $this->error('Error sending notifications: ' . $e->getMessage());
            
            Log::error('Send Notifications Failed: ' . $e->getMessage());
        }
    }
}
