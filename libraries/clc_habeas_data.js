(function($) {

  Drupal.behaviors.clcHabeasData = {
    attach: function(context, settings) {
      var $inputs = $('form input');

      $.each($inputs, function(key, value) {
        if ($(this).attr('type') == 'tel') {
          $(this).attr('type', 'number');
        }
      });
    }
  }
})(jQuery);
