<?php
include_once("../config.php");
include_once('../common.php');


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



//
// $fieldset->addElement('static')->setContent('
// <a href="/signup" class="btn btn-default active" role="button">Cancel</a>
// <input type="submit" class="btn btn-primary" value="Add Category">');

$code = 200;
if ($form->validate()) {

    // Check if the input password matches the hash from the database
    if (create_cat()) {
      $code = '201';
      $resp['category_id'] = $GLOBALS["db"]->lastInsertId();
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
** Function: create_bucket
** Description: Create a new task bucket
** Return: Boolean - result of the password comparison
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
                          (user_id, category_name, sort_weight)
                          VALUES
                          (:user_id, :category_name, :sort_weight)');

    $sql->bindParam(':user_id', $_SESSION["user"]["user_id"]);
    $sql->bindParam(':category_name', $_POST["category_name"]);
    $sql->bindParam(':sort_weight', $weight);
    $sql->execute();

    $_SESSION["msg"]["success"][] = "Added category ". $_POST["category_name"];

    return true;


  } catch (\PDOException $e) {
    $_SESSION["msg"]["danger"][] = "ERROR: PDO Exception on insert";
    return false;
  }


}

?>
