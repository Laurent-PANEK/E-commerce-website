<?php
include_once "includes/Session.php";
include_once "includes/init.php";


if (!empty($_POST)) {
    if (!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['pseudo']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password_c'])) {

        if ($_POST['password'] == $_POST['password_c']) {

            $mail_exist = $bdd->query('SELECT email FROM users WHERE  email = :email', [':email' => htmlspecialchars($_POST['email'])])->fetch();
            
            if (empty($mail_exist)) {

                $bdd->query('INSERT INTO users(last_name,first_name,pseudonym, password, email, created_at) VALUES (:last_name,:first_name,:pseudonym,:password,:email, CURRENT_TIME )',
                    [
                        ':last_name' => htmlspecialchars($_POST['last_name']),
                        ':first_name' => htmlspecialchars($_POST['first_name']),
                        ':pseudonym' => htmlspecialchars($_POST['pseudo']),
                        ':password' => password_hash(htmlspecialchars($_POST['password']), PASSWORD_BCRYPT),
                        ':email' => htmlspecialchars($_POST['email']),
                    ]);
                $user = $bdd->query('SELECT * FROM users WHERE email = :email ',
                    [
                        ':email' => htmlspecialchars($_POST['email']),
                    ])->fetch();
		// add true parameter to mkdir recursively
                mkdir("./users/user-".$user['id_user']);
                mkdir("./users/user-".$user['id_user']."/data");
                mkdir("./users/user-".$user['id_user']."/settings");
                $session->connnect($user, $bdd);
            } else {
                $session->setFlash('error', 'Cet email est déjà utilisé !');
            }
        } else {
            $session->setFlash('error', 'Mot de passe invalide');
        }
        
    } else {
        $session->setFlash('error', 'Formulaire incomplet !');
    }
}
?>
<?php include 'includes/header.php'; ?>


<div class="container form">
  <div class="row">
    <div class="col-md-6 col-md-offset-3 form-custom">
      <h2 class="img-header">
        <img class="img-logo" src="assets/icones/logo.png" />
        <div class="content">
          Inscription
      </div>
  </h2>
  <form action="" method="POST">
    <div class="form-group">
        <i class="socicon-users custom-icon"></i><input class="custom-font" name="first_name" placeholder="Nom" type="nom"/>
    </div>
    <div class="form-group">   <i class="socicon-users custom-icon"></i><input class="custom-font" name="last_name" placeholder="Prénom" type="prenom" />
    </div>
    <div class="form-group">
        <i class="socicon-users custom-icon"></i><input class="custom-font" name="pseudo" placeholder="Nom d'utilisateur" type="pseudo" />
    </div>

    <div class="form-group">
        <i class="socicon-envelope custom-icon"></i><input class="custom-font" name="email" placeholder="Email" type="text" />
    </div>
    <div>
       <i class="socicon-padlock custom-icon"></i><input class="custom-font" name="password" placeholder="Mot de passe" type="password" />
   </div>
   <div>
       <i class="socicon-padlock custom-icon"></i><input class="custom-font" name="password" placeholder="Mot de passe" type="password" />
   </div>

   <div>
    <i class="socicon-check2 custom-icon"></i><input class="custom-font" name="password_c" placeholder="Confirmation" type="password" />
</div>

<div>
    <input type="checkbox">
    <label>J'ai lu et j'accepte les <a href=""> conditions générales de vente</a>.</label>
</div>

<input type="submit" value="Se connecter" class="ui fluid large blue submit button">
<div class="message suggestion">
    Vous avez déjà un compte ? <a href="login.php">Connexion</a>
</div>
<div class="error message"></div>

<div class="ui error message"></div>
</form>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>


