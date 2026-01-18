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
                                    <th scope="col">Kedudukan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="keputusanBody">
                                <tr id="noKeputusanRow">
                                    <td colspan="7" class="text-center text-muted py-4">
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
                        <label class="form-label">Kedudukan <span class="text-danger">*</span></label>
                        <div id="standingsContainer">
                            <p class="text-muted">Pilih kategori terlebih dahulu untuk memuatkan senarai peserta</p>
                        </div>
                        <small class="text-muted">Semua kedudukan mesti diisi berdasarkan bilangan peserta yang menyertai</small>
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
    const standingsContainer = document.getElementById('standingsContainer');
    const keputusanStatus = document.getElementById('keputusanStatus');
    const keputusanId = document.getElementById('keputusanId');
    
    let currentCategoryType = null;
    let currentParticipants = [];
    let participantCount = 0;
    
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
            standingsContainer.innerHTML = '<p class="text-muted">Pilih kategori terlebih dahulu untuk memuatkan senarai peserta</p>';
            currentParticipants = [];
            participantCount = 0;
            return Promise.resolve();
        }
        
        standingsContainer.innerHTML = '<p class="text-muted">Memuatkan senarai peserta...</p>';
        
        const url = '<?php echo url("ajax/get_participants_by_kategori.php"); ?>?kategori_id=' + encodeURIComponent(kategori_id);
        console.log('Fetching participants from:', url);
        
        return fetchJSON(url)
            .then(res=>{
                console.log('Participants response:', res);
                if(res && res.success && Array.isArray(res.data)){
                    currentCategoryType = res.type;
                    currentParticipants = res.data;
                    participantCount = res.data.length;
                    console.log('Found', participantCount, 'participants, type:', res.type);
                    
                    if(res.data.length > 0){
                        // Generate dynamic table
                        generateStandingsTable(res.data);
                        updateParticipantDropdowns();
                    } else {
                        standingsContainer.innerHTML = '<p class="text-danger">Tiada peserta didaftarkan untuk kategori ini</p>';
                    }
                } else {
                    console.warn('Invalid response or no data:', res);
                    standingsContainer.innerHTML = '<p class="text-danger">Tiada peserta didaftarkan</p>';
                }
            })
            .catch(err=>{
                console.error('Failed to fetch participants', err);
                standingsContainer.innerHTML = '<p class="text-danger">Ralat memuat peserta</p>';
            });
    }
    
    function generateStandingsTable(participants){
        const optionHtml = '<option value="">Pilih Peserta</option>' +
            participants.map(p => `<option value="${p.id}">${p.display_name || p.nama || p.nama_pasukan}</option>`).join('');
        
        let tableHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
        tableHtml += '<thead><tr><th style="width: 80px;">Kedudukan</th><th>Peserta</th></tr></thead>';
        tableHtml += '<tbody>';
        
        for(let i = 1; i <= participantCount; i++){
            tableHtml += `<tr>
                <td class="align-middle"><strong>${i}</strong></td>
                <td>
                    <select class="form-select form-select-sm standings-select" data-position="${i}" name="standing_${i}">
                        ${optionHtml}
                    </select>
                </td>
            </tr>`;
        }
        
        tableHtml += '</tbody></table></div>';
        standingsContainer.innerHTML = tableHtml;
        
        // Add event listeners to all selects
        const selects = standingsContainer.querySelectorAll('.standings-select');
        selects.forEach(select => {
            select.addEventListener('change', function(){
                updateParticipantDropdowns(this);
            });
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
            
            // Format standings for display
            let standingsHtml = '';
            if (r.standings && Array.isArray(r.standings) && r.standings.length > 0) {
                const standingsList = r.standings.map(s => {
                    const pos = s.position || '';
                    const name = s.participant_name || s.participant_id || '-';
                    return `${pos}. ${name}`;
                }).join('<br>');
                standingsHtml = `<div class="small">${standingsList}</div>`;
            } else {
                standingsHtml = '<span class="text-muted">-</span>';
            }
            
            tr.innerHTML = `
                <td>${idx+1}</td>
                <td>${r.sukan || ''}</td>
                <td>${r.kategori || ''}</td>
                <td>${r.tarikh || ''}</td>
                <td>${standingsHtml}</td>
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
        standingsContainer.innerHTML = '<p class="text-muted">Pilih kategori terlebih dahulu untuk memuatkan senarai peserta</p>';
        currentCategoryType = null;
        currentParticipants = [];
        participantCount = 0;
        
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
                    
                    // Store the standings for later use
                    const standings = r.standings || [];
                    const kategoriId = r.kategori_id || '';
                    
                    // Load kategori for the sport, then set value and load participants
                    loadKategori(r.sukan_id, keputusanKategori).then(()=>{
                        keputusanKategori.value = kategoriId;
                        if(kategoriId){
                            loadParticipants(kategoriId).then(()=>{
                                // Populate standings table with existing data
                                if (standings && standings.length > 0) {
                                    standings.forEach(standing => {
                                        const position = standing.position;
                                        const participantId = standing.participant_id;
                                        const select = standingsContainer.querySelector(`.standings-select[data-position="${position}"]`);
                                        if (select && participantId) {
                                            select.value = participantId;
                                        }
                                    });
                                }
                                
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
        const selects = standingsContainer.querySelectorAll('.standings-select');
        if (!selects || selects.length === 0) return;
        
        // Collect all selected values
        const selectedValues = {};
        selects.forEach(select => {
            const position = parseInt(select.getAttribute('data-position'));
            const value = select.value;
            if (value && value !== '') {
                selectedValues[position] = value;
            }
        });
        
        // If a selection was just changed, check for duplicates
        if (changedSelect) {
            const newValue = changedSelect.value;
            const changedPosition = parseInt(changedSelect.getAttribute('data-position'));
            
            if (newValue && newValue !== '') {
                // Check if this value is already selected in another position
                for (const [pos, val] of Object.entries(selectedValues)) {
                    if (parseInt(pos) !== changedPosition && val === newValue) {
                        // Duplicate found - reset the selection
                        changedSelect.value = '';
                        delete selectedValues[changedPosition];
                        
                        // Show warning
                        if (window.Swal) {
                            Swal.fire({
                                text: `Pasukan/peserta ini sudah dipilih untuk kedudukan ${pos}`,
                                icon: 'warning',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        }
                        break;
                    }
                }
            }
        }
        
        // Re-collect selected values after potential reset
        const finalSelectedValues = {};
        selects.forEach(select => {
            const position = parseInt(select.getAttribute('data-position'));
            const value = select.value;
            if (value && value !== '') {
                finalSelectedValues[position] = value;
            }
        });
        
        // Enable all options first, then disable selected ones in other dropdowns
        selects.forEach(select => {
            const currentPosition = parseInt(select.getAttribute('data-position'));
            const currentValue = select.value;
            
            Array.from(select.options).forEach(opt => {
                if (opt.value === '') {
                    opt.disabled = false;
                    opt.style.color = '';
                } else {
                    // Check if this option is selected in another position
                    let isSelectedElsewhere = false;
                    for (const [pos, val] of Object.entries(finalSelectedValues)) {
                        if (parseInt(pos) !== currentPosition && val === opt.value) {
                            isSelectedElsewhere = true;
                            break;
                        }
                    }
                    
                    if (isSelectedElsewhere && opt.value !== currentValue) {
                        opt.disabled = true;
                        opt.style.color = '#999';
                    } else {
                        opt.disabled = false;
                        opt.style.color = '';
                    }
                }
            });
        });
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
        // Collect standings from dynamic table
        const selects = standingsContainer.querySelectorAll('.standings-select');
        const standings = [];
        const selectedIds = [];
        
        for (let i = 0; i < selects.length; i++) {
            const select = selects[i];
            const position = parseInt(select.getAttribute('data-position'));
            const participantId = select.value ? select.value.trim() : '';
            
            if (!participantId || participantId === '') {
                if (window.Swal) {
                    Swal.fire({
                        text: `Kedudukan ${position} mesti diisi`,
                        icon: 'warning'
                    });
                } else {
                    alert(`Kedudukan ${position} mesti diisi`);
                }
                return;
            }
            
            // Check for duplicates
            if (selectedIds.includes(participantId)) {
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
            
            selectedIds.push(participantId);
            standings.push({
                position: position,
                participant_id: participantId
            });
        }
        
        // Validate all positions are filled
        if (standings.length !== participantCount) {
            if (window.Swal) {
                Swal.fire({
                    text: `Semua kedudukan mesti diisi. Kategori ini mempunyai ${participantCount} peserta.`,
                    icon: 'warning'
                });
            } else {
                alert(`Semua kedudukan mesti diisi. Kategori ini mempunyai ${participantCount} peserta.`);
            }
            return;
        }
        
        const data = {
            id: keputusanId.value || null,
            sukan_id: keputusanSukan.value,
            kategori_id: keputusanKategori.value,
            tarikh: keputusanTarikh.value,
            standings: standings,
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
    // Event listeners for dynamic standings table are added in generateStandingsTable()
    
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
.standings-select option:disabled {
    color: #999 !important;
    font-style: italic;
    background-color: #f5f5f5;
    cursor: not-allowed;
}

/* Visual indicator for disabled options */
.standings-select option[disabled]:not([value=""]) {
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

/* Standings table styling */
#standingsContainer table {
    margin-bottom: 0;
}

#standingsContainer .standings-select {
    min-width: 250px;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>

