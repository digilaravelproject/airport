<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active'];

    /**
     * Channels related to this package.
     * Include pivot sort_order and default-order by it.
     */
    public function channels()
    {
        // make ordering defensive: if sort_order is null, push to the end
        return $this->belongsToMany(\App\Models\Channel::class, 'channel_package')
                    ->withPivot('sort_order')
                    ->orderByRaw('COALESCE(channel_package.sort_order, 999999) ASC');
    }

    public function clients()
    {
        return $this->belongsToMany(\App\Models\Client::class, 'client_package');
    }

    public function inventories()
    {
        return $this->belongsToMany(\App\Models\Inventory::class, 'inventory_package');
    }
}
