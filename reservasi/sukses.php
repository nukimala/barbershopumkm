<?php
$antrian = isset($_GET['antrian']) ? $_GET['antrian'] : '-';
$nama = isset($_GET['nama']) ? $_GET['nama'] : 'Customer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sukses | Pak To Barbershop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-wrapper {
            text-align: center;
            padding: 50px 20px;
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .checkmark {
            font-size: 5rem;
            color: var(--gold);
            margin-bottom: 20px;
        }
        .queue-box {
            background: var(--bg-card);
            padding: 30px;
            border-radius: 15px;
            border: 2px solid var(--gold);
            margin: 30px 0;
            box-shadow: 0 0 20px rgba(209, 159, 104, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .queue-title {
            color: var(--text-muted);
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .queue-number {
            font-size: 6rem;
            font-weight: 800;
            color: var(--text-light);
            line-height: 1;
            margin: 10px 0;
        }
        .btn-home {
            background: transparent;
            border: 2px solid var(--gold);
            color: var(--gold);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-home:hover {
            background: var(--gold);
            color: var(--bg-dark);
        }
    </style>
</head>
<body>

    <div class="container success-wrapper">
        <div class="checkmark">✔</div>
        <h1 class="title">Reservasi Berhasil!</h1>
        <p class="subtitle">Terima kasih, <?php echo htmlspecialchars($nama); ?>.</p>

        <div class="queue-box">
            <div class="queue-title">Nomor Antrian Anda Adalah</div>
            <div class="queue-number"><?php echo htmlspecialchars($antrian); ?></div>
            <div class="subtitle">Mohon tunggu sampai nomor antrian anda dipanggil.</div>
        </div>

        <a href="../index.php" class="btn-home">Kembali ke Halaman Utama</a>
    </div>

</body>
</html>