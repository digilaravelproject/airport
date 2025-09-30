<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'location_name', 'terminal', 'area',
        'level', 'description', 'image_path'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

