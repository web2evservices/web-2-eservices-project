<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\NotificationService;

class Feddback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'service_request_id',
        'citizen_id',
        'rating',
        'comment',
        'response',
    ];

    protected static function booted()
    {
        static::created(function (Feddback $feedback) {
            $feedback->load(['serviceRequest.service.office', 'citizen']);

            if ($officeUserId = $feedback->serviceRequest?->service?->office?->user_id) {
                NotificationService::send(
                    $officeUserId,
                    'Feedback received',
                    sprintf(
                        'Citizen %s submitted feedback for request #%s.',
                        $feedback->citizen?->username ?? 'a citizen',
                        $feedback->service_request_id
                    ),
                    'feedback_received'
                );
            }
            NotificationService::sendToAdmins(
                'Feedback received',
                sprintf(
                    'Citizen %s submitted feedback for request #%s.',
                    $feedback->citizen?->username ?? 'a citizen',
                    $feedback->service_request_id
                ),
                'admin_activity'
            );
        });
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequests::class);
    }

    public function citizen()
    {
        return $this->belongsTo(Users::class, 'citizen_id');
    }
}
