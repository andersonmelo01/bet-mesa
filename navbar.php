<nav class="navbar">

    <div class="nav-left">
        <a href="dashboard.php">🏠 Início</a>
        <a href="mesas.php">🎲 Mesas</a>
        <a href="criar_mesa.php">➕ Criar Mesa</a>
    </div>

    <div class="nav-right">
        <span class="text-white me-3">
            👤 <?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['nik']) ?>
        </span>

        <a href="logout.php" class="btn btn-outline-light btn-sm">
            Sair
        </a>
    </div>

</nav>