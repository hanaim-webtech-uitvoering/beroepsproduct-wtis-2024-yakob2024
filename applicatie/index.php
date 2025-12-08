<?php
require_once 'db_connectie.php';
session_start();


$ingelogd = isset($_SESSION['username'], $_SESSION['role']);
$rol = $_SESSION['role'] ?? null;


$loginFout = "";
$registratieFout = "";
$registratieSucces = "";


if (isset($_POST['login'])) {
    $naam = trim($_POST['login_naam'] ?? '');
    $wachtwoord = $_POST['login_wachtwoord'] ?? '';

    if ($naam === '' || $wachtwoord === '') {
        $loginFout = "Vul zowel gebruikersnaam als wachtwoord in.";
    } else {
        try {
            $db = maakVerbinding();

            $sql = "SELECT username, password, first_name, last_name, role
                    FROM [user]
                    WHERE username = :username";
            $stmt = $db->prepare($sql);
            $stmt->execute([':username' => $naam]);
            $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

            $loginGelukt = false;

            if ($gebruiker) {
                $dbWachtwoord = $gebruiker['password'];

                
                if (password_verify($wachtwoord, $dbWachtwoord)) {
                    $loginGelukt = true;
                }
                
                elseif ($wachtwoord !== '' && $wachtwoord === $dbWachtwoord) {
                    $nieuweHash = password_hash($wachtwoord, PASSWORD_DEFAULT);

                    $sql_update = "UPDATE [user]
                                   SET password = :password
                                   WHERE username = :username";
                    $stmt_update = $db->prepare($sql_update);
                    $stmt_update->execute([
                        ':password' => $nieuweHash,
                        ':username' => $gebruiker['username']
                    ]);

                    $loginGelukt = true;
                }
            }

            if ($loginGelukt) {
                session_regenerate_id(true);

                $_SESSION['username']   = $gebruiker['username'];
                $_SESSION['first_name'] = $gebruiker['first_name'];
                $_SESSION['role']       = $gebruiker['role'];

                
                $ingelogd = true;
                $rol = $_SESSION['role'];
            } else {
                $loginFout = "Ongeldige combinatie van gebruikersnaam en wachtwoord.";
            }
        } catch (PDOException $e) {
            $loginFout = "Er ging iets mis bij het inloggen. Probeer het later opnieuw.";
        }
    }
}


if (isset($_POST['registreer'])) {
    $reg_naam       = trim($_POST['reg_naam'] ?? '');
    $reg_wachtwoord = $_POST['reg_wachtwoord'] ?? '';
    $reg_voornaam   = trim($_POST['reg_voornaam'] ?? '');
    $reg_achternaam = trim($_POST['reg_achternaam'] ?? '');

    if ($reg_naam === '' || $reg_wachtwoord === '' || $reg_voornaam === '' || $reg_achternaam === '') {
        $registratieFout = "Vul alle velden in.";
    } elseif (strlen($reg_wachtwoord) < 8) {
        $registratieFout = "Kies een wachtwoord van minimaal 8 tekens.";
    } else {
        try {
            $db = maakVerbinding();

            $sql_check = "SELECT 1 FROM [user] WHERE username = :username";
            $stmt_check = $db->prepare($sql_check);
            $stmt_check->execute([':username' => $reg_naam]);

            if ($stmt_check->fetch()) {
                $registratieFout = "Gebruikersnaam bestaat al.";
            } else {
                $hashed_password = password_hash($reg_wachtwoord, PASSWORD_DEFAULT);

                $sql_insert = "INSERT INTO [user] (username, password, first_name, last_name, role)
                               VALUES (:username, :password, :first_name, :last_name, 'Client')";
                $stmt_insert = $db->prepare($sql_insert);
                $stmt_insert->execute([
                    ':username'   => $reg_naam,
                    ':password'   => $hashed_password,
                    ':first_name' => $reg_voornaam,
                    ':last_name'  => $reg_achternaam
                ]);

                $registratieSucces = "Registratie succesvol! Je kunt nu inloggen.";
                $reg_naam = $reg_voornaam = $reg_achternaam = '';
            }
        } catch (PDOException $e) {
            $registratieFout = "Er ging iets mis bij het registreren.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Pizzeria Sole Machina - Login & Registratie</title>
</head>
<body>

<h1>Pizzeria Sole Machina</h1>

<?php if ($ingelogd): ?>
  <p>
    Je bent ingelogd als 
    <strong><?= htmlspecialchars($_SESSION['first_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
    
    <?php if ($rol === 'Client'): ?>
        Ga naar je <a href="profiel.php">profiel</a>.
    <?php elseif ($rol === 'Personnel'): ?>
        Ga naar het <a href="overzichtpersoneel.php">Overzichtpersoneel</a>.
    <?php endif; ?>

    | <a href="loguit.php">Uitloggen</a>
</p>

<?php else: ?>
    <p>
        Welkom!
    </p>
<?php endif; ?>

<p>
    Ben je nog niet ingelogd? Dan kun je alvast het 
    <a href="menu.php">menu bekijken</a>.
</p>

<hr>

<h2>Inloggen</h2>

<?php if ($loginFout !== ""): ?>
    <p style="color: red;">
        <?= htmlspecialchars($loginFout, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </p>
<?php endif; ?>

<form method="post">
    <label for="login_naam">Gebruikersnaam:</label><br>
    <input type="text" name="login_naam" id="login_naam" required><br><br>

    <label for="login_wachtwoord">Wachtwoord:</label><br>
    <input type="password" name="login_wachtwoord" id="login_wachtwoord" required><br><br>

    <input type="submit" name="login" value="Inloggen">
</form>

<hr>

<h2>Registreren als klant</h2>

<?php if ($registratieFout !== ""): ?>
    <p style="color: red;">
        <?= htmlspecialchars($registratieFout, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </p>
<?php endif; ?>

<?php if ($registratieSucces !== ""): ?>
    <p style="color: green;">
        <?= htmlspecialchars($registratieSucces, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </p>
<?php endif; ?>

<form method="post">
    <label for="reg_naam">Gebruikersnaam:</label><br>
    <input
        type="text"
        name="reg_naam"
        id="reg_naam"
        value="<?= isset($reg_naam) ? htmlspecialchars($reg_naam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>"
        required
    ><br><br>

    <label for="reg_wachtwoord">Wachtwoord:</label><br>
    <input type="password" name="reg_wachtwoord" id="reg_wachtwoord" required><br><br>

    <label for="reg_voornaam">Voornaam:</label><br>
    <input
        type="text"
        name="reg_voornaam"
        id="reg_voornaam"
        value="<?= isset($reg_voornaam) ? htmlspecialchars($reg_voornaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>"
        required
    ><br><br>

    <label for="reg_achternaam">Achternaam:</label><br>
    <input
        type="text"
        name="reg_achternaam"
        id="reg_achternaam"
        value="<?= isset($reg_achternaam) ? htmlspecialchars($reg_achternaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>"
        required
    ><br><br>

    <input type="submit" name="registreer" value="Registreren">
</form>

<p><a href="privacy.php">Privacyverklaring</a></p>

</body>
</html>
