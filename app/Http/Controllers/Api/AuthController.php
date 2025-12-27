<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Menangani proses login dan pembuatan token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => ['code' => 422, 'status' => 'error', 'message' => 'Validasi gagal'],
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'meta' => ['code' => 401, 'status' => 'error', 'message' => 'Email atau Password salah'],
                'data' => null
            ], 401);
        }

        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'meta' => ['code' => 403, 'status' => 'error', 'message' => 'Akun belum aktif'],
        //         'data' => [
        //             'error' => 'Email belum diverifikasi. Silakan cek inbox email Anda.'
        //         ]
        //     ], 403);
        // }

        // 5. Generate Token Sanctum
        // Hapus token lama jika ingin single device login (opsional)
        // $user->tokens()->delete();

        $token = $user->createToken('geoaset-mobile-app')->plainTextToken;

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Login berhasil'
            ],
            'data' => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user
            ]
        ], 200);
    }

    /**
     * POST /api/logout
     * Menghapus token saat ini (Harus ada Header Authorization).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Logout berhasil'],
            'data' => null
        ], 200);
    }

    /**
     * GET /api/me
     * Cek data user yang sedang login (untuk testing token).
     */
    public function me(Request $request)
    {
        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Data User'],
            'data' => $request->user()
        ]);
    }
    /**
     * POST /api/email/resend
     * Mengirim ulang link verifikasi email manual.
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email ini sudah terverifikasi sebelumnya.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => 'Link verifikasi telah dikirim ulang ke ' . $user->email
        ]);
    }

    /**
     * GET /api/email/verify/{id}/{hash}
     * Ini adalah endpoint yang akan dipanggil saat user klik link di email.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Link verifikasi tidak valid atau rusak.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah terverifikasi sebelumnya.'], 200);
        }

        // 4. Lakukan Verifikasi!
        if ($user->markEmailAsVerified()) {
            // Opsional: Fire event verified
            // event(new Verified($user));
        }

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => 'Email berhasil diverifikasi! Anda sekarang bisa login.'
        ]);
    }
    /**
     * POST /api/profile
     * Update data diri & password user yang sedang login.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['meta' => ['code' => 422, 'status' => 'error'], 'data' => $validator->errors()], 422);
        }

        $dataToUpdate = [
            'name' => $request->name,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataToUpdate);

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Profil berhasil diperbarui'],
            'data' => $user
        ]);
    }
}
