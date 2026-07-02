<?php
requireLogin();
$pdo = getDB();
$userId = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('/auth/login.php');
}

?>

<div class="settings-page-header fade-in">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
        <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; box-shadow: 0 8px 24px var(--primary-glow);">
            <i class="fa-solid fa-sliders"></i>
        </div>
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: #fff; letter-spacing: -0.5px;">Pengaturan</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola preferensi akun, tampilan, dan pemutar musik Anda.</p>
        </div>
    </div>
</div>

<div class="settings-layout fade-in" style="animation-delay: 0.1s;">
    <!-- LEFT PANEL: Settings Sidebar -->
    <div class="settings-sidebar">
        <!-- 👤 Akun Group -->
        <div class="settings-menu-group">
            <div class="settings-group-title">
                <i class="fa-regular fa-circle-user" style="font-size: 1rem;"></i> Akun
            </div>
            <ul class="settings-menu-list">
                <li class="settings-menu-item active" onclick="switchSettingsTab('profil')" id="tab-menu-profil">
                    <i class="fa-regular fa-user"></i> Profil
                </li>
                <li class="settings-menu-item" onclick="switchSettingsTab('edit-profil')" id="tab-menu-edit-profil">
                    <i class="fa-regular fa-pen-to-square"></i> Edit Profil
                </li>
                <li class="settings-menu-item" onclick="switchSettingsTab('ganti-password')" id="tab-menu-ganti-password">
                    <i class="fa-solid fa-key"></i> Ganti Password
                </li>
            </ul>
        </div>

        <!-- 🎨 Tampilan Group -->
        <div class="settings-menu-group">
            <div class="settings-group-title">
                <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 0.9rem;"></i> Tampilan
            </div>
            <ul class="settings-menu-list">
                <li class="settings-menu-item" onclick="switchSettingsTab('tema-warna')" id="tab-menu-tema-warna">
                    <i class="fa-solid fa-palette"></i> Tema Warna
                </li>
                <li class="settings-menu-item" onclick="switchSettingsTab('bahasa')" id="tab-menu-bahasa">
                    <i class="fa-solid fa-language"></i> Bahasa
                </li>
            </ul>
        </div>

        <!-- 🎵 Pemutar Musik Group -->
        <div class="settings-menu-group">
            <div class="settings-group-title">
                <i class="fa-solid fa-sliders" style="font-size: 0.9rem;"></i> Pemutar Musik
            </div>
            <ul class="settings-menu-list">
                <li class="settings-menu-item" onclick="switchSettingsTab('autoplay')" id="tab-menu-autoplay">
                    <i class="fa-solid fa-circle-play"></i> Autoplay
                </li>
            </ul>
        </div>
    </div>

    <!-- RIGHT PANEL: Settings Content -->
    <div class="settings-content">
        
        <!-- ==================== TAB: PROFIL ==================== -->
        <div class="settings-tab-panel active" id="panel-profil">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Profil Pengguna</h2>
                <p class="settings-section-desc">Informasi publik tentang akun Anda.</p>
            </div>
            
            <div class="settings-profile-card">
                <div class="settings-profile-avatar-wrapper">
                    <img id="settings-profile-img" src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="settings-profile-avatar-letter" style="display: none; width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: 2.2rem; font-weight: 700; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                </div>
                <div class="settings-profile-meta">
                    <h3 id="settings-profile-fullname" style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 4px;"><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 12px;">@<?= htmlspecialchars($user['username']) ?></p>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="switchSettingsTab('edit-profil')" class="settings-btn settings-btn-primary" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-regular fa-pen-to-square"></i> Edit Profil</button>
                    </div>
                </div>
            </div>

            <div class="settings-details-grid">
                <div class="settings-detail-item">
                    <div class="label">Nama Lengkap</div>
                    <div class="val"><?= htmlspecialchars($user['full_name']) ?></div>
                </div>
                <div class="settings-detail-item">
                    <div class="label">Username</div>
                    <div class="val">@<?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="settings-detail-item">
                    <div class="label">Email</div>
                    <div class="val"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="settings-detail-item">
                    <div class="label">Biografi</div>
                    <div class="val" style="font-style: <?= empty($user['bio']) ? 'italic' : 'normal' ?>; color: <?= empty($user['bio']) ? 'var(--text-dim)' : 'var(--text)' ?>;">
                        <?= empty($user['bio']) ? 'Belum menulis bio' : htmlspecialchars($user['bio']) ?>
                    </div>
                </div>
                <div class="settings-detail-item">
                    <div class="label">Jenis Kelamin</div>
                    <div class="val">
                        <?php
                        if ($user['gender'] === 'male') echo 'Laki-laki';
                        elseif ($user['gender'] === 'female') echo 'Perempuan';
                        elseif ($user['gender'] === 'other') echo 'Lainnya';
                        else echo '<span style="color: var(--text-dim); font-style: italic;">Belum diatur</span>';
                        ?>
                    </div>
                </div>
                <div class="settings-detail-item">
                    <div class="label">Tanggal Lahir</div>
                    <div class="val">
                        <?= empty($user['date_of_birth']) ? '<span style="color: var(--text-dim); font-style: italic;">Belum diatur</span>' : date('d F Y', strtotime($user['date_of_birth'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB: EDIT PROFIL ==================== -->
        <div class="settings-tab-panel" id="panel-edit-profil">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Edit Profil</h2>
                <p class="settings-section-desc">Perbarui data diri dan foto profil Anda.</p>
            </div>
            
            <form onsubmit="handleProfileUpdate(event)" id="form-edit-profile" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border);">
                    <div class="settings-profile-avatar-wrapper" style="position: relative;">
                        <img id="edit-avatar-preview" src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="settings-profile-avatar-letter" style="display: none; width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-size: 2.2rem; font-weight: 700; align-items: center; justify-content: center;">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <div>
                        <h4 style="color: #fff; font-size: 0.95rem; margin-bottom: 8px; font-weight: 600;">Foto Profil</h4>
                        <div class="file-upload-wrapper">
                            <label class="settings-btn" style="background: rgba(255,255,255,0.06); border: 1px solid var(--border); padding: 8px 16px; font-size: 0.85rem; border-radius: var(--radius); cursor: pointer; display: inline-block;">
                                <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 6px;"></i> Pilih Foto Baru
                                <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                            </label>
                            <span id="file-chosen" style="color: var(--text-muted); font-size: 0.8rem; margin-left: 12px;">Maksimal 2MB (JPG, PNG, WEBP)</span>
                        </div>
                    </div>
                </div>

                <div class="settings-form-grid">
                    <div class="settings-form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($user['full_name']) ?>" required placeholder="Nama lengkap Anda">
                    </div>
                    
                    <div class="settings-form-group">
                        <label>Username *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required placeholder="Username unik">
                    </div>
                    
                    <div class="settings-form-group" style="grid-column: span 2;">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="Alamat email aktif">
                    </div>
                    
                    <div class="settings-form-group" style="grid-column: span 2;">
                        <label>Biografi (Bio)</label>
                        <textarea name="bio" placeholder="Tulis sesuatu tentang dirimu..." rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="settings-form-group">
                        <label>Jenis Kelamin</label>
                        <select name="gender">
                            <option value="">-- Pilih --</option>
                            <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Perempuan</option>
                            <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="settings-form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                    </div>
                </div>

                <div class="settings-form-actions" style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="switchSettingsTab('profil')" class="settings-btn" style="background: transparent; color: var(--text-muted); border: 1px solid var(--border);">Batal</button>
                    <button type="submit" class="settings-btn settings-btn-primary" id="btn-save-profile">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <!-- ==================== TAB: GANTI PASSWORD ==================== -->
        <div class="settings-tab-panel" id="panel-ganti-password">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Ganti Password</h2>
                <p class="settings-section-desc">Ganti password secara berkala untuk menjaga keamanan akun Anda.</p>
            </div>
            
            <form onsubmit="handlePasswordChange(event)" id="form-change-password" style="max-width: 480px;">
                <input type="hidden" name="action" value="change_password">
                
                <div class="settings-form-group" style="margin-bottom: 20px;">
                    <label>Password Saat Ini *</label>
                    <div style="position: relative;">
                        <input type="password" name="old_password" required placeholder="Masukkan password lama" style="width: 100%;">
                        <button type="button" onclick="togglePassVisibility(this)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"><i class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <div class="settings-form-group" style="margin-bottom: 20px;">
                    <label>Password Baru *</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" required placeholder="Minimal 6 karakter" style="width: 100%;">
                        <button type="button" onclick="togglePassVisibility(this)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"><i class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <div class="settings-form-group" style="margin-bottom: 28px;">
                    <label>Konfirmasi Password Baru *</label>
                    <div style="position: relative;">
                        <input type="password" name="confirm_password" required placeholder="Ulangi password baru" style="width: 100%;">
                        <button type="button" onclick="togglePassVisibility(this)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"><i class="fa-regular fa-eye"></i></button>
                    </div>
                </div>

                <div class="settings-form-actions" style="display: flex; gap: 12px;">
                    <button type="button" onclick="switchSettingsTab('profil')" class="settings-btn" style="background: transparent; color: var(--text-muted); border: 1px solid var(--border);">Batal</button>
                    <button type="submit" class="settings-btn settings-btn-primary" id="btn-save-password">Ubah Password</button>
                </div>
            </form>
        </div>

        <!-- ==================== TAB: TEMA WARNA ==================== -->
        <div class="settings-tab-panel" id="panel-tema-warna">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Tema Warna Utama</h2>
                <p class="settings-section-desc">Pilih warna aksen utama yang akan diterapkan pada seluruh antarmuka aplikasi.</p>
            </div>
            
            <div class="theme-grid">
                <!-- Violet (Default) -->
                <div class="theme-option-card" onclick="selectThemeColor('violet')" id="theme-violet">
                    <div class="theme-option-preview" style="background: linear-gradient(135deg, #8B5CF6 0%, #070A11 100%);">
                        <div class="theme-dot" style="background: #8B5CF6;"></div>
                    </div>
                    <div class="theme-option-label">
                        <span>Midnight Violet</span>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                </div>

                <!-- Emerald -->
                <div class="theme-option-card" onclick="selectThemeColor('emerald')" id="theme-emerald">
                    <div class="theme-option-preview" style="background: linear-gradient(135deg, #10B981 0%, #070A11 100%);">
                        <div class="theme-dot" style="background: #10B981;"></div>
                    </div>
                    <div class="theme-option-label">
                        <span>Emerald Glass</span>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                </div>

                <!-- Ocean Blue -->
                <div class="theme-option-card" onclick="selectThemeColor('ocean')" id="theme-ocean">
                    <div class="theme-option-preview" style="background: linear-gradient(135deg, #3B82F6 0%, #070A11 100%);">
                        <div class="theme-dot" style="background: #3B82F6;"></div>
                    </div>
                    <div class="theme-option-label">
                        <span>Ocean Blue</span>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                </div>

                <!-- Ruby Crimson -->
                <div class="theme-option-card" onclick="selectThemeColor('ruby')" id="theme-ruby">
                    <div class="theme-option-preview" style="background: linear-gradient(135deg, #E11D48 0%, #070A11 100%);">
                        <div class="theme-dot" style="background: #E11D48;"></div>
                    </div>
                    <div class="theme-option-label">
                        <span>Ruby Crimson</span>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                </div>

                <!-- Amber Sunset -->
                <div class="theme-option-card" onclick="selectThemeColor('amber')" id="theme-amber">
                    <div class="theme-option-preview" style="background: linear-gradient(135deg, #F59E0B 0%, #070A11 100%);">
                        <div class="theme-dot" style="background: #F59E0B;"></div>
                    </div>
                    <div class="theme-option-label">
                        <span>Amber Sunset</span>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB: BAHASA ==================== -->
        <div class="settings-tab-panel" id="panel-bahasa">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Bahasa Aplikasi</h2>
                <p class="settings-section-desc">Pilih bahasa tampilan untuk aplikasi Sultify.</p>
            </div>
            
            <div class="settings-form-group" style="max-width: 320px;">
                <label style="margin-bottom: 12px; display: block;">Bahasa Tampilan</label>
                <select id="select-language" onchange="changeAppLanguage(this.value)" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.06); border: 1px solid var(--border); border-radius: var(--radius); outline: none; color: #fff;">
                    <option value="id">Bahasa Indonesia</option>
                    <option value="en">English (US)</option>
                </select>
            </div>
            
            <div style="margin-top: 24px; padding: 16px; background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.1); border-radius: var(--radius); display: flex; gap: 12px; max-width: 480px;">
                <i class="fa-solid fa-circle-info" style="color: var(--primary); font-size: 1.1rem; margin-top: 2px;"></i>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Perubahan bahasa akan langsung diterapkan pada preferensi lokal browser Anda.</p>
            </div>
        </div>

        <!-- ==================== TAB: AUTOPLAY ==================== -->
        <div class="settings-tab-panel" id="panel-autoplay">
            <div class="settings-section-header">
                <h2 class="settings-section-title">Putar Otomatis (Autoplay)</h2>
                <p class="settings-section-desc">Secara otomatis memutar lagu berikutnya di antrean setelah lagu yang sedang diputar berakhir.</p>
            </div>
            
            <div class="settings-toggle-card">
                <div class="settings-toggle-info">
                    <h4 style="color: #fff; font-size: 0.95rem; margin-bottom: 4px; font-weight: 600;">Autoplay di Perangkat ini</h4>
                    <p style="color: var(--text-muted); font-size: 0.8rem;">Gunakan penyimpanan lokal untuk menyimpan status putar otomatis.</p>
                </div>
                <label class="switch-container">
                    <input type="checkbox" id="autoplay-toggle" onchange="toggleAutoplayState(this.checked)">
                    <span class="switch-slider"></span>
                </label>
            </div>
        </div>

    </div>
</div>

<script>
    // Initialize active tab from URL parameter if exists, otherwise default to 'profil'
    setTimeout(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'profil';
        switchSettingsTab(tab, false);
        
        // Init theme select state
        const currentTheme = localStorage.getItem('theme-color') || 'violet';
        document.querySelectorAll('.theme-option-card').forEach(el => el.classList.remove('active'));
        const activeCard = document.getElementById('theme-' + currentTheme);
        if (activeCard) activeCard.classList.add('active');

        // Init language select state
        const currentLang = localStorage.getItem('language') || 'id';
        const selectLang = document.getElementById('select-language');
        if (selectLang) selectLang.value = currentLang;

        // Init autoplay state
        const currentAutoplay = localStorage.getItem('autoplay') !== 'false';
        const autoplayToggle = document.getElementById('autoplay-toggle');
        if (autoplayToggle) autoplayToggle.checked = currentAutoplay;
    }, 50);
</script>
