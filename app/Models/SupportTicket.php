<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'ticket_number',
        'subject',
        'user_id',
        'comments',
        'attachment_path',
        'status',
        'priority',
        'assigned_to',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['username', 'email'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id')->orderByDesc('created_at');
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (isset($array['assigned_to']) && is_array($array['assigned_to'])) {
            $array['assignedTo'] = $array['assigned_to'];
            $array['assigned_to'] = (int) $this->getAttribute('assigned_to');
        }

        $array['ticket_id'] = $this->ticket_number;

        return $array;
    }

    public function getUsernameAttribute()
    {
        return $this->user?->getDisplayName() ?? 'Anonymous';
    }

    public function getEmailAttribute()
    {
        return $this->user?->email ?? null;
    }

    public function getTicketIdAttribute()
    {
        return $this->ticket_number;
    }
}
