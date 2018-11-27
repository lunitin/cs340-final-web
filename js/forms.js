$( document ).ready(function() {


  // BUCKET EVENTS
  $('#addBucket').on('show.bs.modal', function (e) {

    var caller = $(e.relatedTarget);
    var bucket_id = '';

    if (caller.attr('data-bucket-id')) {
     bucket_id = "?bucket_id="+ caller.attr('data-bucket-id');
     $('#submitAddBucket').html('Update Bucket');
   } else {
     $('#submitAddBucket').html('Add Bucket');
   }

    $.get( "forms/addbucket.php" + bucket_id, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#addBucketFormContainer", "#addBucket" );
    });

  })

  // Submit button function handler
  $('#submitAddBucket').click( function(data) {
    data = {};


    // Collect form data and send it back to QuickForm
    $('#add_bucket input, #add_bucket hidden').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );
    // POST form data back to PHP and update bucket containers
    jQuery.post( "forms/addbucket.php", data, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#addBucketFormContainer", "#addBucket" );
      fetchBuckets();
    });
  });


  // CATEGORY EVENTS

  // Fetch the form and put it in the modal on click
  $('#addCategory').on('show.bs.modal', function (e) {

    var bucket_id = $('#buckets li.active').attr('data-bucket-id');

    var category_id = '';
    caller = $(e.relatedTarget);
    if (caller.attr('data-category-id')) {
     category_id = "&category_id="+ caller.attr('data-category-id');
     $('#submitAddCategory').html('Update Category');
   } else {
     $('#submitAddCategory').html('Add Category');
   }

    jQuery.get( "forms/addcategory.php?bucket_id="+ bucket_id + category_id, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#addCategoryFormContainer", "#addCategory" );
    });
  });

  // Submit button function handler
  $('#submitAddCategory').click( function(data) {
    data = {};
    // Collect form data and send it back to QuickForm
    $('#add_category input, #add_category select,  #add_category hidden').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );

    data['bucket_id'] = $('#buckets li.active').attr('data-bucket-id');

    // POST form data back to PHP
    jQuery.post( "forms/addcategory.php", data, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#addCategoryFormContainer", "#addCategory" );

      // If we had a successful insert, rebuild category view for this bucket
      if (res.code == 201) {
        fetchCategories($('#buckets li.active').attr('data-bucket-id'));
      }
    });
  });

  // TASK EVENTS
  $('#addTask').on('show.bs.modal', function (e) {

      // Save the category id of the caller on the submit button
      // for easy access on form submit
      caller = $(e.relatedTarget);
      $('#submitAddTask').attr('data-category-id', caller.attr('data-category-id'));

      var bucket_id = $('#buckets li.active').attr('data-bucket-id');
      var category_id = caller.attr('data-category-id');
      var task_id = '';

      if (caller.attr('data-task-id')) {
        task_id = "&task_id="+ caller.attr('data-task-id');
        $('#submitAddTask').html('Update Task');
      } else {
        $('#submitAddTask').html('Add Task');
      }

      jQuery.get( "forms/addtask.php?bucket_id="+bucket_id+"&category_id="+category_id + task_id, function( data ) {
        res = JSON.parse(data);
        parseFormResponse(res, "#addTaskFormContainer", "#addTask" );
      });
    })

  // Submit button function handler
  $('#submitAddTask').click( function(data) {
    data = {};
    // Collect form data and send it back to QuickForm
    $('#add_task input, #add_task select, #add_task textarea,  #add_task hidden').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );
    data['bucket_id'] = $('#buckets li.active').attr('data-bucket-id');
    data['category_id'] = $('#submitAddTask').attr('data-category-id');

    // POST form data back to PHP
    jQuery.post( "forms/addtask.php", data, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#addTaskFormContainer", "#addTask" );

      // If we had a successful insert, inject a new task object
      if (res.code == 201) {
        fetchCategories($('#buckets li.active').attr('data-bucket-id'));
      }

    });
  });



  // PROFILE EVENTS
  $('#updateProfile').on('show.bs.modal', function (e) {

    $.get( "forms/profile.php", function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#updateProfileFormContainer", "#updateProfile" );
    });

  })

  // Submit button function handler
  $('#submitUpdateProfile').click( function(data) {
    data = {};

    // Collect form data and send it back to QuickForm
    $('#update_profile input').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );
    // POST form data back to PHP
    jQuery.post( "forms/profile.php", data, function( data ) {
      res = JSON.parse(data);
      parseFormResponse(res, "#updateProfileFormContainer", "#updateProfile" );
    });
  });


});


/*********************************************************************
** Function: parseFormResponse
** Description: Handler to parse form results and either close the
**     modal and display messages or display the form html.
** Paramaters: data - response from backend
**             elem - ID of form html container
**             modal - ID of modal container
** Return: none
*********************************************************************/
function parseFormResponse(res, elem, modal) {

  if (res.code < 300) {
    $(elem).html(res.html);
  }

  if (res.code == 201 || res.code == 501) {
    $(modal).modal('hide');
  }

  parseAlerts(res.messages);

}

/*********************************************************************
** Function: parseAlerts
** Description: Handler to parse success and error alerts and display
**     them in the messages container.
** Parameters: alerts object
** Return: none
*********************************************************************/
function parseAlerts(alerts) {

    if (! $.isEmptyObject(alerts['danger'])) {
      if (Object.keys(alerts['danger']).length > 0) {
        for (var j = 0; j < alerts['danger'].length; j++) {
            $('#messages').append(buildAlert('alert-danger', alerts['danger'][j]))
          }
      }
    }
    if (! $.isEmptyObject(alerts['success'])) {
      if (Object.keys(alerts['success']).length > 0) {
        for (var j = 0; j < alerts['success'].length; j++) {
            $('#messages').append(buildAlert('alert-success', alerts['success'][j]))
          }
      }
    }
    // Add auto fade out of messages after a bit of time
    $(".alert").fadeTo(3500, 600).slideUp(400, function () {
        $(".alert").slideUp(600);
        $(".alert").remove();
    });

}

/*********************************************************************
** Function: buildAlert
** Description: Return the HTML needed for a bootstrap alert
** Parameters: type of alert, and the message to display
** Return: none
*********************************************************************/
function buildAlert(type, message) {
  return '<div class="alert '+ type +' alert-dismissible fade show" role="alert">'
    + message
    + '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
    + '  <span aria-hidden="true">&times;</span> '
    + '</button></div>';
}
