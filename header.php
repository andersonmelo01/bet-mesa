<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$saldo = 0;

if (isset($_SESSION['usuario_id'])) {

    $stmt = $pdo->prepare("
        SELECT saldo
        FROM carteira
        WHERE usuario_id=?
    ");

    $stmt->execute([$_SESSION['usuario_id']]);
    $carteira = $stmt->fetch();

    $saldo = $carteira['saldo'] ?? 0;
}
?>
<style>
    /* ==============================
   NAVBAR RESPONSIVA
============================== */
    .navbar-aposta {
        background: linear-gradient(90deg, #0f172a, #1e293b);
        padding: 12px 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
        font-style: italic;
    }

    .container-nav {
        max-width: 1100px;
        margin: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        /* permite quebrar linha em mobile */
        gap: 10px;
    }

    .logo {
        font-size: 20px;
        font-weight: bold;
        color: #22c55e;
        font-style: italic;
    }

    .nav-area {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-style: italic;
    }

    .user {
        color: #fff;
        font-weight: 500;
        font-style: italic;
    }

    .saldo {
        background: #facc15;
        color: #000;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: bold;
        font-style: italic;
    }

    .btn-aposta {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        font-weight: bold;
        cursor: pointer;
        font-style: italic;
    }

    .btn-mesa {
        background: #22c55e;
        color: #fff;
    }

    .btn-deposito {
        background: #facc15;
    }

    .btn-sair {
        background: transparent;
        border: 1px solid #fff;
        color: #fff;
    }

    .btn-aposta:hover {
        opacity: 0.9;
    }

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {
        .logo {
            font-size: 18px;
        }

        .btn-aposta {
            font-size: 12px;
            padding: 5px 10px;
        }

        .saldo {
            font-size: 12px;
            padding: 5px 10px;
        }

        .nav-area {
            justify-content: flex-start;
            gap: 5px;
        }
    }
</style>

<nav class="navbar-aposta">

    <div class="container-nav">

        <div class="logo">
            🎲 Plataforma de Mesas
        </div>

        <div class="nav-area">

            <a href="criar_mesa.php">
                <button class="btn-aposta btn-mesa">
                    ➕ Criar Mesa
                </button>
            </a>

            <span class="user">
                👤 <?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['nik'] ?? 'Usuário') ?>
            </span>

            <div class="saldo">
                💰 R$ <?= number_format($saldo ?? 0, 2, ",", ".") ?>
            </div>

            <a href="deposito.php">
                <button class="btn-aposta btn-deposito">
                    💳 Depositar
                </button>
            </a>

            <a href="logout.php">
                <button class="btn-aposta btn-sair">
                    Sair
                </button>
            </a>

        </div>

    </div>

</nav>