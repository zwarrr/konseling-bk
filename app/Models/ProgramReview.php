<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramReview extends Model
{
    protected $table = 'program_reviews';

    protected $fillable = ['program_id', 'name', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
