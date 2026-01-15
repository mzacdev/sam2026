<?php
/**
 * Results Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Keputusan';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Keputusan</h2>
                    <p class="text-muted">Rekod keputusan pertandingan</p>
                </div>
                <button class="btn btn-primary">
                    <i class="cil cil-plus me-1"></i> Rekod Keputusan
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <select class="form-select" id="filterSport">
                <option value="">Semua Sukan</option>
            </select>
        </div>
        <!-- event filter removed as requested -->
        <div class="col-md-3">
            <select class="form-select" id="filterKategori" disabled>
                <option value="">Semua Kategori</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" class="form-control" id="filterDate">
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="completed">Selesai</option>
                <option value="ongoing">Sedang Berlangsung</option>
                <option value="upcoming">Akan Datang</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Senarai Keputusan</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Sukan</th>
                                    <th scope="col">Acara</th>
                                    <th scope="col">Tarikh</th>
                                    <th scope="col">Tempat Pertama</th>
                                    <th scope="col">Tempat Kedua</th>
                                    <th scope="col">Tempat Ketiga</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <tr id="noResultsRow">
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="cil cil-award" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada keputusan direkodkan</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <script>
            // Load sports, events and results; simple AJAX wiring
            (function(){
                const sportSel = document.getElementById('filterSport');
                // event filter removed
                const dateInp = document.getElementById('filterDate');
                const statusSel = document.getElementById('filterStatus');
                const resultsBody = document.getElementById('resultsBody');
                const noRow = document.getElementById('noResultsRow');

                function fetchJSON(url){
                    return fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                        .then(r => r.json()).catch(()=>({success:false}));
                }

                function loadSports(){
                    return fetchJSON('<?php echo url("ajax/sport_list.php"); ?>').then(res=>{
                        console.log('sport_list response', res);
                        if(!res){ console.warn('Empty response from sport_list'); }
                        if(res && res.success){
                            res.data.forEach(s=>{
                                const o = document.createElement('option'); o.value = s.id; o.textContent = s.nama_sukan;
                                sportSel.appendChild(o);
                            });
                        } else {
                            console.warn('sport_list returned success=false or no data');
                        }
                        // after sports loaded
                    }).catch(err=>{ console.error('Failed to fetch sport_list', err); });
                }

                // loadEvents removed (event filter disabled)

                // Load kategori (categories) dependent on sukan
                const kategoriSel = document.getElementById('filterKategori');
                function resetKategori(){
                    if(!kategoriSel) return;
                    kategoriSel.innerHTML = '<option value="">Semua Kategori</option>';
                    kategoriSel.disabled = true;
                }

                function loadKategori(sukan_id){
                    if(!kategoriSel) return;
                    resetKategori();
                    if(!sukan_id) return;
                    kategoriSel.innerHTML = '<option>Loading...</option>';
                    kategoriSel.disabled = true;
                    fetchJSON('<?php echo url("ajax/get_kategori_by_sukan.php"); ?>?sukan_id=' + encodeURIComponent(sukan_id)).then(res=>{
                        console.log('get_kategori_by_sukan response', res);
                        if(res && res.success && Array.isArray(res.data) && res.data.length){
                            kategoriSel.innerHTML = '<option value="">Pilih Kategori</option>';
                            res.data.forEach(p=>{
                                const o = document.createElement('option'); o.value = p.id; o.textContent = p.nama_kategori || ('Kategori ' + p.id);
                                kategoriSel.appendChild(o);
                            });
                            kategoriSel.disabled = false;
                        } else {
                            kategoriSel.innerHTML = '<option value="">Tiada kategori untuk sukan ini</option>';
                            kategoriSel.disabled = true;
                        }
                    }).catch(err=>{ console.error('Failed to fetch kategori', err); kategoriSel.innerHTML = '<option value="">Ralat memuat kategori</option>'; kategoriSel.disabled = true; });
                }

                function renderResults(rows){
                    resultsBody.innerHTML = '';
                    if(!rows || rows.length === 0){
                        noRow.style.display = '';
                        return;
                    }
                    noRow.style.display = 'none';
                    rows.forEach((r, idx)=>{
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${idx+1}</td>
                            <td>${r.sukan || ''}</td>
                            <td>${r.acara || ''}</td>
                            <td>${r.tarikh || ''}</td>
                            <td>${r.tempat_pertama || ''}</td>
                            <td>${r.tempat_kedua || ''}</td>
                            <td>${r.tempat_ketiga || ''}</td>
                            <td>${r.status || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-secondary">Lihat</button>
                            </td>`;
                        resultsBody.appendChild(tr);
                    });
                }

                function loadResults(){
                    const params = new URLSearchParams();
                    if(sportSel.value) params.set('sukan_id', sportSel.value);
                    // event filter removed
                    if(dateInp.value) params.set('tarikh', dateInp.value);
                    if(statusSel.value) params.set('status', statusSel.value);
                    fetchJSON('<?php echo url("ajax/results_list.php"); ?>?' + params.toString()).then(res=>{
                        console.log('results_list response', res);
                        if(res && res.success){
                            renderResults(res.data || []);
                        } else renderResults([]);
                    }).catch(err=>{ console.error('Failed to fetch results_list', err); renderResults([]); });
                }

                sportSel.addEventListener('change', function(){ loadKategori(this.value); loadResults(); });
                // event filter listener removed
                dateInp.addEventListener('change', loadResults);
                statusSel.addEventListener('change', loadResults);

                // init: load sports first so dependent events dropdown can populate
                loadSports().then(()=> loadResults());
            })();
            </script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>

