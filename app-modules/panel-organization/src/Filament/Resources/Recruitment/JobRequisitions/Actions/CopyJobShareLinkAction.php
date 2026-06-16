<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use App\Enums\FilamentPanel;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource as CandidateJobRequisitionResource;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Js;

class CopyJobShareLinkAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.copy_share_link.label'))
            ->icon(Heroicon::OutlinedLink)
            ->color('gray')
            ->authorize('view')
            ->disabled(fn (JobRequisition $record): bool => blank(self::shareUrlFor($record)))
            ->tooltip(fn (JobRequisition $record): ?string => blank(self::shareUrlFor($record))
                ? (string) __('panel-organization::filament.actions.copy_share_link.tooltip_unavailable')
                : null)
            ->actionJs(fn (JobRequisition $record): string => $this->clipboardJs($record));
    }

    /**
     * URL pública de detalhe da vaga para compartilhamento, ou null quando a vaga não é
     * compartilhável: precisa estar Published (mesma regra de visibilidade usada na busca
     * e nas recomendações do candidato) e ter um anúncio com slug.
     */
    public static function shareUrlFor(JobRequisition $record): ?string
    {
        if ($record->status !== RequisitionStatusEnum::Published) {
            return null;
        }

        $slug = $record->post?->slug;

        if (blank($slug)) {
            return null;
        }

        return CandidateJobRequisitionResource::getUrl(
            name: 'view',
            parameters: ['record' => $slug],
            panel: FilamentPanel::App->value,
        );
    }

    public static function getDefaultName(): ?string
    {
        return 'copyShareLink';
    }

    /**
     * Apresenta a action como botão somente-ícone para a tabela: clique rápido, sem
     * ocupar espaço com texto. O tooltip (title) carrega o rótulo para discoverability
     * quando habilitada, ou a explicação de indisponibilidade quando não há anúncio.
     */
    public function tableIconButton(): static
    {
        return $this
            ->iconButton()
            ->tooltip(fn (JobRequisition $record): string => blank(self::shareUrlFor($record))
                ? __('panel-organization::filament.actions.copy_share_link.tooltip_unavailable')
                : __('panel-organization::filament.actions.copy_share_link.label'));
    }

    private function clipboardJs(JobRequisition $record): string
    {
        // Js::from() escapa as aspas como sequências hex ("), evitando aspas duplas
        // literais. O ComponentAttributeBag do Laravel serializa atributos com
        // str_replace('"', '\"', ...) — aspas duplas no JS quebrariam o atributo HTML e
        // truncariam o handler Alpine. json_encode() produzia exatamente esse problema.
        $url = Js::from(self::shareUrlFor($record));
        $message = Js::from(
            __('panel-organization::filament.actions.copy_share_link.notification_copied'),
        );

        return <<<JS
            if ({$url}) {
                window.navigator.clipboard.writeText({$url});
                new FilamentNotification().title({$message}).success().send();
            }
            JS;
    }
}
