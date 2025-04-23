<?php

namespace App\Models;
use App\UserableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory,UserableTrait;

public function locations()
{
    return $this->belongsToMany(Location::class)
                ->withPivot('status') // علشان تقدر توصل لحقل status
                ->withTimestamps();
}}