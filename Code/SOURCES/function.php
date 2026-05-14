<?php
declare(strict_types=1);
session_start();

enum ErrorLevel: int {
    case ALL     = 0;
    case INFO    = 1;
    case WARNING = 2;
    case ERROR   = 3;
	case SUCCESS = 9;
}

enum TechLevel: int {
    case COMPTA = 0;
    case TECH   = 1;
    case COMM   = 2;
    case ADMIN  = 3;
}

class ErrorItem {
    public function __construct(
        public readonly string $content,
        public readonly ErrorLevel $level,
        public readonly \DateTimeImmutable $date
    ) {}
}

class Errors {

    private static array $errors = [];

    public static function add(string $content, ErrorLevel $level): bool {
        $content = trim(strip_tags($content));
        if (strlen($content) < 5) {return false;}
        if (class_exists(\Transliterator::class)) {$trans = \Transliterator::create('Any-Latin; Latin-ASCII');if ($trans !== null) {$content = $trans->transliterate($content);}}
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        self::$errors[] = new ErrorItem(content: $content,level: $level,date: new \DateTimeImmutable());
        self::sort();
        return true;
    }
    public static function get(?ErrorLevel $level = null): array {
        self::load();
		if ($level === null || $level === ErrorLevel::ALL) {
            return self::$errors;
        }
        return array_values(
            array_filter(
                self::$errors,
                fn (ErrorItem $e) => $e->level === $level
            )
        );
    }
    public static function clear(): void {
        self::$errors = [];
		if (isset($_SESSION['errors'])) {
			$_SESSION['errors'] = [];
		}
    }
    private static function sort(): void {
        usort(
            self::$errors,
            fn (ErrorItem $a, ErrorItem $b) =>
                $a->level->value <=> $b->level->value
        );
    }
	public static function save(string $content, string $level = "error"): void{
		$content = trim(strip_tags($content));
		if (strlen($content) < 2) return;
		$level = strtolower($level);
		if (!in_array($level, ["error", "info", "success"])) {
			$level = "info";
		}
		if (!isset($_SESSION['errors'])) {
			$_SESSION['errors'] = [];
		}
		$_SESSION['errors'][] = [
			"content" => $content,
			"level"   => $level,
			"time"    => time()
		];
	}
	public static function load(): void{
		if (!isset($_SESSION['errors'])) {
			$_SESSION['errors'] = [];
		}
		self::$errors = array_merge(self::$errors, $_SESSION['errors']);
	}
}

class admin {

