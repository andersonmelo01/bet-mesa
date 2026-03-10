<?php
require 'conexao.php';
require 'header.php';

// Verificar se o usuário está logado e se é mestre
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['nivel']) || $_SESSION['nivel'] != 'mestre') {
    die("Acesso negado.");
}


$mesa_id = 0;

if (isset($_GET['id'])) {
    $mesa_id = intval($_GET['id']);
}

if ($mesa_id <= 0) {
    die("Mesa inválida.");
}

// Buscar dados da mesa
$stmt = $pdo->prepare("SELECT * FROM mesas WHERE id=?");
$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

if (!$mesa) {
    die("Mesa não encontrada.");
}

// Buscar participantes
$stmt = $pdo->prepare("
SELECT 
u.id, u.nik, p.pagou, p.aceitou, p.iniciou, p.vencedor_reportado, p.prova
FROM participantes p
JOIN usuarios u ON u.id = p.usuario_id
WHERE p.mesa_id=?
");
$stmt->execute([$mesa_id]);
$participantes = $stmt->fetchAll();

$total = count($participantes);
$pagos = 0;
foreach ($participantes as $p) {
    if ($p['pagou']) $pagos++;
}
$progresso = $total > 0 ? round(($pagos / $total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Admin - Mesa #<?= $mesa_id ?></title>
    <style>
        body {
            background: #0f172a;
            color: #fff;
            font-family: Arial;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 10px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .card {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .info-box {
            background: #334155;
            padding: 15px;
            border-radius: 8px;
        }

        .label {
            font-size: 13px;
            color: #94a3b8;
        }

        .valor {
            font-size: 20px;
            font-weight: bold;
            margin-top: 5px;
        }

        .participante {
            display: flex;
            justify-content: space-between;
            background: #334155;
            padding: 10px;
            border-radius: 6px;
            margin-top: 8px;
            align-items: center;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            color: #fff;
        }

        .btn-green {
            background: #22c55e;
        }

        .btn-red {
            background: #ef4444;
        }

        .btn-yellow {
            background: #facc15;
            color: #000;
        }

        .progress {
            background: #334155;
            border-radius: 10px;
            overflow: hidden;
            height: 22px;
            margin-top: 10px;
        }

        .progress span {
            display: block;
            height: 100%;
            background: #22c55e;
            text-align: center;
            font-size: 12px;
            line-height: 22px;
        }

        .upload-area {
            background: #334155;
            padding: 10px;
            border-radius: 8px;
        }

        .vencedor {
            background: #22c55e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Administração da Mesa #<?= $mesa_id ?></h2>

        <!-- INFORMAÇÕES DA MESA -->
        <div class="card">
            <h3>Informações da mesa</h3>
            <div class="info-grid">
                <div class="info-box">
                    <div class="label">Valor da aposta</div>
                    <div class="valor">R$ <?= number_format($mesa['valor_aposta'], 2, ",", ".") ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Premiação total</div>
                    <div class="valor">R$ <?= number_format($mesa['valor_aposta'] * $total, 2, ",", ".") ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Participantes</div>
                    <div class="valor"><?= $total ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="valor"><?= strtoupper($mesa['status']) ?></div>
                </div>
            </div>
        </div>

        <!-- PROGRESSO -->
        <div class="card">
            <h3>Pagamentos</h3>
            <div class="progress"><span style="width:<?= $progresso ?>%"><?= $progresso ?>%</span></div>
            <p><?= $pagos ?> de <?= $total ?> participantes pagaram</p>
        </div>

        <!-- PARTICIPANTES -->
        <div class="card">
            <h3>Participantes</h3>
            <?php foreach ($participantes as $p): ?>
                <div class="participante">
                    <div>
                        <?= htmlspecialchars($p['nik']) ?>
                        <?php
                        if ($p['aceitou'] == 0) echo "(Convite pendente)";
                        elseif ($p['pagou'] == 0) echo "(Aguardando pagamento)";
                        else echo "(Pago)";
                        ?>
                    </div>
                    <div>
                        <?php if ($p['prova']): ?>
                            <a href="<?= htmlspecialchars($p['prova']) ?>" target="_blank" class="btn btn-green">Ver prova</a>
                        <?php else: ?>
                            <span class="btn btn-red">Sem prova</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- APROVAR VENCEDOR -->
        <?php if ($mesa['status'] != 'finalizada'): ?>
            <div class="card">
                <h3>Definir vencedor</h3>
                <form method="post" action="admin_aprovar_vencedor.php">
                    <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
                    <?php foreach ($participantes as $p): ?>
                        <button type="submit" name="vencedor_id" value="<?= $p['id'] ?>" class="btn btn-green" style="margin:5px;">
                            <?= htmlspecialchars($p['nik']) ?>
                        </button>
                    <?php endforeach; ?>
                </form>
                <div class="alert-info">
                    Ao aprovar o vencedor, a mesa será finalizada e os resultados confirmados.
                </div>
            </div>
        <?php endif; ?>

        <!-- VENCEDOR FINAL -->
        <?php if ($mesa['status'] == 'finalizada' && $mesa['vencedor_id']): ?>
            <?php
            $stmt = $pdo->prepare("SELECT nik FROM usuarios WHERE id=?");
            $stmt->execute([$mesa['vencedor_id']]);
            $v = $stmt->fetch();
            ?>
            <div class="vencedor">
                🏆 Vencedor: <?= htmlspecialchars($v['nik']) ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>