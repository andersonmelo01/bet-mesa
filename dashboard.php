<?php
// ======================
// INICIALIZAÇÃO
// ======================
if (session_status() === PHP_SESSION_NONE) session_start();

require 'conexao.php';

// ======================
// VERIFICAR LOGIN
// ======================
$usuario_id = $_SESSION['usuario_id'] ?? 0;
if ($usuario_id == 0) {
    header('Location: login.php');
    exit;
}

// ======================
// PEGAR DADOS DO USUÁRIO
// ======================
$user = [
    'nome' => $_SESSION['nome'] ?? '',
    'nik' => $_SESSION['nik'] ?? '',
    'email' => '',
];
$saldo = 0;

try {
    // Buscar email e nome do usuário no banco
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id=?");
    $stmt->execute([$usuario_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $user['email'] = $row['email'];

    // Buscar saldo do usuário
    $stmt = $pdo->prepare("SELECT saldo FROM carteira WHERE usuario_id=?");
    $stmt->execute([$usuario_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $saldo = $row['saldo'] ?? 0;

    // Buscar convites pendentes
    $stmt = $pdo->prepare("
        SELECT c.*, m.titulo
        FROM convites c
        LEFT JOIN mesas m ON m.id=c.mesa_id
        WHERE c.aceitou=0 AND c.usuario_id=?
        ORDER BY c.criado_em DESC
    ");
    $stmt->execute([$usuario_id]);
    $convites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Listar mesas
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*
        FROM mesas m
        LEFT JOIN participantes p ON p.mesa_id=m.id
        WHERE m.criador_id=? OR p.usuario_id=?
        ORDER BY m.criado_em DESC
    ");
    $stmt->execute([$usuario_id, $usuario_id]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

require 'header.php';
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar-aposta {
            background: linear-gradient(90deg, #1e293b, #0f172a);
            padding: 12px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-aposta .logo {
            font-size: 22px;
            font-weight: bold;
            color: #22c55e;
        }

        .navbar-aposta .nav-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-aposta .nav-area .user {
            font-weight: 500;
            color: #fff;
        }

        .navbar-aposta .saldo {
            background: #facc15;
            color: #000;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn-aposta {
            padding: 7px 14px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-mesa {
            background: #22c55e;
            color: #fff;
        }

        .btn-deposito {
            background: #facc15;
            color: #000;
        }

        .btn-sair {
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
        }

        .btn-aposta:hover {
            opacity: 0.85;
        }

        .dashboard {
            max-width: 1000px;
            margin: auto;
            margin-top: 30px;
            padding-bottom: 50px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            background: #1e293b;
            margin-bottom: 20px;
        }

        .card-title {
            font-weight: 600;
            color: #f1f5f9;
        }

        .mesa-item {
            transition: 0.3s;
            border-radius: 8px;
            background: #0f172a;
            margin-bottom: 6px;
            padding: 10px 15px;
        }

        .mesa-item:hover {
            background: #1e293b;
        }

        .badge {
            font-size: 14px;
        }

        .btn-success {
            background: #22c55e;
            border: none;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .list-group-item-action {
            background: #0f172a;
            border: none;
            color: #f1f5f9;
        }

        .list-group-item-action:hover {
            background: #1e293b;
            color: #fff;
        }

        .alert-light {
            background: #1e293b;
            color: #f1f5f9;
            border: none;
        }

        body {
            background: #0f172a;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-style: italic;
            /* <- DEIXA TODO O TEXTO EM CURSIVO */
        }

        .navbar-aposta .logo,
        .navbar-aposta .nav-area .user,
        .navbar-aposta .saldo,
        .card-title,
        .mesa-item,
        .list-group-item-action,
        .alert-light,
        .badge {
            font-style: italic;
            /* Garantir que elementos específicos também fiquem cursivos */
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <!-- <nav class="navbar-aposta">
        <div class="logo">🎲 Plataforma de Mesas</div>
        <div class="nav-area">
            <a href="criar_mesa.php"><button class="btn-aposta btn-mesa">➕ Criar Mesa</button></a>
            <span class="user">👤 <?= htmlspecialchars($user['nome'] ?: $user['nik'] ?: 'Usuário') ?></span>
            <div class="saldo">💰 R$ <?= number_format($saldo, 2, ",", ".") ?></div>
            <a href="deposito.php"><button class="btn-aposta btn-deposito">💳 Depositar</button></a>
            <a href="logout.php"><button class="btn-aposta btn-sair">Sair</button></a>
        </div>
    </nav>-->

    <!-- DASHBOARD -->
    <div class="dashboard">

        <!-- CONVITES -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">📩 Convites Pendentes</h5>
                <?php if (empty($convites)) { ?>
                    <div class="alert alert-light border">Nenhum convite pendente.</div>
                <?php } else { ?>
                    <div class="list-group">
                        <?php foreach ($convites as $c) { ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center mesa-item">
                                <div><strong>Mesa:</strong> <?= htmlspecialchars($c['titulo']) ?></div>
                                <a href="aceitar_convite.php?token=<?= $c['token'] ?>" class="btn btn-success btn-sm">Aceitar</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- MESAS -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">👥 Minhas Mesas</h5>
                <?php if (empty($mesas)) { ?>
                    <div class="alert alert-light border">Você ainda não participa de nenhuma mesa.</div>
                <?php } else { ?>
                    <div class="list-group">
                        <?php foreach ($mesas as $m) { ?>
                            <a href="mesa.php?id=<?= $m['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mesa-item">
                                <div><strong><?= htmlspecialchars($m['titulo']) ?></strong></div>
                                <span class="badge bg-primary">Abrir</span>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>