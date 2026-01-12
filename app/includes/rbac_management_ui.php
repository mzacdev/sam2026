<?php
/**
 * RBAC Management UI Component
 * Dynamic Role-Based Access Control Management Interface
 */
?>

<!-- RBAC Management Section -->
<div class="row">
    <!-- Roles Management -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="cil cil-shield-alt me-2"></i><strong>Urus Peranan</strong>
                </div>
                <button type="button" class="btn btn-sm btn-light" onclick="showCreateRoleModal()">
                    <i class="cil cil-plus me-1"></i> Peranan Baru
                </button>
            </div>
            <div class="card-body">
                <div id="rolesListContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Memuatkan...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User-Role Assignment -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="cil cil-people me-2"></i><strong>Tugasan Peranan Pengguna</strong>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="userSelect" class="form-label">Pilih Pengguna:</label>
                    <select class="form-select" id="userSelect" onchange="loadUserRoles()">
                        <option value="">-- Pilih Pengguna --</option>
                    </select>
                </div>
                <div id="userRolesContainer">
                    <p class="text-muted text-center">Pilih pengguna untuk melihat peranan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Access Rules -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="cil cil-lock-locked me-2"></i><strong>Peraturan Akses Halaman</strong>
                </div>
                <button type="button" class="btn btn-sm btn-light" onclick="showCreatePageRuleModal()">
                    <i class="cil cil-plus me-1"></i> Peraturan Baru
                </button>
            </div>
            <div class="card-body">
                <div id="pageRulesListContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Memuatkan...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true" data-coreui-backdrop="true" data-coreui-keyboard="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="roleModalLabel">Peranan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="roleId" name="role_id">
                    
                    <div class="mb-3">
                        <label for="roleCode" class="form-label">Kod Peranan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleCode" name="role_code" 
                               placeholder="cth: MANAGER" required pattern="[A-Z0-9_]+" 
                               title="Hanya huruf besar, nombor dan underscore">
                        <div class="form-text">Kod unik untuk peranan (huruf besar sahaja)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Nama Peranan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="role_name" 
                               placeholder="cth: Pengurus" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Penerangan</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isSystemRole" name="is_system_role">
                            <label class="form-check-label" for="isSystemRole">
                                Peranan Sistem (tidak boleh dipadam)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kebenaran:</label>
                        <div id="permissionsList" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Memuatkan...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveRole()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Page Rule Modal -->
<div class="modal fade" id="pageRuleModal" tabindex="-1" aria-labelledby="pageRuleModalLabel" aria-hidden="true" data-coreui-backdrop="true" data-coreui-keyboard="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="pageRuleModalLabel">Peraturan Akses Halaman</h5>
                <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pageRuleForm">
                    <input type="hidden" id="pageRuleId" name="page_rule_id">
                    
                    <div class="mb-3">
                        <label for="pagePath" class="form-label">Laluan Halaman <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pagePath" name="page_path" 
                               placeholder="cth: pages/settings.php" required>
                        <div class="form-text">Laluan relatif dari root (cth: pages/settings.php atau index.php)</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isPublic" name="is_public" 
                                   onchange="toggleRoleSelection()">
                            <label class="form-check-label" for="isPublic">
                                Halaman Awam (tidak memerlukan log masuk)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="requiresAuth" name="requires_auth" checked>
                            <label class="form-check-label" for="requiresAuth">
                                Memerlukan Pengesahan
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="rolesSelectionContainer">
                        <label class="form-label">Peranan yang Dibenarkan:</label>
                        <div id="rolesCheckboxList" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="text-center py-2">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Memuatkan...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info" onclick="savePageRule()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
