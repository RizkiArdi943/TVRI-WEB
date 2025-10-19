        </div>
    </main>
    <br/><br/><br/>
    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="index.php?page=dashboard" class="nav-item <?php echo ($_GET['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="index.php?page=cases" class="nav-item <?php echo ($_GET['page'] ?? '') === 'cases' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i>
            <span>Laporan</span>
        </a>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="index.php?page=users" class="nav-item <?php echo ($_GET['page'] ?? '') === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>User</span>
        </a>
        <?php endif; ?>
        <a href="index.php?page=cases/create" class="nav-item <?php echo ($_GET['page'] ?? '') === 'cases/create' ? 'active' : ''; ?>">
            <i class="fas fa-plus"></i>
            <span>Tambah</span>
        </a>
        <a href="index.php?page=profile" class="nav-item <?php echo ($_GET['page'] ?? '') === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </nav>

    <!-- JavaScript -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>
</html> 