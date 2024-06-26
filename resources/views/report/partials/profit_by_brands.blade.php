<div class="table-responsive">
    <table class="table table-bordered table-striped table-text-center ajax_view hide-footer dataTable table-styling table-hover table-primary no-footer" id="profit_by_brands_table">
        <thead>
            <tr>
                <th>@lang('product.brand')</th>
                <th>@lang('lang_v1.gross_profit')</th>
            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 footer-total">
                <td><strong>@lang('sale.total'):</strong></td>
                <td><span class="display_currency footer_total" data-currency_symbol ="true"></span></td>
            </tr>
        </tfoot>
    </table>

    <p class="text-muted">
        @lang('lang_v1.profit_note')
    </p>
</div>