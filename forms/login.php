<?php


function form_login() {
  $form = new HTML_QuickForm2('login', 'POST', array('action' => '/'));

  // Define all fields and add them all to the form
  $field_def = array('email'  => 'Enter your E-mail address:',
                     'pass'  => 'Enter a password:');

  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset')->setLabel('User Information')->addClass('form-horizontal');

  foreach ($field_def as $name => $msg) {
    $fields[$name] = $fieldset->addElement(
                    ( $name == 'pass' ? 'password' : 'text'),
                    $name,
                    array('size' => 50))
                   ->setLabel($msg)
                   ->addRule('required', $name .' is required');
  }

  $fieldset->addElement('static')->setContent('
    <a href="/signup" class="btn btn-success active" role="button">Sign Up</a>
    <input type="submit" class="btn btn-primary" value="Login">');

  if ($form->validate()) {

    // Check if the input password matches the hash from the database
    if (authenticate_user()) {
      $_SESSION["msg"]["success"][] = "Welcome back ". $_SESSION["user"]["name"] . "!";
    }

    header("Location: /");
    exit;
  }
  return $form;

}

/*********************************************************************
** Function: authenticate_user
** Description: Attempt to authenticate a user against the DB.
** Return: Boolean - result of the password comparison
*********************************************************************/
function authenticate_user() {

  try {
    $sql = $GLOBALS["db"]->prepare('SELECT *
                          FROM user
                          WHERE email=:email');

    $sql->bindParam(':email', $_POST["email"]);
    $sql->execute();
    $row = $sql->fetch();

    // If the supplied password matches they have authenticated
    if (count($row) > 0 && password_verify($_POST['pass'], $row['pass'])) {

      // Store the user data for later reference
      $_SESSION["user"]["user_id"] = $row["user_id"];
      $_SESSION["user"]["email"] = $row["email"];
      $_SESSION["user"]["name"] = $row["name"];

      // Create a simple token to verify that the browser is logged in
      $_SESSION["user"]["token"] = password_hash(TOKEN_SALT .  $row["user_id"], PASSWORD_DEFAULT);
      return true;
    }

    $_SESSION["msg"]["error"][] = "ERROR: Log-in failure.";
    return false;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["error"][] = "ERROR: PDO Exception";
      return false;
  }


}

?>
