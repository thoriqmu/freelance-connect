<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'freelancer_id', 'note', 'status'])]
class Submission extends Model
{
    /**
     * Get the project associated with this submission.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the freelancer who made this submission.
     */
    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class, 'freelancer_id');
    }

    /**
     * Get all attachments for this submission.
     */
    public function submissionAttachments(): HasMany
    {
        return $this->hasMany(SubmissionAttachment::class);
    }
}
