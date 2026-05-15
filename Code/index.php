<?php
	require_once("SOURCES/function.php");
	$_SESSION['Unlock'] = false;
    $Database =  new PDO("mysql:host=localhost;dbname=cms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES => false]);
	if (isset($_GET['suivi']) || isset($_POST['id'])) {
		if(isset($_GET['suivi'])){$_SESSION['code'] = $_GET['suivi'];}
		else{$_SESSION['code'] = $_POST['id'];}
		try{
			$_SESSION['id'] = ShortID::decode($_SESSION['code']);
		}
		catch(RuntimeException $e){
			header("Location: 404.php");
			exit;
		}
		if(!$_SESSION['id'] || !is_numeric($_SESSION['id']) || $_SESSION['id']<=0){
			header("Location: 404.php");
			exit;
		}
		if(isset($_POST['phone'])){
			$raw = getInfos($Database,$_POST['phone'],$_SESSION['id']);
			$docs = getDocs($Database,$_POST['phone'],$_SESSION['id']);
			if(!$raw || $raw==null){header("Location: 404.php");exit;}
			if(!$_SESSION['Unlock']){
				Errors::add('Téléphone incorrect',ErrorLevel::ERROR);
			}
			else{
				Errors::add('Dévérouillage réussit',ErrorLevel::SUCCESS);
			}
		}
		else{
			$raw = getInfos($Database,"",$_SESSION['id']);
			$docs = getDocs($Database,"",$_SESSION['id']);
		}
	}
	else{
		$raw['nom'] = fake_blocks(8);
		$raw['prenom'] = fake_blocks(8);
		$raw['type'] = fake_blocks(8);
		$raw['marque'] = fake_blocks(8);
		$raw['modele'] = fake_blocks(8);
		$raw['statut'] = fake_blocks(8);
		$raw['miseAJours'] = fake_blocks(12);
		$raw['typeIntervention'] = fake_blocks(9);
		$raw['notes'] = fake_blocks(8);
		$raw['avancement'] = 0;
		$raw['adresse'] = fake_blocks(20);
		$raw['telephone'] = fake_blocks(10);
		$raw['email'] = fake_blocks(18);
		$raw['serie'] = fake_blocks(7);
		$raw['serie2'] = fake_blocks(6);
		$raw['couleur'] = fake_blocks(6);
		$raw['wallet'] = "?id=".$_SESSION['code'];
		$docs = [];
		header("Location: 404.php");
		exit;
	}
	$errorMessage = Errors::get(ErrorLevel::ALL);
	function getInfos($Database,$phone,$id){
		$raw = intervention::get($Database,"",$id);
		if(!$raw || $raw==null){header("Location: 404.php");exit;}
		if($phone!==$raw['telephone']){
			$raw['nom'] = fake_blocks(8);
			$raw['adresse'] = fake_blocks(20);
			$raw['telephone'] = fake_blocks(10);
			$raw['email'] = fake_blocks(18);
			$raw['serie'] = fake_blocks(7);
			$raw['serie2'] = fake_blocks(6);
			$raw['wallet'] = "?id=".$_SESSION['code'];
		}
		else{$_SESSION['Unlock'] = true;}
		return $raw;
	}
	function getDocs($Database,$phone,$id){
		$docs = [];
		$raw = intervention::get($Database,"",$id);
		if($phone===$raw['telephone']){
			$_SESSION['Unlock'] = true;
			$folder = __DIR__ . "/SOURCES/Docs/" . intval($id);
			if (is_dir($folder)) {
				foreach (scandir($folder) as $file) {
					if ($file === "." || $file === "..") continue;
					$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					if (in_array($ext, ["pdf", "doc", "docx", "jpg", "png"])) {
						$docs[] = $file;
					}
				}
			}
		}
		return $docs;
	}
	function fake_blocks($len = 10) {
		return str_repeat("█", $len);
	}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>ROYJohanInfo - Suivi intervention</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link href="SOURCES/style.css" rel="stylesheet">
		<style>
            :root {--bg-color: #121212;--card-color: #1E1E1E;--accent-color: #03DAC6;--accent-color-soft: rgba(3, 218, 198, 0.2);--text-primary: #FFFFFF;--text-secondary: #B3B3B3;--divider-color: #2C2C2C;--error-color: #CF6679;--shadow-soft: 0 4px 12px rgba(0,0,0,0.4);--radius: 12px;}
            * {box-sizing: border-box;}
            body {margin: 0;padding: 0;font-family: 'Roboto', sans-serif;background: radial-gradient(circle at top, #1F1F1F 0, #000 60%);color: var(--text-primary);}
            .page-wrapper {max-width: 1100px;margin: 0 auto;padding: 24px 16px 40px;}
            header {display: flex;align-items: center;gap: 16px;margin-bottom: 24px;}
            header img.logo {height: 60px;width: auto;border-radius: 8px;box-shadow: var(--shadow-soft);}
            header .title-block {display: flex;flex-direction: column;}
            header .title-block h1 {margin: 0;font-size: 1.8rem;letter-spacing: 0.05em;}
            header .title-block span {margin-top: 4px;font-size: 0.9rem;color: var(--text-secondary);}
            .grid {display: grid;grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));gap: 16px;}
            .card {background: var(--card-color);border-radius: var(--radius);padding: 18px 20px;box-shadow: var(--shadow-soft);border: 1px solid var(--divider-color);}
            .card h2 {margin: 0 0 12px;font-size: 1.1rem;font-weight: 500;display: flex;align-items: center;justify-content: space-between;}
            .info-row {display: flex;justify-content: space-between;margin-bottom: 8px;font-size: 0.95rem;}
            .info-row .label {color: var(--text-secondary);}
            .info-row .value {font-weight: 400;}
            .divider {height: 1px;background: var(--divider-color);margin: 10px 0 14px;}
            .wallet-link {margin-top: 10px;}
            .wallet-link a {display: inline-flex;align-items: center;padding: 8px 12px;border-radius: 999px;background: var(--accent-color-soft);color: var(--accent-color);text-decoration: none;font-size: 0.9rem;font-weight: 500;transition: background 0.2s, transform 0.1s;}
            .wallet-link a:hover {background: rgba(3, 218, 198, 0.35);transform: translateY(-1px);}
            .wallet-link img {height: 20px;margin-right: 8px;}
            .progress-container {margin: 10px 0 14px;}
            .progress-label {display: flex;justify-content: space-between;font-size: 0.85rem;color: var(--text-secondary);margin-bottom: 4px;}
            .progress-bar-bg {width: 100%;height: 8px;border-radius: 999px;background: #2A2A2A;overflow: hidden;}
            .progress-bar-fill {height: 100%;width: 0;border-radius: 999px;background: linear-gradient(90deg, #03DAC6, #00BFA5);transition: width 0.4s ease-out;}
            .status-chip {display: inline-flex;align-items: center;padding: 4px 10px;border-radius: 999px;background: rgba(3, 218, 198, 0.12);color: var(--accent-color);font-size: 0.8rem;font-weight: 500;}
            .status-chip-dot {width: 8px;height: 8px;border-radius: 50%;background: var(--accent-color);margin-right: 6px;}
            .update-date {font-size: 0.8rem;color: var(--text-secondary);margin-top: 4px;}
            .intervention-details {margin-top: 10px;font-size: 0.9rem;}
            .intervention-details .detail-row {margin-bottom: 6px;}
            .intervention-details .detail-label {color: var(--text-secondary);font-size: 0.85rem;}
            .intervention-details .detail-value {font-weight: 400;}
            .intervention-details .notes {margin-top: 8px;padding: 10px;border-radius: 8px;background: #252525;border: 1px solid #333;font-size: 0.85rem;color: var(--text-secondary);}
            .documents-list {display: flex;flex-direction: column;gap: 10px;margin-top: 6px;}
            .doc-item {display: flex;align-items: center;justify-content: space-between;padding: 10px 12px;border-radius: 10px;background: #252525;border: 1px solid #333;transition: background 0.2s, transform 0.1s;}
            .doc-item:hover {background: #2C2C2C;transform: translateY(-1px);}
            .doc-info {display: flex;align-items: center;gap: 10px;}
            .doc-icon {width: 28px;height: 28px;border-radius: 6px;background: linear-gradient(135deg, #03DAC6, #00BFA5);display: flex;align-items: center;justify-content: center;font-size: 1rem;color: #000;font-weight: 700;}
            .doc-text-title {font-size: 0.95rem;}
            .doc-text-sub {font-size: 0.8rem;color: var(--text-secondary);}
            .doc-link a {font-size: 0.85rem;color: var(--accent-color);text-decoration: none;font-weight: 500;}
            .doc-link a:hover {text-decoration: underline;}
            @media (max-width: 600px) {
                header {flex-direction: column;align-items: flex-start;}
            }

            .blur-sensitive {filter: blur(6px);user-select: none;transition: filter 0.3s ease;}
            .unlock-card {background: var(--card-color);border: 1px solid var(--divider-color);border-radius: var(--radius);padding: 20px;margin-bottom: 20px;box-shadow: var(--shadow-soft);}
            .unlock-card h2 {margin: 0 0 10px;font-size: 1.2rem;color: var(--accent-color);}
            .unlock-card input {width: 100%;padding: 10px;border-radius: 8px;border: 1px solid #333;background: #1A1A1A;color: white;margin-top: 10px;}
            .unlock-card button {margin-top: 12px;width: 100%;padding: 10px;border-radius: 8px;background: var(--accent-color);color: black;font-weight: 600;border: none;cursor: pointer;}
            .unlock-card button:hover {background: #00bfa5;}
        </style>
    </head>
    <body>
        <div class="page-wrapper">
            <header>
                <img src="SOURCES/icon.png"
                     alt="Logo ROYJohanInfo" class="logo">
                <div class="title-block">
                    <h1>ROYJohanInfo</h1>
                    <span>Suivi de votre intervention &amp; documents</span>
                </div>
            </header>
            <div class="grid">
			<form method="POST" class="unlock-card" id="unlockCard">
                <h2>Déverrouiller les informations</h2>
                <p>Pour afficher vos données personnelles, veuillez saisir votre numéro de portable.</p>
                <input type="text" name="phone" id="unlockInput" placeholder="Entrez votre numéro">
                <input type="hidden" name="id" id="unlockInput" placeholder="<?php if($_SESSION['code']){echo $_SESSION['code'];}?>">
                <input type="submit" name="unlock" value="Déverrouiller">
            </form>
                <section class="card">
                    <h2>
                        Informations personnelles
                        <span class="label">Client</span>
                    </h2>
                    <div class="info-row">
                        <span class="label">Nom</span>
                        <span class="value sensitive blur-sensitive" data-field="nom">
                            <?php echo $raw['nom']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Prénom</span>
                        <span class="value" data-field="prenom">
                            <?php echo $raw['prenom']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Adresse</span>
                        <span class="value sensitive blur-sensitive" style="text-align:right;" data-field="adresse">
                            <?php echo $raw['adresse']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Téléphone</span>
                        <span class="value sensitive blur-sensitive" data-field="telephone">
                            <?php echo $raw['telephone']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value sensitive blur-sensitive" data-field="email">
                            <?php echo $raw['email']; ?>
                        </span>
                    </div>
                    <div class="divider"></div>
                    <div class="wallet-link">
                        <a href="<?php echo $raw['wallet']; ?>"
                           class="sensitive blur-sensitive">
                            <img src="https://www.gstatic.com/instantbuy/svg/dark_gpay.svg" alt="Add to Google Wallet">
                            Ajouter la carte de fidélité
                        </a>
                    </div>
                </section>

                <section class="card">
                    <h2>
                        Informations de l'appareil
                        <span class="label">Appareil</span>
                    </h2>
                    <div class="info-row">
                        <span class="label">Type d'appareil</span>
                        <span class="value"><?php echo htmlspecialchars($raw['type']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Marque</span>
                        <span class="value"><?php echo htmlspecialchars($raw['marque']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Modèle</span>
                        <span class="value"><?php echo htmlspecialchars($raw['modele']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Série</span>
                        <span class="value sensitive blur-sensitive" data-field="serie">
                            <?php echo $raw['serie']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Série 2</span>
                        <span class="value sensitive blur-sensitive" data-field="serie2">
                            <?php echo $raw['serie2']; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Couleur</span>
                        <span class="value"><?php echo htmlspecialchars($raw['couleur']); ?></span>
                    </div>
                </section>

                <section class="card">
                    <h2>
                        Mon intervention
                        <span class="label">Suivi</span>
                    </h2>
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Avancement</span>
                            <span><?php echo (int) $raw['avancement']; ?>%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" id="progressBar"></div>
                        </div>
                    </div>
                    <div>
                        <span class="status-chip">
                            <span class="status-chip-dot"></span>
                            <?php echo htmlspecialchars($raw['statut']); ?>
                        </span>
                        <div class="update-date">
                            Dernière mise à jour : <?php echo htmlspecialchars($raw['miseAJours']); ?>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="intervention-details">
                        <div class="detail-row">
                            <div class="detail-label">Type d'intervention</div>
                            <div class="detail-value"><?php echo htmlspecialchars($raw['typeIntervention']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status détaillé</div>
                            <div class="detail-value"><?php echo htmlspecialchars($raw['statut']); ?></div>
                        </div>
                        <div class="notes">
                            <strong>Notes du technicien :</strong><br>
                            <?php echo nl2br(htmlspecialchars($raw['notes'])); ?>
                        </div>
                    </div>
                </section>
                <section class="card">
                    <h2>
                        Mes documents
                        <span class="label">PDF</span>
                    </h2>
                    <div class="documents-list">
						<?php foreach ($docs as $file): ?>
						<?php
							$base = pathinfo($file, PATHINFO_FILENAME);
							$icon = strtoupper($base[0]);
							$url  = "SOURCES/Docs/" . intval($_SESSION['id']) . "/" . rawurlencode($file);
						?>
						<div class="doc-item">
							<div class="doc-info">
								<div class="doc-icon"><?= $icon ?></div>
							<div>
							<div class="doc-text-title sensitive blur-sensitive"
								data-field="<?= htmlspecialchars($base) ?>">
								<?= htmlspecialchars($base) ?>
							</div>
							<div class="doc-text-sub">Document disponible</div>
						</div>
					</div>
					<div class="doc-link">
						<a href="javascript:void(0);"
						class="open-doc"
						data-url="<?= htmlspecialchars($url) ?>">
							Ouvrir
						</a>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
            </section>
            </div>
        </div>
		<div id="toast-container"></div>
        <script>
		function showToast(message, type = "error") {
				const container = document.getElementById('toast-container');
				const toast = document.createElement('div');
				toast.className = 'toast';

				let icon = "ℹ️";
				let border = "#ffa500";

				if (type === "error") {
					icon = "❌";
					border = "#ff3b3b";
				} else if (type === "success") {
					icon = "✔️";
					border = "#2ecc71";
				}

				toast.style.borderLeft = `4px solid ${border}`;

				toast.innerHTML = `
					<div class="toast-icon">${icon}</div>
					<div>${message}</div>
					<div class="toast-close" onclick="closeToast(this.parentElement)">✖</div>
				`;

				container.appendChild(toast);
				setTimeout(() => closeToast(toast), 5000);
			}

			function closeToast(toast) {
				toast.style.animation = 'toast-out 0.25s forwards';
				setTimeout(() => toast.remove(), 250);
			}

			<?php if (!empty($errorMessage) && is_array($errorMessage)): ?>
				<?php foreach ($errorMessage as $err): ?>
					showToast(
						"<?= htmlspecialchars($err->content, ENT_QUOTES, 'UTF-8') ?>",
						"<?= $err->level === ErrorLevel::ERROR ? "error" : ($err->level === ErrorLevel::SUCCESS ? "success" : "info") ?>"
					);
				<?php endforeach; ?>
				<?php Errors::clear(); ?>
			<?php endif; ?>
			document.querySelectorAll('.open-doc').forEach(link => {
				link.addEventListener('click', () => {
					const stillBlurred = document.querySelector('.sensitive.blur-sensitive');
					if (stillBlurred) {
						showToast("Impossible d’ouvrir un document tant que les données sensibles sont protégées.", "error");
						return;
					}
					const url = link.dataset.url;
					window.open(url, "_blank");
				});
			});
            document.addEventListener("DOMContentLoaded", function () {
                var progress = <?php echo (int)$raw['avancement']; ?>;
                var bar = document.getElementById("progressBar");
                bar.style.width = progress + "%";
            });
        </script>
		<?php if ($_SESSION['Unlock']): ?>
<script>
document.querySelectorAll(".sensitive").forEach(el => {
    el.classList.remove("blur-sensitive");
});
</script>
<?php endif; ?>
    </body>
</html>
