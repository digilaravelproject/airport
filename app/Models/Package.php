<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'active'];

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_package');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_package');
    }

     // ✅ Many-to-Many with Inventory
    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_package');
    }
}

