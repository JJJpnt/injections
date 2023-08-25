<?php
$host = 'db';
$db   = 'testdatabase';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $sql = "CREATE TABLE comments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        comment TEXT NOT NULL
    )";

    $pdo->query($sql);
    echo "Table comments created successfully";

    // Insertion de commentaires d'exemple
    $comments = [
        ['John', 'L\'injection SQL est une technique où un attaquant peut insérer du code SQL malveillant. Cela peut être très dangereux si le code n\'est pas correctement désinfecté.'],
        ['Alice', 'Les failles XSS permettent à des scripts malveillants d\'être exécutés dans le navigateur de l\'utilisateur. Cela peut conduire à des vols d\'informations.'],
        ['Bob', '<script>alert("Ceci est une attaque XSS!")</script> Regardez! J\'ai pu exécuter du JavaScript sur votre page. CHEH!']
    ];

    foreach ($comments as $comment) {
        $stmt = $pdo->prepare("INSERT INTO comments (username, comment) VALUES (?, ?)");
        $stmt->execute([$comment[0], $comment[1]]);
    }

    echo "\nSample comments added successfully";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>