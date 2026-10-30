<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MeetingReminderStatus;
use App\Enums\MeetingReminderType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingReminder extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'meeting_id',
        'recipient_id',
        'status',
        'type',
        'sent_at',
    ];

    protected $casts = [
        'status' => MeetingReminderStatus::class,
        'type' => MeetingReminderType::class,
        'sent_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
