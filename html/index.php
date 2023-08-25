<?php

require_once "connect.php";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $comment = $_POST["comment"];

// Injection vulnérable
if(isset($_POST['comment']) && !empty($_POST['comment'])
    && isset($_POST['username']) && !empty($_POST['username'])) {
    
    // Cette ligne est vulnérable car elle concatène directement l'entrée utilisateur à la requête SQL
    $requete = "INSERT INTO comments SET username='" . $username . "', comment='" . $comment . "'";
    $pdo->query($requete);
}

// Récupérer tous les commentaires
$comments = $pdo->query('SELECT * FROM comments')->fetchAll();
}
$comments = $pdo->query("SELECT * FROM comments")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Injection SQL & XSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .warning {
            padding: 15px;
            background-color: #ffeeee;
            border: 1px solid #cc0000;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        form {
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            height: 100px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .info-section {
            border: 2px solid #007BFF; /* Une bordure bleue */
            background-color: #E9F5FF; /* Un fond bleu clair */
            padding: 10px 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-family: Arial, sans-serif; /* Une police générique; modifiez selon vos préférences */
        }
        .info-section h2, 
        .info-section h3 {
            color: #0056b3; /* Une teinte de bleu plus foncée pour les titres */
            margin-top: 0;
        }

        .info-section p {
            margin: 10px 0;
            color: #003366; /* Un bleu encore plus foncé pour le texte normal */
        }

        .info-section code {
            background-color: #D6E5F3; /* Un fond encore plus clair pour le code */
            padding: 2px 5px;
            border-radius: 3px;
            display: block; /* Fait en sorte que le code apparaisse sur sa propre ligne */
            margin: 10px 0;
        }


    </style>
</head>
<body>

<div class="warning">
    <strong>Attention:</strong> Cette démonstration est intentionnellement vulnérable aux injections SQL et aux attaques XSS. N'utilisez jamais un tel code dans un environnement réel!
    
    <h3>Injection SQL:</h3>
    <p>
        L'injection SQL se produit lorsqu'un attaquant peut "injecter" ou insérer des requêtes SQL malveillantes via les entrées utilisateur. Ces requêtes peuvent être utilisées pour lire, modifier, voire supprimer des données dans une base de données sans autorisation.
    </p>
    <strong>Exemple:</strong>
    <pre>username'; DROP TABLE comments; --</pre>
    Dans cet exemple, l'utilisation de `';` termine la requête SQL en cours. Ensuite, `DROP TABLE comments;` est une nouvelle requête qui supprime la table des commentaires. Le `--` est utilisé pour commenter le reste de la requête SQL originale, rendant le reste inoffensif.
    
    <h3>Attaques XSS (Cross-Site Scripting):</h3>
    <p>
        Les attaques XSS permettent à un attaquant d'injecter des scripts malveillants dans des pages Web. Ces scripts sont ensuite exécutés par un autre utilisateur final qui visite cette page. Cela peut conduire à du vol d'informations, la propagation de malware, et d'autres actions malveillantes.
    </p>
    <strong>Exemple:</strong>
    <pre>&lt;script&gt;alert('CHEH!')&lt;/script&gt;</pre>
    Si un utilisateur soumet ce script via le formulaire et que le site ne filtre pas ou ne désinfecte pas correctement les entrées, tout autre utilisateur visitant la page verra une alerte avec le message "CHEH!".

</div>

<h1>Ajouter un commentaire</h1>

<form action="index.php" method="post">
    Nom d'utilisateur : <input type="text" name="username" required><br><br>
    Commentaire : <textarea name="comment" required></textarea><br><br>
    <input type="submit" value="Ajouter">
</form>

<h2>Commentaires :</h2>

<ul>
    <?php foreach ($comments as $comment) { ?>
        <li><strong><?php echo $comment["username"]; ?></strong>: <?php echo $comment["comment"]; ?></li>
    <?php } ?>
</ul>

<div class="info-section">
    <h2>Comprendre l'Injection SQL</h2>

    <h3>Étape 1: La base d'une requête SQL</h3>
    <p>Lorsque vous utilisez un formulaire de connexion, le système back-end forme généralement une requête SQL pour vérifier les identifiants. Par exemple :</p>
    <code>
        SELECT * FROM users WHERE username = 'john_doe' AND password = 'securePass';
    </code>

    <h3>Étape 2: La requête comme une chaîne de caractères</h3>
    <p>SQL traite la totalité de la requête comme une chaîne de caractères. Si cette chaîne est formée en ajoutant directement les entrées de l'utilisateur sans validation, cela peut mener à des vulnérabilités.</p>

    <h3>Étape 3: Visualisation de l'injection</h3>
    <p>Par exemple, pour contourner un système de connexion mal codé, un attaquant pourrait saisir :</p>
    <code>john_doe'; --</code>
    <p>Transformant la requête en :</p>
    <code>
        SELECT * FROM users WHERE username = 'john_doe'; --' AND password = 'whatever';
    </code>
    <p>Ici, l'attaquant termine la requête SQL et commente le reste, contournant ainsi le contrôle du mot de passe.</p>

    <h3>Étape 4: Des attaques plus sophistiquées</h3>
    <p>Un attaquant pourrait, par exemple, supprimer des tables ou extraire des données :</p>
    <code>'; DROP TABLE users; --</code>
    <p>Cela effacerait toutes les données utilisateur.</p>

    <h3>Protection contre l'injection SQL</h3>
    <p>La préparation des requêtes, l'échappement des entrées et la validation stricte sont essentiels pour se protéger contre ces attaques.</p>
</div>

<div class="info-section">
    <h2>Comprendre les Failles XSS (Cross-Site Scripting)</h2>

    <h3>Qu'est-ce qu'une faille XSS ?</h3>
    <p>La faille XSS permet à un attaquant d'injecter du code JavaScript malveillant dans des pages web, qui est ensuite exécuté par d'autres utilisateurs lorsqu'ils visitent ces pages.</p>

    <h3>Types de XSS</h3>
    <p>Il existe principalement trois types de failles XSS :</p>
    <ul>
        <li><strong>XSS réfléchi :</strong> Le script malveillant est inclus dans l'URL et est exécuté lorsqu'un utilisateur clique dessus.</li>
        <li><strong>XSS stocké :</strong> Le script malveillant est stocké sur le serveur (par exemple, dans une base de données) et est exécuté chaque fois qu'un utilisateur charge une page qui contient le script.</li>
        <li><strong>XSS basé sur le DOM :</strong> Le script est exécuté à la suite d'une modification du DOM (Document Object Model) de la page par le code JavaScript existant.</li>
    </ul>

    <h3>Comment cela fonctionne ?</h3>
    <p>Un attaquant pourrait, par exemple, insérer le code suivant dans un commentaire ou un champ de saisie sur un site web :</p>
    <code>&lt;script&gt;alert("Attaque XSS réussie!");&lt;/script&gt;</code>
    <p>Si le site ne désinfecte pas correctement les entrées, ce script serait exécuté pour chaque utilisateur visitant la page contenant ce commentaire ou cette entrée.</p>

    <h3>Protection contre les attaques XSS</h3>
    <p>Il est essentiel d'appliquer des méthodes de désinfection sur toutes les données saisies par les utilisateurs avant de les afficher. L'utilisation de librairies et de fonctions spécifiquement conçues pour prévenir le XSS est fortement recommandée.</p>
</div>

<div class="info-section">
    <h2>Comprendre les Failles XSS (Cross-Site Scripting)</h2>

    <h3>XSS réfléchi</h3>
    <p>
        Le script malveillant est inclus dans l'URL et est exécuté lorsque l'utilisateur la visite. 
    </p>
    <p><strong>Exemple :</strong> Un attaquant envoie un lien tel que <code>http://votresite.com/recherche?q=&lt;script&gt;alert('XSS')&lt;/script&gt;</code> par e-mail ou sur un forum. Si un utilisateur clique sur ce lien et que votre site ne filtre pas correctement les entrées, l'utilisateur verra une alerte indiquant "XSS".</p>

    <h3>XSS stocké</h3>
    <p>
        Le script malveillant est stocké sur le serveur (par exemple, dans une base de données) et est exécuté chaque fois qu'un utilisateur charge une page contenant ce script.
    </p>
    <p><strong>Exemple :</strong> Un attaquant publie un commentaire sur un blog contenant le code <code>&lt;script&gt;alert('XSS stocké')&lt;/script&gt;</code>Chaque fois qu'un utilisateur visite cette page de commentaires, il verra une alerte avec le message "XSS stocké".</p>

    <!-- <h3>XSS basé sur le DOM</h3>
    <p>
        Ce type d'attaque se produit lorsque le code JavaScript existant sur la page modifie le DOM en utilisant des données malveillantes provenant d'une source non fiable.
    </p>
    <p><strong>Exemple :</strong> Supposons que vous ayez une page avec le script suivant : <code>document.getElementById("output").innerHTML = document.location.hash.substring(1);</code>. Un attaquant pourrait modifier l'URL pour inclure <code>#&lt;script&gt;alert('XSS basé sur le DOM')&lt;/script&gt;</code>, faisant ainsi apparaître une alerte lorsqu'un utilisateur visite cette URL modifiée.</p> -->

    <h3>Protection contre les attaques XSS</h3>
    <p>Il est essentiel d'appliquer des méthodes de désinfection sur toutes les données saisies par les utilisateurs avant de les afficher. L'utilisation de librairies et de fonctions spécifiquement conçues pour prévenir le XSS est fortement recommandée.</p>
</div>

<div class="info-section">
    <h2>Protection contre l'Injection SQL</h2>

    <h3>1. Requêtes préparées avec liaisons de paramètres</h3>
    <p>L'utilisation de requêtes préparées avec des liaisons de paramètres est la méthode recommandée pour éviter les injections SQL. Avec PDO, vous pouvez lier des paramètres pour éviter que les utilisateurs n'injectent du code SQL malveillant.</p>

    <code>
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username AND password = :password');
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    </code>

    <h3>2. Échapper les entrées avec PDO</h3>
    <p>PDO fournit une méthode pour échapper les entrées : <code>PDO::quote()</code>. Cependant, il est généralement recommandé d'utiliser des requêtes préparées plutôt que cette méthode.</p>

    <h3>3. Réduire les privilèges</h3>
    <p>Limitez les permissions de votre compte de base de données. Par exemple, si une application n'a pas besoin d'effacer des données, alors elle ne devrait pas avoir de permissions pour effacer des tables.</p>

    <h3>4. Valider les entrées</h3>
    <p>Ne faites jamais confiance aux entrées des utilisateurs. Vérifiez toujours que les données sont de la forme attendue.</p>

</div>

<div class="info-section">
    <h2>Protection contre les attaques XSS</h2>

    <h3>1. Échapper les sorties</h3>
    <p>Avant d'afficher des données fournies par l'utilisateur, échappez-les pour qu'elles ne soient pas interprétées comme du code.</p>
    
    <code>
    echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
    </code>

    <h3>2. Utiliser des en-têtes HTTP de sécurité</h3>
    <p>L'ajout d'un en-tête <code>Content-Security-Policy</code> peut réduire le risque d'attaques XSS en limitant les sources d'où le contenu peut être chargé.</p>

    <h3>3. Valider et désinfecter les entrées</h3>
    <p>Assurez-vous que toutes les entrées utilisateur sont validées et désinfectées. Ne permettez que des caractères spécifiques et évitez ceux qui pourraient avoir une signification spéciale en HTML ou JavaScript.</p>

    <h3>4. Éviter d'utiliser des fonctions d'évaluation de code</h3>
    <p>En JavaScript, évitez d'utiliser la fonction <code>eval()</code> ou d'autres fonctions qui peuvent exécuter du code.</p>

    <h3>5. Appliquer une politique de moindre privilège</h3>
    <p>Ne donnez pas aux scripts plus de privilèges qu'ils n'en ont besoin. Par exemple, évitez d'utiliser <code>innerHTML</code> en JavaScript et préférez des méthodes qui ne permettent pas d'insérer du code, comme <code>textContent</code>.</p>
</div>




</body>
</html>