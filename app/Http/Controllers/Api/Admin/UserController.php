<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s): void {
                    $q->where('full_name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('username', 'like', "%{$s}%");
                });
            }

            if ($request->filled('role')) {
                $query->role($request->role);
            }

            if ($request->filled('is_active')) {
                $query->where('active_status', $request->boolean('is_active'));
            }

            $users = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            $data = collect($users->items())->map(fn ($u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'username' => $u->username,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url,
                'whatsapp' => $u->whatsapp,
                'active_status' => $u->active_status,
                'email_verified_at' => $u->email_verified_at,
                'roles' => $u->getRoleNames(),
                'created_at' => $u->created_at?->toISOString(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal mengambil data pengguna'), 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'whatsapp' => $user->whatsapp,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'address' => $user->address,
                    'active_status' => $user->active_status,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->getRoleNames(),
                    'created_at' => $user->created_at?->toISOString(),
                    'updated_at' => $user->updated_at?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Pengguna tidak ditemukan')], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'whatsapp' => 'nullable|string|max:20',
                'roles' => 'nullable|array',
                'roles.*' => 'string|exists:roles,name',
                'active_status' => 'boolean',
            ]);

            $user = User::create([
                'full_name' => $validated['full_name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'whatsapp' => $validated['whatsapp'] ?? null,
                'active_status' => $validated['active_status'] ?? true,
            ]);

            if (! empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            } else {
                $user->assignRole('customer');
            }

            return response()->json([
                'status' => 'success',
                'message' => __('Pengguna berhasil dibuat'),
                'data' => ['id' => $user->id],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal membuat pengguna'), 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'username' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => 'sometimes|string|min:8',
                'whatsapp' => 'nullable|string|max:20',
                'roles' => 'nullable|array',
                'roles.*' => 'string|exists:roles,name',
                'active_status' => 'boolean',
            ]);

            $updateData = collect($validated)->except(['password', 'roles'])->toArray();
            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }
            $user->update($updateData);

            if (array_key_exists('roles', $validated)) {
                $user->syncRoles($validated['roles'] ?? []);
            }

            return response()->json(['status' => 'success', 'message' => __('Pengguna berhasil diperbarui')]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal memperbarui pengguna'), 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            if ($user->hasRole('super_admin')) {
                return response()->json(['status' => 'error', 'message' => __('Tidak dapat menghapus Super Admin')], 403);
            }
            $user->delete();
            return response()->json(['status' => 'success', 'message' => __('Pengguna berhasil dihapus')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal menghapus pengguna')], 500);
        }
    }

    public function toggleActive(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            if ($user->hasRole('super_admin')) {
                return response()->json(['status' => 'error', 'message' => __('Tidak dapat menonaktifkan Super Admin')], 403);
            }
            $user->update(['active_status' => ! $user->active_status]);
            return response()->json([
                'status' => 'success',
                'message' => $user->active_status ? __('Pengguna diaktifkan') : __('Pengguna dinonaktifkan'),
                'data' => ['active_status' => $user->active_status],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal mengubah status pengguna')], 500);
        }
    }

    public function roles(): JsonResponse
    {
        try {
            $roles = \Spatie\Permission\Models\Role::pluck('name');
            return response()->json(['status' => 'success', 'data' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal mengambil roles')], 500);
        }
    }

    /**
     * Daftar vendor.
     */
    public function vendors(): JsonResponse
    {
        try {
            $vendors = \App\Models\Vendor::orderBy('store_name')->get();

            $data = $vendors->map(fn ($v) => [
                'id' => $v->id,
                'store_name' => $v->store_name,
                'contact_person' => $v->contact_person,
                'no_telp' => $v->no_telp,
                'is_active' => $v->is_active,
                'created_at' => $v->created_at?->toISOString(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
