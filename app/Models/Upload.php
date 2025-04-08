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
}
