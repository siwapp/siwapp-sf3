function updateInvoiceTotals(path, $changedItem) {
    $.post(path, $changedItem.parents('form').serialize())
        .done(function(data) {
            $('td.base-amount').text(data.invoice_base_amount);
            $('td.tax-amount').text(data.invoice_tax_amount);
            $('td.gross-amount').text(data.invoice_gross_amount);
            for (index in data.items) {
                $('input[name*="[items][' + index + ']"]')
                    .parents('tr')
                    .find('.item-gross-amount')
                    .text(data.items[index].gross_amount);
            }
        });
}

function selectInvoiceItemAutocomplete(event, ui) {
    var $target = $(this);
    $target.val(ui.item.reference);
    var $row = $target.parents('tr');
    $row.find('[name$="[unitary_cost]"]').val(ui.item.unitary_cost).trigger('change');
    $row.find('[name$="[description]"]').val(ui.item.description);

    return false;
}

function renderInvoiceItemAutocomplete(ul, item) {
    return $('<li>')
        .append('<a>' + item.description + '</a>')
        .appendTo(ul);
}

function addInvoiceItemDescriptionAutocomplete(path) {
    $('.product-autocomplete-description:not(.ui-autocomplete-input)').each(function () {
        $(this).autocomplete({
            source: path,
            select: selectInvoiceItemAutocomplete,
        }).autocomplete( "instance" )._renderItem = renderInvoiceItemAutocomplete;
    });
}
