<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    // Se sua tabela se chama 'attributes', não precisa definir o nome.
    // protected $table = 'attributes';

    // Permitir campos que podem ser atualizados em massa
    protected $fillable = [
        'header_image',
        'noimage',
        'logo_palace',
        'logo_bridal',
        'BannerHorizontal',
        'banner1',
        'banner2',
        'banner3',
        'banner4',
        'banner5',
        'banner6',
        'banner7',
        'banner8',
        'banner9',
        'banner10',
        'banner1_link',
        'banner2_link',
        'banner3_link',
        'banner4_link',
        'banner5_link',
        'banner6_link',
        'banner7_link',
        'banner8_link',
        'banner9_link',
        'banner10_link',
        'text_topo',
        'icon_info',
        'icon_cabide',
        'icon_help',
        'whatsapp_banner'
    ];

    public static function logoUrl(): ?string
    {
        $attribute = static::first();
        return $attribute?->header_image
            ? asset('storage/uploads/' . $attribute->header_image)
            : null;
    }
}
