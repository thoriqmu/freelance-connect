<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'skills', 'hourly_rate', 'portfolio_url', 'bio', 'availability'])]
class FreelancerProfile extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'skills' => 'array',
        ];
    }

    /**
     * Get the user that owns the freelancer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all proposals submitted by this freelancer.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'freelancer_id');
    }

    /**
     * Get all submissions by this freelancer.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'freelancer_id');
    }

    /**
     * Get all projects assigned to this freelancer.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'freelancer_id');
    }
}