// RBAC Management JavaScript
const RBACManager = {
    apiBase: '<?php echo BASE_URL; ?>api/rbac/',
    
    init: function() {
        this.loadRoles();
        this.loadUsers();
        this.loadPageRules();
        this.loadPermissions();
    },
    
    loadRoles: async function() {
        try {
            const response = await fetch(this.apiBase + 'roles.php?action=list');
            const data = await response.json();
            
            if (data.success) {
                this.renderRoles(data.data);
            }
        } catch (error) {
            console.error('Error loading roles:', error);
            document.getElementById('rolesListContainer').innerHTML = 
                '<div class="alert alert-danger">Ralat memuatkan peranan</div>';
        }
    },
    
    renderRoles: function(roles) {
        const container = document.getElementById('rolesListContainer');
        
        if (roles.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">Tiada peranan dijumpai</p>';
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr><th>Kod</th><th>Nama</th><th>Pengguna</th><th>Kebenaran</th><th>Tindakan</th></tr></thead><tbody>';
        
        roles.forEach(role => {
            html += `<tr>
                <td><code>${role.role_code}</code></td>
                <td>${role.role_name}</td>
                <td><span class="badge bg-secondary">${role.user_count || 0}</span></td>
                <td><span class="badge bg-info">${role.permission_count || 0}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editRole(${role.id})">
                        <i class="cil cil-pencil"></i>
                    </button>
                    ${role.is_system_role ? '' : `<button class="btn btn-sm btn-danger" onclick="deleteRole(${role.id})">
                        <i class="cil cil-trash"></i>
                    </button>`}
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    },
    
    loadUsers: async function() {
        try {
            const response = await fetch(this.apiBase + 'users.php?action=list');
            const data = await response.json();
            
            if (data.success) {
                this.renderUserSelect(data.data);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    },
    
    renderUserSelect: function(users) {
        const select = document.getElementById('userSelect');
        select.innerHTML = '<option value="">-- Pilih Pengguna --</option>';
        
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.full_name} (${user.username})`;
            select.appendChild(option);
        });
    },
    
    loadPageRules: async function() {
        try {
            const response = await fetch(this.apiBase + 'pages.php?action=list');
            const data = await response.json();
            
            if (data.success) {
                this.renderPageRules(data.data);
            }
        } catch (error) {
            console.error('Error loading page rules:', error);
            document.getElementById('pageRulesListContainer').innerHTML = 
                '<div class="alert alert-danger">Ralat memuatkan peraturan</div>';
        }
    },
    
    renderPageRules: function(rules) {
        const container = document.getElementById('pageRulesListContainer');
        
        if (rules.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">Tiada peraturan dijumpai</p>';
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr><th>Laluan Halaman</th><th>Awam</th><th>Perlu Auth</th><th>Peranan</th><th>Tindakan</th></tr></thead><tbody>';
        
        rules.forEach(rule => {
            html += `<tr>
                <td><code>${rule.page_path}</code></td>
                <td>${rule.is_public ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'}</td>
                <td>${rule.requires_auth ? '<span class="badge bg-warning">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'}</td>
                <td>${rule.allowed_roles || '<span class="text-muted">Tiada</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editPageRule(${rule.id})">
                        <i class="cil cil-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deletePageRule(${rule.id})">
                        <i class="cil cil-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    },
    
    loadPermissions: async function() {
        // This would load from permissions API if available
        // For now, we'll use a placeholder
        return [];
    }
};

// Function to close all modals
function closeAllModals() {
    // Close all visible modals
    const visibleModals = document.querySelectorAll('.modal.show');
    visibleModals.forEach(modal => {
        const instance = coreui.Modal.getInstance(modal) || bootstrap?.Modal?.getInstance(modal);
        if (instance) {
            instance.hide();
        } else {
            // Fallback: manually hide
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
    });
    
    // Clean up all backdrops immediately
    cleanupModalBackdrops();
}

// Function to fix modal z-index - Bootstrap Standard
function fixModalZIndex() {
    // Hide loading overlay first
    hideLoadingOverlayForModal();
    
    // Ensure all modals follow Bootstrap standard z-index
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        // INCREASED: modal z-index 1060 (MUST be above navbar/header 1000)
        modal.style.zIndex = '1060';
        modal.style.position = 'fixed';
        
        // CRITICAL: Ensure modal is always above navbar/header
        const navbar = document.querySelector('.navbar');
        const header = document.querySelector('.header, .header-sticky');
        if (navbar) {
            navbar.style.zIndex = '1000';
        }
        if (header) {
            header.style.zIndex = '1000';
        }
        
        // Modal-dialog and modal-content inherit from modal container
        // No separate z-index needed (Bootstrap standard)
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.style.pointerEvents = 'auto';
            // Remove any non-standard z-index
            dialog.style.zIndex = '';
        }
        
        const content = modal.querySelector('.modal-content');
        if (content) {
            content.style.pointerEvents = 'auto';
            // Remove any non-standard z-index
            content.style.zIndex = '';
        }
    });
    
    // Ensure all backdrops follow Bootstrap standard z-index
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach((backdrop, index) => {
        // Standard Bootstrap: backdrop z-index 1040
        backdrop.style.zIndex = '1040';
        backdrop.style.position = 'fixed';
        backdrop.style.pointerEvents = 'auto';
        
        // CRITICAL: Remove duplicate backdrops (only first one should exist)
        if (index > 0) {
            backdrop.remove();
        }
    });
    
    // Ensure loading overlay is hidden and behind everything
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.zIndex = '-1';
        loadingOverlay.style.display = 'none';
        loadingOverlay.style.pointerEvents = 'none';
    }
}

// Global function to clean up modal backdrops
function cleanupModalBackdrops() {
    // Remove all but one backdrop (CoreUI should manage this, but we'll ensure)
    const backdrops = document.querySelectorAll('.modal-backdrop');
    
    // If multiple backdrops exist, remove extras
    if (backdrops.length > 1) {
        for (let i = 1; i < backdrops.length; i++) {
            backdrops[i].remove();
        }
    }
    
    // Wait a bit for CoreUI to finish its animation
    setTimeout(() => {
        // Check if any modals are still showing
        const visibleModals = document.querySelectorAll('.modal.show');
        
        if (visibleModals.length === 0) {
            // No modals are showing, remove all backdrops
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Remove modal-open class and restore body styles
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        } else {
            // Modals are showing, ensure only one backdrop exists
            const remainingBackdrops = document.querySelectorAll('.modal-backdrop');
            if (remainingBackdrops.length > 1) {
                for (let i = 1; i < remainingBackdrops.length; i++) {
                    remainingBackdrops[i].remove();
                }
            }
            
            // Ensure backdrop z-index is correct
            fixModalZIndex();
        }
    }, 150); // Small delay to allow CoreUI animations to complete
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Clean up any lingering backdrops on page load
    cleanupModalBackdrops();
    
    // Hide loading overlay if it's still visible
    hideLoadingOverlayForModal();
    
    // CRITICAL: Move all modals to body level on page load (Bootstrap best practice)
    // This prevents stacking context issues with parent containers
    // IMPORTANT: Only select elements with class "modal" (NOT tab-pane or other elements)
    const allModals = document.querySelectorAll('div.modal, .modal');
    allModals.forEach(modal => {
        // Double-check it's actually a modal element (not a tab-pane or other element)
        // Exclude tab-panes and other elements that might have "modal" in their class list
        if (modal.classList.contains('modal') && 
            !modal.classList.contains('tab-pane') && 
            !modal.id.includes('display') && 
            !modal.id.includes('system') &&
            modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
    
    // Global event listener for ALL modal hidden events
    document.addEventListener('hidden.coreui.modal', function(e) {
        // Clean up backdrops when any modal is hidden
        cleanupModalBackdrops();
    });
    
    // Also listen for Bootstrap modal events (if using Bootstrap)
    document.addEventListener('hidden.bs.modal', function(e) {
        cleanupModalBackdrops();
    });
    
    // Only initialize if we're on the user tab
    const userTab = document.getElementById('user-tab');
    if (userTab) {
        userTab.addEventListener('shown.coreui.tab', function() {
            RBACManager.init();
            // Clean up any backdrops when tab is shown
            cleanupModalBackdrops();
            // Hide loading overlay
            hideLoadingOverlayForModal();
        });
        
        // Also initialize if tab is already active
        if (userTab.classList.contains('active')) {
            RBACManager.init();
        }
    }
    
    // CRITICAL: Hide loading overlay when any modal is shown
    document.addEventListener('show.coreui.modal', function(e) {
        // Force hide loading overlay immediately
        hideLoadingOverlayForModal();
        
        // Also check and hide after a short delay
        setTimeout(hideLoadingOverlayForModal, 10);
        
        // Fix z-index when modal is shown
        setTimeout(fixModalZIndex, 10);
    });
    
    // Also hide on modal show event (Bootstrap)
    document.addEventListener('show.bs.modal', function(e) {
        hideLoadingOverlayForModal();
        setTimeout(hideLoadingOverlayForModal, 10);
    });
    
    // Fix z-index when modal is fully shown
    document.addEventListener('shown.coreui.modal', function() {
        // Ensure loading overlay is hidden
        hideLoadingOverlayForModal();
        fixModalZIndex();
        // Also fix after multiple delays to ensure DOM is updated
        setTimeout(fixModalZIndex, 10);
        setTimeout(fixModalZIndex, 50);
        setTimeout(fixModalZIndex, 100);
    });
    
    // Continuously monitor and fix z-index while modal is open
    setInterval(function() {
        const visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            fixModalZIndex();
        }
    }, 500); // Check every 500ms while modals are open
    
    // Monitor for modal backdrop creation and fix z-index
    const modalObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // If modal backdrop is added, fix z-index
                        if (node.classList && node.classList.contains('modal-backdrop')) {
                            hideLoadingOverlayForModal();
                            node.style.zIndex = '1040';
                            node.style.position = 'fixed';
                            // Fix z-index after backdrop is added
                            setTimeout(fixModalZIndex, 10);
                        }
                        // If modal is shown, fix z-index
                        if (node.classList && node.classList.contains('modal')) {
                            if (node.classList.contains('show')) {
                                hideLoadingOverlayForModal();
                                setTimeout(fixModalZIndex, 10);
                            }
                        }
                    }
                });
            }
            
            // Also check for attribute changes (like class changes)
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.classList.contains('modal') && target.classList.contains('show')) {
                    setTimeout(fixModalZIndex, 10);
                }
                if (target.classList.contains('modal-backdrop')) {
                    setTimeout(fixModalZIndex, 10);
                }
            }
        });
    });
    
    // Observe body for modal/backdrop additions and changes
    modalObserver.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    // Clean up backdrops when clicking outside modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            // Find and close the associated modal
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const instance = coreui.Modal.getInstance(modal) || bootstrap?.Modal?.getInstance(modal);
                if (instance) {
                    instance.hide();
                }
            });
            cleanupModalBackdrops();
        }
    });
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        cleanupModalBackdrops();
        hideLoadingOverlayForModal();
    });
});

// Helper functions - Modal instances
let roleModalInstance = null;
let pageRuleModalInstance = null;

// Function to hide loading overlay when modal opens
function hideLoadingOverlayForModal() {
    // Use forceHide for immediate hiding when modals are involved
    if (typeof LoadingOverlay !== 'undefined') {
        if (LoadingOverlay.forceHide) {
            LoadingOverlay.forceHide();
        } else {
            LoadingOverlay.hide();
        }
    }
    
    // Also directly hide the overlay element with aggressive styles
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
        loadingOverlay.style.zIndex = '-1';
        loadingOverlay.style.pointerEvents = 'none';
        loadingOverlay.style.opacity = '0';
        loadingOverlay.style.visibility = 'hidden';
        loadingOverlay.classList.remove('show', 'hide');
    }
}

async function showCreateRoleModal() {
    // CRITICAL: Close any existing modals first to prevent stacking
    closeAllModals();
    
    // Hide loading overlay before showing modal
    hideLoadingOverlayForModal();
    
    document.getElementById('roleForm').reset();
    document.getElementById('roleId').value = '';
    document.getElementById('roleModalLabel').textContent = 'Peranan Baru';
    
    // Load permissions
    await loadPermissionsForRole();
    
    // Get or create modal instance
    const modalElement = document.getElementById('roleModal');
    
    // Always get or create fresh instance
    if (roleModalInstance) {
        // Dispose old instance if exists
        try {
            roleModalInstance.dispose();
        } catch (e) {
            // Ignore if already disposed
        }
    }
    
    // CRITICAL: Move modal to body level to avoid stacking context issues
    if (modalElement.parentElement !== document.body) {
        document.body.appendChild(modalElement);
    }
    
    roleModalInstance = new coreui.Modal(modalElement, {
        backdrop: true,
        keyboard: true,
        focus: true
    });
    
    // Hide loading overlay when modal is shown
    modalElement.addEventListener('show.coreui.modal', function() {
        hideLoadingOverlayForModal();
        // Ensure z-index is correct
        fixModalZIndex();
    });
    
    // Clean up backdrop on hidden
    modalElement.addEventListener('hidden.coreui.modal', function() {
        cleanupModalBackdrops();
    });
    
    // Show modal
    roleModalInstance.show();
    
    // CRITICAL: Force z-index immediately and continuously
    modalElement.style.zIndex = '1060';
    modalElement.style.position = 'fixed';
    
    // Ensure navbar/header are below modal
    const navbar = document.querySelector('.navbar');
    const header = document.querySelector('.header, .header-sticky');
    if (navbar) navbar.style.zIndex = '1000';
    if (header) header.style.zIndex = '1000';
    
    // Fix z-index after showing
    setTimeout(function() {
        modalElement.style.zIndex = '1060';
        modalElement.style.position = 'fixed';
        if (navbar) navbar.style.zIndex = '1000';
        if (header) header.style.zIndex = '1000';
        fixModalZIndex();
    }, 50);
    
    // Continuous monitoring to ensure z-index is maintained
    const zIndexInterval = setInterval(function() {
        if (modalElement.classList.contains('show')) {
            modalElement.style.zIndex = '1060';
            modalElement.style.position = 'fixed';
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
        } else {
            clearInterval(zIndexInterval);
        }
    }, 100);
}

async function loadPermissionsForRole() {
    const container = document.getElementById('permissionsList');
    container.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Memuatkan kebenaran...</div>';
    
    try {
        // Load permissions from API (if available) or use static list
        const permissions = await fetchPermissions();
        
        if (!permissions || permissions.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Tiada kebenaran tersedia. Sila jalankan migrasi pangkalan data terlebih dahulu.</div>';
            return;
        }
        
        // Group permissions by module
        const grouped = {};
        permissions.forEach(perm => {
            const module = perm.module || 'other';
            if (!grouped[module]) {
                grouped[module] = [];
            }
            grouped[module].push(perm);
        });
        
        let html = '';
        Object.keys(grouped).sort().forEach(module => {
            html += `<div class="mb-3">
                <h6 class="text-primary border-bottom pb-1">${module.toUpperCase()}</h6>`;
            
            grouped[module].forEach(perm => {
                html += `<div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                           value="${perm.id}" id="perm_${perm.id}">
                    <label class="form-check-label" for="perm_${perm.id}">
                        <strong>${perm.permission_name || perm.permission_code}</strong>
                        ${perm.description ? `<br><small class="text-muted">${perm.description}</small>` : ''}
                    </label>
                </div>`;
            });
            
            html += '</div>';
        });
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading permissions:', error);
        container.innerHTML = '<div class="alert alert-danger">Ralat memuatkan kebenaran: ' + (error.message || 'Ralat tidak diketahui') + '</div>';
    }
}

async function fetchPermissions() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>api/rbac/permissions.php?action=list');
        if (!response.ok) {
            console.warn('Permissions API not available, using fallback');
            return getFallbackPermissions();
        }
        const data = await response.json();
        if (data.success && data.data && data.data.length > 0) {
            return data.data;
        }
        // If API returns empty, use fallback
        return getFallbackPermissions();
    } catch (error) {
        console.warn('Error fetching permissions, using fallback:', error);
        return getFallbackPermissions();
    }
}

function getFallbackPermissions() {
    // Fallback permissions list if database doesn't have them yet
    return [
        { id: 1, permission_code: 'user.create', permission_name: 'Cipta Pengguna', description: 'Membuat pengguna baru', module: 'users' },
        { id: 2, permission_code: 'user.edit', permission_name: 'Edit Pengguna', description: 'Mengubah maklumat pengguna', module: 'users' },
        { id: 3, permission_code: 'user.delete', permission_name: 'Padam Pengguna', description: 'Memadam pengguna', module: 'users' },
        { id: 4, permission_code: 'user.view', permission_name: 'Lihat Pengguna', description: 'Melihat senarai pengguna', module: 'users' },
        { id: 5, permission_code: 'role.create', permission_name: 'Cipta Peranan', description: 'Membuat peranan baru', module: 'rbac' },
        { id: 6, permission_code: 'role.edit', permission_name: 'Edit Peranan', description: 'Mengubah peranan', module: 'rbac' },
        { id: 7, permission_code: 'role.delete', permission_name: 'Padam Peranan', description: 'Memadam peranan', module: 'rbac' },
        { id: 8, permission_code: 'role.assign', permission_name: 'Tugaskan Peranan', description: 'Menugaskan peranan kepada pengguna', module: 'rbac' },
        { id: 9, permission_code: 'contingent.create', permission_name: 'Cipta Kontinjen', description: 'Membuat kontinjen baru', module: 'contingent' },
        { id: 10, permission_code: 'contingent.edit', permission_name: 'Edit Kontinjen', description: 'Mengubah maklumat kontinjen', module: 'contingent' },
        { id: 11, permission_code: 'contingent.delete', permission_name: 'Padam Kontinjen', description: 'Memadam kontinjen', module: 'contingent' },
        { id: 12, permission_code: 'sports.create', permission_name: 'Cipta Sukan', description: 'Membuat sukan baru', module: 'sports' },
        { id: 13, permission_code: 'sports.edit', permission_name: 'Edit Sukan', description: 'Mengubah maklumat sukan', module: 'sports' },
        { id: 14, permission_code: 'sports.delete', permission_name: 'Padam Sukan', description: 'Memadam sukan', module: 'sports' },
        { id: 15, permission_code: 'results.create', permission_name: 'Cipta Keputusan', description: 'Memasukkan keputusan', module: 'results' },
        { id: 16, permission_code: 'results.edit', permission_name: 'Edit Keputusan', description: 'Mengubah keputusan', module: 'results' },
        { id: 17, permission_code: 'results.delete', permission_name: 'Padam Keputusan', description: 'Memadam keputusan', module: 'results' },
        { id: 18, permission_code: 'settings.edit', permission_name: 'Edit Tetapan', description: 'Mengubah tetapan sistem', module: 'settings' }
    ];
}

async function saveRole() {
    const form = document.getElementById('roleForm');
    const formData = new FormData(form);
    const roleId = document.getElementById('roleId').value;
    
    // Get selected permissions
    const permissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked'))
        .map(cb => parseInt(cb.value));
    
    const data = {
        role_code: document.getElementById('roleCode').value.toUpperCase(),
        role_name: document.getElementById('roleName').value,
        description: document.getElementById('roleDescription').value,
        is_system_role: document.getElementById('isSystemRole').checked,
        permissions: permissions
    };
    
    try {
        const url = RBACManager.apiBase + 'roles.php?action=' + (roleId ? 'update&id=' + roleId : 'create');
        const method = roleId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peranan berjaya disimpan');
            }
            
            // Close modal
            if (roleModalInstance) {
                roleModalInstance.hide();
            } else {
                const modal = coreui.Modal.getInstance(document.getElementById('roleModal'));
                if (modal) modal.hide();
            }
            
            // Clean up backdrop (cleanupModalBackdrops will handle it via event listener)
            
            // Reload roles list
            RBACManager.loadRoles();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat menyimpan peranan');
            } else {
                alert(result.message || 'Ralat menyimpan peranan');
            }
        }
    } catch (error) {
        console.error('Error saving role:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function editRole(id) {
    try {
        const response = await fetch(RBACManager.apiBase + 'roles.php?action=get&id=' + id);
        const result = await response.json();
        
        if (result.success && result.data) {
            const role = result.data;
            
            document.getElementById('roleId').value = role.id;
            document.getElementById('roleCode').value = role.role_code;
            document.getElementById('roleName').value = role.role_name;
            document.getElementById('roleDescription').value = role.description || '';
            document.getElementById('isSystemRole').checked = role.is_system_role == 1;
            
            document.getElementById('roleModalLabel').textContent = 'Edit Peranan';
            
            // Load permissions and check assigned ones
            await loadPermissionsForRole();
            
            // Wait a bit for permissions to load, then check assigned ones
            setTimeout(() => {
                if (role.permissions && role.permissions.length > 0) {
                    role.permissions.forEach(perm => {
                        const checkbox = document.getElementById('perm_' + perm.id);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            }, 500);
            
            // CRITICAL: Close any existing modals first
            closeAllModals();
            
            // Hide loading overlay before showing modal
            hideLoadingOverlayForModal();
            
            // Get or create modal instance
            const modalElement = document.getElementById('roleModal');
            
            // Always get or create fresh instance
            if (roleModalInstance) {
                try {
                    roleModalInstance.dispose();
                } catch (e) {
                    // Ignore if already disposed
                }
            }
            
            // CRITICAL: Move modal to body level to avoid stacking context issues
            if (modalElement.parentElement !== document.body) {
                document.body.appendChild(modalElement);
            }
            
            roleModalInstance = new coreui.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            
            // Hide loading overlay when modal is shown
            modalElement.addEventListener('show.coreui.modal', function() {
                hideLoadingOverlayForModal();
                fixModalZIndex();
            });
            
            // Clean up backdrop on hidden
            modalElement.addEventListener('hidden.coreui.modal', function() {
                cleanupModalBackdrops();
            });
            
            // Show modal
            roleModalInstance.show();
            
            // CRITICAL: Force z-index immediately and continuously
            modalElement.style.zIndex = '1060';
            modalElement.style.position = 'fixed';
            
            // Ensure navbar/header are below modal
            const navbar = document.querySelector('.navbar');
            const header = document.querySelector('.header, .header-sticky');
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
            
            setTimeout(function() {
                modalElement.style.zIndex = '1060';
                modalElement.style.position = 'fixed';
                if (navbar) navbar.style.zIndex = '1000';
                if (header) header.style.zIndex = '1000';
                fixModalZIndex();
            }, 50);
            
            // Continuous monitoring to ensure z-index is maintained
            const zIndexInterval = setInterval(function() {
                if (modalElement.classList.contains('show')) {
                    modalElement.style.zIndex = '1060';
                    modalElement.style.position = 'fixed';
                    if (navbar) navbar.style.zIndex = '1000';
                    if (header) header.style.zIndex = '1000';
                } else {
                    clearInterval(zIndexInterval);
                }
            }, 100);
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error('Ralat memuatkan peranan');
            } else {
                alert('Ralat memuatkan peranan');
            }
        }
    } catch (error) {
        console.error('Error loading role:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function deleteRole(id) {
    if (!confirm('Adakah anda pasti ingin memadam peranan ini? Tindakan ini tidak boleh dibatalkan.')) {
        return;
    }
    
    try {
        const response = await fetch(RBACManager.apiBase + 'roles.php?action=delete&id=' + id, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peranan berjaya dipadam');
            }
            RBACManager.loadRoles();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat memadam peranan');
            } else {
                alert(result.message || 'Ralat memadam peranan');
            }
        }
    } catch (error) {
        console.error('Error deleting role:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function loadUserRoles() {
    const userId = document.getElementById('userSelect').value;
    const container = document.getElementById('userRolesContainer');
    
    if (!userId) {
        container.innerHTML = '<p class="text-muted text-center">Pilih pengguna untuk melihat peranan</p>';
        return;
    }
    
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';
    
    try {
        const response = await fetch(RBACManager.apiBase + 'users.php?action=get&id=' + userId);
        const result = await response.json();
        
        if (result.success && result.data) {
            const user = result.data;
            const currentRoles = user.roles || [];
            const currentRoleIds = currentRoles.map(r => r.id);
            
            // Load all available roles
            const rolesResponse = await fetch(RBACManager.apiBase + 'roles.php?action=list');
            const rolesData = await rolesResponse.json();
            const allRoles = rolesData.success ? rolesData.data : [];
            
            let html = `<div class="mb-3">
                <h6>Peranan Semasa untuk: <strong>${user.full_name}</strong></h6>
                <p class="text-muted small">${user.email}</p>
            </div>`;
            
            if (allRoles.length === 0) {
                html += '<p class="text-muted">Tiada peranan tersedia</p>';
            } else {
                html += '<div class="list-group">';
                allRoles.forEach(role => {
                    const isAssigned = currentRoleIds.includes(role.id);
                    html += `<div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${role.role_name}</strong> <code class="small">${role.role_code}</code>
                            ${role.description ? `<br><small class="text-muted">${role.description}</small>` : ''}
                        </div>
                        <div>
                            ${isAssigned 
                                ? `<button class="btn btn-sm btn-danger" onclick="removeUserRole(${user.id}, ${role.id})">
                                    <i class="cil cil-minus"></i> Buang
                                </button>`
                                : `<button class="btn btn-sm btn-success" onclick="assignUserRole(${user.id}, ${role.id})">
                                    <i class="cil cil-plus"></i> Tugaskan
                                </button>`
                            }
                        </div>
                    </div>`;
                });
                html += '</div>';
            }
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="alert alert-danger">Ralat memuatkan peranan pengguna</div>';
        }
    } catch (error) {
        console.error('Error loading user roles:', error);
        container.innerHTML = '<div class="alert alert-danger">Ralat sistem. Sila cuba lagi.</div>';
    }
}

async function assignUserRole(userId, roleId) {
    try {
        const response = await fetch(RBACManager.apiBase + 'users.php?action=assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                role_id: roleId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peranan berjaya ditugaskan');
            }
            loadUserRoles();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat menugaskan peranan');
            } else {
                alert(result.message || 'Ralat menugaskan peranan');
            }
        }
    } catch (error) {
        console.error('Error assigning role:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function removeUserRole(userId, roleId) {
    if (!confirm('Adakah anda pasti ingin membuang peranan ini daripada pengguna?')) {
        return;
    }
    
    try {
        const response = await fetch(
            RBACManager.apiBase + `users.php?action=remove&user_id=${userId}&role_id=${roleId}`,
            { method: 'DELETE' }
        );
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peranan berjaya dibuang');
            }
            loadUserRoles();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat membuang peranan');
            } else {
                alert(result.message || 'Ralat membuang peranan');
            }
        }
    } catch (error) {
        console.error('Error removing role:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function showCreatePageRuleModal() {
    // CRITICAL: Close any existing modals first to prevent stacking
    closeAllModals();
    
    // Hide loading overlay before showing modal
    hideLoadingOverlayForModal();
    
    document.getElementById('pageRuleForm').reset();
    document.getElementById('pageRuleId').value = '';
    document.getElementById('pageRuleModalLabel').textContent = 'Peraturan Akses Halaman Baru';
    document.getElementById('isPublic').checked = false;
    document.getElementById('requiresAuth').checked = true;
    toggleRoleSelection();
    
    await loadRolesForPageRule();
    
    // Get or create modal instance
    const modalElement = document.getElementById('pageRuleModal');
    
    // Always get or create fresh instance
    if (pageRuleModalInstance) {
        // Dispose old instance if exists
        try {
            pageRuleModalInstance.dispose();
        } catch (e) {
            // Ignore if already disposed
        }
    }
    
    // CRITICAL: Move modal to body level to avoid stacking context issues
    if (modalElement.parentElement !== document.body) {
        document.body.appendChild(modalElement);
    }
    
    pageRuleModalInstance = new coreui.Modal(modalElement, {
        backdrop: true,
        keyboard: true,
        focus: true
    });
    
    // Hide loading overlay when modal is shown
    modalElement.addEventListener('show.coreui.modal', function() {
        hideLoadingOverlayForModal();
        // Ensure z-index is correct
        fixModalZIndex();
    });
    
    // Clean up backdrop on hidden
    modalElement.addEventListener('hidden.coreui.modal', function() {
        cleanupModalBackdrops();
    });
    
    // Show modal
    pageRuleModalInstance.show();
    
    // CRITICAL: Force z-index immediately and continuously
    modalElement.style.zIndex = '1060';
    modalElement.style.position = 'fixed';
    
    // Ensure navbar/header are below modal
    const navbar = document.querySelector('.navbar');
    const header = document.querySelector('.header, .header-sticky');
    if (navbar) navbar.style.zIndex = '1000';
    if (header) header.style.zIndex = '1000';
    
    // Fix z-index after showing
    setTimeout(function() {
        modalElement.style.zIndex = '1060';
        modalElement.style.position = 'fixed';
        if (navbar) navbar.style.zIndex = '1000';
        if (header) header.style.zIndex = '1000';
        fixModalZIndex();
    }, 50);
    
    // Continuous monitoring to ensure z-index is maintained
    const zIndexInterval = setInterval(function() {
        if (modalElement.classList.contains('show')) {
            modalElement.style.zIndex = '1060';
            modalElement.style.position = 'fixed';
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
        } else {
            clearInterval(zIndexInterval);
        }
    }, 100);
}

async function loadRolesForPageRule() {
    const container = document.getElementById('rolesCheckboxList');
    container.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    
    try {
        const response = await fetch(RBACManager.apiBase + 'roles.php?action=list');
        const result = await response.json();
        
        if (result.success && result.data) {
            const roles = result.data;
            
            if (roles.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Tiada peranan tersedia</p>';
                return;
            }
            
            let html = '';
            roles.forEach(role => {
                html += `<div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="role_ids[]" 
                           value="${role.id}" id="role_${role.id}">
                    <label class="form-check-label" for="role_${role.id}">
                        <strong>${role.role_name}</strong> <code class="small">${role.role_code}</code>
                    </label>
                </div>`;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted text-center">Tiada peranan tersedia</p>';
        }
    } catch (error) {
        console.error('Error loading roles:', error);
        container.innerHTML = '<div class="alert alert-danger">Ralat memuatkan peranan</div>';
    }
}

async function savePageRule() {
    const pageRuleId = document.getElementById('pageRuleId').value;
    const pagePath = document.getElementById('pagePath').value.trim();
    const isPublic = document.getElementById('isPublic').checked;
    const requiresAuth = document.getElementById('requiresAuth').checked;
    
    if (!pagePath) {
        if (typeof Toast !== 'undefined') {
            Toast.error('Laluan halaman diperlukan');
        } else {
            alert('Laluan halaman diperlukan');
        }
        return;
    }
    
    // Get selected role IDs
    const roleIds = isPublic ? [] : Array.from(document.querySelectorAll('input[name="role_ids[]"]:checked'))
        .map(cb => parseInt(cb.value));
    
    const data = {
        page_path: pagePath,
        is_public: isPublic,
        requires_auth: requiresAuth,
        role_ids: roleIds
    };
    
    try {
        const url = RBACManager.apiBase + 'pages.php?action=' + (pageRuleId ? 'update&id=' + pageRuleId : 'create');
        const method = pageRuleId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peraturan berjaya disimpan');
            }
            
            // Close modal
            if (pageRuleModalInstance) {
                pageRuleModalInstance.hide();
            } else {
                const modal = coreui.Modal.getInstance(document.getElementById('pageRuleModal'));
                if (modal) modal.hide();
            }
            
            // Clean up backdrop (cleanupModalBackdrops will handle it via event listener)
            
            // Reload page rules list
            RBACManager.loadPageRules();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat menyimpan peraturan');
            } else {
                alert(result.message || 'Ralat menyimpan peraturan');
            }
        }
    } catch (error) {
        console.error('Error saving page rule:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function editPageRule(id) {
    try {
        const response = await fetch(RBACManager.apiBase + 'pages.php?action=get&id=' + id);
        const result = await response.json();
        
        if (result.success && result.data) {
            const rule = result.data;
            
            document.getElementById('pageRuleId').value = rule.id;
            document.getElementById('pagePath').value = rule.page_path;
            document.getElementById('isPublic').checked = rule.is_public == 1;
            document.getElementById('requiresAuth').checked = rule.requires_auth == 1;
            
            document.getElementById('pageRuleModalLabel').textContent = 'Edit Peraturan Akses Halaman';
            
            toggleRoleSelection();
            
            // Load roles and check assigned ones
            await loadRolesForPageRule();
            
            // Wait a bit for roles to load, then check assigned ones
            setTimeout(() => {
                if (rule.roles && rule.roles.length > 0) {
                    rule.roles.forEach(role => {
                        const checkbox = document.getElementById('role_' + role.id);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            }, 500);
            
            // CRITICAL: Close any existing modals first
            closeAllModals();
            
            // Hide loading overlay before showing modal
            hideLoadingOverlayForModal();
            
            // Get or create modal instance
            const modalElement = document.getElementById('pageRuleModal');
            
            // Always get or create fresh instance
            if (pageRuleModalInstance) {
                try {
                    pageRuleModalInstance.dispose();
                } catch (e) {
                    // Ignore if already disposed
                }
            }
            
            // CRITICAL: Move modal to body level to avoid stacking context issues
            if (modalElement.parentElement !== document.body) {
                document.body.appendChild(modalElement);
            }
            
            pageRuleModalInstance = new coreui.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            
            // Hide loading overlay when modal is shown
            modalElement.addEventListener('show.coreui.modal', function() {
                hideLoadingOverlayForModal();
                fixModalZIndex();
            });
            
            // Clean up backdrop on hidden
            modalElement.addEventListener('hidden.coreui.modal', function() {
                cleanupModalBackdrops();
            });
            
            // Show modal
            pageRuleModalInstance.show();
            
            // CRITICAL: Force z-index immediately and continuously
            modalElement.style.zIndex = '1060';
            modalElement.style.position = 'fixed';
            
            // Ensure navbar/header are below modal
            const navbar = document.querySelector('.navbar');
            const header = document.querySelector('.header, .header-sticky');
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
            
            setTimeout(function() {
                modalElement.style.zIndex = '1060';
                modalElement.style.position = 'fixed';
                if (navbar) navbar.style.zIndex = '1000';
                if (header) header.style.zIndex = '1000';
                fixModalZIndex();
            }, 50);
            
            // Continuous monitoring to ensure z-index is maintained
            const zIndexInterval = setInterval(function() {
                if (modalElement.classList.contains('show')) {
                    modalElement.style.zIndex = '1060';
                    modalElement.style.position = 'fixed';
                    if (navbar) navbar.style.zIndex = '1000';
                    if (header) header.style.zIndex = '1000';
                } else {
                    clearInterval(zIndexInterval);
                }
            }, 100);
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error('Ralat memuatkan peraturan');
            } else {
                alert('Ralat memuatkan peraturan');
            }
        }
    } catch (error) {
        console.error('Error loading page rule:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

async function deletePageRule(id) {
    if (!confirm('Adakah anda pasti ingin memadam peraturan ini? Tindakan ini tidak boleh dibatalkan.')) {
        return;
    }
    
    try {
        const response = await fetch(RBACManager.apiBase + 'pages.php?action=delete&id=' + id, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof Toast !== 'undefined') {
                Toast.success(result.message || 'Peraturan berjaya dipadam');
            }
            RBACManager.loadPageRules();
        } else {
            if (typeof Toast !== 'undefined') {
                Toast.error(result.message || 'Ralat memadam peraturan');
            } else {
                alert(result.message || 'Ralat memadam peraturan');
            }
        }
    } catch (error) {
        console.error('Error deleting page rule:', error);
        if (typeof Toast !== 'undefined') {
            Toast.error('Ralat sistem. Sila cuba lagi.');
        } else {
            alert('Ralat sistem. Sila cuba lagi.');
        }
    }
}

function toggleRoleSelection() {
    const isPublic = document.getElementById('isPublic').checked;
    const container = document.getElementById('rolesSelectionContainer');
    container.style.display = isPublic ? 'none' : 'block';
    
    // Uncheck all roles if page is public
    if (isPublic) {
        document.querySelectorAll('input[name="role_ids[]"]').forEach(cb => cb.checked = false);
    }
}
</script>

