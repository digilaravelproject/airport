<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_id',
        'box_model',
        'box_serial_no',
        'box_mac',
        'box_fw',
        'box_remote_model',
        'warranty_date',
        'client_id',
        'location',
        'terminal',   // NEW
        'level',      // NEW
        'box_ip',
        'mgmt_url',
        'mgmt_token',
        'photo',
        'box_subnet',
        'gateway',
        'box_os',
        'supplier_name',
        'status',
        'created_at',
    ];

    protected $casts = [
        'warranty_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'inventory_package');
    }
}
