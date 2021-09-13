jQuery(document).ready(function($) {
  "use strict";

  var $table_sidebars = $('#kungfu-table-sidebars');

  $table_sidebars.on('click', '.kungfu-remove-sidebar', function(e) {
    var row = $(this).parents('tr').first();
    var sidebar_name = row.find('td').eq(0).text();
    var sidebar_class = row.find('td').eq(1).text();
    var answer = confirm("Are you sure you want to remove \"" + sidebar_name + "\" ?\nThis will remove any widgets you have assigned to this sidebar.");
    if (answer) {

      var data = {
        action: 'remove_sidebar',
        sidebar_class: sidebar_class
      };

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function(data) {
          data = $.parseJSON(data);
          if (data.status) {
            row.remove();
          } else {
            alert(data.messages);
          }
        }
      });
    } else {
      return false;
    }
  });

  $('#kungfu-add-sidebar').on('click', function(e) {
    var sidebar_name_input = $('#sidebar_name');
    var sidebar_name = sidebar_name_input.val();

    var data = {
      action: 'add_sidebar',
      sidebar_name: sidebar_name
    };

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(data) {
        data = $.parseJSON(data);
        console.log(data);
        if (data.status) {
          $table_sidebars.append('<tr><td>' + sidebar_name + '</td><td>' + data.messages + '</td><td><a href="javascript:void(0);" class="button kungfu-remove-sidebar"><i class="fa fa-remove"></i>Remove</a></td></tr>');
          sidebar_name_input.val('');
        } else {
          alert(data.messages);
        }
      }
    });
  });

});
