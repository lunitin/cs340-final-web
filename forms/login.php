<?php
/*********************************************************************
** Program Filename: login.php
** Author: Casey Dinsmore
** Date: 2018-11-09
** Description: Functions to present login form and processing to
**              inject into the HTML template
********************************************************************/


/*********************************************************************
** Function: form_login
** Description: Render the login up form as html
** Return: String
*********************************************************************/
function form_login() {
  $form = new HTML_QuickForm2('login', 'POST', array('action' => '/'));

  // Create a custom renderer for BootStrap
  $r = HTML_QuickForm2_Renderer::factory('callback');
  $r->setCallbackForClass('HTML_QuickForm2_Element', function($renderer, $element) {
      $error = $element->getError();
      if ($error) {
          $html[] = '<div class="clearfix form-group">';
          $element->addClass('is-invalid');
      } else {
          $html[] = '<div class="clearfix form-group">';
      }
      $html[] = $renderer->renderLabel($element->addClass('red'));
      $html[] = '<div class="input">'.$element;
      if ($error) {
          $html[] = '<span class="invalid-feedback">'.$error.'</span>';
      } else {
          $label = $element->getLabel();
        if (is_array($label) && !empty($label[1])) {
              $html[] = '<span class=" valid-feedback">'.$label[1].'</span>';
        }
      }
      $html[] = '</div></div>';
      return implode('', $html);
  });

  // Define all fields and add them all to the form
  $field_def = array('email'  => 'E-mail address:',
                     'pass'  => 'Password:');

  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset')->addClass('form-horizontal');

  foreach ($field_def as $name => $msg) {
    $fields[$name] = $fieldset->addElement(
                    ( $name == 'pass' ? 'password' : 'text'),
                    $name)
                   ->setLabel($msg)
                   ->addClass('form-control')
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

  ob_start();
  print $form->render($r);
  $html = ob_get_clean();
  return $html;

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
      $_SESSION["user"]["token"] = password_hash(TOKEN_SALT .  $row["user_id"] . $row["email"], PASSWORD_DEFAULT);
      return true;
    }

    $_SESSION["msg"]["danger"][] = "Log-in failure.";
    return false;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "PDO Exception";
      return false;
  }


}

?>
