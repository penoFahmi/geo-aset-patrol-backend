<!DOCTYPE html>
<html>
<head>
    <title>Akses Login GeoAset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">

        <h2 style="color: #00796B; text-align: center;">GeoAset Patrol</h2>
        <hr>

        <p>Halo <strong>{{ $user->name }}</strong>,</p>

        <p>Akun Anda telah didaftarkan sebagai Petugas di sistem <strong>GeoAset Patrol BKAD</strong>.</p>
        <p>Berikut adalah kredensial untuk login ke aplikasi:</p>

        <div style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Password:</strong> <span style="color: #d9534f; font-weight: bold;">{{ $password }}</span></p>
            <p style="margin: 5px 0;"><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
        </div>

        <p>Demi keamanan, mohon segera login dan ubah password Anda.</p>

        <br>
        <p>Terima Kasih,<br>Admin GeoAset</p>

    </div>

</body>
</html>
