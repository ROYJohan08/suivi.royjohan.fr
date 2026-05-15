<?php
// Tu peux ajouter ici ton include Errors::get() si nécessaire
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Code Intervention</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #0d1117;
            font-family: Arial, sans-serif;
            color: white;
        }

        .tile {
            background: #161b22;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
            text-align: center;
            width: 350px;
            border: 1px solid #30363d;
        }

        .tile h2 {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }

        .tile input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #30363d;
            background: #0d1117;
            color: white;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .tile button {
            width: 100%;
            padding: 12px;
            background: #5865F2;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }

        .tile button:hover {
            background: #4752C4;
        }
    </style>

    <script>
        function goToSuivi() {
            const code = document.getElementById("code").value.trim();
            if (code === "") {
                alert("Merci d’entrer un code d’intervention.");
                return;
            }
            window.location.href = "index.php?suivi=" + encodeURIComponent(code);
        }
    </script>
</head>

<body>

<div class="tile">
    <h2>Entrer le code de l'intervention</h2>
    <input type="text" id="code" placeholder="Code intervention">
    <button onclick="goToSuivi()">Valider</button>
</div>

</body>
</html>
