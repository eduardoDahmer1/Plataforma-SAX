<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    // Permitir os campos que podem ser preenchidos automaticamente
    protected $fillable = [
        'title',
        'description',
        'file_type',
        'file_path',
        'original_name',
        'mime_type',
        'user_id',
    ];

    // Relacionamento com a model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // No modelo Upload
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Se sua tabela não usa os timestamps padrão, desabilite-os:
    // public $timestamps = false;

    // Caso você queira definir o tipo de dados para alguns campos, por exemplo:
    // protected $casts = [
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    // ];
}
