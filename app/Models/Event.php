<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    //entende q o campo input 'items' é um array
    protected $casts = [
        'items' => 'array'
    ];

    //informando q é um campo de data
    protected $dates = ['date'];

    //para update - tudo q for enviado pode ser enviado sem restrição
    protected $guarded = [];

    //One to Many - retorna um usuario q tem varios eventos
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    //Many to Many - retorna usuarios que possui varios eventos
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
