<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'plan_id' ,'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');

    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');

    }
}
