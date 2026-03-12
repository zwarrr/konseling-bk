<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaReview extends Model
{
    protected $table = 'agenda_reviews';

    protected $fillable = ['agenda_id', 'name', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }
}
