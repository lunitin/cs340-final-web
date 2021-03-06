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

  // Return false to prevent the enter key from taking over POST operation in modal
  $form = new HTML_QuickForm2('add_bucket', 'POST');

  // Set the default values, this allows the same form to
  // be re-used for an edit operation
  if (isset($_GET["bucket_id"])) {
    $defaults = load_bucket( (int) $_GET["bucket_id"]);
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array($defaults));
    $form->addElement('hidden', 'bucket_id');
  }

  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset');

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

  // Render the form with custom Bootstrap classes
  ob_start();
  print $form->render(fetch_bootstrap_renderer());
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
