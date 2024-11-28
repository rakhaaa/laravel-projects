<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = "string";

    public $incrementing = false;

    protected $fillable = ['description', 'amount', 'category', 'date'];
}