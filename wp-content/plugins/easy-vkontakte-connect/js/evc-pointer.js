jQuery(document).ready( function($) {
  evc_open_pointer(0);
  function evc_open_pointer(i) {
    pointer = evcPointer.pointers[i];
    options = $.extend( pointer.options, {
      close: function() {
        $.post( ajaxurl, {
          pointer: pointer.pointer_id,
          action: 'dismiss-wp-pointer'
        });
      }
    });
 
    $(pointer.target).pointer( options ).pointer('open');
  }
});