    public static function connect(PDO $Database, string $username, string $password): bool {
        $stmt = $Database->prepare(" SELECT id, username, password FROM suivi_user WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {Errors::add("Utilisateur introuvable", ErrorLevel::ERROR);return false;}
        if (!password_verify($password, $user["password"])) {Errors::add("Mot de passe incorrect", ErrorLevel::ERROR);return false;}
        $privateKey = file_get_contents(__DIR__ . "/admin.key");
        if (!$privateKey) {Errors::add("Clé privée introuvable", ErrorLevel::ERROR);return false;}
        $payload = ["username" => $user["username"],"role" => "admin","iat" => time(),"exp" => time() + 3600];
        $header = ["alg" => "RS256","typ" => "JWT"];
        $base64UrlHeader  = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        openssl_sign("$base64UrlHeader.$base64UrlPayload",$signature,$privateKey,OPENSSL_ALGO_SHA256);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        session_regenerate_id(true);
        $_SESSION["admin"] = true;
        $_SESSION["admin_uid"] = (int)$user["id"];
        $_SESSION["admin_username"] = $user["username"];
        $_SESSION["admin_token"] = $jwt;
		Errors::save("Connexion réussie", ErrorLevel::SUCCESS);
		header("Location: 409.php");
		exit;
        return true;
    }
    public static function checkToken(): bool {
        if (empty($_SESSION["admin_token"])) {Errors::add("Token manquant", ErrorLevel::ERROR);return false;}
        $jwt = $_SESSION["admin_token"];
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {Errors::add("Format JWT invalide", ErrorLevel::ERROR);return false;}
        [$headerB64, $payloadB64, $signatureB64] = $parts;
        $header  = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
        $signature = base64_decode(strtr($signatureB64, '-_', '+/'));
        if (!$header || !$payload) {Errors::add("JWT illisible", ErrorLevel::ERROR);return false;}
        if (($header["alg"] ?? null) !== "RS256") {Errors::add("Algorithme JWT invalide", ErrorLevel::ERROR);return false;}
        $publicKey = file_get_contents(__DIR__ . "/admin.pub");
        if (!$publicKey) {Errors::add("Clé publique introuvable", ErrorLevel::ERROR);return false;}
        $dataToVerify = "$headerB64.$payloadB64";
        $isValid = openssl_verify($dataToVerify,$signature,$publicKey,OPENSSL_ALGO_SHA256);
        if ($isValid !== 1) {Errors::add("Signature JWT invalide", ErrorLevel::ERROR);return false;}
        if (!isset($payload["exp"]) || time() > $payload["exp"]) {Errors::add("Token expiré", ErrorLevel::ERROR);return false;}
        if (($payload["role"] ?? null) !== "admin") {Errors::add("Accès refusé : rôle invalide", ErrorLevel::ERROR);return false;}
        if (!isset($_SESSION["admin_username"]) || $payload["username"] !== $_SESSION["admin_username"]) {Errors::add("Utilisateur non reconnu", ErrorLevel::ERROR);return false;}
        return true;
    }
	public static function createUser(PDO $db, string $username, string $password): bool {
		$username = trim($username);
		if (strlen($username) < 3 || strlen($password) < 6) {Errors::add("Identifiants trop courts", ErrorLevel::ERROR);return false;}
		$check = $db->prepare("SELECT id FROM suivi_user WHERE username = ?");
		$check->execute([$username]);
		if ($check->fetch()) {Errors::add("Nom d'utilisateur déjà utilisé", ErrorLevel::ERROR);return false;}
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $db->prepare("INSERT INTO suivi_user (username, password)VALUES (?, ?)");
		if(!$stmt->execute([$username, $hash])){return false;}
		Errors::add("User cée avec l'username : ".$username, ErrorLevel::SUCCESS);
		return true;
	}


}
class intervention {
    public static function get(PDO $Database, string $telephone, ?string $intervention = null): ?array {
        if ($intervention !== null) {
            $sql = "SELECT id,nom, miseAJours, prenom, adresse, telephone, email, wallet, marque, modele, serie, serie2, couleur, type, avancement, statut, statutComplet, miseAJours, typeIntervention, notes FROM suivi_intervention WHERE id = ? LIMIT 1";
            $stmt = $Database->prepare($sql);
            $stmt->execute([$intervention]);
        }
        else {
            $sql = "SELECT id,nom, prenom, miseAJours, adresse, telephone, email, wallet, marque, modele, serie, serie2, couleur, type, avancement, statut, statutComplet, miseAJours, typeIntervention, notes FROM suivi_intervention WHERE telephone = ? ORDER BY miseAJours DESC LIMIT 1 ";
            $stmt = $Database->prepare($sql);
            $stmt->execute([$telephone]);
        }
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }
	public static function delFile($id, $file) {
		if (!admin::checkToken()) {Errors::add("Vous n'etes pas administrateur", ErrorLevel::ERROR);header("Location: 400.php");exit;return null;}
		if (!is_numeric($id)) {Errors::add("ID invalide", ErrorLevel::ERROR);return false;}
		$file = basename($file);
		$folder = __DIR__ . "/Docs/" . $id;
		$path = $folder . "/" . $file;
		if (!is_dir($folder)) {Errors::add("Dossier introuvable", ErrorLevel::ERROR);return false;}
		if (!file_exists($path)) {Errors::add("Fichier introuvable", ErrorLevel::ERROR);return false;}
		if (!unlink($path)) {Errors::add("Impossible de supprimer le fichier", ErrorLevel::ERROR);return false;}
		Errors::add("Fichier supprimé", ErrorLevel::SUCCESS);
		return true;
	}
    public static function save(PDO $db, array $data): int {

    // Vérification admin
    if (!admin::checkToken()) {
        Errors::add("Vous n'êtes pas administrateur", ErrorLevel::ERROR);
        header("Location: 400.php");
        exit;
    }

    // ID ou null
    $id = (!empty($data['id']) && is_numeric($data['id'])) ? intval($data['id']) : null;

    // Champs autorisés
    $fields = [
        "nom", "prenom", "adresse", "telephone", "email", "wallet",
        "marque", "modele", "serie", "serie2", "couleur", "type",
        "avancement", "statut", "statutComplet", "typeIntervention", "notes"
    ];

    // Construction du tableau de valeurs
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = $data[$f] ?? null;
    }

