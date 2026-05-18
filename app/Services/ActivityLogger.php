<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        string $subjectType,
        ?int $subjectId,
        string $description,
        array $properties = [],
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function created(string $subjectType, int $subjectId, string $description, array $attributes = []): ActivityLog
    {
        return self::log('created', $subjectType, $subjectId, $description, [
            'attributes' => $attributes,
        ]);
    }

    public static function updated(string $subjectType, int $subjectId, string $description, array $old = [], array $new = []): ActivityLog
    {
        return self::log('updated', $subjectType, $subjectId, $description, [
            'old' => $old,
            'new' => $new,
        ]);
    }

    public static function deleted(string $subjectType, int $subjectId, string $description, array $attributes = []): ActivityLog
    {
        return self::log('deleted', $subjectType, $subjectId, $description, [
            'attributes' => $attributes,
        ]);
    }
}
