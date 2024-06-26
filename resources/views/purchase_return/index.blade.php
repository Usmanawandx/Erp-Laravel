@extends('layouts.app')
<style>
    .btn-edt {
        font-size: 14px !important;
        padding: 7px 8px 9px !important;
        border-radius: 50px !important;
        margin: 0px 5px 0px 5px;
    }
    
    .btn-vew {
        font-size: 14px !important;
        padding: 9px 8px 9px !important;
        border-radius: 50px !important;
    }
    
    .btn-dlt {
        font-size: 14px !important;
        padding: 7px 8px 9px !important;
        border-radius: 50px !important;
        margin-right: 5px;
    }
        
    </style>
@section('title', __('lang_v1.purchase_return'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="top-heading">@lang('lang_v1.purchase_return')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('purchase_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('purchase_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('purchase_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_purchase_returns')])
        @can('purchase.debit_note.add')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('CombinedPurchaseReturnController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
                <!--<div class="box-tools" style="margin-right: 10px;">-->
                <!--    <a class="btn btn-block btn-primary" href="{{ url('/DeleteRecords', ['type' => 'purchase_return']) }}">-->
                <!--    <i class="fa fa-trash"></i> @lang('Delete Records')</a>-->
                <!--</div>-->
            @endslot
        @endcan
            
        @can('purchase.debit_note')
            @include('purchase_return.partials.purchase_return_list')
        @endcan
        
    @endcomponent

    <div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>

<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready( function(){
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
               purchase_return_table.ajax.reload();
            }
        );
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_list_filter_date_range').val('');
            purchase_return_table.ajax.reload();
        });

        //Purchase table
        var i=1;
        purchase_return_table = $('#purchase_return_datatable').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[0, 'desc']],
            ajax: {
            url: '/purchase-return',
            data: function(d) {
                if ($('#purchase_list_filter_location_id').length) {
                    d.location_id = $('#purchase_list_filter_location_id').val();
                }

                var start = '';
                var end = '';
                if ($('#purchase_list_filter_date_range').val()) {
                    start = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#purchase_list_filter_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;
            },
        },
        
            columnDefs: [ {
                "targets": [7, 8],
                "orderable": false,
                "searchable": false
            } ],
            
            columns: [
                { data: 'action', name: 'action' , orderable: false, searchable: false },
                {
                render: function(d) {
                        return i++;
                }
                , orderable: false, searchable: false ,
                visible:false
                },
                
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'ref_no', name: 'ref_no'},
                { data: 'parent_purchase', name: 'T.ref_no',visible:false},
                
                { data: 'name', name: 'contacts.supplier_business_name'},
                { data: 'payment_status', name: 'payment_status',visible:false},
                { data: 'final_total', name: 'final_total'},
                { data: 'location_name', name: 'BS.name'},
                { data: 'payment_due', name: 'payment_due',visible:false},
            ],
            "fnDrawCallback": function (oSettings) {
                var total_purchase = sum_table_col($('#purchase_return_datatable'), 'final_total');
                $('#footer_purchase_return_total').text(total_purchase);
                
                $('#footer_payment_status_count').html(__sum_status_html($('#purchase_return_datatable'), 'payment-status-label'));

                var total_due = sum_table_col($('#purchase_return_datatable'), 'payment_due');
                $('#footer_total_due').text(total_due);
                
                __currency_convert_recursively($('#purchase_return_datatable'));
            },
            createdRow: function( row, data, dataIndex ) {
                $( row ).find('td:eq(5)').attr('class', 'clickable_td');
            }
        });

        $(document).on(
        'change',
            '#purchase_list_filter_location_id',
            function() {
                purchase_return_table.ajax.reload();
            }
        );
    });
</script>
	
@endsection