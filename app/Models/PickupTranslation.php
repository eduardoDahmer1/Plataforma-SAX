<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupTranslation extends Model
{
    protected $table = 'pickup_translations';
    
    public $timestamps = true;

    protected $fillable = [
        'pickup_id',
        'locale',
        'title',
        'subtitle'
    ];

    // Relacionamento reverso com o Pickup/Ponto de Retirada principal
    public function pickup()
    {
        return $this->belongsTo(Pickup::class, 'pickup_id');
    }
}