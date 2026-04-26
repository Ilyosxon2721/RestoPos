<?php

namespace App\Application\Http\Controllers\Api\V1\Organization;

use App\Application\Http\Controllers\Controller;
use App\Domain\Organization\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * List all branches for current organization.
     */
    public function index(Request $request): JsonResponse
    {
        $branches = Branch::where('organization_id', $request->user()->organization_id)
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $branches->map(fn ($branch) => $this->formatBranch($branch)),
        ]);
    }

    /**
     * Get single branch.
     */
    public function show(Branch $branch): JsonResponse
    {
        return response()->json([
            'data' => $this->formatBranch($branch, true),
        ]);
    }

    /**
     * Create new branch.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'working_hours' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $branch = Branch::create([
            'organization_id' => $request->user()->organization_id,
            ...$request->only(['name', 'code', 'address', 'phone', 'email', 'working_hours', 'settings']),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Филиал успешно создан.',
            'data' => $this->formatBranch($branch),
        ], 201);
    }

    /**
     * Update branch.
     */
    public function update(Request $request, Branch $branch): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'working_hours' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($request->only([
            'name', 'code', 'address', 'phone', 'email',
            'working_hours', 'settings', 'is_active',
        ]));

        return response()->json([
            'message' => 'Филиал успешно обновлён.',
            'data' => $this->formatBranch($branch),
        ]);
    }

    /**
     * Delete branch.
     */
    public function destroy(Branch $branch): JsonResponse
    {
        // Check if this is the last active branch
        $activeBranches = Branch::where('organization_id', $branch->organization_id)
            ->where('is_active', true)
            ->count();

        if ($activeBranches <= 1 && $branch->is_active) {
            return response()->json([
                'message' => 'Нельзя удалить последний активный филиал.',
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'message' => 'Филиал успешно удалён.',
        ]);
    }

    /**
     * Format branch for response.
     */
    private function formatBranch(Branch $branch, bool $detailed = false): array
    {
        $data = [
            'id' => $branch->id,
            'uuid' => $branch->uuid,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'phone' => $branch->phone,
            'is_active' => $branch->is_active,
        ];

        if ($detailed) {
            $data['email'] = $branch->email;
            $data['working_hours'] = $branch->working_hours;
            $data['settings'] = $branch->settings;
            $data['created_at'] = $branch->created_at;
            $data['updated_at'] = $branch->updated_at;
        }

        return $data;
    }
}
