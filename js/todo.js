$(document).ready( function() {

    function toggleFields(id){
        $('#task_view_' + id).toggle();
        $('#task_edit_' + id).toggle();
    }

    // tie form submission to ajax
    $('.editLink').click( function() {
        var id = $(this).attr('link_id');
        toggleFields(id);
        return false;  
    });

    $('.task_edit').focusout( function(){    
        var id = $(this).attr('id');
        var new_value = $(this).val();
        id = id.replace("task_edit_",""); 
        if ($(this).val() != '') {
            $("#task_view_"+id).html(new_value);
            $.ajax({  
                type: "POST",  
                url: "/index.php/tasklist/edit/" + id + "/" + new_value,  
                data: "",  
                success: toggleFields(id)
            }); 
        }
        return false;
    });

    // function on form success
    function formSuccess(data) {
        toggleFields(id);

        if(data.substr(0, 5) === 'error') {
            errorMsg = data.substring(6);
            displayError(errorMsg);
        }
        else {
            $('#task-list').append(data);
            $('#task_name').val('').focus();
        }
    };


});