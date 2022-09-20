<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = "areas";

    protected $fillable = ["id" ,"area_name", "area_status", "city_id","area_num"];

    protected $casts = [
        'id' => 'integer',
        'area_status' => 'integer',
        'city_id' => 'integer',
        'area_num' => 'integer',
    ];

    public function city() {
      return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
