<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?= e($bukti['nama_dokumen']) ?> — PKKM</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if (strtolower($bukti['file_ext']) === 'pdf'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <?php endif; ?>
    <style>
        * { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <!-- Top Navigation Toolbar -->
    <header class="bg-slate-950/90 backdrop-blur border-b border-slate-800 px-4 py-3 sticky top-0 z-30 flex items-center justify-between flex-wrap gap-3">
        <!-- Title & Metadata -->
        <div class="flex items-center space-x-3 min-w-0">
            <span class="px-2 py-1 rounded bg-brand-600/30 border border-brand-500/40 text-brand-300 font-bold text-xs uppercase tracking-wider">
                <?= e($bukti['file_ext']) ?>
            </span>
            <div class="min-w-0">
                <h1 class="text-sm font-bold text-white truncate max-w-md md:max-w-xl" title="<?= e($bukti['nama_dokumen']) ?>">
                    <?= e($bukti['nama_dokumen']) ?>
                </h1>
                <p class="text-[11px] text-slate-400">
                    <?= e($bukti['kategori'] ?? 'Umum') ?> · <?= round($bukti['file_size'] / 1024, 1) ?> KB · Diunggah oleh <?= e($bukti['pengunggah'] ?? '-') ?>
                </p>
            </div>
        </div>

        <!-- Controls for PDF -->
        <?php if (strtolower($bukti['file_ext']) === 'pdf'): ?>
        <div class="flex items-center space-x-1.5 bg-slate-800/80 px-2 py-1 rounded-xl border border-slate-700/60 text-xs">
            <button id="btnPrevPage" class="p-1.5 hover:bg-slate-700 rounded-lg text-slate-300 hover:text-white transition disabled:opacity-40" title="Halaman Sebelumnya">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span class="px-2 text-slate-300 font-medium">
                Halaman <span id="pageNum" class="text-white font-bold">1</span> / <span id="pageCount" class="text-white font-bold">-</span>
            </span>
            <button id="btnNextPage" class="p-1.5 hover:bg-slate-700 rounded-lg text-slate-300 hover:text-white transition disabled:opacity-40" title="Halaman Berikutnya">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="w-px h-4 bg-slate-700 mx-1"></div>
            <button id="btnZoomOut" class="p-1.5 hover:bg-slate-700 rounded-lg text-slate-300 hover:text-white transition" title="Perkecil">
                <i class="fa-solid fa-magnifying-glass-minus"></i>
            </button>
            <span id="zoomPercent" class="px-1 text-slate-300 font-medium text-[11px]">100%</span>
            <button id="btnZoomIn" class="p-1.5 hover:bg-slate-700 rounded-lg text-slate-300 hover:text-white transition" title="Perbesar">
                <i class="fa-solid fa-magnifying-glass-plus"></i>
            </button>
        </div>
        <?php endif; ?>

        <!-- Right Action Buttons -->
        <div class="flex items-center space-x-2">
            <button onclick="downloadFromMemory()"
               class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold flex items-center space-x-1.5 transition">
                <i class="fa-solid fa-download"></i>
                <span class="hidden sm:inline">Unduh</span>
            </button>
            <button onclick="window.close()"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-rose-900/50 hover:border-rose-700/60 text-slate-300 hover:text-rose-300 border border-slate-700 text-xs font-semibold flex items-center space-x-1.5 transition">
                <i class="fa-solid fa-xmark"></i>
                <span class="hidden sm:inline">Tutup Tab</span>
            </button>
        </div>
    </header>

    <!-- Main Content Viewer -->
    <main class="flex-1 flex items-center justify-center p-4 overflow-auto bg-slate-900">
        <?php $ext = strtolower($bukti['file_ext']); ?>

        <?php if ($ext === 'pdf'): ?>
            <!-- PDF Viewer with PDF.js (In-Memory Base64 - 100% Zero HTTP Fetch, Immune to IDM) -->
            <div id="pdfViewerContainer" class="flex flex-col items-center justify-center w-full max-w-5xl py-4 space-y-4">
                <div id="loadingIndicator" class="text-center py-12">
                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-brand-500 mb-3 block"></i>
                    <p class="text-xs text-slate-400">Merender dokumen PDF...</p>
                </div>
                <div id="canvasWrapper" class="hidden shadow-2xl rounded-lg overflow-hidden border border-slate-800 bg-white">
                    <canvas id="pdfCanvas" class="block"></canvas>
                </div>
            </div>

            <!-- In-Memory Data Island -->
            <script id="rawBase64Data" type="text/plain"><?= $base64 ?></script>

            <script>
                const b64Data = document.getElementById('rawBase64Data').textContent.trim();
                let pdfDoc = null;
                let pageNum = 1;
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.35;
                const canvas = document.getElementById('pdfCanvas');
                const ctx = canvas.getContext('2d');

                function renderPage(num) {
                    pageRendering = true;
                    pdfDoc.getPage(num).then(page => {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        const renderTask = page.render(renderContext);

                        renderTask.promise.then(() => {
                            pageRendering = false;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });

                    document.getElementById('pageNum').textContent = num;
                    document.getElementById('btnPrevPage').disabled = (num <= 1);
                    document.getElementById('btnNextPage').disabled = (num >= pdfDoc.numPages);
                }

                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                document.getElementById('btnPrevPage').addEventListener('click', () => {
                    if (pageNum <= 1) return;
                    pageNum--;
                    queueRenderPage(pageNum);
                });

                document.getElementById('btnNextPage').addEventListener('click', () => {
                    if (pageNum >= pdfDoc.numPages) return;
                    pageNum++;
                    queueRenderPage(pageNum);
                });

                document.getElementById('btnZoomIn').addEventListener('click', () => {
                    if (scale >= 3.0) return;
                    scale += 0.2;
                    document.getElementById('zoomPercent').textContent = Math.round((scale / 1.35) * 100) + '%';
                    queueRenderPage(pageNum);
                });

                document.getElementById('btnZoomOut').addEventListener('click', () => {
                    if (scale <= 0.6) return;
                    scale -= 0.2;
                    document.getElementById('zoomPercent').textContent = Math.round((scale / 1.35) * 100) + '%';
                    queueRenderPage(pageNum);
                });

                // Decode base64 in memory without any fetch / HTTP request
                try {
                    const binaryString = atob(b64Data);
                    const len = binaryString.length;
                    const bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }

                    pdfjsLib.getDocument({ data: bytes }).promise.then(pdf => {
                        pdfDoc = pdf;
                        document.getElementById('pageCount').textContent = pdf.numPages;
                        document.getElementById('loadingIndicator').classList.add('hidden');
                        document.getElementById('canvasWrapper').classList.remove('hidden');
                        renderPage(pageNum);
                    }).catch(err => {
                        document.getElementById('loadingIndicator').innerHTML = `
                            <div class="bg-rose-950/50 border border-rose-800 p-6 rounded-2xl max-w-md text-center">
                                <i class="fa-solid fa-circle-exclamation text-rose-500 text-3xl mb-2"></i>
                                <h4 class="text-white font-bold text-sm mb-1">Gagal Membuka PDF</h4>
                                <p class="text-xs text-rose-300">${err.message}</p>
                            </div>
                        `;
                    });
                } catch (e) {
                    document.getElementById('loadingIndicator').innerHTML = `
                        <div class="bg-rose-950/50 border border-rose-800 p-6 rounded-2xl max-w-md text-center">
                            <i class="fa-solid fa-circle-exclamation text-rose-500 text-3xl mb-2"></i>
                            <h4 class="text-white font-bold text-sm mb-1">Gagal Decode Berkas</h4>
                            <p class="text-xs text-rose-300">${e.message}</p>
                        </div>
                    `;
                }
            </script>

        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
            <!-- Image Viewer via In-Memory Data URL (Zero HTTP request, IDM-immune) -->
            <div class="flex items-center justify-center w-full h-full p-4">
                <img src="data:<?= $mime ?>;base64,<?= $base64 ?>" 
                     alt="<?= e($bukti['nama_dokumen']) ?>"
                     class="max-h-[85vh] max-w-full object-contain rounded-xl shadow-2xl border border-slate-800 bg-slate-950/60 p-2">
            </div>

        <?php else: ?>
            <!-- Office Document / Other Format Info Card -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-8 max-w-md text-center shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-brand-600/20 text-brand-400 flex items-center justify-center text-3xl mx-auto mb-4 border border-brand-500/30">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <h3 class="text-base font-bold text-white mb-1"><?= e($bukti['nama_dokumen']) ?></h3>
                <p class="text-xs text-slate-400 mb-6">
                    Format berkas <b>.<?= e($bukti['file_ext']) ?></b> tidak mendukung pratinjau canvas langsung. Anda dapat mengunduh berkas ini.
                </p>
                <button onclick="downloadFromMemory()"
                   class="inline-flex items-center justify-center space-x-2 px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-brand-600 hover:bg-brand-500 transition shadow-lg shadow-brand-600/30">
                    <i class="fa-solid fa-download"></i>
                    <span>Unduh Berkas Sekarang</span>
                </button>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function downloadFromMemory() {
        const raw = atob('<?= $base64 ?>');
        const u8 = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) {
            u8[i] = raw.charCodeAt(i);
        }
        const blob = new Blob([u8], { type: '<?= $mime ?>' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = '<?= addslashes($bukti['nama_dokumen'] . '.' . $ext) ?>';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }
    </script>
</body>
</html>
