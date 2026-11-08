<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
        <h1 class="text-6xl font-extrabold text-slate-800">404</h1>
        <p class="text-sm text-slate-500 mt-2">Halaman yang Anda tuju tidak ditemukan atau telah dipindahkan.</p>
        <a href="<?= base_url('dashboard') ?>" class="mt-4 inline-block px-4 py-2 bg-slate-800 text-white rounded-lg text-xs font-bold">Kembali ke Dashboard</a>
    </div>
</body>
</html>
