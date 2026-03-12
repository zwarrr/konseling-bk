<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutFeature extends Model
{
    protected $table = 'about_features';

    protected $fillable = ['title', 'description', 'sort_order'];
}
