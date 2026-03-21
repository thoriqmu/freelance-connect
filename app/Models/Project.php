<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['client_id', 'freelancer_id', 'title', 'description', 'budget', 'timeline', 'status'])]
class Project extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the client profile that owns this project.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }

    /**
     * Get the freelancer profile assigned to this project.
     */
    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class, 'freelancer_id');
    }

    /**
     * Get all attachments for this project.
     */
    public function projectAttachments(): HasMany
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    /**
     * Get all submissions for this project.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get all reviews for this project.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all messages for this project.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all proposals for this project.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
