<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjectTypeLabel(): string
    {
        return match ($this->subject_type) {
            'service' => 'Service',
            'appointment' => 'Appointment',
            'service_request' => 'Service Request',
            default => ucfirst(str_replace('_', ' ', $this->subject_type)),
        };
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            default => 'secondary',
        };
    }
}
