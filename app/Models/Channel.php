<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_name',
        'channel_source_in',
        'channel_source_details',
        'channel_stream_type_out',
        'channel_url',
        'channel_genre',
        'channel_resolution',
        'channel_type',
        'language',
        'encryption',
        'active',
    ];

    protected $casts = [
        'encryption' => 'boolean',
        'active' => 'boolean',
    ];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'channel_package');
    }
}
