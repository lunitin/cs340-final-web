$( document ).ready(function() {


  // BUCKET EVENTS
  $('#addBucket').on('show.bs.modal', function (e) {
    $.get( "forms/addbucket.php", function( data ) {
      parseFormResponse(data, "#addBucketFormContainer", "#addBucket" );
    });

  })

  // Submit button function handler
  $('#submitAddBucket').click( function(data) {
    data = {};
    // Collect form data and send it back to QuickForm
    $('#add_bucket input').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );
    // POST form data back to PHP and update bucket containers
    jQuery.post( "forms/addbucket.php", data, function( data ) {
      parseFormResponse(data, "#addBucketFormContainer", "#addBucket" );
      fetchBuckets();
    });
  });


  // CATEGORY EVENTS

  // Fetch the form and put it in the modal on click
  $('#addCategory').on('show.bs.modal', function (e) {

      jQuery.get( "forms/addcategory.php", function( data ) {
        parseFormResponse(data, "#addCategoryFormContainer", "#addCategory" );
      });
    })

  // Submit button function handler
  $('#submitAddCategory').click( function(data) {
    data = {};
    // Collect form data and send it back to QuickForm
    $('#add_category input, #add_category select').each(
        function(index){
            var input = $(this);
            data[input.attr('name')] = input.val();
        }
    );

    // POST form data back to PHP
    jQuery.post( "forms/addcategory.php", data, function( data ) {
      parseFormResponse(data, "#addCategoryFormContainer", "#addCategory" );
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
function parseFormResponse(data, elem, modal) {
  res = JSON.parse(data);
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
            console.log(alerts['danger'][j]);
            $('#messages').append(buildAlert('alert-danger', alerts['danger'][j]))
          }
      }
    }
    if (! $.isEmptyObject(alerts['success'])) {
      if (Object.keys(alerts['success']).length > 0) {
        for (var j = 0; j < alerts['success'].length; j++) {
            console.log(alerts['success'][j]);
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
