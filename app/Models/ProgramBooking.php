<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SiswaAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramBooking extends Model
{
    protected $table = 'program_bookings';

    protected $fillable = [
        'program_id',
        'user_id',
        'scheduled_at',
        'message',
        'status',
        'booking_type',
        'method',
        'participants',
        'responded_at',
        'responded_by',
        'reminded_lead_at',
        'reminded_24h_at',
        'reminded_1h_at',
        'chat_confirmed_at',
        'chat_reconfirmed_at',
        'chat_reconfirm_declined_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'responded_at' => 'datetime',
        'participants' => 'array',
        'reminded_lead_at' => 'datetime',
        'reminded_24h_at' => 'datetime',
        'reminded_1h_at' => 'datetime',
        'chat_confirmed_at' => 'datetime',
        'chat_reconfirmed_at' => 'datetime',
        'chat_reconfirm_declined_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function user()
    {
        return $this->belongsTo(SiswaAccount::class, 'user_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(BkAccount::class, 'responded_by');
    }
}
