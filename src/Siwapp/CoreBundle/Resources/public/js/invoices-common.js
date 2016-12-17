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
