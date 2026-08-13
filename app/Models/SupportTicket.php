<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_id',
        'subject',
        'category',
        'user_id',
        'username',
        'email',
        'comments',
        'attachment_path',
        'internal_notes',
        'status',
        'priority',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
