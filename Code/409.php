<?php
	require_once("SOURCES/function.php");
    $Database =  new PDO("mysql:host=localhost;dbname=cms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES => false]);
	if(!admin::checkToken()){
		header("Location: 400.php");
		exit;
	}
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['supprimer'])) {
		$id = trim($_POST["id"] ?? "");
		$file = trim($_POST["file"] ?? "");
		if(!$id || !$file){Error::add("Id ou file non remplis",ErrorLevel::ERROR);}
		intervention::delFile($id,$file);
		$raw = intervention::get($Database,"",$id);
	}
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save'])) {
		$id = trim($_POST["id"] ?? "");
		$id = intervention::save($Database,$_POST);
		$raw = intervention::get($Database,"",$id);
	}
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search'])) {
		$code = trim($_POST["id"] ?? "");
		if(!$code){Errors::add("Recherche non remplis",ErrorLevel::ERROR);$raw=null;}
		$code = str_replace(" ","",str_replace(".","",$code));
		if(strlen($code)==10){
			$raw = intervention::get($Database,$code);
		}
		else{
			$raw = intervention::get($Database,"",$code);
		}
	}
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['uploadDoc']) && !empty($_POST['id']) && !empty($_FILES['document'])) {
		$id = intval($_POST['id']);
		$file = $_FILES['document'];
		if ($file['error'] !== UPLOAD_ERR_OK) {
			Errors::add("Erreur lors de l'upload", ErrorLevel::ERROR);
		}
		else {
			$filename = basename($file['name']);
			$folder = __DIR__ . "/SOURCES/Docs/" . $id;
			if (!is_dir($folder)) {
				mkdir($folder, 0775, true);
			}
			$target = $folder . "/" . $filename;
			if (move_uploaded_file($file['tmp_name'], $target)) {Errors::add("Document uploadé avec succès", ErrorLevel::SUCCESS);}
			else {Errors::add("Impossible de sauvegarder le fichier", ErrorLevel::ERROR);}
			$raw = intervention::get($Database,"",$id);
		}
	}
	$errorMessage = Errors::get(ErrorLevel::ERROR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - ROYJohanInfo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
	<link href="SOURCES/style.css" rel="stylesheet">
</head>
<body>
<div class="page-wrapper">
    <header>
        <img src="SOURCES/icon.png" alt="Logo">
        <div>
            <h1>Dashboard Administrateur</h1>
            <span style="color:var(--text-secondary)">Gestion complète des interventions</span>
        </div>
    </header>
    <div class="grid-4">
        <section class="card full">
            <form method="POST" class="card-content search">
                <h2>Rechercher une intervention</h2>
                <div class="input-group">
                    <label>Recherche</label>
                    <input class="search-input" name="id" placeholder="Id ou téléphone">
                </div>
                <div class="btn-row">
                    <input type="submit" name="search" value="Rechercher" class="btn">
                </div>
            </form>
        </section>
		<form id="interventionForm" style="display: none;" method="POST"></form>
		<section class="card">
			<div class="card-content">
				<h2>Client<?= isset($raw['id']) ? " : ".ShortID::encode((int) htmlspecialchars($raw['id'], ENT_QUOTES, 'UTF-8')) : '' ?></h2>
				<input type="hidden" name="id" form="interventionForm" value="<?= isset($raw['id']) ? htmlspecialchars($raw['id'], ENT_QUOTES, 'UTF-8') : '' ?>">
				<div class="input-group"><label>Nom</label><input type="text" name="nom" form="interventionForm" value="<?= isset($raw['nom']) ? htmlspecialchars($raw['nom'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Prénom</label><input type="text" name="prenom" form="interventionForm" value="<?= isset($raw['prenom']) ? htmlspecialchars($raw['prenom'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Adresse</label><input type="text" name="adresse" form="interventionForm" value="<?= isset($raw['adresse']) ? htmlspecialchars($raw['adresse'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Téléphone</label><input type="text" name="telephone" form="interventionForm" value="<?= isset($raw['telephone']) ? htmlspecialchars($raw['telephone'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Email</label><input type="text" name="email" form="interventionForm" value="<?= isset($raw['email']) ? htmlspecialchars($raw['email'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Wallet</label><input type="text" name="wallet" form="interventionForm" value="<?= isset($raw['wallet']) ? htmlspecialchars($raw['wallet'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
			</div>
		</section>
		<section class="card">
			<div class="card-content">
				<h2>Appareil</h2>

				<div class="input-group"><label>Type d'appareil</label><input name="type" type="text" form="interventionForm" value="<?= isset($raw['type']) ? htmlspecialchars($raw['type'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Marque</label><input type="text" name="marque" form="interventionForm" value="<?= isset($raw['marque']) ? htmlspecialchars($raw['marque'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Modèle</label><input type="text" name="modele" form="interventionForm" value="<?= isset($raw['modele']) ? htmlspecialchars($raw['modele'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Couleur</label><input type="text" name="couleur" form="interventionForm" value="<?= isset($raw['couleur']) ? htmlspecialchars($raw['couleur'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Série</label><input type="text" name="serie" form="interventionForm" value="<?= isset($raw['serie']) ? htmlspecialchars($raw['serie'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
				<div class="input-group"><label>Série 2</label><input type="text" name="serie2" form="interventionForm" value="<?= isset($raw['serie2']) ? htmlspecialchars($raw['serie2'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>
			</div>
		</section>
		<section class="card">
			<div class="card-content">
				<h2>Intervention</h2>

				<div class="input-group"><label>Type d'intervention</label><input type="text" name="typeIntervention" form="interventionForm" value="<?= isset($raw['typeIntervention']) ? htmlspecialchars($raw['typeIntervention'], ENT_QUOTES, 'UTF-8') : '' ?>"></div>

				<div class="input-group">
					<label>Statut</label>
					<select form="interventionForm"  name="statut">
						<option>Attente réponse devis</option>
						<option>En cours</option>
						<option>Terminé</option>
					</select>
				</div>

				<div class="input-group">
					<label>Avancement (%)</label>
					<input type="number" name="avancement" form="interventionForm" min="0" max="100" value="<?= isset($raw['avancement']) ? htmlspecialchars($raw['avancement'], ENT_QUOTES, 'UTF-8') : '' ?>">
				</div>

				<div class="input-group">
					<label>Notes technicien</label>
					<textarea rows="4" name="notes" form="interventionForm" ><?= isset($raw['notes']) ? htmlspecialchars($raw['notes'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
				</div>

				<div class="btn-row">
					<input type="submit" name="save" value="Enregistrer" form="interventionForm" class="btn">
				</div>
			</div>
		</section>
        <section class="card">
            <div class="card-content">
                <h2>Documents</h2>

                <?php if (!empty($raw['id'])): ?>
					<?php
						$folder = __DIR__ . "/SOURCES/Docs/" . $raw['id'];
						$files = [];
						if (is_dir($folder)) {
							$scan = scandir($folder);
							foreach ($scan as $file) {
								if ($file === "." || $file === "..") continue;
								if (is_file($folder . "/" . $file)) {
									$files[] = $file;
								}
							}
						}
					?>
					<?php if (!empty($files)): ?>
						<div id="docList">
							<?php foreach ($files as $file): ?>
								<form method="POST" class="doc-item">
									<span><?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?></span>
									<input type="hidden" name="file" value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="id" value="<?= isset($raw['id']) ? htmlspecialchars($raw['id'], ENT_QUOTES, 'UTF-8') : '' ?>">
									<input type="submit" name="supprimer" value="Supprimer" data-file="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
								</form>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<p>Aucun document trouvé pour cette intervention.</p>
					<?php endif; ?>
				<?php endif; ?>
                <?php if (!empty($raw['id'])): ?>
					<form method="POST" enctype="multipart/form-data">
						<input type="hidden" name="id" value="<?= htmlspecialchars($raw['id']) ?>">
						<div class="input-group" style="margin-top:16px;">
							<label>Ajouter un document</label>
							<input type="file" name="document" required>
						</div>
						<div class="btn-row">
							<button class="btn" name="uploadDoc">Uploader</button>
						</div>
					</form>
				<?php endif; ?>
            </div>
        </section>
    </div>
		<div id="toast-container"></div>
		</div>
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
</script>


</body>
</html>
