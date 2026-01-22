<?php

declare(strict_types=1);

namespace App\Console\Commands;

use He4rt\Users\User;
use Illuminate\Console\Command;

class FodaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fodase-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::query()->where('name', 'admin')->first();

        //        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Queued, ['vai caralho'], $admin->getKey()));
        //        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Processing, ['vai caralho'], $admin->getKey()));
        //        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, ['vai caralho'], $admin->getKey()));
    }
}
