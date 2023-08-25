<?php

require 'connect.php';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $sql = "DROP TABLE IF EXISTS comments;";
    $pdo->query($sql);
    echo "Table comments dropped successfully";
    $sql = "DROP TABLE IF EXISTS utilisateurs;";
    $pdo->query($sql);
    echo "Table utilisateurs dropped successfully";
    
    $sql = "CREATE TABLE comments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        comment TEXT NOT NULL
    )";
    $pdo->query($sql);
    echo "Table comments created successfully";
        
    $sql = "CREATE TABLE utilisateurs (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        password VARCHAR(255) NOT NULL
    )";
    $pdo->query($sql);
    echo "Table utilisateurs created successfully";

    // Insertion de commentaires d'exemple
    $comments = [
        ['John', 'L\'injection SQL est une technique où un attaquant peut insérer du code SQL malveillant. Cela peut être très dangereux si le code n\'est pas correctement désinfecté.'],
        ['Alice', 'Les failles XSS permettent à des scripts malveillants d\'être exécutés dans le navigateur de l\'utilisateur. Cela peut conduire à des vols d\'informations.'],
        ['Bob', '<script>alert("Ceci est une attaque XSS!")</script> Regardez! J\'ai pu exécuter du JavaScript sur votre page. CHEH!']
    ];

    $users = [
        ['admin', 'password_admin'],
        ['jjj', 'password_jjj'],
    ];

    foreach ($comments as $comment) {
        $stmt = $pdo->prepare("INSERT INTO comments (username, comment) VALUES (?, ?)");
        $stmt->execute([$comment[0], $comment[1]]);
    }
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password) VALUES (?, ?)");
        $stmt->execute([$user[0], $user[1]]);
    }

    echo "\nSample comments added successfully";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>