<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function record(?User $actor, string $event, Model $subject, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $actor?->id,
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'metadata' => $metadata,
        ]);
    }
}