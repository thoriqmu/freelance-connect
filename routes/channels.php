<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'prefix' => 'api/v1', 
    'middleware' => ['auth:sanctum']
]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    $project = \App\Models\Project::find($projectId);
    
    if (!$project) return false;

    return $user->clientProfile?->id === $project->client_id || 
           $user->freelancerProfile?->id === $project->freelancer_id;
});