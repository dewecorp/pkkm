<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rekapitulasi Nilai & Laporan PKKM</h2>
            <p class="text-xs text-slate-500 mt-1">Hasil kumulatif penilaian kinerja kepala madrasah empat tahunan</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('laporan/excel/' . $periode['id']) ?>" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 shadow">
                <i class="fa-solid fa-file-excel"></i>
                <span>Ekspor Excel</span>
            </a>
            <a href="<?= base_url('laporan/pdf/' . $periode['id']) ?>" target="_blank" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 shadow">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Cetak PDF</span>
            </a>
        </div>
    </div>

    <!-- Rincian Identitas -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div>
            <h4 class="font-bold text-slate-800 uppercase tracking-wider mb-2 border-b pb-1">Data Madrasah</h4>
            <div class="space-y-1 text-slate-600">
                <div>Nama Madrasah: <strong class="text-slate-800"><?= e($periode['nama_madrasah']) ?></strong></div>
                <div>NPSN / NSM: <?= e($periode['npsn']) ?> / <?= e($periode['nsm']) ?></div>
                <div>Status Akreditasi: <?= e($periode['status_akreditasi']) ?> (Tahun <?= e($periode['tahun_akreditasi']) ?>)</div>
                <div>Kabupaten / Provinsi: <?= e($periode['kabupaten_kota']) ?> / <?= e($periode['provinsi']) ?></div>
            </div>
        </div>
        <div>
            <h4 class="font-bold text-slate-800 uppercase tracking-wider mb-2 border-b pb-1">Data Kepala Madrasah</h4>
            <div class="space-y-1 text-slate-600">
                <div>Nama Kepala Madrasah: <strong class="text-slate-800"><?= e($periode['nama_kamad']) ?></strong></div>
                <div>NIP / NPK: <?= e($periode['kamad_nip']) ?> / <?= e($periode['kamad_npk']) ?></div>
                <div>Pangkat / Golongan: <?= e($periode['kamad_gol']) ?></div>
                <div>Tahun Penilaian: <?= e($periode['tahun_penilaian']) ?></div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekapitulasi Sesuai Excel -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-sm">Tabel Pembobotan Nilai 6 Kelompok Penilai</h3>
            <p class="text-xs text-slate-500">Rumus: NKTE = (4×KS + 2×KY + PM + GR + TK + KM) / 10</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200 font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 w-12 text-center">No</th>
                        <th class="px-6 py-3">Jenis Penilai</th>
                        <th class="px-6 py-3 text-center">Bobot</th>
                        <th class="px-6 py-3 text-center">Status Kelengkapan</th>
                        <th class="px-6 py-3 text-right">Skor Rata-Rata</th>
                        <th class="px-6 py-3 text-right">Hasil Bobot (Bobot x Skor / 10)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php
                    $defGroups = [
                        ['no' => 1, 'kode' => 'KS', 'nama' => 'Hasil Penilaian Kepala Seksi', 'bobot' => 4],
                        ['no' => 2, 'kode' => 'KY', 'nama' => 'Hasil Penilaian Ketua Yayasan', 'bobot' => 2],
                        ['no' => 3, 'kode' => 'WASMAD', 'nama' => 'Hasil Rata-Rata Penilaian Pengawas Madrasah', 'bobot' => 1],
                        ['no' => 4, 'kode' => 'GURU', 'nama' => 'Hasil Rata-Rata Penilaian Guru', 'bobot' => 1],
                        ['no' => 5, 'kode' => 'TENDIK', 'nama' => 'Hasil Rata-Rata Penilaian Tenaga Kependidikan', 'bobot' => 1],
                        ['no' => 6, 'kode' => 'KOMITE', 'nama' => 'Hasil Rata-Rata Penilaian Komite', 'bobot' => 1],
                    ];
                    foreach ($defGroups as $g):
                        $grpData = $rekap['groups'][$g['kode']] ?? null;
                        $avg = $grpData['rata_rata'] ?? 0.0;
                        $weighted = round(($avg * $g['bobot']) / 10.0, 2);
                    ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium"><?= $g['no'] ?>.</td>
                            <td class="px-6 py-4 font-bold text-slate-800"><?= $g['nama'] ?></td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700"><?= $g['bobot'] ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($grpData && $grpData['is_lengkap']): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lengkap (<?= $grpData['penilai_selesai'] ?> Penilai)</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Belum Lengkap (<?= $grpData['penilai_selesai'] ?? 0 ?>/<?= $grpData['total_penilai'] ?? 0 ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800"><?= number_format($avg, 2) ?></td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800"><?= number_format($weighted, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right uppercase text-slate-900">Hasil Penilaian Empat Tahunan (NKTE):</td>
                        <td class="px-6 py-4 text-right text-base text-slate-900"><?= number_format($rekap['nkte'], 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right uppercase text-slate-700">Predikat Penilaian Kinerja:</td>
                        <td class="px-6 py-3 text-right text-sm text-emerald-700 uppercase"><?= e($rekap['predikat']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
