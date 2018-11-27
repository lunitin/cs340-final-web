<?php
/*********************************************************************
** Program Filename: addtask.php
** Author: Casey Dinsmore
** Date: 2018-11-22
** Description: Provide Add/Edit task form to the frontend encoded
**              as JSON
********************************************************************/
include_once("../config.php");
include_once("../common.php");

$resp = array();

// Check for authentication token
if (verify_login()) {

  $form = new HTML_QuickForm2('add_task', 'POST');

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
  if (isset($_GET["task_id"])) {
    $defaults = load_task( (int) $_GET["task_id"]);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array($defaults));
    $form->addElement('hidden', 'task_id');
  } else {
    $defaults = ( count($_POST) > 0 ? $_POST : $_GET);
    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
        'bucket_id' => (  isset($defaults['bucket_id']) ? (int) $defaults['bucket_id'] : ''),
        'category_id' => ( isset($defaults['category_id']) ? (int) $defaults['category_id']  : '')
    )));
  }


  // Create a field set and add all fields to the form
  $fieldset = $form->addElement('fieldset')->addClass('form-horizontal');

  $name = $fieldset->addElement(
                 ('text'),
                 'task_name',
                 array('size' => 50))
                ->setLabel('Task Name:')
                ->addClass('form-control')
                ->addRule('required', 'Task Name is required');


  $title = $fieldset->addElement(
                  ('textarea'),
                  'task_details',
                  array('size' => 50))
                  ->addClass('form-control')
                 ->setLabel('Task Details:');


  // Fetch the bucket list and format for the select list
  $bopts = array();
  $bucks = fetch_buckets();
  foreach($bucks as $k => $v) {
    $bopts[$v["bucket_id"]] = $v["bucket_name"];
  }
  $bucket = $fieldset->addElement(
                  'select',
                  'bucket_id')
                  ->loadOptions($bopts)
                 ->setLabel('Bucket:')
                 ->addClass('form-control')
                 ->addRule('required', 'Bucket is required');

  // Fetch the categopy list and format for the select list
  $copts = array();
  $cats = fetch_categories((int)$defaults['bucket_id']);
  foreach($cats as $k => $v) {
    $copts[$v["category_id"]] = $v["category_name"];
  }
  $category = $fieldset->addElement(
                 'select',
                 'category_id')
                 ->loadOptions($copts)
                ->setLabel('Category:')
                ->addClass('form-control')
                ->addRule('required', 'Category is required');



  $code = 200;
  if ($form->validate()) {

      if (isset($_POST["task_id"])) {
        $action = update_task();
        $resp['task']['task_id'] = (int) $_POST["task_id"];
      } else {
        $action = create_task();
        $resp['task']['task_id'] = $GLOBALS["db"]->lastInsertId();
      }

      // Check if the db query was a success
      if ($action) {
        $code = 201;
        $resp['task'] = $_POST;

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
** Function: create_task
** Description: Create a new task task
** Return: Boolean - result of the insert statement
*********************************************************************/
function create_task() {
  try {

    $sql = $GLOBALS["db"]->prepare('SELECT MAX(sort_weight) as next
                                    FROM tasks
                                    WHERE category_id=:category_id
                                    AND bucket_id=:bucket_id');

    $sql->bindParam(':category_id', $_POST["category_id"]);
    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->execute();
    $row = $sql->fetch();

    $weight = $row["next"] + 10;

    $sql = $GLOBALS["db"]->prepare('INSERT INTO tasks
                          (user_id, category_id, bucket_id, task_name, task_details, sort_weight, created_date)
                          VALUES
                          (:user_id, :category_id, :bucket_id, :task_name, :task_details, :sort_weight, NOW())');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':category_id', $_POST["category_id"]);
    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':task_name', $_POST["task_name"]);
    $sql->bindParam(':task_details', $_POST["task_details"]);
    $sql->bindParam(':sort_weight', $weight);
    $sql->execute();

    return true;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on Insert";

  }

  return false;
}


/*********************************************************************
** Function: update_task
** Description: Create a new task task
** Return: Boolean - result of the insert statement
*********************************************************************/
function update_task() {
  try {


    $sql = $GLOBALS["db"]->prepare('UPDATE tasks
                          SET
                          category_id = :category_id,
                          bucket_id = :bucket_id,
                          task_name = :task_name,
                          task_details = :task_details
                          WHERE task_id = :task_id
                          AND user_id = :user_id');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':task_id', $_POST["task_id"]);
    $sql->bindParam(':category_id', $_POST["category_id"]);
    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':task_name', $_POST["task_name"]);
    $sql->bindParam(':task_details', $_POST["task_details"]);
    $sql->execute();


    if ($sql->rowCount() > 0) {
        return true;
    } else {
        return false;
    }

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on Update";

  }

  return false;
}




/*********************************************************************
** Function: load_task
** Description: Load details of a task
** Return: Boolean - result of the insert statement
*********************************************************************/
function load_task($task_id) {
  try {

    $sql = $GLOBALS["db"]->prepare('SELECT *
                                    FROM tasks
                                    WHERE task_id=:task_id
                                    AND user_id=:user_id');

    $sql->bindParam(':task_id', $task_id);
    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->execute();

    return $sql->fetch();

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on load task";

  }

  return array();
}


?>
