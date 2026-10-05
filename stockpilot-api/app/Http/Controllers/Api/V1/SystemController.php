<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function settings()
    {
        return response()->json(['data' => Setting::orderBy('key')->get()->mapWithKeys(fn ($s) => [$s->key => $this->cast($s->value, $s->type)])]);
    }

    public function updateSettings(Request $r)
    {
        $data = $r->validate(['company_name' => 'sometimes|string|max:160', 'company_address' => 'nullable|string', 'currency' => 'sometimes|string|size:3', 'date_format' => 'sometimes|string|max:30', 'timezone' => 'sometimes|timezone', 'default_warehouse_id' => 'nullable|exists:warehouses,id', 'allow_negative_stock' => 'sometimes|boolean', 'require_po_approval' => 'sometimes|boolean', 'require_adjustment_approval' => 'sometimes|boolean']);
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : $value, 'type' => is_bool($value) ? 'boolean' : 'string', 'is_public' => in_array($key, ['company_name', 'currency', 'date_format', 'timezone'])]);
        }

return response()->json(['message' => 'Settings saved.', 'data' => $data]);
    }

    public function notifications(Request $r)
    {
        return response()->json($r->user()->notifications()->latest()->paginate(30));
    }

    public function readNotification(Request $r, string $id)
    {
        $notification = $r->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAllNotifications(Request $r)
    {
        $r->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function audits(Request $r)
    {
        $q = AuditLog::with('user:id,name,email')->latest();
        if ($r->action) {
            $q->where('action', $r->action);
        }if ($r->user_id) {
            $q->where('user_id', $r->user_id);
        }if ($r->entity) {
            $q->where('auditable_type', 'like', '%'.$r->entity);
        }if ($r->date_from) {
            $q->whereDate('created_at', '>=', $r->date_from);
        }if ($r->date_to) {
            $q->whereDate('created_at', '<=', $r->date_to);
        }

return response()->json($q->paginate(min($r->integer('per_page', 30), 100)));
    }

    private function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL),'integer' => (int) $value,'json' => json_decode($value,true),default => $value
        };
    }
}
