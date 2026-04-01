<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskCompleted;
use App\Models\AuditLog;

class LogTaskAudit
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCreated|TaskStatusChanged|TaskCompleted $event): void
    {
        // Определяем действие в зависимости от типа события
        $action = match (true) {
            $event instanceof TaskCreated => 'created',
            $event instanceof TaskStatusChanged => 'status_changed',
            $event instanceof TaskCompleted => 'completed',
            default => null,
        };

        if (!$action) return;

        // Для смены статуса добавим мета-информацию
        $meta = [];
        if ($event instanceof TaskStatusChanged) {
            $meta['old_status'] = $event->oldStatus;
            $meta['new_status'] = $event->newStatus;
        }

        // Записываем в таблицу audit_logs
        AuditLog::create([
            'entity_type' => get_class($event->task), // полное имя класса Task
            'entity_id' => $event->task->id,
            'action' => $action,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }
}
