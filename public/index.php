<?php
// Kompresi output (mod_deflate tidak aktif di Laragon)
if (!ini_get('zlib.output_compression')
    && extension_loaded('zlib')
    && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
    ini_set('zlib.output_compression', 'On');
    ini_set('zlib.output_compression_level', '6');
}

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/helpers.php';

use App\Core\Router;
use App\Core\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PenilaianController;
use App\Controllers\FormPenilaianController;
use App\Controllers\BuktiDukungController;
use App\Controllers\ReportController;
use App\Controllers\Admin\PeriodeController;
use App\Controllers\Admin\MadrasahController;
use App\Controllers\Admin\KepalaController;
use App\Controllers\Admin\PenggunaController;
use App\Controllers\Admin\InstrumenController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\PengaturanController;
use App\Controllers\NotifikasiController;

$router = new Router();

// Auth Routes
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/',                  [DashboardController::class, 'index'], ['admin','kamad','penilai']);
$router->get('/dashboard',         [DashboardController::class, 'index'], ['admin','kamad','penilai']);
$router->get('/admin/dashboard',   [DashboardController::class, 'adminDashboard'],  ['admin']);
$router->get('/kamad/dashboard',   [DashboardController::class, 'kamadDashboard'],  ['kamad']);
$router->get('/penilai/dashboard', [DashboardController::class, 'penilaiDashboard'],['penilai']);

// Penilaian Individual
$router->get('/penilai/penilaian',     [PenilaianController::class, 'index'],     ['admin','penilai']);
$router->get('/penilaian',             [PenilaianController::class, 'index'],     ['admin','penilai']);
$router->get('/penilaian/form/{id}',   [PenilaianController::class, 'form'],      ['admin','penilai']);
$router->post('/penilaian/save-score', [PenilaianController::class, 'saveScore'], ['admin','penilai']);
$router->post('/penilaian/submit',     [PenilaianController::class, 'submit'],    ['admin','penilai']);
$router->post('/penilaian/unlock',     [PenilaianController::class, 'unlock'],    ['admin']);

// Menu: Form Penilaian (admin & kamad)
$router->get('/form-penilaian', function() {
    $c = new FormPenilaianController();
    if (\App\Core\Auth::hasRole('admin')) {
        $c->indexAdmin();
    } else {
        $c->indexKamad();
    }
}, ['admin', 'kamad']);
$router->get('/form-penilaian/detail/{id}', [FormPenilaianController::class, 'detail'],      ['admin', 'kamad']);
$router->get('/form-penilaian/cetak/{id}',  [FormPenilaianController::class, 'print'],       ['admin', 'kamad']);
$router->get('/form-penilaian/excel/{id}',  [FormPenilaianController::class, 'exportExcel'], ['admin', 'kamad']);

// Bukti Dukung
$router->get('/bukti',              [BuktiDukungController::class, 'index']);
$router->post('/bukti/upload',      [BuktiDukungController::class, 'upload'],      ['admin','kamad']);
$router->post('/bukti/update/{id}', [BuktiDukungController::class, 'update'],      ['admin','kamad']);
$router->post('/bukti/delete/{id}', [BuktiDukungController::class, 'delete'],      ['admin','kamad']);
$router->get('/bukti/preview/{id}', [BuktiDukungController::class, 'previewPage']);
$router->get('/bukti/stream/{id}',  [BuktiDukungController::class, 'streamFile']);
$router->get('/bukti/view/{id}',    [BuktiDukungController::class, 'previewPage']);

// Laporan & Rekapitulasi
$router->get('/laporan',            [ReportController::class, 'index']);
$router->get('/laporan/pdf/{id}',   [ReportController::class, 'exportPdf']);
$router->get('/laporan/excel/{id}', [ReportController::class, 'exportExcel']);

// ── Admin: Periode ──────────────────────────────────────────────────────────
$router->get('/admin/periode',          [PeriodeController::class, 'index'],  ['admin']);
$router->post('/admin/periode/store',   [PeriodeController::class, 'store'],  ['admin']);
$router->post('/admin/periode/update',  [PeriodeController::class, 'update'], ['admin']);
$router->post('/admin/periode/delete',  [PeriodeController::class, 'destroy'],['admin']);

// ── Admin: Madrasah ─────────────────────────────────────────────────────────
$router->get('/admin/madrasah',         [MadrasahController::class, 'index'],  ['admin']);
$router->post('/admin/madrasah/store',  [MadrasahController::class, 'store'],  ['admin']);
$router->post('/admin/madrasah/update', [MadrasahController::class, 'update'], ['admin']);
$router->post('/admin/madrasah/delete', [MadrasahController::class, 'destroy'],['admin']);

// ── Admin: Kepala Madrasah ──────────────────────────────────────────────────
$router->get('/admin/kepala-madrasah',         [KepalaController::class, 'index'],  ['admin']);
$router->post('/admin/kepala-madrasah/store',  [KepalaController::class, 'store'],  ['admin']);
$router->post('/admin/kepala-madrasah/update', [KepalaController::class, 'update'], ['admin']);
$router->post('/admin/kepala-madrasah/delete', [KepalaController::class, 'destroy'],['admin']);

// ── Admin: Pengguna & Penilai ───────────────────────────────────────────────
$router->get('/admin/pengguna',                 [PenggunaController::class, 'index'],        ['admin']);
$router->post('/admin/pengguna/store',          [PenggunaController::class, 'store'],        ['admin']);
$router->post('/admin/pengguna/update',         [PenggunaController::class, 'update'],       ['admin']);
$router->post('/admin/pengguna/reset-password', [PenggunaController::class, 'resetPassword'],['admin']);
$router->post('/admin/pengguna/toggle-status',  [PenggunaController::class, 'toggleStatus'], ['admin']);
$router->post('/admin/pengguna/delete',         [PenggunaController::class, 'destroy'],      ['admin']);

// ── Admin: Master Instrumen ────────────────────────────────────────────────
$router->get('/admin/instrumen',                   [InstrumenController::class, 'index'],            ['admin']);
$router->post('/admin/instrumen/indikator/store',  [InstrumenController::class, 'storeIndikator'],   ['admin']);
$router->post('/admin/instrumen/indikator/update', [InstrumenController::class, 'updateIndikator'],  ['admin']);
$router->post('/admin/instrumen/indikator/delete', [InstrumenController::class, 'destroyIndikator'], ['admin']);

// ── Admin: Audit Log ────────────────────────────────────────────────────────
$router->get('/admin/audit-log',        [AuditLogController::class, 'index'], ['admin']);
$router->post('/admin/audit-log/clear', [AuditLogController::class, 'clear'], ['admin']);

// ── Admin: Pengaturan Sistem & Tampilan ──────────────────────────────────────
$router->get('/admin/pengaturan',          [PengaturanController::class, 'index'],   ['admin']);
$router->post('/admin/pengaturan/update',  [PengaturanController::class, 'update'],  ['admin']);
$router->post('/admin/pengaturan/reset-bg',[PengaturanController::class, 'resetBg'], ['admin']);

// Notifikasi (admin & kepala madrasah)
$router->get('/notifikasi',      [NotifikasiController::class, 'index'], ['admin', 'kamad']);
$router->post('/notifikasi/read', [NotifikasiController::class, 'read'],  ['admin', 'kamad']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
