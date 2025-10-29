        </div>
    </main>
    
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

    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #64748b;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            min-width: 60px;
        }
        
        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .nav-item span {
            font-size: 12px;
            font-weight: 500;
        }
        
        .nav-item:hover {
            color: #3B82F6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .nav-item.active {
            color: #3B82F6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .nav-item.active i {
            color: #3B82F6;
        }
        
        @media (max-width: 768px) {
            .nav-item {
                min-width: 50px;
                padding: 6px 8px;
            }
            
            .nav-item i {
                font-size: 18px;
            }
            
            .nav-item span {
                font-size: 11px;
            }
        }
    </style>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html> 

    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #64748b;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            min-width: 60px;
        }
        
        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .nav-item span {
            font-size: 12px;
            font-weight: 500;
        }
        
        .nav-item:hover {
            color: #3B82F6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .nav-item.active {
            color: #3B82F6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .nav-item.active i {
            color: #3B82F6;
        }
        
        @media (max-width: 768px) {
            .nav-item {
                min-width: 50px;
                padding: 6px 8px;
            }
            
            .nav-item i {
                font-size: 18px;
            }
            
            .nav-item span {
                font-size: 11px;
            }
        }
    </style>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html> 