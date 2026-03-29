<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class SkillController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $skills = Skill::query()
            ->when($search, function ($query, $search) {
                return $query->where('title', 'ilike', '%' . $search . '%');
            })
            ->orderBy('title')
            ->limit(20)
            ->get();

        return $this->successResponse('Success', $skills, 200);
    }
}
