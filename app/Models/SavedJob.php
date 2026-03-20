<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'project_id'])]
class SavedJob extends Model
{
    /**
     * Get the user that saved this job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that was saved.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
