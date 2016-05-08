jQuery(function($){
  $('#payment-modal').on('show.bs.modal', function(e) {
    var $body = $(this).find('.modal-body');
    $body.load($(e.relatedTarget).attr('href') + ' #bd .content');
  });
});
