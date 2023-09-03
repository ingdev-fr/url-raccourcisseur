<?php

// --------- CONNEXION A LA BDD ---------------------------
        $bdd = new PDO(
            'mysql:host=127.0.0.1;port=8889;dbname=cours_mysql;charset=utf8',
            'root',
            'root',
        );

// ------------ CREATION DE L'URL RACCOURCIE + ENREGISTREMENT DANS BDD -------------
if(isset($_POST['url']) && $_POST['url'] != '') {
    // Je rentre ma valeur postée dans une variable
    $url = $_POST['url'];
    // Je crée un raccourci unique à partir de l'url envoyée par l'utilisateur
    $shortcut = crypt($url, rand()); 
    echo $shortcut;

    // Je vérifie que l'url envoyée par l'utilisateur est valide (c'est bien une url) et qu'il y a bien une suite (path) après le nom de domaine 
    if(!filter_var($url, FILTER_VALIDATE_URL /*, FILTER_FLAG_PATH_REQUIRED*/)) {
        // Je redirige vers la page d'accueil avec 2 arguments dans l'url : erreur=true et le message d'erreur
        header('location: index.php?error=true&message=Adresse url non valide');
        exit();
    }
    else {
        // On vérifie si l'url a déjà été proposée
        // Je crée une requête pour savoir si l'url à raccourcir a déjà fait l'objet d'un enregistrement dans la bdd
        $req = $bdd->prepare('SELECT COUNT(*) AS url_deja_la FROM links WHERE url = ?');
        $req->execute(array($url));

        // Vérification si l'url existe déjà dans la bdd
        while($result = $req->fetch()) {
            // Ensuite, si l'url à raccourcir est déjà dans la bdd, je renvoie vers la page avec une erreur affichée
            if($result['url_deja_la'] != 0) {
                header('location: index.php?error=true&message=Adresse url déjà raccourcie');
                exit();
            }
            else {
                // SI l'url n'est pas déjà dans la bdd, j'envois l'url et l'adresse raccourcie dans la bdd
                $req = $bdd->prepare('INSERT INTO links(url, shortcut) VALUES(?, ?)');
                $req->execute(array($url, $shortcut));
                // Je redirige ensuite vers l'index et je passe l'url raccourci comme argument
                header('location:index.php?short=' . $shortcut);
                exit();
            }
        }
    }
}

// ------------ SYSTEME POUR RETROUVER LA BONNE URL A PARTIR DU SHORTCUT -----------

if(isset($_GET['shorturl'])) { 
    // Je récupère le shortcut dans une variable 
    $shortcut = $_GET['shorturl'];
     // Je vérifie que le shortcut existe bien dans la bdd
     $req = $bdd->prepare('SELECT COUNT(*) AS shortcut_deja_la FROM links WHERE shortcut = ?');
     $req->execute(array($shortcut));
     while($result = $req->fetch()) {
        // Ensuite, si le shortcut n'est pas dans la bdd, je renvoie vers la page avec une erreur affichée
        if($result['shortcut_deja_la'] == 0) {
            header('location: index.php?error=true&message=L\'url raccourcie renseignée n\'existe pas dans la base de données');
            exit();
        }
        // Si le shortcut est bien dans notre bdd, 
        else {
            // Je récupère l'url associée au shortcut
            $req = $bdd->prepare('SELECT url FROM links WHERE shortcut = ?');
            $req->execute(array($shortcut));
            while($result = $req->fetch()) {
                // Je renvoie vers la bonne url
                header('location: ' .$result['url']);
            }
        }
    }
}
?>

<!------------------------ AFFICHAGE HTML DE LA PAGE ----------------->

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Raccourcisseur url</title>
        <link rel="icon" href="pictures/favicon.png" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
            rel="stylesheet"
        >
        <link rel="stylesheet" href="css/default.css">
    </head>
    <body>
        <!-- HEADER -->
        <section class="header container-fluid">
            <!-- Navigation -->
            <?php include_once('includes/header.php'); ?>
            
            <!-- Hero -->
            <div class="d-flex flex-column align-items-center pb-5">
                <h1 class="text-uppercase pb-2 text-white text-center" >Une URL longue ? Raccourcissez-la !</h1>
                <h2 class="fs-3 text-white text-center">Largement meilleur et plus court que les autres.</h2>
                <!-- Formulaire -->
                <form class="p-5" method="post" action="index.php">
                    <div class="d-flex flex-wrap justify-content-center mb-2">
                        <input class="input me-1 rounded ps-2 shadow" type="url" name="url" placeholder="Collez un lien à raccourcir">
                        <input type="submit" class="shadow btn btn-warning text-uppercase" value="Raccourcir">
                    </div>
                    <!-- S'il y a un message d'erreur dans l'url, je l'affiche ici -->
                    <?php
                        if(isset($_GET['error']) && isset($_GET['message'])) {
                            $message = htmlspecialchars($_GET['message']); // Ne pas oublier de sécuriser les arguments dans l'url !
                            echo '
                                <div class="alert alert-warning" role="alert">
                                    ' . $message . '
                                </div>
                            ';
                        }
                    ?>
                </form>
                <!-- Affichage de l'url raccourcie, je passe le shortcut comme argument url (q= ...). Ca permettra de retrouver la bonne url quand on cliquera sur celle raccourcie -->
                <div class="resultat border border-white rounded">
                    <div class="text-center p-2 text-white">URL RACCOURCIE : http://localhost:8080/?shorturl=<?php if(isset($_GET['short'])) { echo $_GET['short'];} ?></div>
                </div>
            </div>
        </section>

        <!-- Section des marques -->
        <section class="marques container-fluid p-4">
            <div class="d-flex flex-column align-items-center">
                <h2 class="text-warning">Ces marques nous font confiance</h2>
                <div class=" d-flex justify-content-around flex-wrap p-4">
                    <img class="entreprise p-3" src="pictures/1.png" alt="logo1">
                    <img class="entreprise p-3" src="pictures/2.png" alt="logo2">
                    <img class="entreprise p-3" src="pictures/3.png" alt="logo3">
                    <img class="entreprise p-3" src="pictures/4.png" alt="logo4">
                </div>
            </div>

        <!-- FOOTER -->
        </section>
        <?php include_once('includes/footer.php'); ?>
    </body>
</html>