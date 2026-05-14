<?php
declare(strict_types=1);
session_start();

enum ErrorLevel: int {
    case ALL     = 0;
    case INFO    = 1;
    case WARNING = 2;
    case ERROR   = 3;
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
    }
    private static function sort(): void {
        usort(
            self::$errors,
            fn (ErrorItem $a, ErrorItem $b) =>
                $a->level->value <=> $b->level->value
        );
    }
}

class admin {

    public static function connect(PDO $Database, string $username, string $password): bool {
        $stmt = $Database->prepare(" SELECT id, username, password FROM suivi_user WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {Errors::add("Utilisateur introuvable", ErrorLevel::ERROR);return false;}
        if (!password_verify($password, $user["password"])) {Errors::add("Mot de passe incorrect", ErrorLevel::ERROR);return false;}
        $privateKey = file_get_contents(__DIR__ . "/SOURCES/admin.key");
        if (!$privateKey) {Errors::add("Clé privée introuvable", ErrorLevel::ERROR);return false;}
        $payload = ["username" => $user["username"],"role"     => "admin","iat"      => time(),"exp"      => time() + 3600 // 1h];
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
        $publicKey = file_get_contents(__DIR__ . "/SOURCES/admin.pub");
        if (!$publicKey) {Errors::add("Clé publique introuvable", ErrorLevel::ERROR);return false;}
        $dataToVerify = "$headerB64.$payloadB64";
        $isValid = openssl_verify($dataToVerify,$signature,$publicKey,OPENSSL_ALGO_SHA256);
        if ($isValid !== 1) {Errors::add("Signature JWT invalide", ErrorLevel::ERROR);return false;}
        if (!isset($payload["exp"]) || time() > $payload["exp"]) {Errors::add("Token expiré", ErrorLevel::ERROR);return false;}
        if (($payload["role"] ?? null) !== "admin") {Errors::add("Accès refusé : rôle invalide", ErrorLevel::ERROR);return false;}
        if (!isset($_SESSION["admin_username"]) || $payload["username"] !== $_SESSION["admin_username"]) {Errors::add("Utilisateur non reconnu", ErrorLevel::ERROR);return false;}
        return true;
    }
}

