<?php

declare(strict_types=1);

namespace He4rt\Candidates\Listeners;

use He4rt\Candidates\Actions\Onboarding\StoreCandidateResume;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use He4rt\Candidates\Events\AnalyzeResumeEvent;
use He4rt\Candidates\Models\Candidate;

/**
 * Persiste o currículo analisado no servidor, sem depender do navegador do candidato.
 *
 * `AiAnalyzeResumeJob` transmite o resultado da análise pelo websocket, e quem gravava era
 * a aba aberta ao receber o evento. Com a aba fechada, a análise se perdia — o job rodou,
 * a chamada da IA foi paga, e nada chegou ao banco. Como `broadcast()` também despacha o
 * evento localmente (`PendingBroadcast::__destruct`), este listener grava dentro do próprio
 * job, e o broadcast segue atualizando a tela como antes.
 *
 * **Só age sobre quem já concluiu o onboarding**, ou seja, o re-upload em Meu Perfil, onde
 * a gravação sempre foi automática. No wizard o resultado é oferecido para revisão antes de
 * salvar, e as Actions gravam com `firstOrCreate`: persistir aqui faria o registro extraído
 * pela IA chegar primeiro e vencer as correções do candidato.
 */
final readonly class StoreAnalyzedResume
{
    public function __construct(private StoreCandidateResume $storeResume) {}

    public function handle(AnalyzeResumeEvent $event): void
    {
        if ($event->status !== ResumeAnalyzeStatus::Finished || ! $event->fields instanceof CandidateOnboardingDTO) {
            return;
        }

        $candidate = Candidate::query()
            ->where('user_id', $event->userId)
            ->where('is_onboarded', true)
            ->first();

        if (! $candidate instanceof Candidate) {
            return;
        }

        $this->storeResume->execute($candidate, $event->fields);

        $candidate->update(['cv_last_uploaded_at' => now()]);
    }
}
