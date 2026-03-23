<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BkNews extends Model
{
    protected $table = 'bk_news';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'author',
        'img_card',
        'img_cards',
        'img_detail_1',
        'img_detail_2',
    ];

    /** Auto-generate a unique slug from the title on create. */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $news) {
            if (empty($news->slug)) {
                $news->slug = static::generateUniqueSlug($news->title, $news->id ?? 0);
            }
        });

        static::updating(function (self $news) {
            if ($news->isDirty('title') && empty($news->slug)) {
                $news->slug = static::generateUniqueSlug($news->title, $news->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, int $exceptId = 0): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $i    = 1;
        while (static::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Returns only published news, newest first. */
    public function scopePublished($query)
    {
        return $query->latest();
    }

    /** Route model binding key. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
