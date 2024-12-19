<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Embed\Embed;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    
    protected $fillable = ['content', 'user_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    public function getLinkPreviewAttribute()
    {
        // Extract the first URL from the content
        preg_match('/\bhttps?:\/\/\S+/i', $this->content, $matches);

        if (!empty($matches)) {
            $url = $matches[0];
            return Cache::remember("link_preview_{$url}", now()->addDay(), function () use ($url) {
                try {
                    // Create an Embed instance
                    $embed = new Embed();
                    $info = $embed->get($url);

                    return [
                        'title' => $info->title,
                        'description' => $info->description,
                        'image' => $info->image ? (string) $info->image : null, // Cast image URL to string
                        'url' => (string) $info->url, // Cast URL to string
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            });
        }

        return null;
    }


}
