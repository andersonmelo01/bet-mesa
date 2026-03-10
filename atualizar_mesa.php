<?php
require 'conexao.php';
session_start();

$mesa_id = intval($_GET['mesa_id']);

/* PARTICIPANTES */
$stmt = $pdo->prepare("
SELECT 
u.nik,
p.pagou,
p.aceitou
FROM participantes p
JOIN usuarios u ON u.id = p.usuario_id
WHERE p.mesa_id=?
");
$stmt->execute([$mesa_id]);
$participantes = $stmt->fetchAll();

/* PROGRESSO */
$total = count($participantes);
$pagos = 0;
foreach ($participantes as $p) {
    if ($p['pagou']) $pagos++;
}
$progresso = $total > 0 ? round(($pagos / $total) * 100) : 0;

/* STATUS MESA */
$stmt = $pdo->prepare("SELECT status FROM mesas WHERE id=?");
$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

?>

<div id="progresso-mesa">
    <h3>Pagamentos da mesa</h3>
    <div class="progress">
        <span style="width:<?= $progresso ?>%"><?= $progresso ?>%</span>
    </div>
    <p><?= $pagos ?> de <?= $total ?> participantes pagaram</p>
</div>

<div id="participantes-mesa">
    <h3>Participantes</h3>
    <?php foreach ($participantes as $p): ?>
        <div class="participante">
            <span><?= htmlspecialchars($p['nik']) ?></span>
            <span>
                <?php if ($p['aceitou'] == 0): ?>
                    <span class="pendente">Convite pendente</span>
                <?php elseif ($p['pagou']): ?>
                    <span class="pago">Pago</span>
                <?php else: ?>
                    <span class="pendente">Aguardando pagamento</span>
                <?php endif; ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<div class="status">
    Status da mesa: <b><?= $mesa['status'] ?></b>
</div>