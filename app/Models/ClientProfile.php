<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'company_name', 'company_field'])]
class ClientProfile extends Model
{
    /**
     * Get the user that owns the client profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all projects associated with this client.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }
}
