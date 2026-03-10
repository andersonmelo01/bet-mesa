<?php
require 'conexao.php';
require 'header.php';

if (!isset($_SESSION['usuario_id'])) die("Usuário não logado");
$usuario_id = $_SESSION['usuario_id'];

$mesa_id = intval($_GET['id'] ?? $_GET['mesa'] ?? 0);
if ($mesa_id <= 0) die("Mesa inválida");

$stmt = $pdo->prepare("SELECT * FROM mesas WHERE id=?");
$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();
if (!$mesa) die("Mesa não encontrada");

$voto1 = $mesa['vencedor_reportado_1'];
$voto2 = $mesa['vencedor_reportado_2'];

$stmt = $pdo->prepare("
SELECT u.id,u.nik,p.pagou,p.aceitou,p.iniciou
FROM participantes p
JOIN usuarios u ON u.id = p.usuario_id
WHERE p.mesa_id=?
");
$stmt->execute([$mesa_id]);
$participantes = $stmt->fetchAll();

$adversario_id = null;
foreach ($participantes as $p) if ($p['id'] != $usuario_id) $adversario_id = $p['id'];

$total = count($participantes);
$pagos = 0;
foreach ($participantes as $p) if ($p['pagou']) $pagos++;

$progresso = $total > 0 ? round(($pagos / $total) * 100) : 0;

$stmt = $pdo->prepare("SELECT pagou FROM participantes WHERE mesa_id=? AND usuario_id=?");
$stmt->execute([$mesa_id, $usuario_id]);
$meu = $stmt->fetch();
$ja_pagou = $meu['pagou'] ?? 0;

if ($pagos == $total && $total > 0) {
    $pdo->prepare("UPDATE participantes SET iniciou=1 WHERE mesa_id=?")->execute([$mesa_id]);
}

$ja_votou = false;
$j1 = $participantes[0]['id'] ?? null;
$j2 = $participantes[1]['id'] ?? null;
if (($usuario_id == $j1 && $voto1) || ($usuario_id == $j2 && $voto2)) $ja_votou = true;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Mesa <?= $mesa_id ?></title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: #fff;
        }

        .container {
            display: flex;
            flex-direction: column;
            max-width: 1400px;
            margin: auto;
            height: 100vh;
            padding: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .main-grid {
            display: flex;
            flex: 1;
            gap: 10px;
        }

        .left,
        .right {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow: hidden;
        }

        .card {
            background: #1e293b;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .info-box {
            background: #334155;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .label {
            font-size: 12px;
            color: #94a3b8;
        }

        .valor {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        input[type=text],
        input[type=file] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: none;
        }

        button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 5px;
        }

        button:hover {
            opacity: 0.9;
        }

        .btn-green {
            background: #22c55e;
            color: #fff;
        }

        .btn-red {
            background: #ef4444;
            color: #fff;
        }

        .btn-yellow {
            background: #facc15;
            color: #000;
        }

        .progress {
            background: #334155;
            border-radius: 10px;
            overflow: hidden;
            height: 20px;
            margin-top: 5px;
        }

        .progress span {
            display: block;
            height: 100%;
            background: #22c55e;
            text-align: center;
            font-size: 12px;
            line-height: 20px;
        }

        .participantes-list {
            overflow-y: auto;
            flex: 1;
            padding-right: 5px;
        }

        .participante {
            display: flex;
            justify-content: space-between;
            background: #334155;
            padding: 8px;
            border-radius: 5px;
            margin-top: 5px;
        }

        .pendente {
            color: #facc15;
        }

        .pago {
            color: #22c55e;
            font-weight: bold;
        }

        .status {
            font-size: 13px;
            margin-top: 5px;
        }

        .vencedor {
            background: #22c55e;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 20px;
        }

        .upload-area {
            margin-top: 5px;
            background: #334155;
            padding: 10px;
            border-radius: 8px;
        }

        .alert-info {
            background: #0ea5e9;
            padding: 8px;
            border-radius: 5px;
            font-size: 12px;
            margin-top: 5px;
        }

        .alert-sucesso {
            background: #22c55e;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .alert-erro {
            background: #ef4444;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .scrollable {
            overflow-y: auto;
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>🎲 Mesa #<?= $mesa_id ?></h2>

        <?php if (isset($_SESSION['msg_sucesso'])): ?><div class="alert-sucesso"><?= $_SESSION['msg_sucesso']; ?></div><?php unset($_SESSION['msg_sucesso']);
                                                                                                                    endif; ?>
        <?php if (isset($_SESSION['msg_erro'])): ?><div class="alert-erro"><?= $_SESSION['msg_erro']; ?></div><?php unset($_SESSION['msg_erro']);
                                                                                                        endif; ?>

        <div class="main-grid">
            <div class="left scrollable">
                <!-- Informações da mesa -->
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

                <!-- Progresso -->
                <div class="card">
                    <h3>Pagamentos</h3>
                    <div class="progress"><span style="width:<?= $progresso ?>%"><?= $progresso ?>%</span></div>
                    <p><?= $pagos ?> de <?= $total ?> participantes pagaram</p>
                </div>

                <!-- Vencedor -->
                <?php if ($mesa['status'] != 'finalizada' && $pagos == $total): ?>
                    <div class="card">
                        <h3>Quem venceu?</h3>
                        <form method="post" action="reportar_vencedor.php">
                            <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
                            <button class="btn-green" name="vencedor_id" value="<?= $usuario_id ?>" <?= $ja_votou ? 'disabled' : '' ?>>Fui eu</button>
                            <?php if ($adversario_id): ?>
                                <button class="btn-red" name="vencedor_id" value="<?= $adversario_id ?>" <?= $ja_votou ? 'disabled' : '' ?>>Adversário</button>
                            <?php endif; ?>
                        </form>
                        <?php if ($ja_votou): ?><p style="color:#22c55e;margin-top:5px;">Você já confirmou o resultado.</p><?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Enviar prova -->
                <div class="card">
                    <h3>Enviar prova</h3>
                    <?php if ($mesa['status'] != 'finalizada'): ?>
                        <form method="post" action="enviar_prova.php" enctype="multipart/form-data">
                            <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
                            <div class="upload-area">
                                <label>Envie print, vídeo ou documento</label>
                                <input type="file" name="arquivo" required>
                                <button class="btn-green" style="margin-top:5px;">Enviar prova</button>
                            </div>
                            <div class="alert-info">
                                Caso haja divergência, a prova será analisada em até 72 horas.
                            </div>
                        </form>
                    <?php else: ?>
                        <p style="color:#f87171;">⚠️ A mesa foi finalizada. Não é possível enviar provas.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagamento -->
                <div class="card">
                    <form method="post" action="confirmar_pagamento.php">
                        <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
                        <button class="btn-yellow" <?= $ja_pagou ? 'disabled' : '' ?>><?= $ja_pagou ? 'Pagamento confirmado' : 'Confirmar pagamento' ?></button>
                    </form>
                    <div class="status">Status: <b><?= $mesa['status'] ?></b></div>
                </div>

                <!-- Resultado final -->
                <?php if ($mesa['status'] == 'finalizada'):
                    $stmt = $pdo->prepare("SELECT nik FROM usuarios WHERE id=?");
                    $stmt->execute([$mesa['vencedor_id']]);
                    $v = $stmt->fetch();
                ?>
                    <div class="vencedor">🏆 Vencedor: <?= htmlspecialchars($v['nik']) ?></div>
                <?php endif; ?>
            </div>

            <div class="right scrollable">
                <!-- Enviar convite -->
                <div class="card">
                    <h3>Enviar convite</h3>
                    <?php if ($mesa['status'] != 'finalizada'): ?>
                        <form method="post" action="enviar_convite.php">
                            <input type="hidden" name="mesa_id" value="<?= $mesa_id ?>">
                            <input type="text" name="destino" placeholder="Nik ou Email" required>
                            <button class="btn-green">Enviar</button>
                        </form>
                    <?php else: ?>
                        <p style="color:#f87171;">⚠️ A mesa foi finalizada. Não é possível enviar convites.</p>
                    <?php endif; ?>
                    <!-- Participantes -->
                    <h3 style="margin-top:10px;">Participantes</h3>
                    <div class="participantes-list">
                        <?php foreach ($participantes as $p): ?>
                            <div class="participante">
                                <span><?= htmlspecialchars($p['nik']) ?></span>
                                <span class="<?= $p['aceitou'] == 0 ? 'pendente' : ($p['pagou'] ? 'pago' : 'pendente') ?>">
                                    <?= $p['aceitou'] == 0 ? 'Convite pendente' : ($p['pagou'] ? 'Pago' : 'Aguardando pagamento') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div> <!-- main-grid -->
    </div> <!-- container -->
</body>

</html>