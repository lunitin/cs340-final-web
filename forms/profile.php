<?php
/*********************************************************************
** Program Filename: profile.php
** Author: Casey Dinsmore
** Date: 2018-11-09
** Description: Provide a simple form to display and update user data.
********************************************************************/
include_once("../config.php");
include_once('../common.php');

$resp = array();

// Check for authentication token
if (verify_login()) {


  $form = new HTML_QuickForm2('update_profile', 'POST');

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

  // Set defaults for the form elements
  $form->addDataSource(new HTML_QuickForm2_DataSource_Array($_SESSION["user"]));
  $form->addElement('hidden', 'user_id');


  $fieldset = $form->addElement('fieldset');

  $age = $fieldset->addElement('text', 'name')
                ->setLabel('Name:')
                ->addClass('form-control')
                ->addRule('Required', 'Name is required');


  // Add unique elements to the form with unique paramters and rules
  $user = $fieldset->addElement('text', 'email',
                  array('maxlength' => 20, 'disabled' => true))
                  ->addClass('form-control')
                 ->setLabel('Email Address:');

  // Leverage browser enforced length limits

    $curpass = $fieldset->addElement('password', 'cur_password',
                    array('minlength' => 6, 'maxlength' => 40))
                    ->addClass('form-control')
                   ->setLabel('Current Password:');

    $newpass = $fieldset->addElement('password', 'new_password',
                    array('minlength' => 6, 'maxlength' => 40))
                    ->addClass('form-control')
                   ->setLabel('New Password: (minimum length 6, maximum length 40)');

     $newpass2 = $fieldset->addElement('password', 'new_password2',
                     array('minlength' => 6, 'maxlength' => 40))
                     ->addClass('form-control')
                    ->setLabel('Confirm New Password:');

  // Only add password rules if the user filled out the fields
  if (!empty($_POST["cur_password"]) ||
      !empty($_POST["new_password"]) ||
      !empty($_POST["new_password2"])
    ) {

    $curpass->addRule('required', 'Current password is required')
           ->and_($curpass->addRule('callback', 'Current password incorrect.', 'check_curpass'));

    $newpass->addRule('required', 'New Password is required')
         ->and_($newpass->addRule('minlength', 'Minimum length is 6', 6))
         ->and_($newpass->addRule('maxlength', 'Maximum length is 40', 40));

    $newpass2->addRule('callback', 'New passwords do not match.', 'check_newpass');

  }


  $code = 200;
  if ($form->validate()) {
    if (update_user()) {
      $code = 201;
    }
  }

  ob_start();
  print $form->render($r);
  $resp['html'] = ob_get_clean();

} else {
  $code = 500;
  $_SESSION["msg"]["danger"][] = "Not authorized.";
}

$resp['code'] = $code;
$resp['messages'] = $_SESSION["msg"];
$_SESSION["msg"] = array();

print json_encode($resp);



/*********************************************************************
** Function: update_user
** Description: Attempt to update user information.
** Return: Boolean - status of update query
*********************************************************************/
function update_user() {

  // Extract the password and hash it
  // @NOTE: salt parameter has been deprecated, will use internal salt algo
  //$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Construct an INSERT PDO query and send it to the DB
  try {

    // No password changes
    if (empty($_POST["cur_password"])) {
      $sql = $GLOBALS["db"]->prepare('UPDATE user
                                      SET
                                      name = :name
                                      WHERE
                                      user_id = :user_id');

    } else {
      // Make sure the password passes validation again to prevent abuse
      if (check_curpass()) {
        $sql = $GLOBALS["db"]->prepare('UPDATE user
                                        SET
                                        name = :name,
                                        pass = :pass
                                        WHERE
                                        user_id = :user_id');

        $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $sql->bindParam(':pass', $pass);

      } else {
        $_SESSION["msg"]["danger"][] = "There was a problem processing your update.";
        return false;
      }
    }

    $sql->bindParam(':name', $_POST['name']);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);

   if ($sql->execute()) {
     $_SESSION["msg"]["success"][] = $_POST["name"] . ", your profile has been updated.";
     $_SESSION["user"]["name"] = $_POST["name"];
     return true;
   } else {
     $_SESSION["msg"]["danger"][] = "There was a problem processing your update.";
   }

  } catch (\PDOException $e) {
     $_SESSION["msg"]["danger"][]  = "ERROR: PDO Insert Error";
  }

   return false;
}




/*********************************************************************
** Function: check_curpass
** Description: Check if the current password matches
** Return: Boolean - status of check
*********************************************************************/
function check_curpass() {

  // Construct an INSERT PDO query and send it to the DB
  try {

    $sql = $GLOBALS["db"]->prepare('SELECT *
                          FROM user
                          WHERE user_id=:user_id');

    $sql->bindParam(':user_id', $_SESSION['user']['user_id']);
    $sql->execute();
    $row = $sql->fetch();

    // If the supplied password matches they have authenticated
    if (count($row) > 0 && password_verify($_POST['cur_password'], $row['pass'])) {
      return true;
    }

    return false;

  } catch (\PDOException $e) {
      $_SESSION["msg"]["danger"][]  = "ERROR: PDO error checking current password.";
  }

  return false;
}

/*********************************************************************
** Function: check_newpass
** Description: Check if the new passwords match
** Return: Boolean - status of check
*********************************************************************/
function check_newpass() {

  // Construct an INSERT PDO query and send it to the DB
  if ($_POST["new_password"] == $_POST["new_password2"]) {
    return true;
  }

  return false;
}


?>
