<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if($lang == "de" || $lang == "ch" || $lang == "at"){
    require("./languages/de.php");
} else {
    require("./languages/en.php");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $messages["set_new_password"] ?></title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
  </head>
  <body>
    <form class="login" action="resetpassword.php?name=<?php echo $_GET["name"] ?>" method="post">
      <?php
      session_start();
      if(!isset($_SESSION['username'])){
        header("Location: login.php");
        exit;
      }
      if(!isset($_GET["name"])){
        ?>
        <div class="error">
          <h4>Es wurde keine Anfrage gestellt.</h4>
        </div>
        <?php
        exit;
      }
      if(isset($_POST["submit"]) && isset($_POST["CSRFToken"])){
        require("./mysql.php");
        if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
          ?>
          <div class="error">
            <h4><?php echo $messages["csrf_err"] ?></h4>
          </div>
          <?php
          exit;
        }
        if($_POST['pw1'] == $_POST['pw2']){
          $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :username");
          $stmt->bindParam(":username", $_GET["name"], PDO::PARAM_STR);
          $stmt->execute();
          $data = 0;
          while($row = $stmt->fetch()){
            $data++;
          }
          if($data == 1){
            $stmt = $mysql->prepare("SELECT AUTHCODE FROM accounts WHERE USERNAME = :username");
            $stmt->bindParam(":username", $_GET["name"], PDO::PARAM_STR);
            $stmt->execute();
            $data = 0;
            while($row = $stmt->fetch()){
              if($row["AUTHCODE"] == "initialpassword"){
                $PWHash = password_hash($_POST['pw1'], PASSWORD_BCRYPT);
                $stmt = $mysql->prepare("UPDATE accounts SET PASSWORD=:hash, AUTHCODE='null' WHERE USERNAME=:username");;
                $stmt->bindParam(":hash", $PWHash, PDO::PARAM_STR);
                $stmt->bindParam(":username", $_GET["name"], PDO::PARAM_STR);
                $stmt->execute();
                header("Location: index.php");
              } else {
                ?>
                <div class="error">
                  <h4>Das Passwort für diesen Account kann nicht gesetzt werden.</h4>
                </div>
                <?php
              }
            }
          } else {
            ?>
            <div class="error">
              <h4>Dieser Account existiert nicht.</h4>
            </div>
            <?php
          }
        } else {
          ?>
          <div class="error">
            <h4><?php echo $messages["password_not_same"] ?></h4>
          </div>
          <?php
        }
      } else {
        //Erstelle Token wenn Formular nicht abgesendet wurde
        require("datamanager.php");
        $_SESSION["CSRF"] = generateRandomString(25);
      }
       ?>
      <h1 id="pw"><i class="fas fa-key"></i> <?php echo $messages["set_new_password"] ?></h1>
      <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
      <input type="password" name="pw1" placeholder="<?php echo $messages["new_password"] ?>" minlength="6" autocomplete="new-password" required><br>
      <input type="password" name="pw2" placeholder="<?php echo $messages["new_password2"] ?>" minlength="6" autocomplete="new-password" required><br>
      <button type="submit" name="submit"><?php echo $messages["set_new_password"] ?></button><br>
    </form>
  </body>
</html>
