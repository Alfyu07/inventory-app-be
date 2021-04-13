<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'condition',
        'purchase_date',
        'price',
        'location',
        'description',
        'picture_path',
        'hash'
    ];


    public function user(){
        $this->belongsTo(User::class);
    }

     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */


    protected $casts = [
        'purchase_date' => 'datetime:Y-m-d',
    ];

}
