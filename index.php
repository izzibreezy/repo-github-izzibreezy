<?php
session_start();

class AES256Encryption {
    private $key;
    private $cipher = 'aes-256-cbc';

    public function __construct($key) {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt($plaintext) {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plaintext, $this->cipher, $this->key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt($encrypted) {
        $data = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encryptedText = substr($data, $ivLength);
        return openssl_decrypt($encryptedText, $this->cipher, $this->key, 0, $iv);
    }
}

$inputText = $_POST['inputText'] ?? '';
$encryptionKey = $_POST['encryptionKey'] ?? '';
$action = $_POST['action'] ?? '';
$encrypted = $_SESSION['encrypted'] ?? '';
$decrypted = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'Encrypt') {
    $aes = new AES256Encryption($encryptionKey);
    $encrypted = $aes->encrypt($inputText);
    $_SESSION['encrypted'] = $encrypted;
    $_SESSION['encryptionKey'] = $encryptionKey;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'Decrypt') {
    $aes = new AES256Encryption($_SESSION['encryptionKey'] ?? '');
    $decrypted = $aes->decrypt($encrypted);
    session_unset();
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AES-256 Encryption and Decryption</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif; /* Menggunakan font Poppins */
            background: linear-gradient(135deg, #000000, #1a1a1a, #ff00ff, #00ffff);
            background-size: 400% 400%;
            animation: gradient 10s ease infinite;
            color: #ffffff; /* Warna teks putih */
            margin: 0;
            padding: 20px;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h2 {
            color: #ffffff; /* Warna judul putih */
            text-align: center;
            font-weight: 600; /* Font berat untuk judul */
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        label {
            font-weight: 400; /* Font berat normal untuk label */
            margin-top: 10px;
            display: block;
            color: #ffffff; /* Warna label putih */
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff; /* Warna teks di input */
        }

        input[type="submit"] {
            background-color: #00ccff; /* Warna tombol neon */
            color: black;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            font-weight: 600; /* Font berat untuk tombol */
        }

        input[type="submit"]:hover {
            background-color: #00aaff; /* Warna neon lebih gelap saat hover */
        }

        h3 {
            margin-top: 20px;
            color: #ffffff; /* Warna subjudul putih */
            font-weight: 600; /* Font berat untuk subjudul */
        }

        textarea {
            resize: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff; /* Warna teks di textarea */
            border: 1px solid #ccc;
        }

        /* Media Queries for Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            input[type="text"], textarea {
                padding: 8px;
            }
            input[type="submit"] {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>AES-256 Encryption and Decryption</h2>
        <form method="POST">
            <label for="inputText">Input Text:</label>
            <input type="text" id="inputText" name="inputText" value="<?= htmlspecialchars($inputText) ?>" required>

            <label for="encryptionKey">Encryption Key:</label>
            <input type="text" id="encryptionKey" name="encryptionKey" value="<?= htmlspecialchars($encryptionKey) ?>" required>

            <input type="submit" name="action" value="Encrypt">
            
            <?php if (!empty($encrypted)): ?>
                <h3>Encrypted Text:</h3>
                <textarea rows="4" readonly><?= htmlspecialchars($encrypted) ?></textarea>
                <input type="submit" name="action" value="Decrypt">
            <?php endif; ?>
        </form>

        <?php if (!empty($decrypted)): ?>
            <h3>Decrypted Text:</h3>
            <textarea rows="4" readonly><?= htmlspecialchars($decrypted) ?></textarea>
        <?php endif; ?>
    </div>
</body>
</html>
