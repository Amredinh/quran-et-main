<?php
require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        return self::$instance;
    }

    public static function init(): void {
        $pdo = self::getConnection();

        $pdo->exec("CREATE TABLE IF NOT EXISTS translations (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            xml LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(500) NOT NULL,
            excerpt TEXT,
            content TEXT NOT NULL,
            image VARCHAR(1000),
            date VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS reciters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(500) NOT NULL,
            subfolder VARCHAR(255) NOT NULL UNIQUE,
            is_every_ayah TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS donation_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT,
            button_text VARCHAR(500),
            link VARCHAR(1000),
            enabled TINYINT(1) DEFAULT 1,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed default data if tables are empty
        $count = $pdo->query("SELECT COUNT(*) FROM reciters")->fetchColumn();
        if ($count == 0) {
            self::seedDefaults($pdo);
        }

        $dc = $pdo->query("SELECT COUNT(*) FROM donation_config")->fetchColumn();
        if ($dc == 0) {
            $pdo->prepare("INSERT INTO donation_config (message, button_text, link, enabled) VALUES (?, ?, ?, ?)")->execute([
                "Help us keep Quran.et free and sustainable for the Ethiopian community.",
                "Donate Now",
                "https://example.com/donate",
                1
            ]);
        }
    }

    private static function seedDefaults(PDO $pdo): void {
        $reciters = [
            ['Abdulbasit Abdulsamad (Sura Recitation)', 'basit', 0],
            ['Maher Al-Meaqli (Sura Recitation)', 'maher', 0],
            ['Mishary Alafasi (Sura Recitation)', 'afs', 0],
            ['Saad Al-Ghamdi (Sura Recitation)', 's_gmd', 0],
            ['Abdulrahman Al-Sudaes (Sura Recitation)', 'sds', 0],
            ['Abdul Basit (Every Ayah - AbdulSamad_64kbps)', 'AbdulSamad_64kbps_QuranExplorer.Com', 1],
            ['Mishary Alafasy (Every Ayah - Alafasy_128kbps)', 'Alafasy_128kbps', 1],
            ['Saad Al-Ghamdi (Every Ayah - Ghamadi_40kbps)', 'Ghamadi_40kbps', 1],
            ['Mahmoud Al-Husary (Every Ayah - Husary_128kbps)', 'Husary_128kbps', 1],
            ['Maher Al-Muaiqly (Every Ayah - Maher_AlMuaiqly_64kbps)', 'Maher_AlMuaiqly_64kbps', 1],
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO reciters (name, subfolder, is_every_ayah) VALUES (?, ?, ?)");
        foreach ($reciters as $r) {
            $stmt->execute($r);
        }

        // Seed default blogs
        $blogs = [
            [
                'title' => "Understanding the Revelation of Surah Al-Alaq",
                'excerpt' => "The first revealed surah holds deep significance...",
                'content' => "The revelation of Surah Al-Alaq marks the beginning of the Quranic journey. 'Iqra' or 'Read' was the first command given to Prophet Muhammad (PBUH) in the cave of Hira. This command highlights the supreme importance of knowledge, education, and literacy in the Islamic faith. Understanding this surah allows believers to appreciate the value of learning as a gateway to spiritual enlightenment and a closer relationship with the Creator.",
                'image' => "https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&q=80&w=800",
                'date' => "Oct 24, 2023"
            ],
            [
                'title' => "The Importance of Tajweed",
                'excerpt' => "Why reciting the Quran correctly matters.",
                'content' => "Tajweed is the set of rules governing the pronunciation of Quranic letters. It is not merely about aesthetics; it is about preserving the divine message exactly as it was revealed. Incorrect pronunciation can, in some cases, alter the meaning of the words entirely. By studying Tajweed, believers ensure they are honoring the text and fulfilling the duty of 'reciting the Quran in slow, measured tones' as commanded in Surah Al-Muzzammil.",
                'image' => "https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800",
                'date' => "Oct 20, 2023"
            ]
        ];
        $stmt = $pdo->prepare("INSERT INTO blogs (title, excerpt, content, image, date) VALUES (?, ?, ?, ?, ?)");
        foreach ($blogs as $b) {
            $stmt->execute([$b['title'], $b['excerpt'], $b['content'], $b['image'], $b['date']]);
        }
    }
}
