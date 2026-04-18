<?php
// db.php – Adatbázis kapcsolat
// Madarász Dóra | TFO727
// Mészáros Márton Bence | KUS0K8

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


define('DB_HOST', 'mysql.omega');
define('DB_NAME', 'cukraszda');       // Nethely adatbázis neve
define('DB_USER', 'cukraszda');      // Nethely felhasználónév
define('DB_PASS', 'GAMF2025!');      // Nethely jelszó
define('DB_CHARSET', 'utf8');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Adatbázis kapcsolat sikertelen: ' . $e->getMessage()]);
            exit();
        }
    }
    return $pdo;
}

// Táblák létrehozása ha nem léteznek
function createTables() {
    $pdo = getDB();
    $pdo->exec("CREATE TABLE IF NOT EXISTS suti (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        nev       VARCHAR(200) NOT NULL,
        tipus     VARCHAR(100) NOT NULL,
        dijazott  TINYINT NOT NULL DEFAULT 0
    ) CHARACTER SET utf8 COLLATE utf8_hungarian_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ar (
        id      INT AUTO_INCREMENT PRIMARY KEY,
        sutiid  INT NOT NULL,
        ertek   INT NOT NULL,
        egyseg  VARCHAR(50) NOT NULL
    ) CHARACTER SET utf8 COLLATE utf8_hungarian_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tartalom (
        id     INT AUTO_INCREMENT PRIMARY KEY,
        sutiid INT NOT NULL,
        mentes VARCHAR(10) NOT NULL
    ) CHARACTER SET utf8 COLLATE utf8_hungarian_ci");
}

