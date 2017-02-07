jQuery(function($) {

  // HTML5 Placeholders for forms
  $('input, textarea').placeholder();

  /*
    Global TABLE functions:
    - Row selection and click action
  */

  // ROW SELECTION AND CLICK ACTION
  $(document)
    // "Select all" toggle.
    .delegate('table :checkbox[name="all"]', 'click', function(e){
      e.stopPropagation();

      var table  = $(this).closest('table');
      var checks = table.find(':checkbox:not([name="all"])');

      if ($(this).is(':checked')) {
        checks.prop('checked', true).closest('tr').addClass('selected');
      } else {
        checks.prop('checked', false).closest('tr').removeClass('selected');
      }
    })
    // All other checkboxes
    .delegate('table :checkbox:not([name="all"])', 'click', function(e){
      e.stopPropagation();

      var table   = $(this).closest('table');
      var all     = table.find(':checkbox[name="all"]');
      var checks  = table.find(':checkbox').not(all);
      var checked = table.find(':checkbox:checked').not(all);

      all.prop('checked', checks.length == checked.length);

      checked.closest('tr').addClass('selected');
      checks.not(checked).closest('tr').removeClass('selected');
    })
    // Row click action
    .delegate('table tr[data-link] td:not(.check, .payments, .no-link)', 'click', function(e){
      document.location.href = $(this).parent().data('link');
    });
  ;

  // Make all btn-danger buttons ask for confirmation.
  $(document).on('click', '[data-confirm]', function (e) {
    if (!confirm(this.dataset.confirm)) {
      e.preventDefault();
    }
  });

  // Make pdf action to open a new window.
  $('[name="print"]').on('click', function(e) {
    var $form = $(this).parents('form');
    $form.attr('target', '_blank');
    setTimeout(function () {$form.attr('target', null);}, 200);
  });

  $('.js-auto-click').each(function () {
    var $link = $(this);
    window.open($link.attr('href'), $link.attr('target'));
  });
});
