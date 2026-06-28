<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectiveLog extends Model
{
    protected $fillable = ['objective_id', 'value', 'note', 'logged_date'];

    protected $casts = [
        'value'       => 'float',
        'logged_date' => 'date',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(QuarterlyObjective::class, 'objective_id');
    }
}
