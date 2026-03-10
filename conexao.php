<?php
$host = 'localhost';
$db   = 'mesaaposta';
$user = 'root';
$pass = 'admin';
$charset = 'utf8mb4';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {

    try {

        $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE ${charset}_general_ci");
        $pdo->exec("USE `$db`");

        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e2) {
        die("Erro ao conectar/criar banco: " . $e2->getMessage());
    }
}

/* ==================================================
   TABELA USUARIOS
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    nik VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    nivel VARCHAR(20) NOT NULL DEFAULT 'comum'
) ENGINE=InnoDB;
");

/* ==================================================
   TABELA CARTEIRA
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS carteira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    saldo DECIMAL(10,2) DEFAULT 0.00,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

/* ==================================================
   TABELA MESAS
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    valor_aposta DECIMAL(10,2) DEFAULT 0.00,
    max_participantes INT DEFAULT 2,
    criador_id INT NOT NULL,

    status ENUM('aguardando','ativa','finalizada','contestado') DEFAULT 'aguardando',

    vencedor_id INT NULL,
    vencedor_reportado_1 INT NULL,
    vencedor_reportado_2 INT NULL,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (criador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

/* ==================================================
   TABELA PARTICIPANTES
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    mesa_id INT NOT NULL,
    usuario_id INT NOT NULL,

    aceitou TINYINT(1) DEFAULT 0,
    pagou TINYINT(1) DEFAULT 0,
    iniciou TINYINT(1) DEFAULT 0,
    ganhou TINYINT(1) DEFAULT NULL,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_participante (mesa_id, usuario_id),

    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

/* ==================================================
   TABELA CONVITES
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS convites (
    id INT AUTO_INCREMENT PRIMARY KEY,

    mesa_id INT,
    usuario_id INT,

    token VARCHAR(120) UNIQUE,
    aceitou TINYINT(1) DEFAULT 0,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

/* ==================================================
   TABELA TRANSACOES
================================================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT,
    valor DECIMAL(10,2),

    tipo ENUM('deposito','aposta','premio'),

    status ENUM('pendente','pago','cancelado') DEFAULT 'pendente',

    pagbank_id VARCHAR(200),

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

/* ==================================================
   USUARIO ADMIN PADRAO
================================================== */

$adminNik = 'admin';

$checkAdmin = $pdo->prepare("SELECT id FROM usuarios WHERE nik=?");
$checkAdmin->execute([$adminNik]);

if ($checkAdmin->rowCount() == 0) {

    $senhaAdmin = password_hash('123456', PASSWORD_DEFAULT);

    $pdo->prepare("
    INSERT INTO usuarios (nome, nik, email, senha, nivel)
    VALUES (?,?,?,?,?)
    ")->execute([
        'Administrador',
        'admin',
        'admin@teste.com',
        $senhaAdmin,
        'mestre'
    ]);
}
