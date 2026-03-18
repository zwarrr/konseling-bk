<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupReadReceipt extends Model
{
    protected $table    = 'group_read_receipts';
    protected $fillable = ['user_id', 'classroom_id', 'last_read_message_id'];
}
