<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Enums\Status;
use App\Services\KYCP\Facades\KYCP;

class SyncApplicationStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kycp:sync-appstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Application Status with KYCP API';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Syncing Application Statuses...');

        $applications = Business::where('status', Business::STATUS_SUBMITTED)->whereNotNull('uid')->get();

        $bar = $this->output->createProgressBar(count($applications));

        foreach ($applications as $application) {
            $statusResponse = KYCP::getApplicationStatus($application->uid);

            if ($statusResponse->clientError() || $statusResponse->serverError() || ! $statusResponse->successful()) {
                continue; // Skip current iteration
            }

            $status = $statusResponse->json();
            $application->update([
                'status' => Status::from($status['StatusId'])->BPStatus(),
                'kycp_status_id' => $status['StatusId']
            ]);

            $bar->advance();
        }

        $bar->finish();
    }
}
