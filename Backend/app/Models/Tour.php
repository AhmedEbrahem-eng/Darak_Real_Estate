<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'property_id', 'status'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function tourDates()
    {
        return $this->hasMany(TourDate::class);
    }
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
