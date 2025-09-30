<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'contact_no', 'email', 'contact_person',
        'type', 'city', 'state', 'pin', 'gst_no'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'client_package');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
