<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $model->getOriginal(), []);
    }

    private function record(string $action, Model $model, array $old, array $new): void
    {
        if ($model instanceof AuditLog || ! Schema::hasTable('audit_logs')) {
            return;
        }AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $model->getMorphClass(), 'auditable_id' => $model->getKey(), 'old_values' => $this->clean($old), 'new_values' => $this->clean($new), 'ip_address' => request()?->ip()]);
    }

    private function clean(array $values): array
    {
        return collect($values)->except(['password', 'remember_token'])->all();
    }
}
