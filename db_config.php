<?php
// ============================================================
//  HILDA'S POULTRY FARM — db_config.php
//  Place this file OUTSIDE your web root for security.
//  Update DB_USER and DB_PASS before deploying.
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'hildas_poultry_farm');
define('DB_USER',    'root');     // ← CHANGE THIS
define('DB_PASS',    ''); // ← CHANGE THIS
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

function loginUser(string $username, string $password): array|false {
    $stmt = getDB()->prepare(
        "SELECT * FROM users WHERE username = :u AND is_active = 1 LIMIT 1"
    );
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        return $user;
    }
    return false;
}

function getAllFlocks(): array {
    try {
        return getDB()->query("SELECT * FROM v_flock_overview ORDER BY flock_name")->fetchAll();
    } catch (Exception $e) { return []; }
}

function recordEggs(int $flockId, int $userId, string $date,
                    int $collected, int $cracked = 0, int $dirty = 0, string $notes = ''): bool {
    $stmt = getDB()->prepare("INSERT INTO egg_production
        (flock_id, recorded_by, record_date, eggs_collected, cracked_eggs, dirty_eggs, notes)
        VALUES (:flock,:user,:date,:collected,:cracked,:dirty,:notes)");
    return $stmt->execute([':flock'=>$flockId,':user'=>$userId,':date'=>$date,
                           ':collected'=>$collected,':cracked'=>$cracked,':dirty'=>$dirty,':notes'=>$notes]);
}

function logMortality(int $flockId, int $userId, string $date,
                      int $qty, string $cause = 'unknown', string $notes = ''): void {
    $db = getDB();
    $db->prepare("INSERT INTO mortality (flock_id,recorded_by,record_date,quantity,cause,notes)
                  VALUES (:flock,:user,:date,:qty,:cause,:notes)")
       ->execute([':flock'=>$flockId,':user'=>$userId,':date'=>$date,':qty'=>$qty,':cause'=>$cause,':notes'=>$notes]);
    $db->prepare("UPDATE flocks SET current_count = current_count - :qty WHERE flock_id = :id")
       ->execute([':qty'=>$qty,':id'=>$flockId]);
}

function recordSale(array $data): int {
    $db = getDB();
    $db->prepare("INSERT INTO sales
        (customer_id,sold_by,sale_date,sale_type,quantity,unit,unit_price,payment_status,amount_paid,notes,flock_id)
        VALUES(:customer,:sold_by,:date,:type,:qty,:unit,:price,:pstatus,:paid,:notes,:flock)")
       ->execute([':customer'=>$data['customer_id'],':sold_by'=>$data['sold_by'],
                  ':date'=>$data['sale_date'],':type'=>$data['sale_type'],
                  ':qty'=>$data['quantity'],':unit'=>$data['unit']??'pieces',
                  ':price'=>$data['unit_price'],':pstatus'=>$data['payment_status']??'paid',
                  ':paid'=>$data['amount_paid'],':notes'=>$data['notes']??'',
                  ':flock'=>$data['flock_id']??null]);
    return (int)$db->lastInsertId();
}
