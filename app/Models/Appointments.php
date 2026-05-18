<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Appointments extends Model
{
    protected $fillable = [
        'office_id',
        'service_id',
        'citizen_id',
        'citizen_name',
        'citizen_email',
        'citizen_phone',
        'date',
        'time_slot',
        'status',
        'notes',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public static function normalizeTimeSlot(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $format = strlen($value) === 5 ? 'H:i' : 'H:i:s';

            return Carbon::createFromFormat($format, $value)->format('H:i:s');
        }

        foreach (['h:i A', 'g:i A', 'h:i a', 'g:i a'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i:s');
            } catch (\Exception) {
                continue;
            }
        }

        throw new InvalidArgumentException("Invalid time slot: {$value}");
    }

    public function setTimeSlotAttribute($value): void
    {
        $this->attributes['time_slot'] = static::normalizeTimeSlot((string) $value);
    }

    public function getFormattedTimeSlotAttribute(): string
    {
        try {
            return Carbon::parse($this->attributes['time_slot'] ?? $this->time_slot)->format('g:i A');
        } catch (\Exception) {
            return (string) ($this->attributes['time_slot'] ?? $this->time_slot);
        }
    }

    public function office() {
        return $this->belongsTo(Government_Offices::class, 'office_id');
    }

    public function citizen() {
        return $this->belongsTo(Users::class, 'citizen_id');
    }

    public function service() {
        return $this->belongsTo(Services::class, 'service_id');
    }

    public function serviceRequest() {
        return $this->hasOne(ServiceRequests::class, 'appointment_id');
    }
}
