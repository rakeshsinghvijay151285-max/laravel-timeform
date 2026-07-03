<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'project_id', 'work_date', 'task_description', 'hours', 'minutes'];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'integer',
        'minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getTotalMinutesAttribute()
    {
        return ($this->hours * 60) + $this->minutes;
    }
}
