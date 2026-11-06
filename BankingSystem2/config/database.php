<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

define('BASE_URL', '/BankingSystem2');   // URL path to the project folder
const DB_HOST = 'localhost';
const DB_NAME = 'banking';              // must match the database you imported
const DB_USER = 'root';                 // your MySQL username
const DB_PASS = '';                     // your MySQL password
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed. Check config/database.php');
}

/* ---------- helpers ---------- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($v): string { return '$' . number_format((float)$v, 2); }
function redirect(string $path): void { header('Location: ' . BASE_URL . '/' . ltrim($path, '/')); exit; }

function flash(?string $msg = null, string $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid request token.'); }
}

function require_login(): void { if (empty($_SESSION['user'])) redirect('login.php'); }
function require_admin(): void {
    require_login();
    if ($_SESSION['user']['role'] !== 'admin') { http_response_code(403); exit('Forbidden'); }
}
function require_customer(): void {
    require_login();
    if ($_SESSION['user']['role'] !== 'customer') redirect('dashboard.php');
}

function my_account(PDO $pdo): ?array {
    $st = $pdo->prepare('SELECT * FROM accounts WHERE user_id = ?');
    $st->execute([$_SESSION['user']['id']]);
    return $st->fetch() ?: null;
}

function new_account_number(PDO $pdo): string {
    do {
        $n = (string)random_int(1000000000, 9999999999);
        $st = $pdo->prepare('SELECT 1 FROM accounts WHERE account_number = ?'); $st->execute([$n]);
    } while ($st->fetch());
    return $n;
}

/**
 * Deposit / withdraw / transfer. Returns null on success or an error message.
 * Uses a DB transaction with row locks so balances can never go inconsistent.
 */
function process_transaction(PDO $pdo, int $userId, string $type, string $amount, string $toNumber = '', string $desc = ''): ?string {
    if (!preg_match('/^\d{1,13}(\.\d{1,2})?$/', $amount) || (float)$amount <= 0) return 'Enter a valid amount (max 2 decimals).';
    $desc = mb_substr(trim($desc), 0, 255);

    try {
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT id FROM accounts WHERE user_id = ?'); $st->execute([$userId]);
        $fromId = $st->fetchColumn();
        if (!$fromId) { $pdo->rollBack(); return 'Account not found.'; }

        $toId = null;
        if ($type === 'transfer') {
            $st = $pdo->prepare('SELECT id FROM accounts WHERE account_number = ?'); $st->execute([$toNumber]);
            $toId = $st->fetchColumn();
            if (!$toId)            { $pdo->rollBack(); return 'Recipient account not found.'; }
            if ($toId == $fromId)  { $pdo->rollBack(); return 'You cannot transfer to your own account.'; }
        }

        // Lock rows in a consistent order (prevents deadlocks)
        $ids = array_values(array_filter([$fromId, $toId])); sort($ids);
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $st  = $pdo->prepare("SELECT * FROM accounts WHERE id IN ($in) ORDER BY id FOR UPDATE");
        $st->execute($ids);
        $rows = []; foreach ($st->fetchAll() as $r) $rows[$r['id']] = $r;
        $from = $rows[$fromId]; $to = $toId ? $rows[$toId] : null;

        if ($from['status'] !== 'active')      { $pdo->rollBack(); return 'Your account is frozen. Contact support.'; }
        if ($to && $to['status'] !== 'active') { $pdo->rollBack(); return 'Recipient account is unavailable.'; }

        $upd = $pdo->prepare('UPDATE accounts SET balance = balance + ? WHERE id = ?');
        $dec = $pdo->prepare('UPDATE accounts SET balance = balance - ? WHERE id = ? AND balance >= ?');
        $log = $pdo->prepare('INSERT INTO transactions (account_id,type,amount,balance_after,related_account_id,description)
                              VALUES (?,?,?,(SELECT balance FROM accounts WHERE id = ?),?,?)');

        if ($type === 'deposit') {
            $upd->execute([$amount, $fromId]);
            $log->execute([$fromId, 'deposit', $amount, $fromId, null, $desc]);
        } else {
            $dec->execute([$amount, $fromId, $amount]);
            if ($dec->rowCount() !== 1) { $pdo->rollBack(); return 'Insufficient funds.'; }
            if ($type === 'withdraw') {
                $log->execute([$fromId, 'withdraw', $amount, $fromId, null, $desc]);
            } else {
                $upd->execute([$amount, $toId]);
                $log->execute([$fromId, 'transfer_out', $amount, $fromId, $toId, $desc]);
                $log->execute([$toId, 'transfer_in', $amount, $toId, $fromId, $desc]);
            }
        }
        $pdo->commit();
        return null;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($e->getMessage());
        return 'Transaction failed. Please try again.';
    }
}
