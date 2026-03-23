<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileBkGallery extends Model
{
    protected $table = 'profile_bk_gallery';

    protected $fillable = [
        'title',
        'description',
        'img',
        'sort_order',
    ];
}
