<?php
// Configuration de la base de données
$servername = "localhost";
$username = "votre_utilisateur";
$password = "votre_mot_de_passe";
$dbname = "votre_base_de_donnees";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialisation des variables
$nom = $prenom = $email = $password = $confirm_password = "";
$errors = [];

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage des données d'entrée
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm-password"]);
    
    // Validation des données
    if (empty($nom)) {
        $errors["nom"] = "Veuillez entrer votre nom";
    }
    
    if (empty($prenom)) {
        $errors["prenom"] = "Veuillez entrer votre prénom";
    }
    
    if (empty($email)) {
        $errors["email"] = "Veuillez entrer votre adresse email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Format d'email invalide";
    } else {
        // Vérification si l'email existe déjà
        $sql = "SELECT id FROM utilisateurs WHERE mail = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $errors["email"] = "Cet email est déjà utilisé";
                }
            } else {
                $errors["general"] = "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            
            $stmt->close();
        }
    }
    
    if (empty($password)) {
        $errors["password"] = "Veuillez entrer un mot de passe";
    } elseif (strlen($password) < 8) {
        $errors["password"] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors["password"] = "Le mot de passe doit contenir au moins une majuscule";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors["password"] = "Le mot de passe doit contenir au moins un chiffre";
    } elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {
        $errors["password"] = "Le mot de passe doit contenir au moins un caractère spécial";
    }
    
    if (empty($confirm_password)) {
        $errors["confirm_password"] = "Veuillez confirmer votre mot de passe";
    } elseif ($password != $confirm_password) {
        $errors["confirm_password"] = "Les mots de passe ne correspondent pas";
    }
    
    // Si aucune erreur, insertion dans la base de données
    if (empty($errors)) {
        $sql = "INSERT INTO utilisateurs (nom, prenom, mail, pwd) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Hashage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Redirection vers la page de connexion avec message de succès
                header("location: login.html?registration=success");
                exit();
            } else {
                $errors["general"] = "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            
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
    <title>LearnUp - Création de compte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Les mêmes styles que dans inscri.html */
        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
        }

        .success-message {
            color: var(--success-color);
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h1>LearnUp</h1>
        </div>
        
        <h2>Créer un compte</h2>
        
        <?php 
        if (!empty($errors["general"])) {
            echo '<div class="error-message">' . $errors["general"] . '</div>';
        }
        
        if (isset($_GET["registration"]) && $_GET["registration"] === "success") {
            echo '<div class="success-message">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="nom">Nom</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $nom; ?>" placeholder="Votre nom" required>
                </div>
                <?php if (!empty($errors["nom"])) echo '<div class="error-message">' . $errors["nom"] . '</div>'; ?>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $prenom; ?>" placeholder="Votre prénom" required>
                </div>
                <?php if (!empty($errors["prenom"])) echo '<div class="error-message">' . $errors["prenom"] . '</div>'; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="Votre adresse email" required>
                </div>
                <?php if (!empty($errors["email"])) echo '<div class="error-message">' . $errors["email"] . '</div>'; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Créez un mot de passe" required>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-hints">
                    <p>Votre mot de passe doit contenir :</p>
                    <ul>
                        <li id="length">Au moins 8 caractères</li>
                        <li id="uppercase">Une lettre majuscule</li>
                        <li id="number">Un chiffre</li>
                        <li id="special">Un caractère spécial</li>
                    </ul>
                </div>
                <?php if (!empty($errors["password"])) echo '<div class="error-message">' . $errors["password"] . '</div>'; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm-password">Confirmer le mot de passe</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirmez votre mot de passe" required>
                </div>
                <?php if (!empty($errors["confirm_password"])) echo '<div class="error-message">' . $errors["confirm_password"] . '</div>'; ?>
            </div>
            
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        
        <div class="links">
            <span>Déjà un compte ? </span>
            <a href="login.html">Se connecter</a>
        </div>
    </div>

    <script>
        // Le même script que dans inscri.html pour la validation côté client
        // et l'indicateur de force du mot de passe
    </script>
</body>
</html>