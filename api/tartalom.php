<?php
// tartalom.php – Tartalom CRUD API
// Madarász Dóra | TFO727
// Mészáros Márton Bence | KUS0K8

require_once 'db.php';

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT tartalom.*, suti.nev AS suti_nev FROM tartalom LEFT JOIN suti ON tartalom.sutiid=suti.id WHERE tartalom.id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $row ? print(json_encode($row)) : (http_response_code(404) && print(json_encode(['error'=>'Nem található'])));
        } else {
            $stmt = $pdo->query("SELECT tartalom.*, suti.nev AS suti_nev FROM tartalom LEFT JOIN suti ON tartalom.sutiid=suti.id ORDER BY tartalom.id");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['sutiid']) || empty($data['mentes'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó mezők']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO tartalom (sutiid, mentes) VALUES (?, ?)");
        $stmt->execute([(int)$data['sutiid'], $data['mentes']]);
        http_response_code(201);
        echo json_encode(['id' => (int)$pdo->lastInsertId(), 'sutiid' => (int)$data['sutiid'], 'mentes' => $data['mentes']]);
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE tartalom SET sutiid=?, mentes=? WHERE id=?");
        $stmt->execute([(int)$data['sutiid'], $data['mentes'], $id]);
        $stmt->rowCount() === 0
            ? (http_response_code(404) && print(json_encode(['error'=>'Nem találhato'])))
            : print(json_encode(['id'=>$id, 'sutiid'=>(int)$data['sutiid'], 'mentes'=>$data['mentes']]));
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID szükséges']); break; }
        $stmt = $pdo->prepare("DELETE FROM tartalom WHERE id=?");
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
