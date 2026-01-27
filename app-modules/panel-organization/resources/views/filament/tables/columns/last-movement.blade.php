@php
    /** @var \He4rt\Applications\Models\Application $record */
    $lastMovement = $record->getLastMovement();

    if ($lastMovement && $lastMovement->toStage) {
        $eventName = $lastMovement->toStage->name;
        $eventDate = $lastMovement->created_at;
    } else {
        // Fallback to application creation if no movement history
        $eventName = __('panel-organization::view.last_movement.applied');
        $eventDate = $record->created_at;
    }

    // Format date in Portuguese - fallback to English format if locale fails
    try {
        $formattedDate = $eventDate->locale('pt_BR')->translatedFormat('d \d\e M. \d\e Y');
    } catch (\Exception $e) {
        $formattedDate = $eventDate->format('d M Y');
    }
@endphp

<div class="flex flex-col">
    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
        {{ $eventName }}
    </span>
    <span class="text-xs text-gray-600 dark:text-gray-400">
        {{ $formattedDate }}
    </span>
</div>
