<?php

declare(strict_types=1);

namespace App\Providers\Filament\Scripts;

use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppPanelScripts
{
    public static function register(Panel $panel): void
    {
        self::registerSavedJobsStore($panel);
    }

    private static function registerSavedJobsStore(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn () => Blade::render(<<<'BLADE'
               <script>
                   document.addEventListener('alpine:init', () => {
                       Alpine.store('savedJobs', {
                           jobs: [],

                           init() {
                               const stored = localStorage.getItem('rpq_saved_jobs');
                               if (stored) {
                                   this.jobs = JSON.parse(stored);
                                   // Migrate old format if needed
                                   this.migrateOldFormat();
                               }
                           },

                           migrateOldFormat() {
                               // Check if jobs have old format (missing savedAt field)
                               let needsMigration = false;
                               this.jobs = this.jobs.map(job => {
                                   if (!job.savedAt) {
                                       needsMigration = true;
                                       // Add savedAt for old jobs (use current time as fallback)
                                       return { ...job, savedAt: new Date().toISOString() };
                                   }
                                   return job;
                               });

                               if (needsMigration) {
                                   this.persist();
                               }
                           },

                           persist() {
                               localStorage.setItem('rpq_saved_jobs', JSON.stringify(this.jobs));
                           },

                           isSaved(id) {
                               return this.jobs.some(j => j.id === id);
                           },

                           save(job) {
                               if (!this.isSaved(job.id)) {
                                   // Add savedAt timestamp
                                   job.savedAt = new Date().toISOString();
                                   this.jobs.push(job);
                                   this.persist();
                               }
                           },

                           remove(id) {
                               this.jobs = this.jobs.filter(j => j.id !== id);
                               this.persist();
                           },

                           toggle(job) {
                               this.isSaved(job.id) ? this.remove(job.id) : this.save(job);
                           },

                           // Get jobs sorted by savedAt (newest first)
                           getSortedJobs() {
                               return [...this.jobs].sort((a, b) => 
                                   new Date(b.savedAt || 0) - new Date(a.savedAt || 0)
                               );
                           },
                       });
                   });
               </script>
            BLADE)
        );
    }
}
