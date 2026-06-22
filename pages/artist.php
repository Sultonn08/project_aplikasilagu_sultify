<?php
$pdo = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM artists WHERE id = ?");
$stmt->execute([$id]);
$artist = $stmt->fetch();

if (!$artist) {
    echo "<div style='text-align: center; margin-top: 100px;'><h1>Artis tidak ditemukan</h1><a href='".BASE_URL."' class='btn-login' style='margin-top:20px; display:inline-block;'>Kembali ke Beranda</a></div>";
    exit;
}

// Ambil 10 Lagu Terpopuler dari Artis
$stmt2 = $pdo->prepare("SELECT * FROM v_song_detail WHERE artist_id = ? ORDER BY play_count DESC LIMIT 10");
$stmt2->execute([$id]);
$topSongs = $stmt2->fetchAll();

// Ambil Diskografi (Album)
$stmt3 = $pdo->prepare("SELECT * FROM albums WHERE artist_id = ? AND is_published = 1 ORDER BY release_date DESC");
$stmt3->execute([$id]);
$albums = $stmt3->fetchAll();
?>

<!-- Menutup page-content sementara untuk merentangkan header artist secara full -->
</div> 
<div class="artist-hero fade-in">
    <!-- Blur Background Effect -->
    <img src="<?= getCoverUrl($artist['photo']) ?>" class="artist-hero-bg" onerror="this.style.display='none'">
    <div class="artist-hero-overlay"></div>
    
    <!-- Artist Info -->
    <div class="artist-hero-content">
        <?php if ($artist['is_verified']): ?>
            <div class="artist-verified"><i class="fa-solid fa-certificate"></i> Artis Terverifikasi</div>
        <?php endif; ?>
        <h1 class="artist-name"><?= htmlspecialchars($artist['name']) ?></h1>
        <div class="artist-listeners"><?= formatNumber($artist['monthly_listeners']) ?> pendengar bulanan</div>
    </div>
</div>

<!-- Membuka kembali page-content -->
<div class="page-content" style="margin-top: 30px;"> 

<div style="display: flex; gap: 24px; margin-bottom: 40px; align-items: center;" class="fade-in">
    <!-- Tombol Play besar (memainkan lagu pertama di tabel) -->
    <button class="play-btn" style="width: 56px; height: 56px; font-size: 1.3rem;" onclick="document.querySelector('.song-list tr').click()">
        <i class="fa-solid fa-play"></i>
    </button>
    <button class="btn-login" style="padding: 10px 28px; font-size: 0.85rem; border-color: var(--text-muted); color: var(--text);">IKUTI</button>
    <i class="fa-solid fa-ellipsis" style="font-size: 1.5rem; color: var(--text-muted); cursor: pointer;" title="Opsi"></i>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
    <!-- Kolom Kiri: Populer -->
    <section class="section fade-in" style="animation-delay: 0.1s;">
        <h2 class="section-title">Populer</h2>
        <table class="song-list">
            <tbody>
                <?php foreach($topSongs as $index => $song): ?>
                    <?php $playArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover']) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
                    <tr id="song-row-<?= $song['id'] ?>" onclick="playSong(<?= $playArgs ?>)">
                        <td class="song-num"><?= $index + 1 ?></td>
                        <td>
                            <div class="song-info">
                                <div class="song-thumb" style="width: 40px; height: 40px;">
                                    <img src="<?= getCoverUrl($song['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                                </div>
                                <div class="song-name"><?= htmlspecialchars($song['title']) ?></div>
                            </div>
                        </td>
                        <td style="color: var(--text-muted); font-size: .875rem; text-align: right; padding-right: 20px;">
                            <?= number_format($song['play_count'], 0, ',', '.') ?>
                        </td>
                        <td class="song-duration"><?= formatDuration($song['duration']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    
    <!-- Kolom Kanan: Info Bio Artis -->
    <section class="section fade-in" style="animation-delay: 0.2s;">
        <h2 class="section-title">Tentang</h2>
        <div class="card" style="padding: 24px; position: relative; overflow: hidden;">
            <img src="<?= getCoverUrl($artist['photo']) ?>" style="width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: var(--radius); margin-bottom: 16px;" onerror="this.style.display='none'">
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                <?= nl2br(htmlspecialchars($artist['bio'] ?? 'Biografi belum tersedia untuk artis ini.')) ?>
            </p>
        </div>
    </section>
</div>

<!-- Diskografi Album -->
<?php if (!empty($albums)): ?>
<section class="section fade-in" style="animation-delay: 0.3s; margin-top: 20px;">
    <h2 class="section-title">Diskografi</h2>
    <div class="cards-grid">
        <?php foreach($albums as $album): ?>
            <div class="card" onclick="navigateTo('<?= BASE_URL ?>/album?id=<?= $album['id'] ?>')">
                <div class="card-cover">
                    <img src="<?= getCoverUrl($album['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    <div class="card-play-btn">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
                <div class="card-title"><?= htmlspecialchars($album['title']) ?></div>
                <div class="card-sub"><?= date('Y', strtotime($album['release_date'])) ?> • <?= ucfirst($album['album_type']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
