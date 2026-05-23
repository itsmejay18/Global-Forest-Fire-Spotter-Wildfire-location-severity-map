<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WildfireIncident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'latitude',
        'longitude',
        'burned_area',
        'fire_date',
        'severity',
        'country',
        'month',
        'year',
        'duration',
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'burned_area' => 'decimal:2',
        'fire_date' => 'date',
        'month' => 'integer',
        'year' => 'integer',
        'duration' => 'integer',
    ];
}
