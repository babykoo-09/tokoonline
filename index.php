<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include 'header.php'; ?>

<header class="hero-section">
    <div class="hero-content">
        <h1>Sensasi Boba Pilihan Terbaik</h1>
        <p>Nikmati kesempurnaan rasa dari daun teh pilihan dan gula aren asli.</p>
        <a href="menu.php" class="cta-button">Pesan Sekarang</a>
    </div>
</header>

<section id="story" class="story-section">
    <div class="container">
        <h2>Cerita Kami</h2>
        <p>Di The Premium Bubble, kami percaya bahwa setiap tegukan harus menjadi sebuah pengalaman. Kami berkelana untuk menemukan daun teh terbaik dan memadukannya dengan gula aren murni yang dimasak secara tradisional. Setiap gelas adalah karya seni yang kami persembahkan untuk Anda.</p>
    </div>
</section>

<section id="signature-menu" class="menu-section">
    <div class="container">
        <h2>Menu Andalan Kami</h2>
        <div class="menu-grid">
            <?php
            include 'koneksi.php';
            $query = "SELECT * FROM products LIMIT 3";
            $result = $koneksi->query($query);
            while ($row = $result->fetch_assoc()):
            ?>
            <div class="menu-item">
                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section id="testimonials" class="testimonial-section">
    <div class="container">
        <h2>Apa Kata Mereka?</h2>
        <div class="testimonial-grid">
            <div class="testimonial">
                <p>"Boba terenak yang pernah saya coba! Gula arennya benar-benar terasa premium."</p>
                <span>- Pelanggan Setia</span>
            </div>
            <div class="testimonial">
                <p>"Tempatnya nyaman dan minumannya berkualitas. Matcha Latte-nya juara!"</p>
                <span>- Pengunjung Pertama</span>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
