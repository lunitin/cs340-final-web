<?php
/*********************************************************************
** Program Filename: signup.php
** Author: Casey Dinsmore
** Date: 2018-11-09
** Description: Provide an interface to sign up for an account for the
**              Simple Authentication Service.
********************************************************************/

/*********************************************************************
** Function: show_form
** Description: Render the sign up form as html
** Return: String
*********************************************************************/
function form_signup() {
  $html = "";

  $form = new HTML_QuickForm2('signup', 'POST', array('action' => '/signup'));

  $fieldset = $form->addElement('fieldset');

  $name = $fieldset->addElement('text', 'name')
                ->setLabel('Name:')
                ->addClass('form-control')
                ->addRule('Required', 'Name is required');


  $user = $fieldset->addElement('text', 'email',
                  array('maxlength' => 20))
                  ->addClass('form-control')
                 ->setLabel('Email Address:');

  $user->addRule('required', 'Email is required')
       ->and_($user->addRule('callback', 'Email already exists', 'check_user'));

  // Leverage browser enforced length limits
  $pass = $fieldset->addElement('password', 'password',
                  array('minlength' => 6, 'maxlength' => 40))
                  ->addClass('form-control')
                 ->setLabel('Password: (minimum length 6, maximum length 40)');

  // Add PHP based limits to stop direct _POST attempts from bypassing rules
  $pass->addRule('required', 'Password is required')
       ->and_($pass->addRule('minlength', 'Minimum length is 6', 6))
       ->and_($pass->addRule('maxlength', 'Maximum length is 40', 40));

  // Add Bootstrap style buttons
  $fieldset->addElement('static')->setContent('
       <a href="/" class="btn btn-default active" role="button">Cancel</a>
       <input type="submit" class="btn btn-primary" value="Sign Up">');


  if ($form->validate()) {
    if (save_user()) {
      header("Location: /");
      exit;
    }
  } else {
    $html = $form;
  }

  // Render the form with custom Bootstrap classes
  ob_start();
  print $form->render(fetch_bootstrap_renderer());
  return ob_get_clean();

}


/*********************************************************************
** Function: save_user
** Description: Attempt to save a new user to the database.
** Return: Boolean - status of insert query
*********************************************************************/
function save_user() {

  // Extract the password and hash it
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  try {

    $sql = $GLOBALS["db"]->prepare('INSERT INTO user
                                 (name,  email,  pass)
                          VALUES (:name, :email, :pass)');

    $sql->bindParam(':name', $_POST['name']);
    $sql->bindParam(':email', $_POST['email']);
    $sql->bindParam(':pass', $password);

   if ($sql->execute()) {
     $_SESSION["msg"]["success"][] = "User ".$_POST["email"]." successfully added.";
     return true;
   } else {
     $_SESSION["msg"]["danger"][] = "There was a problem processing your registration.";
   }

  } catch (\PDOException $e) {
     $_SESSION["msg"]["danger"][]  = "ERROR: PDO Insert Error";
  }

   return false;
}


/*********************************************************************
** Function: check_user
** Description: Check if a user exists in the database
** Return: Boolean - status of insert query
*********************************************************************/
function check_user() {

  try {

    $sql = $GLOBALS['db']->prepare('SELECT email
                          FROM user
                          WHERE email=:email');

    $sql->bindParam(':email', $_POST['email']);
    $sql->execute();
    $row = $sql->fetch();

    return ($row['email'] != $_POST["email"]);


  } catch (\PDOException $e) {
     $_SESSION["msg"]["danger"][]  = "ERROR: PDO Insert Error";
     return false;
  }
}


?>
