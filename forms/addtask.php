<?php
include_once("config.php");


$form = new HTML_QuickForm2('addtask', 'POST');

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

// Create a field set and add all fields to the form
$fieldset = $form->addElement('fieldset')->setLabel('Create Task')->addClass('form-horizontal');


 $title = $fieldset->addElement(
                 ('text'),
                 'bucket_id',
                 array('size' => 50))
                ->setLabel('Bucket ID:')
                ->addClass('form-control')
                ->addRule('required', 'Bucket Id is required');

$title = $fieldset->addElement(
                ('text'),
                'category_id',
                array('size' => 50))
               ->setLabel('Category ID:')
               ->addClass('form-control')
               ->addRule('required', 'Category Id is required');

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



// $fieldset->addElement('static')->setContent('
// <a href="/signup" class="btn btn-default active" role="button">Cancel</a>
// /<input type="submit" class="btn btn-primary" value="Add task">');

$code = 200;
if ($form->validate()) {

    // Check if the input password matches the hash from the database
    if (create_task()) {
      $code = '201';
      $resp['task_id'] = $GLOBALS["db"]->lastInsertId();
    } else {
      $code = 501;
    }

}

// Create a JSON object to send back to the frontend
ob_start();
print $form->render($r);
$html = ob_get_clean();

$resp['code'] = $code;
$resp['html'] = $html;
$resp['messages'] = $_SESSION["msg"];

$_SESSION["msg"] = array();

print json_encode($resp);

/*********************************************************************
** Function: create_task
** Description: Create a new task task
** Return: Boolean - result of the password comparison
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
    print $weight;

    $sql = $GLOBALS["db"]->prepare('INSERT INTO tasks
                          (category_id, bucket_id, task_name, task_details, sort_weight)
                          VALUES
                          (:category_id, :bucket_id, :task_name, :task_details, :sort_weight)');


    print "category_id:" . $_POST["category_id"];
    print "bucket_id:" . $_POST["bucket_id"];
    print "task_name: " . $_POST["task_name"];
    print "task_details: " . $_POST["task_details"];
    print "sort_weight: " . $weight;


    $sql->bindParam(':category_id', $_POST["category_id"]);
    $sql->bindParam(':bucket_id', $_POST["bucket_id"]);
    $sql->bindParam(':task_name', $_POST["task_name"]);
    $sql->bindParam(':task_details', $_POST["task_details"]);
    $sql->bindParam(':sort_weight', $weight);
    $sql->execute();

    $id = $GLOBALS["db"]->lastInsertId();
    print $id;

  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on Insert";
    //print "Exception" . $e->getMessage();
      return false;
  }


}

?>
