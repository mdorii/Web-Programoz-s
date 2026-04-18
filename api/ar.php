<?php
// ar.php – Ár CRUD API
// Madarász Dóra | TFO727
// Mészáros Márton Bence | KUS0K8

require_once 'db.php';

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT ar.*, suti.nev AS suti_nev FROM ar LEFT JOIN suti ON ar.sutiid=suti.id WHERE ar.id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $row ? print(json_encode($row)) : (http_response_code(404) && print(json_encode(['error'=>'Nem található'])));
        } else {
            $stmt = $pdo->query("SELECT ar.*, suti.nev AS suti_nev FROM ar LEFT JOIN suti ON ar.sutiid=suti.id ORDER BY ar.id");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['sutiid']) || empty($data['ertek']) || !isset($data['egyseg'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó mezők']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO ar (sutiid, ertek, egyseg) VALUES (?, ?, ?)");
        $stmt->execute([(int)$data['sutiid'], (int)$data['ertek'], $data['egyseg']]);
        http_response_code(201);
        echo json_encode(['id' => (int)$pdo->lastInsertId(), 'sutiid' => (int)$data['sutiid'], 'ertek' => (int)$data['ertek'], 'egyseg' => $data['egyseg']]);
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE ar SET sutiid=?, ertek=?, egyseg=? WHERE id=?");
        $stmt->execute([(int)$data['sutiid'], (int)$data['ertek'], $data['egyseg'], $id]);
        $stmt->rowCount() === 0
            ? (http_response_code(404) && print(json_encode(['error'=>'Nem található'])))
            : print(json_encode(['id'=>$id, 'sutiid'=>(int)$data['sutiid'], 'ertek'=>(int)$data['ertek'], 'egyseg'=>$data['egyseg']]));
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $stmt = $pdo->prepare("DELETE FROM ar WHERE id=?");
        $stmt->execute([$id]);
        $stmt->rowCount() === 0
            ? (http_response_code(404) && print(json_encode(['error'=>'Nem található'])))
            : print(json_encode(['deleted' => $id]));
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem engedélyezett metódus']);
}
?>
