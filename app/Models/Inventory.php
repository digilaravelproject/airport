<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_model',
        'box_serial_no',
        'box_mac',
        'box_fw',
        'box_remote_model',
        'warranty_date',
        'client_id',
        'location',
        'box_ip',       // ✅ new
        'mgmt_url',     // ✅ new
        'mgmt_token',   // ✅ new
        'photo',
    ];

    protected $casts = [
        'warranty_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ✅ Many-to-Many with Packages
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'inventory_package');
    }
}
