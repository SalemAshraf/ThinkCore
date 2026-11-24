<?php

namespace App\Console\Commands;

use App\Support\InstructorDecisionOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeliverInstructorDecisions extends Command
{
    protected $signature='thinkcore:deliver-instructor-decisions {--send : Explicitly permit transport sends} {--limit=25} {--retry= : Reset one terminal failed notification before processing}';
    protected $description='Inspect pending decision notifications; send only with explicit --send';

    public function handle(InstructorDecisionOutbox $outbox): int
    {
        $limit=filter_var($this->option('limit'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100]]);
        if (!$limit) { $this->error('Limit must be between 1 and 100.'); return self::INVALID; }
        if ($this->option('retry') && !$this->option('send')) { $this->error('Retry requires --send.'); return self::INVALID; }
        if ($this->option('retry')) {
            $id=filter_var($this->option('retry'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            if (!$id || !$outbox->retry($id)) { $this->error('No terminal failed notification was reset.'); return self::FAILURE; }
        }
        $query=DB::table('instructor_decision_notifications')->whereIn('status',['pending','sending'])->orderBy('id');
        if (!$this->option('send')) {
            $this->info('Dry run only. Pending or claimed notifications: '.$query->count().'. No transport used.');
            return self::SUCCESS;
        }
        foreach ($query->limit($limit)->pluck('id') as $id) $this->line($id.': '.$outbox->deliver($id));
        return self::SUCCESS;
    }
}
