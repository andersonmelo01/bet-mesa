<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <title>Depósito PIX</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow-lg border-0">

                    <div class="card-header bg-dark text-white text-center">

                        <h4 class="mb-0">💳 Depósito via PIX</h4>

                    </div>

                    <div class="card-body">

                        <form id="formPix">

                            <div class="mb-3">

                                <label class="form-label">
                                    Valor do depósito
                                </label>

                                <input
                                    type="number"
                                    name="valor"
                                    step="0.01"
                                    min="1"
                                    class="form-control form-control-lg"
                                    placeholder="Ex: 50.00"
                                    required>

                            </div>

                            <div class="d-grid">

                                <button type="submit" id="btnPix" class="btn btn-success btn-lg">
                                    Gerar PIX
                                </button>

                            </div>

                        </form>

                        <hr>

                        <div id="areaPix" class="text-center"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

<script>
    document.getElementById("formPix").addEventListener("submit", function(e) {

        e.preventDefault();

        let formData = new FormData(this);

        document.getElementById("btnPix").innerHTML = "Gerando PIX...";
        document.getElementById("btnPix").disabled = true;

        fetch("criar_pix.php", {
                method: "POST",
                body: formData
            })
            .then(response => {

                if (!response.ok) {
                    throw new Error("Erro HTTP: " + response.status);
                }

                return response.json();

            })
            .then(data => {

                document.getElementById("btnPix").innerHTML = "Gerar PIX";
                document.getElementById("btnPix").disabled = false;

                console.log(data); // debug

                if (data.erro) {

                    document.getElementById("areaPix").innerHTML = `
            <div class="alert alert-danger mt-3">
            ${data.erro}
            </div>
            `;

                    return;
                }

                document.getElementById("areaPix").innerHTML = `

        <div class="mt-4">

        <h5 class="mb-3">Escaneie o QR Code</h5>

        <img src="${data.imagem}" 
        class="img-fluid mb-3 border rounded p-2" 
        style="max-width:250px">

        <div class="input-group mb-3">

        <input 
        type="text" 
        class="form-control" 
        value="${data.codigo}" 
        id="codigoPix" 
        readonly>

        <button 
        class="btn btn-outline-secondary" 
        onclick="copiarPix()">
        Copiar
        </button>

        </div>

        <div class="alert alert-warning">

        ⏳ Aguardando pagamento...

        </div>

        </div>

        `;

            })
            .catch(error => {

                document.getElementById("btnPix").innerHTML = "Gerar PIX";
                document.getElementById("btnPix").disabled = false;

                document.getElementById("areaPix").innerHTML = `
        <div class="alert alert-danger mt-3">
        Erro ao gerar PIX
        </div>
        `;

                console.error(error);

            });

    });


    function copiarPix() {

        let codigo = document.getElementById("codigoPix");

        navigator.clipboard.writeText(codigo.value);

        alert("Código PIX copiado!");

    }
</script>


</html>