<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - ROYJohanInfo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-color: #121212;
            --card-color: #1E1E1E;
            --accent-color: #03DAC6;
            --accent-soft: rgba(3, 218, 198, 0.2);
            --text-primary: #FFFFFF;
            --text-secondary: #B3B3B3;
            --divider-color: #2C2C2C;
            --error-color: #CF6679;
            --radius: 12px;
            --shadow-soft: 0 4px 12px rgba(0,0,0,0.4);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background: radial-gradient(circle at top, #1F1F1F 0, #000 60%);
            color: var(--text-primary);
        }

        .page-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        header img {
            height: 60px;
            border-radius: 8px;
            box-shadow: var(--shadow-soft);
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .card {
            background: var(--card-color);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--divider-color);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 14px;
            font-size: 1.1rem;
        }

        /* Contenu centré dans chaque tuile */
        .card-content {
            max-width: 420px;
            margin: 0 auto;
        }

        /* Pour la tuile de recherche, un peu plus large */
        .card-content.search {
            max-width: 700px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1100px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .input-group {
            margin-bottom: 14px;
        }

        .input-group label {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .input-group input,
        .input-group textarea,
        .input-group select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #1A1A1A;
            color: white;
            margin-top: 4px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            background: var(--accent-color);
            color: black;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-block;
        }

        .btn:hover {
            background: #00bfa5;
            transform: translateY(-1px);
        }

        .btn-row {
            margin-top: 8px;
            text-align: right;
        }

        .search-input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #1A1A1A;
            color: white;
            font-size: 1rem;
        }

        .doc-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #252525;
            border-radius: 8px;
            border: 1px solid #333;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .doc-item button {
            background: var(--error-color);
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
<div class="page-wrapper">

    <!-- HEADER -->
    <header>
        <img src="https://lh3.googleusercontent.com/p/AF1QipPkld8Cg3_GuIjRTeyXdn_o3wLVNIxxXWm_4f2P=w244-h245-n-k-no-nu" alt="Logo">
        <div>
            <h1>Dashboard Administrateur</h1>
            <span style="color:var(--text-secondary)">Gestion complète des interventions</span>
        </div>
    </header>

    <!-- GRID PRINCIPALE -->
    <div class="grid-4">

        <!-- TUILE 1 — FULL WIDTH (RECHERCHE) -->
        <section class="card full">
            <div class="card-content search">
                <h2>Rechercher une intervention</h2>
                <div class="input-group">
                    <label>Recherche</label>
                    <input class="search-input" placeholder="Nom, téléphone, série, modèle...">
                </div>
                <div class="btn-row">
                    <button class="btn">Rechercher</button>
                </div>
            </div>
        </section>

        <!-- TUILE 2 — CLIENT -->
        <section class="card">
            <div class="card-content">
                <h2>Client</h2>

                <div class="input-group"><label>Nom</label><input type="text"></div>
                <div class="input-group"><label>Prénom</label><input type="text"></div>
                <div class="input-group"><label>Adresse</label><input type="text"></div>
                <div class="input-group"><label>Téléphone</label><input type="text"></div>
                <div class="input-group"><label>Email</label><input type="text"></div>
            </div>
        </section>

        <!-- TUILE 3 — APPAREIL -->
        <section class="card">
            <div class="card-content">
                <h2>Appareil</h2>

                <div class="input-group"><label>Type d'appareil</label><input type="text"></div>
                <div class="input-group"><label>Marque</label><input type="text"></div>
                <div class="input-group"><label>Modèle</label><input type="text"></div>
                <div class="input-group"><label>Couleur</label><input type="text"></div>
                <div class="input-group"><label>Série</label><input type="text"></div>
                <div class="input-group"><label>Série 2</label><input type="text"></div>
            </div>
        </section>

        <!-- TUILE 4 — INTERVENTION -->
        <section class="card">
            <div class="card-content">
                <h2>Intervention</h2>

                <div class="input-group"><label>Type d'intervention</label><input type="text"></div>

                <div class="input-group">
                    <label>Statut</label>
                    <select>
                        <option>Attente réponse devis</option>
                        <option>En cours</option>
                        <option>Terminé</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Avancement (%)</label>
                    <input type="number" min="0" max="100">
                </div>

                <div class="input-group">
                    <label>Notes technicien</label>
                    <textarea rows="4"></textarea>
                </div>

                <div class="btn-row">
                    <button class="btn">Enregistrer</button>
                </div>
            </div>
        </section>

        <!-- TUILE 5 — DOCUMENTS -->
        <section class="card">
            <div class="card-content">
                <h2>Documents</h2>

                <div id="docList">
                    <div class="doc-item">
                        <span>facture_001.pdf</span>
                        <button>Supprimer</button>
                    </div>
                    <div class="doc-item">
                        <span>devis_001.pdf</span>
                        <button>Supprimer</button>
                    </div>
                </div>

                <div class="input-group" style="margin-top:16px;">
                    <label>Ajouter un document</label>
                    <input type="file">
                </div>

                <div class="btn-row">
                    <button class="btn">Uploader</button>
                </div>
            </div>
        </section>

    </div>

</div>
</body>
</html>
