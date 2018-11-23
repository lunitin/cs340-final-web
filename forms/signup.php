<?php
/*********************************************************************
** Program Filename: sign-up.php
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

  $field_def = array('name' => 'Name',
                     'email'     => 'E-mail address');

  $fieldset = $form->addElement('fieldset')->setLabel('Create Account');

  $age = $fieldset->addElement('text', 'name',
                 array('size' => 40))
                ->setLabel('Name:')
                ->addRule('Required', 'Name is required');


  // Add unique elements to the form with unique paramters and rules
  $user = $fieldset->addElement('text', 'email',
                  array('size' => 50, 'maxlength' => 20))
                 ->setLabel('Email Address:');

  $user->addRule('required', 'Email is required')
       ->and_($user->addRule('callback', 'Email already exists', 'check_user'));

  // Leverage browser enforced length limits
  $pass = $fieldset->addElement('password', 'password',
                  array('size' => 50, 'minlength' => 6, 'maxlength' => 40))
                 ->setLabel('Password: (minimum length 6, maximum length 40)');

  // Add PHP based limits to stop direct _POST attempts from bypassing rules
  $pass->addRule('required', 'Password is required')
       ->and_($pass->addRule('minlength', 'Minimum length is 6', 6))
       ->and_($pass->addRule('maxlength', 'Maximum length is 40', 40));


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

  return $html;
  //return str_replace('CONTENT', $html, file_get_contents("template/signup.html"));


}


/*********************************************************************
** Function: save_user
** Description: Attempt to save a new user to the database.
** Return: Boolean - status of insert query
*********************************************************************/
function save_user() {

  // Extract the password and hash it
  // @NOTE: salt parameter has been deprecated, will use internal salt algo
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Construct an INSERT PDO query and send it to the DB
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
     $_SESSION["msg"]["error"][] = "ERROR: There was a problem processing your registration.";

   }

  } catch (\PDOException $e) {
     $_SESSION["msg"]["error"][]  = "ERROR: ". $e->getMessage();
  }

   return false;
}

/*********************************************************************
** Function: check_user
** Description: Check if a user exists in the database
** Return: Boolean - status of insert query
*********************************************************************/
function check_user() {
  // Construct an INSERT PDO query and send it to the DB
  try {

    $sql = $GLOBALS['db']->prepare('SELECT email
                          FROM user
                          WHERE email=:email');

    $sql->bindParam(':email', $_POST['email']);

    $sql->execute();

    $row = $sql->fetch();

    return ($row['email'] != $_POST["email"]);


  } catch (\PDOException $e) {
     print "<div class=\"error\"><span class=\"error\">ERROR: ". $e->getMessage() ."</span></div>\n";
  }
}


?>
