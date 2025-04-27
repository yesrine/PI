<?php
// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learnup";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialisation des variables
$email = $password = "";
$error = "";

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage des données d'entrée
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Validation des données
    if (empty($email) ){
        $error = "L'adresse email est requise";
    } elseif (empty($password)) {
        $error = "Le mot de passe est requis";
    } else {
        // Préparation de la requête SQL
        $sql = "SELECT id, nom, prenom, mail, pwd FROM utilisateurs WHERE mail = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Liaison des paramètres
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            
            // Exécution de la requête
            if ($stmt->execute()) {
                // Stockage du résultat
                $stmt->store_result();
                
                // Vérification si l'email existe
                if ($stmt->num_rows == 1) {
                    // Liaison des variables de résultat
                    $stmt->bind_result($id, $nom, $prenom, $mail, $hashed_password);
                    if ($stmt->fetch()) {
                        // Vérification du mot de passe
                        if (password_verify($password, $hashed_password)) {
                            // Démarrage de la session
                            session_start();
                            
                            // Stockage des données dans la session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["nom"] = $nom;
                            $_SESSION["prenom"] = $prenom;
                            
                            // Redirection vers la page d'accueil
                            header("location: acceuil.html");
                        } else {
                            // Mot de passe incorrect
                            $error = "Le mot de passe que vous avez entré n'est pas valide.";
                        }
                    }
                } else {
                    // Email n'existe pas
                    $error = "Aucun compte trouvé avec cette adresse email.";
                }
            } else {
                $error = "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            
            // Fermeture du statement
            $stmt->close();
        }
    }
    
    // Fermeture de la connexion
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnUp - Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Les mêmes styles que dans login.html */
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>LearnUp</h1>
        </div>
        
        <h2>Connexion à votre compte</h2>
        
        <?php 
        if (!empty($error)) {
            echo '<div class="error-message">' . $error . '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="Votre adresse email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required>
                </div>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <div class="links">
            <a href="forgot-password.html">Mot de passe oublié ?</a>
            <span> | </span>
            <a href="inscri.html">Créer un compte</a>
        </div>
    </div>
</body>
</html>