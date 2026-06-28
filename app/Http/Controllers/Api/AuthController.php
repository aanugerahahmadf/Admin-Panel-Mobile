<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'mid_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:users|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users',
            'whatsapp' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:20',
            'identity_type' => 'nullable|string|in:ktp,passport|max:20',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'ktp_photo' => 'nullable|image|max:2048',
            'selfie_photo' => 'nullable|image|max:5120',
            'country' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:indonesia_provinces,id',
            'city_id' => 'nullable|exists:indonesia_cities,id',
            'district_id' => 'nullable|exists:indonesia_districts,id',
            'village_id' => 'nullable|exists:indonesia_villages,id',
            'province_name' => 'nullable|string|max:255',
            'city_name' => 'nullable|string|max:255',
            'district_name' => 'nullable|string|max:255',
            'village_name' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'gender' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'password' => 'required|string|min:12|confirmed',
            'profile_photo' => 'nullable|image|max:10240',
            'avatar_url' => 'nullable|string|max:500',
        ]);

        $validator->sometimes('nik', 'required|size:16', function ($input) {
            return ($input->identity_type ?? '') === 'ktp';
        });

        $validator->sometimes('passport_number', 'required|min:6', function ($input) {
            return ($input->identity_type ?? '') === 'passport';
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Gagal validasi'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $userData = [
            'full_name' => $request->full_name,
            'first_name' => $request->first_name,
            'mid_name' => $request->mid_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'nik' => $request->nik,
            'passport_number' => $request->passport_number,
            'identity_type' => $request->identity_type,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'country' => $request->country,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'village_id' => $request->village_id,
            'province_name' => $request->province_name,
            'city_name' => $request->city_name,
            'district_name' => $request->district_name,
            'village_name' => $request->village_name,
            'postal_code' => $request->postal_code,
            'gender' => $request->gender,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'ip_address' => $request->ip(),
        ];

        if ($request->hasFile('ktp_photo')) {
            $userData['ktp_photo'] = $request->file('ktp_photo')->store('ktp-photos', 'public');
        }

        if ($request->hasFile('selfie_photo')) {
            $userData['selfie_photo'] = $request->file('selfie_photo')->store('selfies', 'public');
            $userData['identity_verified_at'] = now();
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('avatars', 'public');
            $userData['avatar_url'] = $path;
        } elseif ($request->filled('avatar_url')) {
            $userData['avatar_url'] = $request->avatar_url;
        }

        $user = User::create($userData);

        $user->assignRole('user');

        /** @var User $user */
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->login;
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, $login)->orWhere('nik', $login)->orWhere('passport_number', $login)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => __('Data login tidak valid'),
            ], 401);
        }

        Auth::login($user);

        // Check if user is active
        if ($user->active_status === false) {
            Auth::logout();

            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda telah dinonaktifkan oleh admin.',
            ], 403);
        }

        // Ensure user is active on login
        if (! $user->active_status) {
            $user->update(['active_status' => true]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Berhasil keluar'),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first(['*']);

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Email tidak terdaftar.'], 404);
        }

        // TODO: kirim OTP ke email (notification/mail)
        return response()->json([
            'status' => 'success',
            'message' => 'Instruksi reset password akan dikirim ke email Anda.',
            'email' => $user->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|confirmed|min:12',
        ]);

        $user = User::where('email', $request->email)->first(['*']);
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => __('Pengguna tidak ditemukan')], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status' => 'success',
            'message' => __('Reset password berhasil'),
        ]);
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'token' => 'required|string',
        ]);

        // In a real app, verify the token with the provider (Google/Facebook)
        // For now, we simulate success or find user by email if token contains it
        return response()->json([
            'status' => 'success',
            'message' => __('Login sosial berhasil (simulasi)'),
            'data' => [
                'token' => 'SOCIAL-TOKEN-'.Str::random(40),
                'user' => Auth::user() ?: User::first(['*']), // Fallback for simulation
            ],
        ]);
    }

    public function clerkSync(Request $request)
    {
        $request->validate([
            'clerk_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|string|max:500',
            'username' => 'nullable|string|max:255|alpha_dash',
        ]);

        $clerkId = $request->clerk_id;
        $email = $request->email;

        $user = User::where('clerk_id', $clerkId)->orWhere('email', $email)->first();

        if ($user) {
            if (! $user->clerk_id) {
                $user->update(['clerk_id' => $clerkId]);
            }
            if ($request->name && ! $user->full_name) {
                $user->update(['full_name' => $request->name]);
            }
            if ($request->avatar_url && ! $user->avatar_url) {
                $user->update(['avatar_url' => $request->avatar_url]);
            }
        } else {
            $username = $request->username ?? 'user_'.Str::random(8);
            while (User::where('username', $username)->exists()) {
                $username = 'user_'.Str::random(8);
            }

            $user = User::create([
                'clerk_id' => $clerkId,
                'full_name' => $request->name ?? $email,
                'username' => $username,
                'email' => $email,
                'avatar_url' => $request->avatar_url,
                'active_status' => true,
            ]);

            $userRole = Role::where('name', 'user')->first();
            if ($userRole) {
                $user->assignRole($userRole);
            }
        }

        if (! $user->active_status) {
            $user->update(['active_status' => true]);
        }

        $token = $user->createToken('clerk-sync')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'purpose' => 'required|string',
        ]);

        // Simulating OTP sending
        return response()->json([
            'status' => 'success',
            'message' => __('OTP berhasil dikirim ke ').$request->email,
            'otp' => '123456', // Simulated OTP
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'purpose' => 'required|string',
        ]);

        // Simulating OTP verification
        if ($request->otp === '123456') {
            return response()->json([
                'status' => 'success',
                'message' => __('OTP berhasil diverifikasi'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('OTP tidak valid'),
        ], 422);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'mid_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'whatsapp' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'wedding_date' => 'nullable|date',
            'theme_preference' => 'nullable|string',
            'color_preference' => 'nullable|string',
            'event_concept' => 'nullable|string',
            'dream_venue' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $data['avatar_url'] = $path;
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function deleteAccount(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Akun berhasil dihapus'),
        ]);
    }
}
