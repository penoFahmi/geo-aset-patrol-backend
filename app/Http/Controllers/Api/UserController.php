<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Admin melihat daftar semua petugas.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Data user berhasil diambil'],
            'data' => $users
        ]);
    }

    /**
     * POST /api/users
     * Admin membuat akun baru untuk petugas.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Admin
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip'   => 'required|string|unique:users,nip',
            'role'  => 'in:admin,officer',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => ['code' => 422, 'status' => 'error', 'message' => 'Validasi gagal'],
                'data' => $validator->errors()
            ], 422);
        }

        // 2. Generate Password Acak (8 Karakter)
        $randomPassword = Str::random(8);

        // 3. Simpan ke Database
        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'nip'      => $request->nip,
                'phone'    => $request->phone,
                'role'     => $request->role ?? 'officer',
                'password' => Hash::make($randomPassword),
            ]);

            // 4. (TODO) Kirim Email Credentials
            Mail::to($user->email)->send(new UserCredentialsMail($user, $randomPassword));

            event(new Registered($user));

            /**
             * 5. Balikkan Response JSON ke Client
             */
            return response()->json([
                'meta' => ['code' => 201, 'status' => 'success', 'message' => 'User berhasil dibuat'],
                'data' => $user,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'meta' => ['code' => 500, 'status' => 'error', 'message' => 'Server Error'],
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/users/{id}
     * Admin melihat detail 1 user.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['meta' => ['code' => 404, 'status' => 'error', 'message' => 'User tidak ditemukan']], 404);
        }

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'Detail user'],
            'data' => $user
        ]);
    }

    /**
     * PUT /api/users/{id}
     * Admin mengedit data user (Misal salah input NIP).
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['meta' => ['code' => 404, 'status' => 'error', 'message' => 'User tidak ditemukan']], 404);

        $validator = Validator::make($request->all(), [
            'name'  => 'string|max:255',
            'email' => 'email|unique:users,email,'.$id,
            'role'  => 'in:admin,officer',
        ]);

        if ($validator->fails()) {
            return response()->json(['meta' => ['code' => 422, 'status' => 'error'], 'data' => $validator->errors()], 422);
        }

        $user->update($request->all());

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'User berhasil diupdate'],
            'data' => $user
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Admin menghapus user.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['meta' => ['code' => 404, 'status' => 'error', 'message' => 'User tidak ditemukan']], 404);

        $user->delete();

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success', 'message' => 'User berhasil dihapus']
        ]);
    }
}
