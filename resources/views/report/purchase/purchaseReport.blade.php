@extends('layouts.app')
@section('title', 'Purchase Report')

@section('content')
<style>
    .nested-tfoot-2 td{
        border-top: 1px solid #0000003e !important; 
        border-bottom: 1px solid #00000051 !important; 
    }
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>Purchase Report </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('po_list_filter_location_id',  __('purchase.business_location') . ':') !!}
                    {!! Form::select('po_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('po_list_filter_supplier_id',  __('purchase.supplier') . ':') !!}
                    {!! Form::select('po_list_filter_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('purchase_type',  __('Purchase Type') . ':') !!}
                    {!! Form::select('purchase_type', $purchase_type->pluck('Type','id'), null, ['class' => 'form-control select2','id'=>'purchase_type', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('product_type',  __('Product Type') . ':') !!}
                    {!! Form::select('product_type', $product_type->pluck('name','id'), null, ['class' => 'form-control select2','id'=>'product_type','style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('transporter',  __('Transporter') . ':') !!}
                    {!! Form::select('transporter', $transporter->pluck('supplier_business_name','id'), null, ['class' => 'form-control select2','id'=>'transporter','style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('vehicle',  __('Vehicle') . ':') !!}
                    {!! Form::select('vehicle', $vehicles->pluck('vhicle_number','id'), null, ['class' => 'form-control vehicles_select select2','id'=>'vehicle','style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    <input type="text" class="form-control vehicles_input" placeholder="vehile number" style="display: none" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sales_man',  __('Sales Man') . ':') !!}
                    {!! Form::select('sales_man', $sales_man->pluck('supplier_business_name','id'), null, ['class' => 'form-control select2','id'=>'sales_man','style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('Transaction Account',  __('Transaction Account') . ':') !!}
                    {!! Form::select('transaction_account', $transaction_accounts->pluck('name','id'), null, ['class' => 'form-control select2','id'=>'transaction_accounts','style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('po_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('po_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control']); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <br>
                    <div class="checkbox">
                        <label>
                          {!! Form::checkbox('summary_report', 1, false,  
                          [ 'class' => 'input-icheck', 'id' => 'summary_report']); !!}  Summary Report
                        </label>
                    </div> 
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div id="sale-report-table">
               
            </div>
        @endcomponent

    </section>

    <!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var buttons = [
                {
                    extend: 'copy',
                    text: '<i class="fa fa-file" aria-hidden="true"></i> ' + LANG.copy,
                    className: 'buttons-csv btn-sm',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: true,
                    },
                    footer: true,
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG.export_to_csv,
                    className: 'btn-sm',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: true
                    },
                    footer: true,
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel" aria-hidden="true"></i> ' + LANG.export_to_excel,
                    className: 'btn-sm',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: true
                    },
                    footer: true,
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG.print,
                    className: 'btn-sm',
                    exportOptions: {
                        columns: ':visible',
                        stripHtml: true,
                    },
                    footer: true,
                    customize: function ( win ) {
                        // if ($('.print_table_part').length > 0 ) {
                        //     $($('.print_table_part').html()).insertBefore($(win.document.body).find( 'table' ));
                        // }
                        $(win.document.body).find( '.table tbody .hide' ).remove();
                        if ($(win.document.body).find( 'table.hide-footer').length) {
                            $(win.document.body).find( 'table.hide-footer tfoot' ).remove();
                        }
                        __currency_convert_recursively($(win.document.body).find( 'table' ));
                        // ///////////// //

                        var gap = '15mm';
                        var style = document.createElement('style');
                        style.innerHTML = '@page { margin: ' + gap + '; }';
                        win.document.head.appendChild(style);
 
                        $(win.document.body).find( 'table' ).addClass( 'compact' ).css( 'font-size', 'inherit' );
                        $(win.document.body).find( 'table tbody td' ).css({'font-size': '8px'});
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG.col_vis,
                    className: 'btn-sm',
                },
                {
                    text: '<i class="fa fa-image" aria-hidden="true"></i> Export Image',
                    className: 'buttons-csv btn-sm',
                    action: function ( e, dt, node, config ) {
                        html2canvas(document.getElementById('report_table')).then(function(canvas) {
                            var imgData = canvas.toDataURL('image/png');
                            var link = document.createElement('a');
                            link.href = imgData;
                            link.download = 'Sales-Report.png';
                            link.click();
                        });
                    }
                },
            ];
            var pdf_btn = {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG.export_to_pdf,
                className: 'btn-sm',
                exportOptions: {
                    columns: ':visible',
                    stripHtml: true
                },
                customize: function (doc) {
                    doc.pageMargins = [20, 30, 20, 30];
                    doc.defaultStyle.fontSize = 12;
                    doc.defaultStyle.textColor = '#333';
                    doc.styles.tableHeader.fontSize = 8;
                    doc.styles.tableHeader.bold = true;
                    doc.styles.tableBodyEven.fontSize = 6;
                    doc.styles.tableBodyOdd.fontSize = 6;
                    doc.footer = function (currentPage, pageCount) {
                        return {
                            text: 'Page ' + currentPage.toString() + ' of ' + pageCount,
                            style: 'footer'
                        };
                    };
                },
                footer: true,
            };

            if (non_utf8_languages.indexOf(app_locale) == -1) {
                buttons.push(pdf_btn);
            }
            jQuery.extend($.fn.dataTable.defaults, {
                buttons: buttons,
                iDisplayLength: -1,
            });
            //Date range as a button
            $('#po_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#po_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    loadSaleReport();
                }
            );
            $('#po_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#po_list_filter_date_range').val('');
                loadSaleReport();
            });

            $("#transporter").change(function() {
                var id = $("#transporter").val();
                if (id == defaultOtherTransporterId) {
                    $('.vehicles_input').show();
                    $('.vehicles_select').hide();
                    $('.vehicles_select').closest('.form-group').find('.select2-container').hide();
                } else {
                    $('.vehicles_select').show();
                    $('.vehicles_select').closest('.form-group').find('.select2-container').show();
                    $('.vehicles_input').hide();
                }
            });

            loadSaleReport();
            function loadSaleReport(summary = null, weight = null, furtherTax = null) {
                if($('#po_list_filter_date_range').val()) {
                    var start = $('#po_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#po_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                }
                $.ajax({
                    data: {
                            start_date  : start,
                            end_date    : end,
                            location_id : $('#po_list_filter_location_id').val(),
                            supplier_id : $('#po_list_filter_supplier_id').val(),
                            purchase_category: $('#purchase_type').val(),
                            purchase_type : $('#product_type').val(),
                            transaction_accounts : $('#transaction_accounts').val(),
                            transporter : $('#transporter').val(),
                            vehicle     : ($("#transporter").val() == defaultOtherTransporterId) ? $('.vehicles_input').val(): $('#vehicle').val(),
                            sales_man    : $('#sales_man').val(),
                            // control_account : $('#po_list_filter_accountsType').val(),
                            // products    : $('#po_list_filter_products').val(),
                            summary     : summary,
                            weight      : weight,
                            furtherTax  : furtherTax
                        },
                    success: function (response) {
                        $('#sale-report-table').html(response);
                        var table = $('#report_table').DataTable({
                            ordering: false,
                            searching: false,
                        });
                    }
                });
            }
            

            $(document).on('change', '#po_list_filter_products, #po_list_filter_location_id, #po_list_filter_supplier_id,#transporter, #vehicle, #sales_man, #transaction_accounts, #sell_list_filter_accountsType, #purchase_type, #product_type, .vehicles_input',  function() {
                $('#summary_report, #hide_weight, #hide_further_tax').trigger('ifChanged');
            });


            $('#summary_report, #hide_weight, #hide_further_tax').off('ifChanged').on('ifChanged', function(e){
                if ($('#summary_report').is(':checked')) {
                    loadSaleReport(1, 0, 0);
                }else if($('#hide_weight').is(':checked') && !$('#hide_further_tax').is(':checked')){
                    loadSaleReport(0, 1, 0);
                }else if(!$('#hide_weight').is(':checked') && $('#hide_further_tax').is(':checked')){
                    loadSaleReport(0, 0, 1);
                }else if($('#hide_weight').is(':checked') && $('#hide_further_tax').is(':checked')){
                    loadSaleReport(0, 1, 1);
                }else{
                    loadSaleReport();
                }
            })

        });
    </script>

@endsection
