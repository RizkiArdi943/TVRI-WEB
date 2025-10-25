<?php
require_once __DIR__ . '/../../controllers/cases.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at DESC';

// Initialize controller
$casesController = new CasesController();

// Prepare filters for controller
$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category_id'] = $category;
if ($status) $filters['status'] = $status;

// Apply sorting
list($sortBy, $sortOrder) = explode(' ', $sort . ' DESC');
$filters['sort_by'] = $sortBy;
$filters['sort_order'] = strtoupper($sortOrder);

// Get cases from controller
$cases = $casesController->index($filters);

// Filtering and sorting is now handled by CasesController

// Get categories for filter
require_once __DIR__ . '/../../config/database.php';
$db = new Database();
$categories = $db->findAll('categories');

// Add category and user info to cases (without reference to avoid duplicates)
foreach ($cases as $index => $case) {
    $category = $db->find('categories', $case['category_id']);
    $user = $db->find('users', $case['reported_by']);

    $cases[$index]['category_name'] = $category['name'] ?? 'Unknown';
    $cases[$index]['category_color'] = $category['color'] ?? '#3B82F6';
    $cases[$index]['reporter_name'] = $user['full_name'] ?? 'Unknown';
}
?>

<div class="cases-page">
    <div class="page-header">
        <h1>Daftar Laporan</h1>
        <a href="index.php?page=cases/create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Tambah Laporan
        </a>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <div class="filters-form">
            <div class="filter-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari laporan..." value="<?php echo htmlspecialchars($search); ?>">
                    <button id="searchButton" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <select id="categoryFilter">
                        <option value="">Semua Lokasi</option>
                        <option value="transmisi_pangkalanbun">Transmisi Pangkalanbun</option>
                        <option value="transmisi_sampit">Transmisi Sampit</option>
                        <option value="transmisi_palangkaraya">Transmisi Palangkaraya</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select id="sortFilter">
                        <option value="created_at DESC" <?php echo $sort === 'created_at DESC' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="created_at ASC" <?php echo $sort === 'created_at ASC' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="title ASC" <?php echo $sort === 'title ASC' ? 'selected' : ''; ?>>Judul A-Z</option>
                        <option value="damage_condition DESC" <?php echo $sort === 'damage_condition DESC' ? 'selected' : ''; ?>>Kerusakan Tingkat Tinggi</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="button" id="resetBtn" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Reset
                </button>
                <button type="button" id="exportBtn" class="btn btn-success">
                    <i class="fas fa-download"></i>
                    Export CSV
                </button>
                <button type="button" id="exportExcelBtn" class="btn btn-success">
                    <i class="fas fa-file-excel"></i>
                    Export Excel
                </button>
                <button type="button" id="exportPdfBtn" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i>
                    Export PDF
                </button>
                <button type="button" id="printPdfBtn" class="btn btn-danger">
                    <i class="fas fa-print"></i>
                    Print / Save PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State Container -->
    <div id="emptyStateContainer" style="display: none;"></div>

    <!-- Cases List -->
    <div id="casesList" class="cases-list">
        <?php if (empty($cases)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>Tidak ada laporan ditemukan</h3>
                <p>Coba ubah filter atau tambah laporan baru</p>
                <a href="index.php?page=cases/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Laporan
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($cases as $case): ?>
                <div class="case-card" data-category="<?php echo $case['category_id']; ?>" data-location="<?php echo htmlspecialchars($case['location']); ?>" data-status="<?php echo $case['status']; ?>" data-priority="<?php echo $case['priority']; ?>" data-damage-condition="<?php echo $case['damage_condition']; ?>">
                    <div class="case-header">
                        <div class="case-category" style="background-color: <?php echo $case['category_color']; ?>">
                            <?php echo htmlspecialchars($case['category_name']); ?>
                        </div>
                        <div class="case-status status-<?php echo $case['status']; ?>">
                            <?php 
                            $statusLabels = [
                                'pending' => 'Menunggu',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            echo $statusLabels[$case['status']] ?? $case['status'];
                            ?>
                        </div>
                    </div>
                    
                    <div class="case-content">
                        <?php if (!empty($case['image_path'])): ?>
                        <div class="case-thumb" style="margin-bottom:8px;">
                            <?php 
                            // Use upload handler to get correct URL
                            require_once __DIR__ . '/../../config/upload_simple.php';
                            $uploadHandler = new SimpleVercelBlobUploadHandler();
                            $imageUrl = $uploadHandler->getFileUrl($case['image_path']);
                            ?>
                            <?php if ($imageUrl): ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Lampiran" style="max-width:100%; height:auto; border-radius:6px; display:block; margin:0 auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                            <?php endif; ?>
                            <div style="<?php echo $imageUrl ? 'display:none;' : 'display:block;'; ?> background:#f3f4f6; padding:20px; text-align:center; border-radius:6px; color:#6b7280;">
                                <i class="fas fa-image" style="font-size:24px; margin-bottom:8px;"></i><br>
                                <small>Gambar tidak dapat dimuat</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="case-id">ID: <?php echo htmlspecialchars($case['id_laporan'] ?? $case['id']); ?></div>
                        <h4>
                            <a href="index.php?page=cases/view&id=<?php echo $case['id']; ?>">
                                <?php echo htmlspecialchars($case['title']); ?>
                            </a>
                        </h4>
                        <p><?php echo htmlspecialchars(substr($case['description'], 0, 100)) . (strlen($case['description']) > 100 ? '...' : ''); ?></p>
                        
                        <div class="case-meta">
                            <span class="case-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($case['location']); ?>
                            </span>
                            <span class="case-priority priority-<?php echo $case['priority']; ?>">
                                <i class="fas fa-flag"></i>
                                <?php 
                                $priorityLabels = [
                                    'low' => 'Rendah',
                                    'medium' => 'Sedang',
                                    'high' => 'Tinggi'
                                ];
                                echo $priorityLabels[$case['priority']] ?? $case['priority'];
                                ?>
                            </span>
                        </div>
                        
                        <div class="case-footer">
                            <span class="case-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?>
                            </span>
                            <span class="case-reporter">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($case['reporter_name']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="case-actions">
                        <a href="index.php?page=cases/view&id=<?php echo $case['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i>
                            Detail
                        </a>
                        <a href="controllers/download_surat.php?id=<?php echo $case['id']; ?>" class="btn btn-sm btn-warning" onclick="downloadSurat(this.href); return false;">
                            <i class="fas fa-file-excel"></i>
                            Download Surat
                        </a>
                        <a href="index.php?page=cases/edit&id=<?php echo $case['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                            Edit
                        </a>
                        <button onclick="deleteCase(<?php echo $case['id']; ?>)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* Enhanced Filter Styles */
.filters-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    margin-bottom: 24px;
}

.filter-row {
    display: flex;
    gap: 16px;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
    display: flex;
    align-items: center;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    z-index: 1;
}

.search-box input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 2px solid #e5e7eb;
    border-radius: 8px 0 0 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    border-right: none;
}

.search-box button {
    border-radius: 0 8px 8px 0;
    border: 2px solid #3b82f6;
    border-left: none;
    background-color: #3b82f6;
    color: white;
    padding: 8px 12px;
    cursor: pointer;
}

.search-box button {
    border-radius: 0 8px 8px 0;
    border: 2px solid #3b82f6;
    border-left: none;
    background-color: #3b82f6;
    color: white;
    padding: 8px 12px;
    cursor: pointer;
}

.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-group {
    min-width: 150px;
}

.filter-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-outline {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-info {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

/* Loading state */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Toast notification */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    min-width: 300px;
    padding: 16px 20px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideInRight 0.3s ease-out;
}

.toast.success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-left: 4px solid #047857;
}

.toast.error {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border-left: 4px solid #b91c1c;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Case card image styles */
/* Uniform image viewport inside card - keeps ratio without cropping */
.case-thumb {
    width: 100%;
    margin-bottom: 12px;
    height: 200px;              /* fixed viewport height */
    max-height: 200px;
    background-color: #f5f5f5;  /* neutral background for empty space */
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;    /* center image box */
    overflow: hidden;           /* keep layout tidy */
    padding: 2px;               /* tighter gutter to tampilkan gambar sedikit lebih besar */
}

.case-thumb img {
    width: 100% !important;     /* fill container width */
    height: 100% !important;    /* and height of viewport */
    object-fit: contain;         /* keep aspect ratio, no crop */
    object-position: center;     /* center image */
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s ease;
    background-color: transparent;
    transform: translateY(-14px); /* lift image sedikit lebih tinggi */
}

.case-thumb img:hover {
    transform: translateY(-14px) scale(1.02);
}

/* Styling untuk ID laporan pada overview */
.case-id {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

/* Responsive design */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        min-width: auto;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .filter-actions {
        justify-content: center;
    }
}
</style>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<script>
// Toast notification system
function showToast(message, type = 'success', duration = 5000) {
    const container = document.getElementById('toast-container');
    
    // Remove existing toasts
    const existingToasts = container.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, duration);
}

// Realtime filtering
let filterTimeout;

// Function to parse date from DD/MM/YYYY HH:MM format
function parseDateFromText(dateText) {
    // Extract date and time from text like "19/10/2025 09:32"
    const match = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})/);
    if (match) {
        const [, day, month, year, hour, minute] = match;
        // Create date object with MM/DD/YYYY format (JavaScript standard)
        const dateObj = new Date(`${month}/${day}/${year} ${hour}:${minute}`);
        console.log(`Parsed date: ${dateText} -> ${dateObj.toISOString()}`);
        return dateObj;
    }
    console.log(`Failed to parse date: ${dateText}`);
    return new Date(0); // Return epoch if parsing fails
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const locationFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const sortFilter = document.getElementById('sortFilter').value;
    
    console.log("Applying filters - Search:", searchTerm, "Location:", locationFilter, "Status:", statusFilter, "Sort:", sortFilter);
    
    const caseCards = document.querySelectorAll('.case-card');
    let visibleCount = 0;
    
    caseCards.forEach((card, index) => {
        const title = card.querySelector('h4 a').textContent.toLowerCase();
        const description = card.querySelector('p').textContent.toLowerCase();
        const location = card.dataset.location;
        const status = card.dataset.status;
        
        // Search filter - improved to search in all text content
        const cardText = card.textContent.toLowerCase();
        const matchesSearch = !searchTerm || 
            cardText.includes(searchTerm) || 
            title.includes(searchTerm) || 
            description.includes(searchTerm);
        
        // Location filter - exact match to prevent cross-contamination
        const matchesLocation = !locationFilter || locationFilter === "" || 
            (locationFilter === "transmisi_pangkalanbun" && location && location.toLowerCase() === "transmisi pangkalanbun") ||
            (locationFilter === "transmisi_sampit" && location && location.toLowerCase() === "transmisi sampit") ||
            (locationFilter === "transmisi_palangkaraya" && location && location.toLowerCase() === "transmisi palangkaraya");
        
        // Status filter - improved with empty check
        const matchesStatus = !statusFilter || statusFilter === "" || status === statusFilter;
        
        // Show/hide card
        if (matchesSearch && matchesLocation && matchesStatus) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Tampilkan pesan jika tidak ada hasil, tapi jangan mengganti seluruh konten
    const emptyStateContainer = document.getElementById('emptyStateContainer');
    if (visibleCount === 0) {
        // Tampilkan pesan tidak ada hasil
        emptyStateContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Tidak ada laporan ditemukan</h3>
                    <p>Coba ubah filter atau tambah laporan baru</p>
                    <a href="index.php?page=cases/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Laporan
                    </a>
                </div>
            `;
        emptyStateContainer.style.display = 'block';
    } else {
        // Sembunyikan pesan jika ada hasil
        emptyStateContainer.style.display = 'none';
        emptyStateContainer.innerHTML = '';
    }
    
    // Apply sorting - improved to handle all cases
    if (sortFilter) {
        const cardsArray = Array.from(caseCards).filter(card => card.style.display !== 'none');
        
        cardsArray.sort((a, b) => {
            if (sortFilter === 'created_at DESC') {
                // Parse date from DD/MM/YYYY HH:MM format
                const dateA = parseDateFromText(a.querySelector('.case-date').textContent);
                const dateB = parseDateFromText(b.querySelector('.case-date').textContent);
                return dateB - dateA;
            } else if (sortFilter === 'created_at ASC') {
                // Parse date from DD/MM/YYYY HH:MM format
                const dateA = parseDateFromText(a.querySelector('.case-date').textContent);
                const dateB = parseDateFromText(b.querySelector('.case-date').textContent);
                return dateA - dateB;
            } else if (sortFilter === 'title ASC') {
                const titleA = a.querySelector('h4 a').textContent.toLowerCase();
                const titleB = b.querySelector('h4 a').textContent.toLowerCase();
                return titleA.localeCompare(titleB);
            } else if (sortFilter === 'damage_condition DESC') {
                // Sort by damage condition: severe > moderate > light
                const damageOrder = { 'severe': 3, 'moderate': 2, 'light': 1 };
                const damageA = damageOrder[a.dataset.damageCondition] || 0;
                const damageB = damageOrder[b.dataset.damageCondition] || 0;
                return damageB - damageA;
            }
            return 0;
        });
        
        // Reorder cards in DOM
        const casesList = document.getElementById('casesList');
        cardsArray.forEach(card => {
            casesList.appendChild(card);
        });
    }
}

// Event listeners for realtime filtering
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const resetBtn = document.getElementById('resetBtn');
    const exportBtn = document.getElementById('exportBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const printPdfBtn = document.getElementById('printPdfBtn');
    
    // Search with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(applyFilters, 300);
    });
    
    // Search button click
    searchButton.addEventListener('click', function() {
        applyFilters();
    });
    
    // Immediate filter on dropdown change
    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    sortFilter.addEventListener('change', applyFilters);
    
    // Reset filters
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = '';
        statusFilter.value = '';
        sortFilter.value = 'created_at DESC';
        console.log("Filters reset");
        applyFilters();
        showToast('Filter telah direset', 'success');
    });
    
    // Export CSV
    exportBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value;
        const category = categoryFilter.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;
        
        // Build export URL with current filters
        const exportUrl = `index.php?page=export&type=csv&search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(sort)}`;
        
        // Show loading state
        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        exportBtn.disabled = true;
        exportBtn.classList.add('loading');
        
        // Trigger download
        window.location.href = exportUrl;
        
        // Reset button after 2 seconds
        setTimeout(() => {
            exportBtn.innerHTML = '<i class="fas fa-download"></i> Export CSV';
            exportBtn.disabled = false;
            exportBtn.classList.remove('loading');
            showToast('Export CSV berhasil', 'success');
        }, 2000);
    });

    // Export Excel
    exportExcelBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value;
        const category = categoryFilter.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        const exportUrl = `index.php?page=export&type=excel&search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(sort)}`;

        exportExcelBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        exportExcelBtn.disabled = true;
        exportExcelBtn.classList.add('loading');

        window.location.href = exportUrl;

        setTimeout(() => {
            exportExcelBtn.innerHTML = '<i class="fas fa-file-excel"></i> Export Excel';
            exportExcelBtn.disabled = false;
            exportExcelBtn.classList.remove('loading');
            showToast('Export Excel berhasil', 'success');
        }, 2000);
    });

    // Export PDF
    exportPdfBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value;
        const category = categoryFilter.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        const exportUrl = `index.php?page=export&type=pdf&search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(sort)}`;

        exportPdfBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        exportPdfBtn.disabled = true;
        exportPdfBtn.classList.add('loading');

        window.location.href = exportUrl;

        setTimeout(() => {
            exportPdfBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Export PDF';
            exportPdfBtn.disabled = false;
            exportPdfBtn.classList.remove('loading');
            showToast('Export PDF berhasil', 'success');
        }, 2000);
    });

    // Print PDF (inline), user pilih orientation di dialog print
    function buildPrintUrl(orientation) {
        const searchTerm = searchInput.value;
        const category = categoryFilter.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;
        return `index.php?page=export&type=pdf&renderer=print&orientation=${encodeURIComponent(orientation)}&search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(sort)}`;
    }

    // Print inline tanpa pindah halaman: fetch HTML, render ke iframe overlay, auto print
    printPdfBtn.addEventListener('click', async function() {
        const url = buildPrintUrl('portrait') + '&inline=1';
        try {
            showToast('Menyiapkan pratinjau cetak...', 'success', 2000);
            const res = await fetch(url, { cache: 'no-store' });
            const html = await res.text();

            let overlay = document.getElementById('print-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'print-overlay';
                overlay.style.position = 'fixed';
                overlay.style.inset = '0';
                overlay.style.background = 'rgba(0,0,0,0.15)';
                overlay.style.zIndex = '99999';
                overlay.innerHTML = `
                    <div id="print-toolbar" style="position:absolute;top:16px;left:50%;transform:translateX(-50%);background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.08);display:flex;gap:12px;align-items:center;padding:10px 14px;z-index:2">
                        <strong style="font-size:13px;color:#111827">Cetak Laporan</strong>
                        <label style="font-size:12px;color:#374151">Orientasi:</label>
                        <select id="print-orientation" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:12px">
                            <option value="portrait" selected>Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                        <button id="print-start" style="background:#dc2626;color:#fff;border:none;border-radius:6px;padding:8px 12px;font-size:12px;cursor:pointer">Print</button>
                        <button id="print-close" style="background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:12px;cursor:pointer">Tutup</button>
                    </div>
                    <iframe id="print-iframe" style="width:100%;height:100%;border:0;background:white"></iframe>`;
                document.body.appendChild(overlay);
            }

            const iframe = document.getElementById('print-iframe');
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();

            // Toolbar handlers (pilihan orientasi sebelum print)
            const sel = document.getElementById('print-orientation');
            const applyOrientation = (value) => {
                const styleId = 'override-orientation';
                let style = doc.getElementById(styleId);
                const css = `@page { size: A4 ${value}; }`;
                if (!style) {
                    style = doc.createElement('style');
                    style.id = styleId;
                    doc.head.appendChild(style);
                }
                style.textContent = css;
            };
            sel.addEventListener('change', () => applyOrientation(sel.value));
            applyOrientation('portrait');

            document.getElementById('print-start').onclick = function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
            document.getElementById('print-close').onclick = function() {
                if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
            };

            // Tutup overlay setelah print selesai (best-effort)
            const removeOverlay = () => { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); };
            window.addEventListener('focus', removeOverlay, { once: true });
        } catch (e) {
            showToast('Gagal membuka dialog print', 'error');
        }
    });
});

function deleteCase(id) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
        window.location.href = `index.php?page=cases/delete&id=${id}`;
    }
}

// Function untuk download surat laporan dengan error handling
function downloadSurat(url) {
    try {
        // Show loading indicator
        const originalText = event.target.innerHTML;
        // event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        event.target.disabled = true;
        
        // Use fetch for better error handling
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                // Check if response is JSON (error) or file
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    // Handle JSON error response
                    return response.json().then(data => {
                        throw new Error(data.error || 'Unknown error occurred');
                    });
                } else {
                    // Handle file download - return both blob and response for filename
                    return Promise.all([response.blob(), response]);
                }
            })
            .then(([blob, response]) => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                
                // Get filename from Content-Disposition header or use default
                const contentDisposition = response.headers.get('content-disposition');
                let filename = 'Laporan_Kerusakan.xlsx';
                if (contentDisposition) {
                    const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }
                
                // Debug: log headers for troubleshooting
                console.log('Content-Disposition:', contentDisposition);
                console.log('Extracted filename:', filename);
                
                // Fallback: try to extract filename from URL if header is not available
                if (filename === 'Laporan_Kerusakan.xlsx') {
                    const urlParams = new URLSearchParams(url.split('?')[1]);
                    const caseId = urlParams.get('id');
                    if (caseId) {
                        // Generate filename based on case ID and current date
                        const now = new Date();
                        const dateStr = now.toISOString().split('T')[0];
                        filename = `Laporan_Kerusakan_${caseId}_${dateStr}.xlsx`;
                    }
                }
                
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(downloadUrl);
                
                // Reset button
                event.target.innerHTML = originalText;
                event.target.disabled = false;
                
                // Show success message
                showToast('Surat laporan berhasil diunduh!', 'success', 3000);
            })
            .catch(error => {
                console.error('Download error:', error);
                
                // Reset button
                event.target.innerHTML = originalText;
                event.target.disabled = false;
                
                // Show error message
                showToast(error.message || 'Template surat tidak ditemukan, hubungi admin untuk memperbaiki file template.', 'error');
            });
        
    } catch (error) {
        console.error('Download error:', error);
        
        // Reset button
        event.target.innerHTML = originalText;
        event.target.disabled = false;
        
        showToast('Terjadi kesalahan saat mengunduh surat. Silakan coba lagi.', 'error');
    }
}

// Function untuk download surat PDF
function downloadSuratPDF(url) {
    try {
        // Show loading indicator
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        event.target.disabled = true;
        
        // Use fetch for better error handling
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                // Check if response is JSON (error) or file
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    // Handle JSON error response
                    return response.json().then(data => {
                        throw new Error(data.error || 'Unknown error occurred');
                    });
                } else {
                    // Handle file download
                    return response.blob();
                }
            })
            .then(blob => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = 'Laporan_Kerusakan.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(downloadUrl);
                
                // Reset button
                event.target.innerHTML = originalText;
                event.target.disabled = false;
                
                // Show success message
                showToast('Surat laporan PDF berhasil diunduh!', 'success', 3000);
            })
            .catch(error => {
                console.error('Download PDF error:', error);
                
                // Reset button
                event.target.innerHTML = originalText;
                event.target.disabled = false;
                
                // Show error message
                showToast(error.message || 'Template surat tidak ditemukan. Silakan hubungi admin SIPETRA.', 'error');
            });
        
    } catch (error) {
        console.error('Download PDF error:', error);
        
        // Reset button
        event.target.innerHTML = originalText;
        event.target.disabled = false;
        
        showToast('Terjadi kesalahan saat mengunduh surat PDF. Silakan coba lagi.', 'error');
    }
}
</script> 