    // -----------------------------------
    // UPDATE
    // -----------------------------------
    if ($id !== null) {

        $sql = "
            UPDATE suivi_intervention SET
                nom = :nom,
                prenom = :prenom,
                adresse = :adresse,
                telephone = :telephone,
                email = :email,
                wallet = :wallet,
                marque = :marque,
                modele = :modele,
                serie = :serie,
                serie2 = :serie2,
                couleur = :couleur,
                type = :type,
                avancement = :avancement,
                statut = :statut,
                statutComplet = :statutComplet,
                typeIntervention = :typeIntervention,
                notes = :notes,
                miseAJours = NOW()
            WHERE id = :id
        ";

        $stmt = $db->prepare($sql);
        $values['id'] = $id;

        if ($stmt->execute($values)) {
            Errors::add("Intervention mise à jour (ID : $id)", ErrorLevel::SUCCESS);
            return $id;
        }

        Errors::add("Erreur lors de la mise à jour", ErrorLevel::ERROR);
        return 0;
    }

    // -----------------------------------
    // INSERT
    // -----------------------------------
    $sql = "
        INSERT INTO suivi_intervention (
            nom, prenom, adresse, telephone, email, wallet,
            marque, modele, serie, serie2, couleur, type,
            avancement, statut, statutComplet, typeIntervention, notes, miseAJours
        ) VALUES (
            :nom, :prenom, :adresse, :telephone, :email, :wallet,
            :marque, :modele, :serie, :serie2, :couleur, :type,
            :avancement, :statut, :statutComplet, :typeIntervention, :notes, NOW()
        )
    ";

    $stmt = $db->prepare($sql);

    if ($stmt->execute($values)) {
        $id = (int)$db->lastInsertId();
        Errors::add("Intervention créée (ID : $id)", ErrorLevel::SUCCESS);
        return $id;
    }

    Errors::add("Erreur lors de la création de l'intervention", ErrorLevel::ERROR);
    return 0;
}

}
class ShortID {

    private static $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    private static $base = 62;

    // Offset choisi pour garantir au moins 4 caractères (62^3 = 238328)
    private static $offset = 238328;

    public static function encode(int $id): string {
        // On ajoute l’offset
        $n = $id + self::$offset;

        if ($n === 0) {
            return self::$alphabet[0];
        }

        $result = "";
        while ($n > 0) {
            $result = self::$alphabet[$n % self::$base] . $result;
            $n = intdiv($n, self::$base);
        }

        return $result;
    }

    public static function decode(string $str): int {
        $n = 0;
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $pos = strpos(self::$alphabet, $str[$i]);
            if ($pos === false) {
                throw new InvalidArgumentException("Caractère invalide dans l'ID court");
            }
            $n = $n * self::$base + $pos;
        }

        // On retire l’offset
        $id = $n - self::$offset;
        if ($id < 0) {
            throw new RuntimeException("ID décodé invalide (offset incorrect ?)");
        }

        return $id;
    }
}
