<aside class="sidebar">
    <div class="sidebar-logo" style="margin-bottom: 24px;">
        <img src="<?= BASE_URL ?>/assets/images/sultify-logo.png" alt="Sultify Logo" style="height: 130px; margin: -30px 0;">
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item <?= $page === 'home' ? 'active' : '' ?>" onclick="navigateTo('<?= BASE_URL ?>/')">
                <i class="fa-solid fa-house"></i> <span>Beranda</span>
            </li>
            <li class="nav-item <?= $page === 'search' ? 'active' : '' ?>" onclick="navigateTo('<?= BASE_URL ?>/search')">
                <i class="fa-solid fa-magnifying-glass"></i> <span>Cari</span>
            </li>
            <li class="nav-item <?= $page === 'library' ? 'active' : '' ?>" onclick="navigateTo('<?= BASE_URL ?>/library')">
                <i class="fa-solid fa-book-open"></i> <span>Koleksi Kamu</span>
            </li>
            <li class="nav-item <?= $page === 'settings' ? 'active' : '' ?>" onclick="navigateTo('<?= BASE_URL ?>/settings')">
                <i class="fa-solid fa-sliders"></i> <span>Pengaturan</span>
            </li>
        </ul>
    </nav>

    <nav class="sidebar-nav">
        <div class="nav-label">Playlist Anda</div>
        <ul>
            <?php if (isLoggedIn()): ?>
            <li class="nav-item" id="btn-create-playlist" onclick="openCreatePlaylistModal()">
                <i class="fa-solid fa-square-plus nav-icon"></i> Buat Playlist
            </li>
            <?php else: ?>
            <li class="nav-item" onclick="navigateTo('<?= BASE_URL ?>/auth/login.php')">
                <i class="fa-solid fa-square-plus nav-icon"></i> Buat Playlist
            </li>
            <?php endif; ?>
            <li class="nav-item <?= $page === 'favorites' ? 'active' : '' ?>" onclick="navigateTo('<?= BASE_URL ?>/favorites')">
                <div class="liked-icon"><i class="fa-solid fa-heart"></i></div> <span>Lagu yang Disukai</span>
            </li>
        </ul>
    </nav>

    <div class="sidebar-divider"></div>

    <div class="sidebar-playlists">
        <?php if (isLoggedIn()): ?>
            <?php
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY name ASC");
            $stmt->execute([$_SESSION['user_id']]);
            $userPlaylists = $stmt->fetchAll();
            foreach ($userPlaylists as $pl):
            ?>
                <div class="playlist-item" onclick="navigateTo('<?= BASE_URL ?>/playlist?id=<?= $pl['id'] ?>')">
                    <?= htmlspecialchars($pl['name']) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 12px; font-size: 0.8rem; color: var(--text-dim); text-align: center;">
                Masuk untuk melihat playlist
            </div>
        <?php endif; ?>
    </div>
</aside>
