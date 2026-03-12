<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaGroup extends Model
{
    protected $fillable = ['agenda_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }
}
