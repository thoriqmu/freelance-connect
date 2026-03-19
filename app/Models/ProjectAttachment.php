<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'title', 'file_path'])]
class ProjectAttachment extends Model
{
    /**
     * Get the project that owns this attachment.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
