<?php
/**
 * Keputusan (Results) Management Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../api/models/SportModel.php';

$page_title = 'Keputusan';

// Get current user role
Session::start();
$auth = getAuth();
$currentUserRole = Session::get('user_role') ?? '';

// Fetch sports from database
$sports = [];
try {
    $sportModel = new SportModel();
    $result = $sportModel->getAll(['limit' => 1000, 'status' => 1]);
    if ($result['success']) {
        $sports = $result['data'];
    }
} catch (Exception $e) {
    error_log('[keputusan.php] DB error fetching sports: ' . $e->getMessage());
}

ob_start();
?>
<div class="w-100 px-3">
    <!-- Hero -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Keputusan</h2>
                        <p class="text-muted mb-0">Rekod keputusan pertandingan - tempat pertama, kedua, dan ketiga</p>
                    </div>
                    <button class="btn btn-primary" id="btnAddKeputusan">
                        <i class="cil cil-plus me-1"></i> Rekod Keputusan Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-3">
            <select class="form-select" id="filterSport">
                <option value="">Semua Sukan</option>
                <?php foreach ($sports as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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

    <!-- Results List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
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
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Tarikh</th>
                                    <th scope="col">Tempat Pertama</th>
                                    <th scope="col">Tempat Kedua</th>
                                    <th scope="col">Tempat Ketiga</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="keputusanBody">
                                <tr id="noKeputusanRow">
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="modalKeputusan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalKeputusanTitle">Rekod Keputusan Baru</h5>
                <button type="button" class="btn-close" onclick="closeKeputusanModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formKeputusan">
                    <input type="hidden" id="keputusanId" name="id">
                    
                    <div class="mb-3">
                        <label for="keputusanSukan" class="form-label">Sukan <span class="text-danger">*</span></label>
                        <select class="form-select" id="keputusanSukan" name="sukan_id" required>
                            <option value="">Pilih Sukan</option>
                            <?php foreach ($sports as $s): ?>
                                <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keputusanKategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="keputusanKategori" name="kategori_id" required disabled>
                            <option value="">Pilih Kategori</option>
                        </select>
                        <small class="text-muted">Pilih sukan terlebih dahulu</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keputusanTarikh" class="form-label">Tarikh <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="keputusanTarikh" name="tarikh" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pemenang</label>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="keputusanPertama" class="form-label small text-muted">Tempat Pertama</label>
                                <select class="form-select" id="keputusanPertama" name="tempat_pertama">
                                    <option value="">Pilih Pemenang</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="keputusanKedua" class="form-label small text-muted">Tempat Kedua</label>
                                <select class="form-select" id="keputusanKedua" name="tempat_kedua">
                                    <option value="">Pilih Pemenang</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="keputusanKetiga" class="form-label small text-muted">Tempat Ketiga</label>
                                <select class="form-select" id="keputusanKetiga" name="tempat_ketiga">
                                    <option value="">Pilih Pemenang</option>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted">Pilih kategori terlebih dahulu untuk memuatkan senarai peserta</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keputusanStatus" class="form-label">Status</label>
                        <select class="form-select" id="keputusanStatus" name="status">
                            <option value="completed">Selesai</option>
                            <option value="ongoing">Sedang Berlangsung</option>
                            <option value="upcoming">Akan Datang</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeKeputusanModal()">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveKeputusan">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const sportSel = document.getElementById('filterSport');
    const kategoriSel = document.getElementById('filterKategori');
    const dateInp = document.getElementById('filterDate');
    const statusSel = document.getElementById('filterStatus');
    const keputusanBody = document.getElementById('keputusanBody');
    const noRow = document.getElementById('noKeputusanRow');
    
    // Modal instance variable
    let modalKeputusanInstance = null;
    const formKeputusan = document.getElementById('formKeputusan');
    const keputusanSukan = document.getElementById('keputusanSukan');
    const keputusanKategori = document.getElementById('keputusanKategori');
    const keputusanTarikh = document.getElementById('keputusanTarikh');
    const keputusanPertama = document.getElementById('keputusanPertama');
    const keputusanKedua = document.getElementById('keputusanKedua');
    const keputusanKetiga = document.getElementById('keputusanKetiga');
    const keputusanStatus = document.getElementById('keputusanStatus');
    const keputusanId = document.getElementById('keputusanId');
    
    let currentCategoryType = null;
    
    function fetchJSON(url){
        return fetch(url, { 
            method: 'GET', 
            credentials: 'same-origin', 
            headers: { 'Accept': 'application/json' } 
        }).then(r => r.json()).catch(()=>({success:false}));
    }
    
    function checkCategoriesWithResults(sukan_id, kategoriSelect){
        // Only check if we're in the modal (keputusanKategori)
        if (!kategoriSelect || kategoriSelect !== keputusanKategori) {
            return Promise.resolve([]);
        }
        
        if (!sukan_id) {
            return Promise.resolve([]);
        }
        
        const currentResultId = keputusanId.value || null;
        const excludeParam = currentResultId ? '&exclude_id=' + encodeURIComponent(currentResultId) : '';
        const url = '<?php echo url("ajax/check_kategori_has_result.php"); ?>?sukan_id=' + encodeURIComponent(sukan_id) + excludeParam;
        
        return fetchJSON(url)
            .then(res=>{
                if(res && res.success && Array.isArray(res.data)){
                    return res.data; // Array of kategori IDs that have results
                }
                return [];
            })
            .catch(err=>{
                console.error('Failed to check categories with results', err);
                return [];
            });
    }
    
    function updateCategoryRestrictions(){
        // Only update if we're in the modal and sport is set and categories are loaded
        if (!keputusanSukan.value || keputusanKategori.options.length <= 1) {
            return;
        }
        
        const sukanId = keputusanSukan.value;
        
        checkCategoriesWithResults(sukanId, keputusanKategori)
            .then(kategoriWithResults => {
                // Reset all options first
                Array.from(keputusanKategori.options).forEach(opt => {
                    if(opt.value && opt.value !== ''){
                        opt.disabled = false;
                        opt.style.color = '';
                        opt.textContent = (opt.textContent || '').replace(' (Sudah ada keputusan)', '');
                    }
                });
                
                // Disable categories that have results
                Array.from(keputusanKategori.options).forEach(opt => {
                    if(opt.value && opt.value !== ''){
                        const kategoriId = parseInt(opt.value);
                        if(kategoriWithResults.includes(kategoriId)){
                            opt.disabled = true;
                            opt.style.color = '#999';
                            opt.textContent = (opt.textContent || '') + ' (Sudah ada keputusan)';
                        }
                    }
                });
            });
    }
    
    function loadKategori(sukan_id, targetSelect = null){
        const select = targetSelect || kategoriSel;
        if(!select) return Promise.resolve();
        
        select.innerHTML = '<option value="">Loading...</option>';
        select.disabled = true;
        
        if(!sukan_id){
            select.innerHTML = '<option value="">Semua Kategori</option>';
            select.disabled = true;
            return Promise.resolve();
        }
        
        return fetchJSON('<?php echo url("ajax/get_kategori_by_sukan.php"); ?>?sukan_id=' + encodeURIComponent(sukan_id))
            .then(res=>{
                if(res && res.success && Array.isArray(res.data) && res.data.length){
                    select.innerHTML = '<option value="">Pilih Kategori</option>';
                    res.data.forEach(k=>{
                        const o = document.createElement('option');
                        o.value = k.id;
                        o.textContent = k.nama_kategori || ('Kategori ' + k.id);
                        select.appendChild(o);
                    });
                    select.disabled = false;
                    
                    // If this is the modal kategori select, check for existing results
                    if (select === keputusanKategori) {
                        // Use setTimeout to ensure DOM is updated before checking
                        setTimeout(() => {
                            updateCategoryRestrictions();
                        }, 100);
                    }
                    return Promise.resolve();
                } else {
                    select.innerHTML = '<option value="">Tiada kategori untuk sukan ini</option>';
                    select.disabled = true;
                    return Promise.resolve();
                }
            })
            .catch(err=>{
                console.error('Failed to fetch kategori', err);
                select.innerHTML = '<option value="">Ralat memuat kategori</option>';
                select.disabled = true;
                return Promise.resolve();
            });
    }
    
    function loadParticipants(kategori_id){
        console.log('loadParticipants called with kategori_id:', kategori_id);
        if(!kategori_id){
            keputusanPertama.innerHTML = '<option value="">Pilih Pemenang</option>';
            keputusanKedua.innerHTML = '<option value="">Pilih Pemenang</option>';
            keputusanKetiga.innerHTML = '<option value="">Pilih Pemenang</option>';
            return Promise.resolve();
        }
        
        keputusanPertama.innerHTML = '<option value="">Loading...</option>';
        keputusanKedua.innerHTML = '<option value="">Loading...</option>';
        keputusanKetiga.innerHTML = '<option value="">Loading...</option>';
        
        const url = '<?php echo url("ajax/get_participants_by_kategori.php"); ?>?kategori_id=' + encodeURIComponent(kategori_id);
        console.log('Fetching participants from:', url);
        
        return fetchJSON(url)
            .then(res=>{
                console.log('Participants response:', res);
                if(res && res.success && Array.isArray(res.data)){
                    currentCategoryType = res.type;
                    console.log('Found', res.data.length, 'participants, type:', res.type);
                    
                    if(res.data.length > 0){
                        const optionHtml = '<option value="">Pilih Pemenang</option>' +
                            res.data.map(p => `<option value="${p.id}">${p.display_name || p.nama || p.nama_pasukan}</option>`).join('');
                        
                        // Store current selections before clearing
                        const currentPertama = keputusanPertama.value;
                        const currentKedua = keputusanKedua.value;
                        const currentKetiga = keputusanKetiga.value;
                        
                        keputusanPertama.innerHTML = optionHtml;
                        keputusanKedua.innerHTML = optionHtml;
                        keputusanKetiga.innerHTML = optionHtml;
                        
                        // Restore selections if they exist and are valid
                        if (currentPertama) {
                            const opt = keputusanPertama.querySelector(`option[value="${currentPertama}"]`);
                            if (opt) keputusanPertama.value = currentPertama;
                        }
                        if (currentKedua) {
                            const opt = keputusanKedua.querySelector(`option[value="${currentKedua}"]`);
                            if (opt) keputusanKedua.value = currentKedua;
                        }
                        if (currentKetiga) {
                            const opt = keputusanKetiga.querySelector(`option[value="${currentKetiga}"]`);
                            if (opt) keputusanKetiga.value = currentKetiga;
                        }
                        
                        // Update dropdowns to disable already selected options
                        updateParticipantDropdowns();
                    } else {
                        const emptyHtml = '<option value="">Tiada peserta didaftarkan untuk kategori ini</option>';
                        keputusanPertama.innerHTML = emptyHtml;
                        keputusanKedua.innerHTML = emptyHtml;
                        keputusanKetiga.innerHTML = emptyHtml;
                    }
                } else {
                    console.warn('Invalid response or no data:', res);
                    const emptyHtml = '<option value="">Tiada peserta didaftarkan</option>';
                    keputusanPertama.innerHTML = emptyHtml;
                    keputusanKedua.innerHTML = emptyHtml;
                    keputusanKetiga.innerHTML = emptyHtml;
                }
            })
            .catch(err=>{
                console.error('Failed to fetch participants', err);
                const errorHtml = '<option value="">Ralat memuat peserta</option>';
                keputusanPertama.innerHTML = errorHtml;
                keputusanKedua.innerHTML = errorHtml;
                keputusanKetiga.innerHTML = errorHtml;
            });
    }
    
    function renderKeputusan(rows){
        keputusanBody.innerHTML = '';
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
                <td>${r.kategori || ''}</td>
                <td>${r.tarikh || ''}</td>
                <td>${r.tempat_pertama_nama || r.tempat_pertama || '-'}</td>
                <td>${r.tempat_kedua_nama || r.tempat_kedua || '-'}</td>
                <td>${r.tempat_ketiga_nama || r.tempat_ketiga || '-'}</td>
                <td><span class="badge bg-${r.status === 'completed' ? 'success' : r.status === 'ongoing' ? 'warning' : 'info'}">${r.status || ''}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1 btn-edit-keputusan" data-id="${r.id}">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-keputusan" data-id="${r.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>`;
            keputusanBody.appendChild(tr);
        });
    }
    
    function loadKeputusan(){
        const params = new URLSearchParams();
        if(sportSel.value) params.set('sukan_id', sportSel.value);
        if(kategoriSel.value) params.set('kategori_id', kategoriSel.value);
        if(dateInp.value) params.set('tarikh', dateInp.value);
        if(statusSel.value) params.set('status', statusSel.value);
        
        const url = '<?php echo url("ajax/keputusan_list.php"); ?>?' + params.toString();
        console.log('Loading keputusan from:', url);
        
        fetchJSON(url)
            .then(res=>{
                console.log('Keputusan response:', res);
                if(res && res.success){
                    console.log('Found', res.data ? res.data.length : 0, 'results');
                    renderKeputusan(res.data || []);
                } else {
                    console.warn('No success or invalid response:', res);
                    renderKeputusan([]);
                }
            })
            .catch(err=>{
                console.error('Failed to fetch keputusan', err);
                renderKeputusan([]);
            });
    }
    
    function showAddKeputusan(){
        formKeputusan.reset();
        keputusanId.value = '';
        document.getElementById('modalKeputusanTitle').textContent = 'Rekod Keputusan Baru';
        keputusanKategori.disabled = true;
        keputusanKategori.innerHTML = '<option value="">Pilih Kategori</option>';
        keputusanPertama.innerHTML = '<option value="">Pilih Pemenang</option>';
        keputusanKedua.innerHTML = '<option value="">Pilih Pemenang</option>';
        keputusanKetiga.innerHTML = '<option value="">Pilih Pemenang</option>';
        currentCategoryType = null;
        
        // Reset dropdown states
        updateParticipantDropdowns();
        
        const modalEl = document.getElementById('modalKeputusan');
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
        
        if (typeof coreui !== 'undefined' && coreui.Modal) {
            modalKeputusanInstance = new coreui.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
            modalKeputusanInstance.show();
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            modalKeputusanInstance = new bootstrap.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
            modalKeputusanInstance.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
        }
        
        // If sport is already selected, load categories and check for existing results
        if (keputusanSukan.value) {
            loadKategori(keputusanSukan.value, keputusanKategori);
        }
    }
    
    function closeKeputusanModal(){
        const modalEl = document.getElementById('modalKeputusan');
        if (modalKeputusanInstance && typeof modalKeputusanInstance.hide === 'function') {
            modalKeputusanInstance.hide();
        } else if (typeof coreui !== 'undefined' && coreui.Modal) {
            const inst = coreui.Modal.getInstance(modalEl);
            if (inst) inst.hide();
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
        } else {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }
    
    function editKeputusan(id){
        if (window.Swal) {
            Swal.showLoading();
        }
        
        fetchJSON('<?php echo url("ajax/keputusan_list.php"); ?>?id=' + id)
            .then(res=>{
                if (window.Swal) Swal.close();
                
                if(res && res.success && res.data && res.data.length > 0){
                    const r = res.data[0];
                    keputusanId.value = r.id;
                    keputusanSukan.value = r.sukan_id || '';
                    keputusanTarikh.value = r.tarikh || '';
                    keputusanStatus.value = r.status || 'completed';
                    
                    document.getElementById('modalKeputusanTitle').textContent = 'Kemaskini Keputusan';
                    
                    // Store the result values for later use
                    const tempatPertama = r.tempat_pertama || '';
                    const tempatKedua = r.tempat_kedua || '';
                    const tempatKetiga = r.tempat_ketiga || '';
                    const kategoriId = r.kategori_id || '';
                    
                    // Load kategori for the sport, then set value and load participants
                    loadKategori(r.sukan_id, keputusanKategori).then(()=>{
                        keputusanKategori.value = kategoriId;
                        if(kategoriId){
                            loadParticipants(kategoriId).then(()=>{
                                keputusanPertama.value = tempatPertama;
                                keputusanKedua.value = tempatKedua;
                                keputusanKetiga.value = tempatKetiga;
                                
                                // Update dropdowns to disable already selected options
                                updateParticipantDropdowns();
                                
                                const modalEl = document.getElementById('modalKeputusan');
                                if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
                                
                                if (typeof coreui !== 'undefined' && coreui.Modal) {
                                    modalKeputusanInstance = new coreui.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
                                    modalKeputusanInstance.show();
                                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    modalKeputusanInstance = new bootstrap.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
                                    modalKeputusanInstance.show();
                                } else {
                                    modalEl.classList.add('show');
                                    modalEl.style.display = 'block';
                                    document.body.classList.add('modal-open');
                                }
                            });
                        } else {
                            const modalEl = document.getElementById('modalKeputusan');
                            if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
                            
                            if (typeof coreui !== 'undefined' && coreui.Modal) {
                                modalKeputusanInstance = new coreui.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
                                modalKeputusanInstance.show();
                            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                modalKeputusanInstance = new bootstrap.Modal(modalEl, {backdrop: true, keyboard: true, focus: true});
                                modalKeputusanInstance.show();
                            } else {
                                modalEl.classList.add('show');
                                modalEl.style.display = 'block';
                                document.body.classList.add('modal-open');
                            }
                        }
                    });
                } else {
                    if (window.Swal) {
                        Swal.fire({
                            text: 'Keputusan tidak dijumpai',
                            icon: 'error'
                        });
                    } else {
                        alert('Keputusan tidak dijumpai');
                    }
                }
            })
            .catch(err=>{
                console.error('Failed to fetch keputusan', err);
                if (window.Swal) {
                    Swal.close();
                    Swal.fire({
                        text: 'Ralat memuatkan keputusan',
                        icon: 'error'
                    });
                } else {
                    alert('Ralat memuatkan keputusan');
                }
            });
    }
    
    function updateParticipantDropdowns(changedSelect = null){
        // Get current selected values BEFORE the change
        let selectedPertama = keputusanPertama.value;
        let selectedKedua = keputusanKedua.value;
        let selectedKetiga = keputusanKetiga.value;
        
        // If a selection was just changed, get the new value and handle it
        if (changedSelect) {
            const newValue = changedSelect.value;
            
            // If user is clearing a selection (selecting empty), just update and return
            if (!newValue || newValue === '') {
                // Re-enable all options first
                [keputusanPertama, keputusanKedua, keputusanKetiga].forEach(select => {
                    Array.from(select.options).forEach(opt => {
                        opt.disabled = false;
                    });
                });
                
                // Then disable currently selected options
                selectedPertama = keputusanPertama.value;
                selectedKedua = keputusanKedua.value;
                selectedKetiga = keputusanKetiga.value;
            } else {
                // User selected a new value - check if it conflicts
                const allSelected = [selectedPertama, selectedKedua, selectedKetiga];
                const selectedCount = allSelected.filter(v => v && v !== '').length;
                const uniqueCount = new Set(allSelected.filter(v => v && v !== '')).size;
                
                // If there's a duplicate (selectedCount > uniqueCount), prevent the selection
                if (selectedCount > uniqueCount) {
                    // Find which other dropdown has this value
                    let conflictPosition = '';
                    if (changedSelect !== keputusanPertama && selectedPertama === newValue) {
                        conflictPosition = 'Tempat Pertama';
                    } else if (changedSelect !== keputusanKedua && selectedKedua === newValue) {
                        conflictPosition = 'Tempat Kedua';
                    } else if (changedSelect !== keputusanKetiga && selectedKetiga === newValue) {
                        conflictPosition = 'Tempat Ketiga';
                    }
                    
                    // Reset the selection
                    changedSelect.value = '';
                    
                    // Show warning
                    if (window.Swal && conflictPosition) {
                        Swal.fire({
                            text: `Pasukan/peserta ini sudah dipilih untuk ${conflictPosition}`,
                            icon: 'warning',
                            timer: 2500,
                            showConfirmButton: false
                        });
                    }
                    
                    // Recalculate after reset
                    selectedPertama = keputusanPertama.value;
                    selectedKedua = keputusanKedua.value;
                    selectedKetiga = keputusanKetiga.value;
                }
            }
        }
        
        // Get final selected values
        selectedPertama = keputusanPertama.value;
        selectedKedua = keputusanKedua.value;
        selectedKetiga = keputusanKetiga.value;
        
        // Enable all options first (except empty option)
        [keputusanPertama, keputusanKedua, keputusanKetiga].forEach(select => {
            Array.from(select.options).forEach(opt => {
                // Keep empty option enabled, disable others based on selections
                if (opt.value === '') {
                    opt.disabled = false;
                } else {
                    opt.disabled = false; // Will be set below if needed
                }
            });
        });
        
        // Disable selected options in other dropdowns
        // This prevents users from selecting the same team/player in multiple positions
        if (selectedPertama && selectedPertama !== '') {
            Array.from(keputusanKedua.options).forEach(opt => {
                if (opt.value === selectedPertama) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
            Array.from(keputusanKetiga.options).forEach(opt => {
                if (opt.value === selectedPertama) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
        }
        
        if (selectedKedua && selectedKedua !== '') {
            Array.from(keputusanPertama.options).forEach(opt => {
                if (opt.value === selectedKedua) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
            Array.from(keputusanKetiga.options).forEach(opt => {
                if (opt.value === selectedKedua) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
        }
        
        if (selectedKetiga && selectedKetiga !== '') {
            Array.from(keputusanPertama.options).forEach(opt => {
                if (opt.value === selectedKetiga) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
            Array.from(keputusanKedua.options).forEach(opt => {
                if (opt.value === selectedKetiga) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                }
            });
        }
    }
    
    function saveKeputusan(){
        if(!formKeputusan.checkValidity()){
            formKeputusan.reportValidity();
            return;
        }
        
        // Client-side validation: Check if selected category has existing results (only for new records)
        const selectedKategoriId = parseInt(keputusanKategori.value);
        const sukanId = keputusanSukan.value;
        const isNewRecord = !keputusanId.value;
        
        if(isNewRecord && selectedKategoriId && sukanId){
            // Validate category before proceeding
            if (window.Swal) {
                Swal.showLoading();
            }
            
            checkCategoriesWithResults(sukanId, keputusanKategori)
                .then(kategoriWithResults => {
                    if (window.Swal) {
                        Swal.close();
                    }
                    
                    if(kategoriWithResults.includes(selectedKategoriId)){
                        // This category has existing results - prevent saving
                        if (window.Swal) {
                            Swal.fire({
                                text: 'Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.',
                                icon: 'error',
                                timer: 3000
                            });
                        } else {
                            alert('Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.');
                        }
                        return;
                    }
                    
                    // Category is valid, proceed with save
                    performSaveKeputusan();
                })
                .catch(err => {
                    if (window.Swal) {
                        Swal.close();
                    }
                    console.error('Error checking category before save:', err);
                    // If check fails, still proceed (server will validate)
                    performSaveKeputusan();
                });
        } else {
            // No need to check (editing or missing data), proceed with save
            performSaveKeputusan();
        }
    }
    
    function performSaveKeputusan(){
        // Client-side validation: check for duplicate selections
        const tempatPertama = keputusanPertama.value || null;
        const tempatKedua = keputusanKedua.value || null;
        const tempatKetiga = keputusanKetiga.value || null;
        
        const selected = [tempatPertama, tempatKedua, tempatKetiga].filter(v => v !== null && v !== '');
        if (selected.length !== new Set(selected).size) {
            if (window.Swal) {
                Swal.fire({
                    text: 'Pasukan/peserta yang sama tidak boleh dipilih untuk lebih daripada satu tempat',
                    icon: 'warning'
                });
            } else {
                alert('Pasukan/peserta yang sama tidak boleh dipilih untuk lebih daripada satu tempat');
            }
            return;
        }
        
        const data = {
            id: keputusanId.value || null,
            sukan_id: keputusanSukan.value,
            kategori_id: keputusanKategori.value,
            tarikh: keputusanTarikh.value,
            tempat_pertama: tempatPertama,
            tempat_kedua: tempatKedua,
            tempat_ketiga: tempatKetiga,
            status: keputusanStatus.value
        };
        
        if (window.Swal) {
            Swal.showLoading();
        }
        
        fetch('<?php echo url("ajax/keputusan_save.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res=>{
            if (window.Swal) Swal.close();
            
            if(res.success){
                closeKeputusanModal();
                loadKeputusan();
                
                if (window.Swal) {
                    Swal.fire({
                        text: res.message || 'Keputusan berjaya disimpan',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(res.message || 'Keputusan berjaya disimpan');
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        text: res.message || 'Ralat menyimpan keputusan',
                        icon: 'error'
                    });
                } else {
                    alert(res.message || 'Ralat menyimpan keputusan');
                }
            }
        })
        .catch(err=>{
            console.error('Failed to save keputusan', err);
            if (window.Swal) {
                Swal.close();
                Swal.fire({
                    text: 'Ralat menyimpan keputusan',
                    icon: 'error'
                });
            } else {
                alert('Ralat menyimpan keputusan');
            }
        });
    }
    
    function deleteKeputusan(id){
        if (window.Swal) {
            Swal.fire({
                title: 'Padam Keputusan?',
                text: 'Adakah anda pasti mahu memadam keputusan ini? Tindakan ini tidak boleh dipulihkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Padam',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(id);
                }
            });
        } else {
            if(!confirm('Adakah anda pasti mahu memadam keputusan ini?')){
                return;
            }
            performDelete(id);
        }
    }
    
    function performDelete(id){
        if (window.Swal) {
            Swal.showLoading();
        }
        
        fetch('<?php echo url("ajax/keputusan_delete.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({id: id})
        })
        .then(r => r.json())
        .then(res=>{
            if (window.Swal) Swal.close();
            
            if(res.success){
                loadKeputusan();
                
                if (window.Swal) {
                    Swal.fire({
                        text: res.message || 'Keputusan berjaya dipadam',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(res.message || 'Keputusan berjaya dipadam');
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        text: res.message || 'Ralat memadam keputusan',
                        icon: 'error'
                    });
                } else {
                    alert(res.message || 'Ralat memadam keputusan');
                }
            }
        })
        .catch(err=>{
            console.error('Failed to delete keputusan', err);
            if (window.Swal) {
                Swal.close();
                Swal.fire({
                    text: 'Ralat memadam keputusan',
                    icon: 'error'
                });
            } else {
                alert('Ralat memadam keputusan');
            }
        });
    }
    
    // Event listeners
    sportSel.addEventListener('change', function(){
        loadKategori(this.value);
        loadKeputusan();
    });
    
    kategoriSel.addEventListener('change', loadKeputusan);
    dateInp.addEventListener('change', loadKeputusan);
    statusSel.addEventListener('change', loadKeputusan);
    
    keputusanSukan.addEventListener('change', function(){
        const sukanId = this.value;
        keputusanKategori.value = '';
        loadParticipants('');
        
        // Load categories - the loadKategori function will automatically check for existing results
        loadKategori(sukanId, keputusanKategori).then(() => {
            // After categories are loaded, update restrictions
            updateCategoryRestrictions();
        });
    });
    
    keputusanKategori.addEventListener('change', function(){
        console.log('Kategori changed to:', this.value);
        
        // First check: Check if selected category is disabled (has existing result)
        const selectedOption = this.options[this.selectedIndex];
        if(selectedOption && selectedOption.disabled && this.value !== ''){
            if (window.Swal) {
                Swal.fire({
                    text: 'Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.',
                    icon: 'warning',
                    timer: 3000
                });
            } else {
                alert('Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.');
            }
            this.value = '';
            return;
        }
        
        // Second check: Validate with server if sport is set
        if(this.value && keputusanSukan.value){
            const kategoriId = parseInt(this.value);
            checkCategoriesWithResults(keputusanSukan.value, keputusanKategori)
                .then(kategoriWithResults => {
                    if(kategoriWithResults.includes(kategoriId)){
                        // This category has existing results - prevent selection
                        if (window.Swal) {
                            Swal.fire({
                                text: 'Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.',
                                icon: 'warning',
                                timer: 3000
                            });
                        } else {
                            alert('Kategori ini sudah mempunyai keputusan. Sila pilih kategori lain.');
                        }
                        this.value = '';
                        // Re-update restrictions to ensure UI is correct
                        updateCategoryRestrictions();
                        return;
                    }
                    
                    // Category is valid, load participants
                    loadParticipants(kategoriId);
                })
                .catch(err => {
                    console.error('Error checking category:', err);
                    // If check fails, still allow selection but load participants
                    loadParticipants(this.value);
                });
        } else {
            // No validation needed if sport not set, just load participants
            loadParticipants(this.value);
        }
    });
    
    // Note: Date change no longer affects category restrictions since we check regardless of date
    
    // Add event listeners to update dropdowns when selections change
    keputusanPertama.addEventListener('change', function(){ updateParticipantDropdowns(this); });
    keputusanKedua.addEventListener('change', function(){ updateParticipantDropdowns(this); });
    keputusanKetiga.addEventListener('change', function(){ updateParticipantDropdowns(this); });
    
    // Update dropdowns when user focuses on any select to show current restrictions
    [keputusanPertama, keputusanKedua, keputusanKetiga].forEach(select => {
        select.addEventListener('focus', function(){
            updateParticipantDropdowns();
        });
        select.addEventListener('mousedown', function(){
            // Update before dropdown opens
            updateParticipantDropdowns();
        });
    });
    
    // Event listeners for buttons
    document.getElementById('btnAddKeputusan').addEventListener('click', showAddKeputusan);
    document.getElementById('btnSaveKeputusan').addEventListener('click', saveKeputusan);
    
    // Make closeKeputusanModal globally available
    window.closeKeputusanModal = closeKeputusanModal;
    
    // Use event delegation for dynamically created edit/delete buttons
    document.addEventListener('click', function(e){
        if(e.target.closest('.btn-edit-keputusan')){
            const id = e.target.closest('.btn-edit-keputusan').getAttribute('data-id');
            editKeputusan(parseInt(id));
        } else if(e.target.closest('.btn-delete-keputusan')){
            const id = e.target.closest('.btn-delete-keputusan').getAttribute('data-id');
            deleteKeputusan(parseInt(id));
        }
    });
    
    // Initial load
    loadKeputusan();
})();
</script>

<style>
/* Style for disabled options in participant dropdowns */
#keputusanPertama option:disabled,
#keputusanKedua option:disabled,
#keputusanKetiga option:disabled {
    color: #999 !important;
    font-style: italic;
    background-color: #f5f5f5;
    cursor: not-allowed;
}

/* Visual indicator for disabled options */
#keputusanPertama option[disabled]:not([value=""]),
#keputusanKedua option[disabled]:not([value=""]),
#keputusanKetiga option[disabled]:not([value=""]) {
    opacity: 0.6;
}

/* Style for disabled category options */
#keputusanKategori option:disabled {
    color: #999 !important;
    font-style: italic;
    background-color: #fff3cd;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>

