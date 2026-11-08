<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rekapitulasi Nilai & Laporan PKKM</h2>
            <p class="text-xs text-slate-500 mt-1">Hasil kumulatif penilaian kinerja kepala madrasah empat tahunan</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
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

    <!-- Rekapitulasi: kartu di mobile, tabel di desktop -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-sm">Tabel Pembobotan Nilai 6 Kelompok Penilai</h3>
            <p class="text-xs text-slate-500">Rumus: NKTE = (4×KS + 2×KY + PM + GR + TK + KM) / 10</p>
        </div>

        <?php
        $defGroups = [
            ['no' => 1, 'kode' => 'KS', 'nama' => 'Hasil Penilaian Kepala Seksi', 'bobot' => 4],
            ['no' => 2, 'kode' => 'KY', 'nama' => 'Hasil Penilaian Ketua Yayasan', 'bobot' => 2],
            ['no' => 3, 'kode' => 'WASMAD', 'nama' => 'Hasil Rata-Rata Penilaian Pengawas Madrasah', 'bobot' => 1],
            ['no' => 4, 'kode' => 'GURU', 'nama' => 'Hasil Rata-Rata Penilaian Guru', 'bobot' => 1],
            ['no' => 5, 'kode' => 'TENDIK', 'nama' => 'Hasil Rata-Rata Penilaian Tenaga Kependidikan', 'bobot' => 1],
            ['no' => 6, 'kode' => 'KOMITE', 'nama' => 'Hasil Rata-Rata Penilaian Komite', 'bobot' => 1],
        ];

        $rows = [];
        foreach ($defGroups as $g) {
            $grpData = $rekap['groups'][$g['kode']] ?? null;
            $avg = (float)($grpData['rata_rata'] ?? 0.0);
            $rows[] = [
                'meta' => $g,
                'data' => $grpData,
                'avg' => $avg,
                'weighted' => round(($avg * $g['bobot']) / 10.0, 2),
            ];
        }
        $pw = predikat_warna($rekap['predikat'] ?? null);
        ?>

        <!-- Mobile: daftar kartu (tanpa tabel) -->
        <div class="lg:hidden divide-y divide-slate-100">
            <?php foreach ($rows as $row): $g = $row['meta']; $grpData = $row['data']; ?>
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"><?= $g['no'] ?>. <?= e($g['kode']) ?></div>
                        <div class="text-sm font-bold text-slate-800 leading-snug mt-0.5"><?= e($g['nama']) ?></div>
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Bobot <?= $g['bobot'] ?></span>
                </div>

                <div class="mt-2">
                    <?php if ($grpData && $grpData['is_lengkap']): ?>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lengkap (<?= $grpData['penilai_selesai'] ?> Penilai)</span>
                    <?php else: ?>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Belum Lengkap (<?= $grpData['penilai_selesai'] ?? 0 ?>/<?= $grpData['total_penilai'] ?? 0 ?>)</span>
                    <?php endif; ?>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-slate-50 border border-slate-200/70 p-2.5">
                        <div class="text-[10px] text-slate-400 font-medium">Skor Rata-Rata</div>
                        <div class="text-sm font-bold text-slate-800"><?= number_format($row['avg'], 2) ?></div>
                    </div>
                    <div class="rounded-lg bg-brand-50 border border-brand-200/70 p-2.5">
                        <div class="text-[10px] text-brand-600 font-medium">Hasil Bobot</div>
                        <div class="text-sm font-bold text-brand-700"><?= number_format($row['weighted'], 2) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="p-4 bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Hasil Penilaian Empat Tahunan (NKTE)</div>
                <div class="text-2xl font-black text-slate-900 leading-none mt-1"><?= number_format($rekap['nkte'], 2) ?></div>
                <div class="mt-2">
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                          style="background:<?= $pw['bg'] ?>;color:<?= $pw['fg'] ?>;">
                        <?= e($rekap['predikat']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Desktop: tabel -->
        <div class="hidden lg:block overflow-x-auto">
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
                    <?php foreach ($rows as $row): $g = $row['meta']; $grpData = $row['data']; ?>
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
                            <td class="px-6 py-4 text-right font-semibold text-slate-800"><?= number_format($row['avg'], 2) ?></td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800"><?= number_format($row['weighted'], 2) ?></td>
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
                        <td class="px-6 py-3 text-right text-sm uppercase" style="color:<?= $pw['fg'] ?>"><?= e($rekap['predikat']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
