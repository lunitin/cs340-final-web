<?php
/*********************************************************************
** Program Filename: addbucket.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide Bucket form to the frontend via JSON
********************************************************************/
include_once("../config.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

  $form = new HTML_QuickForm2('add_bucket', 'POST');

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

  if (isset($_GET["bucket_id"])) {
    $defaults = load_bucket( (int) $_GET["bucket_id"]);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array($defaults));
    $form->addElement('hidden', 'bucket_id');
  }


  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset')->addClass('form-horizontal');

  $name = $fieldset->addElement(
                  ('text'),
                  'bucket_name',
                  array('size' => 50))
                 ->setLabel('Bucket Name:')
                 ->addClass('form-control')
                 ->addRule('required', 'Bucket Name is required');

   $title = $fieldset->addElement(
                   ('text'),
                   'bucket_title',
                   array('size' => 50))
                  ->setLabel('Bucket Title:')
                  ->addClass('form-control')
                  ->addRule('required', 'Bucket Title is required');

  $code = 200;
  if ($form->validate()) {

    if (isset($_POST["bucket_id"])) {
      $action = update_bucket();
      $resp['bucket_id'] = $_POST["bucket_id"];
    } else {
      $action = create_bucket();
      $resp['bucket_id'] = $GLOBALS["db"]->lastInsertId();
    }

    if ($action) {
      $code = '201';
      $resp['bucket_id'] = $GLOBALS["db"]->lastInsertId();
    } else {
      $code = '501';
    }

  }

  // Create a JSON object to send back to the frontend
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
** Function: create_bucket
** Description: Create a new task bucket
** Return: Boolean - result of the insert statement
*********************************************************************/
function create_bucket() {

  try {

    $sql = $GLOBALS["db"]->prepare('SELECT MAX(sort_weight) as next
                                    FROM buckets
                                    WHERE user_id=:user_id');
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->execute();
    $row = $sql->fetch();

    $weight = $row["next"] + 10;

    $sql = $GLOBALS["db"]->prepare('INSERT INTO buckets
                          (user_id, bucket_name, bucket_title, sort_weight)
                          VALUES
                          (:user_id, :bucket_name, :bucket_title, :sort_weight)');


    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_name', $_POST["bucket_name"]);
    $sql->bindParam(':bucket_title', $_POST["bucket_title"]);
    $sql->bindParam(':sort_weight', $weight);
    $sql->execute();

    $_SESSION["msg"]["success"][] = "Added Bucket ". $_POST["bucket_name"];


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on insert.";
      return false;
  }

}

/*********************************************************************
** Function: update_bucket
** Description: Update an existing bucket
** Return: Boolean - result of the update query
*********************************************************************/
function update_bucket() {

  try {

    $sql = $GLOBALS["db"]->prepare('UPDATE buckets
                          SET
                          bucket_name = :bucket_name,
                          bucket_title = :bucket_title
                          WHERE
                          bucket_id = :bucket_id
                          AND user_id = :user_id');

    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_name', $_POST["bucket_name"]);
    $sql->bindParam(':bucket_title', $_POST["bucket_title"]);
    $sql->execute();

    if ($sql->rowCount() > 0) {
        return true;
    } else {
        return false;
    }


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on update.";
    return false;
  }
}

/*********************************************************************
** Function: load_bucket
** Description: Load a single bucket from the db
** Return: array - result of the select query
*********************************************************************/
function load_bucket($bucket_id) {

  try {

    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM buckets
                                    WHERE user_id=:user_id
                                    AND bucket_id=:bucket_id');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':bucket_id', $bucket_id);
    $sql->execute();

    $row = $sql->fetch();
    return $row;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on select.";
    return false;
  }

}

?>
