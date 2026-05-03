<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMembership extends Pivot
{
    public $incrementing = true;

    protected $table = 'team_user';
    protected $fillable = ['role'];
}