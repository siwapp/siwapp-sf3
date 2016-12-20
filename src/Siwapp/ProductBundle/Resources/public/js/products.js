function selectProductAutocompleteItem(event, ui) {
    var $target = $(this);
    $target.val(ui.item.reference);
    var $row = $target.parents('tr');
    $row.find('[name$="[product]"]').val(ui.item.reference);
    $row.find('[name$="[unitary_cost]"]').val(ui.item.price).trigger('change');
    $row.find('[name$="[description]"]').val(ui.item.description);

    return false;
}

function renderProductAutocompleteItem(ul, item) {
    return $('<li>')
        .append('<a>' + item.reference + '</a>')
        .appendTo(ul);
}

function addProductNameAutocomplete(path) {
    $('.product-autocomplete-name:not(.ui-autocomplete-input)').each(function () {
        $(this).autocomplete({
            source: path,
            select: selectProductAutocompleteItem,
        }).autocomplete( "instance" )._renderItem = renderProductAutocompleteItem;
    });
}
