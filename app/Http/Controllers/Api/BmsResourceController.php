<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\JobDeleteOtpService;
use App\Support\BranchScope;
use App\Support\BmsPermissions;
use App\Support\BmsResourceRegistry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class BmsResourceController extends Controller
{
    public function index(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($request, $resource, 'read');

        $model = BmsResourceRegistry::model($resource);
        $query = $model::query()->orderBy('id');

        $user = $request->user();
        $branch = $this->branchFilterFor($user);
        if ($branch && $this->hasBranchColumn($model)) {
            $query->where('branch', $branch);
        }

        return response()->json($query->get()->map(fn (Model $row) => $this->serialize($row)));
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($request, $resource, 'create');

        $model = BmsResourceRegistry::model($resource);
        $data = $this->validatedPayload($request, $resource, false);
        if ($resource === 'saleslog') {
            $user = $request->user();
            $data['logged_by'] = $user->name;
            if (empty($data['sales_rep_id'])) {
                $data['sales_rep_id'] = \App\Models\Staff::query()->where('user_id', $user->id)->value('id');
            }
        }
        $row = $model::query()->create($data);

        return response()->json($this->serialize($row), 201);
    }

    public function update(Request $request, string $resource, string $id): JsonResponse
    {
        $this->authorizeResource($request, $resource, 'update');

        $model = BmsResourceRegistry::model($resource);
        $row = $model::query()->findOrFail($id);
        $data = $this->validatedPayload($request, $resource, true);
        $row->fill($data);
        $row->save();

        return response()->json($this->serialize($row));
    }

    public function destroy(Request $request, string $resource, string $id): JsonResponse
    {
        $this->authorizeResource($request, $resource, 'delete');

        $user = $request->user();
        if ($resource === 'jobs' && JobDeleteOtpService::requiresOtp($user)) {
            $request->validate([
                'delete_otp' => ['required', 'string', 'regex:/^\d{6}$/'],
            ]);

            if (! JobDeleteOtpService::verify($id, $user, $request->input('delete_otp'))) {
                abort(422, 'Invalid or expired delete code. Request a new code from admin.');
            }
        }

        $model = BmsResourceRegistry::model($resource);
        $row = $model::query()->findOrFail($id);
        $branch = $this->branchFilterFor($request->user());
        if ($branch && $this->hasBranchColumn($model) && ($row->branch ?? null) !== $branch) {
            abort(404);
        }
        $row->delete();

        return response()->json(['ok' => true]);
    }

    private function validatedPayload(Request $request, string $resource, bool $updating): array
    {
        $rules = BmsResourceRegistry::rules($resource, $updating);
        $payload = Validator::make($request->all(), $rules)->validate();
        $casts = BmsResourceRegistry::casts($resource);

        foreach ($casts as $field => $type) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            if ($type === 'bool') {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOLEAN);
            }

            if ($type === 'array' && is_string($payload[$field])) {
                $payload[$field] = json_decode($payload[$field], true) ?? [];
            }
        }

        return $payload;
    }

    private function branchFilterFor(?User $user): ?string
    {
        return BranchScope::filter($user);
    }

    private function authorizeResource(Request $request, string $resource, string $action): void
    {
        $role = $request->user()?->role;

        if (! BmsPermissions::allowed($role, $resource, $action)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /** @param  class-string<Model>  $model */
    private function hasBranchColumn(string $model): bool
    {
        return Schema::hasColumn((new $model)->getTable(), 'branch');
    }

    private function serialize(Model $row): array
    {
        $data = $row->toArray();
        unset($data['created_at'], $data['updated_at'], $data['user_id']);

        foreach ($row->getCasts() as $field => $type) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($type === 'boolean' && $data[$field] !== null) {
                $data[$field] = (bool) $data[$field];
            }

            if ($type === 'decimal:2' && $data[$field] !== null) {
                $data[$field] = (float) $data[$field];
            }

            if ($type === 'integer' && $data[$field] !== null) {
                $data[$field] = (int) $data[$field];
            }
        }

        return $data;
    }
}
