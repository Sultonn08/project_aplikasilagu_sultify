            </div> <!-- Akhir dari page-content -->
        </main> <!-- Akhir dari main-content -->

        <!-- Include Music Player -->
        <?php include 'player.php'; ?>
    </div> <!-- Akhir dari app-layout -->

    <!-- Container Notifikasi Toast -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Script Utama -->
    <script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= time() ?>"></script>

    <!-- ═══════ Modal: Buat Playlist ═══════ -->
    <div id="create-playlist-modal" style="
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;
    " onclick="if(event.target===this) closeCreatePlaylistModal()">
        <div style="
            background: linear-gradient(145deg, #1a1f2e, #0f1623);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
            padding: 32px; width: 420px; max-width: 90vw;
            box-shadow: 0 30px 80px rgba(0,0,0,0.8);
            animation: slideUp 0.3s ease;
        ">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--primary),#a855f7);display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-list-music" style="color:#fff;font-size:1.1rem;"></i>
                </div>
                <h2 style="font-size:1.3rem;font-weight:800;color:#fff;">Buat Playlist Baru</h2>
                <button onclick="closeCreatePlaylistModal()" style="margin-left:auto;background:none;border:none;color:var(--text-muted);font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
            </div>

            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:8px;">Nama Playlist *</label>
                    <input type="text" id="cp-name" placeholder="Contoh: Playlist Pagi Hari" maxlength="100"
                        style="width:100%;padding:12px 16px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#fff;font-size:0.95rem;outline:none;box-sizing:border-box;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'"
                        onkeydown="if(event.key==='Enter') submitCreatePlaylist()">
                </div>
                <div>
                    <label style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:8px;">Deskripsi</label>
                    <textarea id="cp-desc" placeholder="Opsional — tulis deskripsi playlist..." rows="3"
                        style="width:100%;padding:12px 16px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#fff;font-size:0.9rem;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'"></textarea>
                </div>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" id="cp-public" style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;">
                    <span style="font-size:0.9rem;color:var(--text-muted);">Jadikan playlist <strong style="color:#fff;">publik</strong> (dapat dilihat orang lain)</span>
                </label>
            </div>

            <div id="cp-error" style="color:#ff6b6b;font-size:0.85rem;margin-top:12px;display:none;"></div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button onclick="closeCreatePlaylistModal()" style="flex:1;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:transparent;color:var(--text-muted);font-weight:600;cursor:pointer;font-size:0.9rem;transition:all 0.2s;" onmouseover="this.style.color='#fff';this.style.borderColor='rgba(255,255,255,0.3)'" onmouseout="this.style.color='';this.style.borderColor=''">Batal</button>
                <button onclick="submitCreatePlaylist()" id="cp-submit" style="flex:2;padding:12px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-weight:700;cursor:pointer;font-size:0.95rem;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                    <i class="fa-solid fa-plus" style="margin-right:6px;"></i> Buat Playlist
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════ Modal: Tambah ke Playlist ═══════ -->
    <div id="add-to-playlist-modal" style="
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;
    " onclick="if(event.target===this) closeAddToPlaylistModal()">
        <div style="
            background: linear-gradient(145deg, #1a1f2e, #0f1623);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px;
            padding: 28px; width: 380px; max-width: 90vw;
            box-shadow: 0 30px 80px rgba(0,0,0,0.8);
            animation: slideUp 0.3s ease;
        ">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <h2 style="font-size:1.15rem;font-weight:800;color:#fff;">Tambah ke Playlist</h2>
                <button onclick="closeAddToPlaylistModal()" style="margin-left:auto;background:none;border:none;color:var(--text-muted);font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
            </div>
            <div id="atp-song-info" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,0.04);border-radius:10px;margin-bottom:18px;">
                <img id="atp-cover" src="" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">
                <div>
                    <div id="atp-title" style="font-weight:600;font-size:0.9rem;"></div>
                    <div id="atp-artist" style="font-size:0.78rem;color:var(--text-muted);"></div>
                </div>
            </div>
            <div id="atp-list" style="display:flex;flex-direction:column;gap:6px;max-height:250px;overflow-y:auto;"></div>
            <button onclick="openCreatePlaylistModal()" style="width:100%;margin-top:14px;padding:10px;border-radius:10px;border:1px dashed rgba(255,255,255,0.2);background:transparent;color:var(--primary);font-weight:600;cursor:pointer;font-size:0.88rem;transition:all 0.2s;" onmouseover="this.style.background='rgba(var(--primary-rgb),0.1)'" onmouseout="this.style.background='transparent'">
                <i class="fa-solid fa-plus" style="margin-right:6px;"></i> Buat Playlist Baru
            </button>
        </div>
    </div>

    <style>
    @keyframes slideUp {
        from { opacity:0; transform:translateY(30px) scale(0.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    </style>
</body>
</html>

