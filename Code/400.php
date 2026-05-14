<?php
	require_once("SOURCES/function.php");
    $Database =  new PDO("mysql:host=localhost;dbname=cms;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES => false]);
	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		$user = trim($_POST["username"] ?? "");
		$pass = trim($_POST["password"] ?? "");
		

		if (!admin::connect($Database,$user, $pass)) {
			$error = "Identifiants incorrects.";
		}
	}
	$errorMessage = Errors::get(ErrorLevel::ERROR);
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<title>ROYJohanInfo - Connexion admin</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
		<link href="SOURCES/style.css" rel="stylesheet">
	</head>
	<body>
		<div class="page-wrapper">
			<div class="content-center">
				<header>
					<img src="SOURCES/icon.png"
						 alt="Logo ROYJohanInfo" class="logo">
					<div class="title-block">
						<h1>ROYJohanInfo</h1>
						<span>Accès au panneau administrateur</span>
					</div>
				</header>
				<section class="login-card">
					<h2 class="login-title">Connexion administrateur</h2>
					<p class="login-subtitle">Veuillez saisir vos identifiants pour continuer.</p>

					<form method="POST">
						<div class="input-group">
							<label for="username">Nom d'utilisateur</label>
							<input id="username" type="text" name="username" required>
						</div>

						<div class="input-group">
							<label for="password">Mot de passe</label>
							<input id="password" type="password" name="password" required>
						</div>

						<button class="btn-login" type="submit">Se connecter</button>
					</form>

					<div class="footer">
						Accès réservé — ROYJohanInfo
					</div>
				</section>
			</div>
			<div id="toast-container"></div>
		</div>
		
		<script>
			function showToast(message) {
				const container = document.getElementById('toast-container');
				const toast = document.createElement('div');
				toast.className = 'toast';
				toast.innerHTML = `<div class="toast-icon">⚠️</div><div>${message}</div><div class="toast-close" onclick="closeToast(this.parentElement)">✖</div>`;
				container.appendChild(toast);
				setTimeout(() => closeToast(toast), 5000);
			}
			function closeToast(toast) {
				toast.style.animation = 'toast-out 0.25s forwards';
				setTimeout(() => toast.remove(), 250);
			}
			<?php if (!empty($errorMessage) && is_array($errorMessage)): ?>
				<?php foreach ($errorMessage as $err): ?>
					showToast("<?= htmlspecialchars($err->content, ENT_QUOTES, 'UTF-8') ?>");
				<?php endforeach; ?>
				 <?php Errors::clear(); ?>
			<?php endif; ?>
		</script>
	</body>
</html>
