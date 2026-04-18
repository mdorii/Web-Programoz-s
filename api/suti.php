<?php
// suti.php – Süti CRUD API
// Madarász Dóra | TFO727
// Mészáros Márton Bence KUS0K8

require_once 'db.php';

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // ── READ ALL / READ ONE ──────────────────────────────
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM suti WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Nem található']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM suti ORDER BY id");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    // ── CREATE ───────────────────────────────────────────
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nev']) || empty($data['tipus'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó mezők: nev, tipus']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO suti (nev, tipus, dijazott) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['nev'],
            $data['tipus'],
            isset($data['dijazott']) ? (int)$data['dijazott'] : 0
        ]);
        $newId = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode([
            'id'       => (int)$newId,
            'nev'      => $data['nev'],
            'tipus'    => $data['tipus'],
            'dijazott' => isset($data['dijazott']) ? (int)$data['dijazott'] : 0
        ]);
        break;

    // ── UPDATE ───────────────────────────────────────────
    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE suti SET nev=?, tipus=?, dijazott=? WHERE id=?");
        $stmt->execute([
            $data['nev'],
            $data['tipus'],
            isset($data['dijazott']) ? (int)$data['dijazott'] : 0,
            $id
        ]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Nem található']);
        } else {
            echo json_encode(['id' => $id, 'nev' => $data['nev'], 'tipus' => $data['tipus'], 'dijazott' => (int)$data['dijazott']]);
        }
        break;

    // ── DELETE ───────────────────────────────────────────
    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $stmt = $pdo->prepare("DELETE FROM suti WHERE id=?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Nem található']);
        } else {
            echo json_encode(['deleted' => $id]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem engedélyezett metódus']);
}
?>