// Seed adatok betöltése ha üres a tábla
function seedData() {
    $pdo = getDB();
    $count = $pdo->query("SELECT COUNT(*) FROM suti")->fetchColumn();
    if ($count > 0) return;

    $sutiData = [
        [1,'Süni','vegyes',0],[2,'Gesztenyealagút','vegyes',0],[3,'Sajtos pogácsa','sós teasütemény',0],
        [4,'Diós-mákos','bejgli',0],[5,'Sajttorta (málnás)','torta',0],[6,'Citrom','torta',0],
        [7,'Eszterházy','tortaszelet',0],[8,'Rákóczi-túrós','pite',0],[9,'Meggyes kocka','tejszínes sütemény',0],
        [10,'Legényfogó','torta',-1],[11,'Alpesi karamell','tortaszelet',0],[12,'Kókuszcsók','édes teasütemény',0],
        [13,'Habos mákos','pite',0],[14,'Szilvás','pite',0],[15,'Juhtúrós párna','sós teasütemény',0],
        [16,'Mákos guba','tortaszelet',0],[17,'Néró','édes teasütemény',0],[18,'Sacher','tortaszelet',0],
        [19,'Citrom','tortaszelet',0],[20,'Ribizlihabos-almás réteges','különleges torta',-1],
        [21,'Három kívánság','torta',-1],[22,'Dobos','torta',0],[23,'Epres mascarpone','tortaszelet',0],
        [24,'Csokoládémousse','torta',0],[25,'Oroszkrém','torta',0],[26,'Medvetalp','vegyes',0],
        [27,'Trüffel','torta',0],[28,'Tejszínes gyümölcsös (meggy)','torta',0],
        [29,'Mákos-szilvalekváros','bejgli',0],[30,'Ribizlihabos-almás réteges tortaszelet','tortaszelet',0],
        [31,'Marcipános vágott','édes teasütemény',0],[32,'Indiáner','vegyes',0],
        [33,'Meggyes','pite',0],[34,'Mákos','bejgli',0],[35,'Sós karamella','torta',0],
        [36,'Legényfogó','tortaszelet',0],[37,'Rigó Jancsi','torta',0],
        [38,'Tejszínes gyümölcsös (erdei gyümölcs)','torta',0],[39,'Ez+Az (csokoládé és gesztenye)','torta',0],
        [40,'Málnás mascarpone','torta',0],[41,'Dobos','tortaszelet',0],[42,'Ferrero','torta',0],
        [43,'Vegyes házi pite falatok','pite',0],[44,'Ökörszem','édes teasütemény',0],
        [45,'Danubius kocka','tejszínes sütemény',0],[46,'Sajtkrémmel töltött fánkocska','sós teasütemény',0],
        [47,'Túrókrém gyümölccsel díszítve','tortaszelet',0],[48,'Almás','pite',0],
        [49,'Mignon','vegyes',0],[50,'Csokoládémousse fényes csokoládéval','torta',0],
        [51,'Vágott sós (sós omlós)','sós teasütemény',0],[52,'Nagyi sós','sós teasütemény',0],
        [53,'Vegyes sós','sós teasütemény',0],[54,'Somlói','tortaszelet',0],
        [55,'Tiramisu','tortaszelet',0],[56,'Hegyvidék','tortaszelet',0],
        [57,'Szedres csokoládé','tortaszelet',0],[58,'Pogácsák vegyesen','sós teasütemény',0],
        [59,'Lúdláb','torta',0],[60,'Sacher','torta',0],[61,'Eszterházy','torta',0],
        [62,'Zalavári gesztenye','tortaszelet',0],[63,'Gesztenyegolyó','vegyes',0],
        [64,'Pisztáciás-málnás mascarpone','tortaszelet',0],[65,'Habos mákos','vegyes',0],
        [66,'Franciakrémes','krémes',0],[67,'Gesztenye kocka','tejszínes sütemény',0],
        [68,'Pisztáciás-málnás mascarpone','torta',0],[69,'Málnás kocka','tejszínes sütemény',0],
        [70,'Sajttorta (málnás)','tortaszelet',0],[71,'Túrókrém gyümölccsel','torta',0],
        [72,'Csokis kaland','különleges torta',-1],[73,'Somlói','torta',0],
        [74,'Palermo','torta',0],[75,'Szilvalekváros','bejgli',0],
        [76,'Ünnepi diótorta grillázzsal','torta',0],[77,'Oroszkrém','tortaszelet',0],
        [78,'Mini zserbó','édes teasütemény',0],[79,'Sajtos masni','sós teasütemény',0],
        [80,'Zserbó','pite',0],[81,'Tejszínes gyümölcsös (málna)','torta',0],
        [82,'Marcipános csokoládé','torta',0],[83,'Csokis kaland','tortaszelet',0],
        [84,'Marcipán tekercs','édes teasütemény',0],[85,'Képviselőfánk','vegyes',0],
        [86,'Epres omlett','vegyes',0],[87,'Mini linzer','édes teasütemény',0],
        [88,'Linzerkarika','vegyes',0],[89,'Szedres csokoládé','torta',0],
        [90,'Narancsív','édes teasütemény',0],[91,'Gesztenyepüré','vegyes',0],
        [92,'Palermo','tejszínes sütemény',0],[93,'Csokis néró','édes teasütemény',0],
        [94,'Flódni','pite',0],[95,'Mézeskalács','torta',0],[96,'Olívás pogácsa','sós teasütemény',0],
        [97,'Florentin','édes teasütemény',0],[98,'Tiramisu','torta',0],
        [99,'Zoli kedvence (vágott édes tea)','édes teasütemény',0],
        [100,'Erdei gyümölcs kocka','tejszínes sütemény',0],[101,'Rákóczi-túrós','tortaszelet',0],
        [102,'Mézeskrémes','pite',0],[103,'Trüffel','tortaszelet',0],
        [104,'Szilvás papucs','édes teasütemény',0],[105,'Zalavári gesztenye','torta',-1],
        [106,'Danubius','torta',0],[107,'Alpesi karamell','torta',0],[108,'Puncs','torta',0],
        [109,'Gesztenye szív','vegyes',0],[110,'Ez+Az (csokoládé és gesztenye)','tortaszelet',0],
        [111,'Tökmagos félhold','sós teasütemény',0],[112,'Burgonyás pogácsa','sós teasütemény',0],
        [113,'Somlói galuska','vegyes',0],[114,'Puncs','tortaszelet',0],
        [115,'Lekváros vágott','édes teasütemény',0],[116,'Oreo','torta',0],
        [117,'Vintage','torta',0],[118,'Rigó Jancsi','tejszínes sütemény',0],
        [119,'Feketeerdő','torta',0],[120,'Kókuszos vágott','édes teasütemény',0],
        [121,'Feketeerdő','tortaszelet',0],[122,'Moscauer','édes teasütemény',0],
        [123,'Diós','bejgli',0],[124,'Rákóczi-túrós','torta',0],
        [125,'Három kívánság','különleges torta',0],[126,'Gesztenyés-karamellás','bejgli',0],
        [127,'Gesztenyés szív','édes teasütemény',0],[128,'Ropi','sós teasütemény',0],
        [129,'Paleolit étcsokoládé','különleges torta',0],[130,'Túrós','pite',0],
        [131,'Ischler','vegyes',0],[132,'Lúdláb','tortaszelet',0],
        [133,'Csokoládémousse','tortaszelet',0],[134,'Dió','torta',0],
        [135,'Krémes','krémes',0],[136,'Mini ischler','édes teasütemény',0],
        [137,'Paleolit étcsokoládé','tortaszelet',0],[138,'Tejfölös túrós hajtogatott','sós teasütemény',0],
        [139,'Mákos guba','torta',0]
    ];

    $stmt = $pdo->prepare("INSERT INTO suti (id,nev,tipus,dijazott) VALUES (?,?,?,?)");
    foreach ($sutiData as $r) $stmt->execute($r);

    $arData = [
        [1,32,500,'db'],[2,76,10900,'16 szeletes'],[3,106,4300,'8 szeletes'],
        [4,88,300,'db'],[5,116,16200,'24 szeletes'],[6,135,250,'db'],
        [7,127,4400,'kg'],[8,50,13400,'24 szeletes'],[9,70,700,'db'],
        [10,31,5200,'kg'],[11,96,3300,'kg'],[12,116,5700,'8 szeletes'],
        [13,22,9000,'16 szeletes'],[14,138,4400,'kg'],[15,112,2900,'kg'],
        [16,58,3200,'kg'],[17,98,10400,'16 szeletes'],[18,75,2100,'rúd'],
        [19,24,11400,'24 szeletes'],[20,62,600,'db'],[21,61,8400,'16 szeletes'],
        [22,105,10900,'16 szeletes'],[23,20,4700,'8 szeletes'],[24,123,1800,'rúd'],
        [25,60,8200,'16 szeletes'],[26,24,3900,'8 szeletes'],[27,38,4300,'8 szeletes'],
        [28,126,2100,'rúd'],[29,64,750,'db'],[30,109,300,'db'],
        [31,66,350,''],[32,89,13200,'24 szeletes'],[33,98,15400,'24 szeletes'],
        [34,24,7400,'16 szeletes'],[35,76,5700,'8 szeletes'],[36,131,250,'db'],
        [37,50,9200,'16 szeletes'],[38,55,600,'db'],[39,87,3400,'kg'],
        [40,4,3500,'koszorú'],[41,8,400,'db'],[42,100,450,'db'],
        [43,129,5300,'8 szeletes'],[44,35,4700,'8 szeletes'],[45,47,490,'db'],
        [46,89,9000,'16 szeletes'],[47,111,3300,'kg'],[48,94,400,'db'],
        [49,42,16200,'24 szeletes'],[50,80,350,'db']
    ];

    $stmt = $pdo->prepare("INSERT INTO ar (id,sutiid,ertek,egyseg) VALUES (?,?,?,?)");
    foreach ($arData as $r) $stmt->execute($r);

    $tarData = [
        [1,26,'G'],[2,37,'L'],[3,83,'HC'],[4,91,'G'],[5,137,'G'],
        [6,60,'Te'],[7,129,'HC'],[8,122,'To'],[9,90,'G'],[10,26,'To'],
        [11,94,'L'],[12,46,'É'],[13,72,'HC'],[14,114,'Te'],[15,63,'To'],
        [16,12,'Te'],[17,128,'É'],[18,51,'É'],[19,109,'To'],[20,109,'G'],
        [21,97,'G'],[22,97,'To'],[23,24,'L'],[24,91,'To'],[25,137,'L'],
        [26,84,'G'],[27,30,'HC'],[28,108,'Te'],[29,84,'To'],[30,6,'L'],
        [31,108,'L'],[32,12,'L'],[33,79,'É'],[34,72,'G'],[35,118,'L'],
        [36,60,'L'],[37,52,'É'],[38,137,'HC'],[39,114,'L'],[40,90,'To'],
        [41,20,'HC'],[42,63,'G'],[43,129,'G'],[44,129,'L'],[45,15,'É']
    ];

    $stmt = $pdo->prepare("INSERT INTO tartalom (id,sutiid,mentes) VALUES (?,?,?)");
    foreach ($tarData as $r) $stmt->execute($r);
}

createTables();
seedData();
?>
