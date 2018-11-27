<?php
/*********************************************************************
** Program Filename: addcategory.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide category form to the frontend via JSON.
********************************************************************/
include_once("../config.php");
include_once('../common.php');

$resp = array();

// Check for authentication token
if (verify_login()) {


  $form = new HTML_QuickForm2('add_category', 'POST');

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

  // Set the default values, this is used to auto select categories
  // and allows the same form to be re-used for an edit operation
  if (isset($_GET["category_id"])) {
    $defaults = load_cat( (int) $_GET["category_id"]);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array($defaults));
    $form->addElement('hidden', 'category_id');
  } else {
    $defaults = ( count($_POST) > 0 ? $_POST : $_GET);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
        'bucket_id' => (  isset($_GET['bucket_id']) ? (int) $_GET['bucket_id'] : ''),
    )));
  }





  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset')->addClass('form-horizontal');

  $name = $fieldset->addElement(
                  ('text'),
                  'category_name',
                  array('size' => 50))
                 ->setLabel('Category Name:')
                 ->addClass('form-control')
                 ->addRule('required', 'Category Name is required');

   // Fetch the bucket list and format for the select list
   $bucks = fetch_buckets();
   foreach($bucks as $k => $v) {
     $bopts[$v["bucket_id"]] = $v["bucket_name"];
   }
   $buckets = $fieldset->addElement(
                   'select',
                   'bucket_id')
                   ->loadOptions($bopts)
                  ->setLabel('Bucket:')
                  ->addClass('form-control')
                  ->addRule('required', 'Bucket is required');

  $code = 200;
  if ($form->validate()) {

      if (isset($_POST["category_id"])) {
        $action = update_cat();
        $resp['category_id'] = $_POST["category_id"];
      } else {
        $action = create_cat();
        $resp['category_id'] = $GLOBALS["db"]->lastInsertId();
      }

      // Check if the input password matches the hash from the database
      if ($action) {
        $code = 201;
      } else {
        $code = 501;
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
function create_cat() {

  try {

    $sql = $GLOBALS["db"]->prepare('SELECT MAX(sort_weight) as next
                                    FROM categories
                                    WHERE user_id=:user_id');
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->execute();
    $row = $sql->fetch();

    $weight = 10 + (int) $row["next"];

    $sql = $GLOBALS["db"]->prepare('INSERT INTO categories
                          ( user_id, bucket_id, category_name, sort_weight)
                          VALUES
                          (:user_id, :bucket_id, :category_name, :sort_weight)');

    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':category_name', $_POST["category_name"]);
    $sql->bindParam(':sort_weight', $weight);
    $sql->execute();

    return true;


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on insert.";
    return false;
  }


}

/*********************************************************************
** Function: update_cat
** Description: Update an existing Category
** Return: Boolean - result of the update query
*********************************************************************/
function update_cat() {

  try {

    $sql = $GLOBALS["db"]->prepare('Update categories
                          SET
                          bucket_id = :bucket_id,
                          category_name = :category_name
                          WHERE
                          category_id = :category_id
                          AND user_id = :user_id');

    $sql->bindParam(':category_id', $_POST["category_id"]);
    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':category_name', $_POST["category_name"]);
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
** Function: load_cat
** Description: Load a single category from the db
** Return: array - result of the select query
*********************************************************************/
function load_cat($cat_id) {

  try {

    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM categories
                                    WHERE user_id=:user_id
                                    AND category_id=:category_id');
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':category_id', $cat_id);
    $sql->execute();

    $row = $sql->fetch();
    return $row;


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on select.";
    return false;
  }



}

?>
