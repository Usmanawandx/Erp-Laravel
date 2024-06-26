@extends('layouts.app')
<style>

.btn-vew {
    font-size: 14px !important;
    padding: 7px 8px 7px 8px !important;
    border-radius: 50px !important;
    margin: 5px !important;
    }

    .btn-edt {
    font-size: 14px !important;
    padding: 6px 7px 7px 9px !important;
    border-radius: 50px !important;
        
    }

    .btn-list {
    font-size: 14px !important;
    padding: 7px 7px 8px 9px !important;
    border-radius: 50px !important;
        
    }
    
    .btn-dlt {
    font-size: 14px !important;
    padding: 7px 9px 7px 9px !important;
    border-radius: 50px !important;
    margin-left: 0px;
    margin-right: 5px;
}
        
    </style>
@section('title', __( 'lang_v1.sales_order'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.sales_order')
        {{-- <span class="pull-right">Transaction No :{{$t_no+1}}</span> --}}
    </h1>
    
</section>






<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_sale_type',  'Sale Type' . ':') !!}
                {!! Form::select('sell_list_filter_sale_type', $saleType, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3 hide">
            <div class="form-group">
                {!! Form::label('so_list_filter_status',  __('sale.status') . ':') !!}
                {!! Form::select('so_list_filter_status', $sales_order_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @if(!empty($shipping_statuses))
            <div class="col-md-3 hide">
                <div class="form-group">
                    {!! Form::label('so_list_shipping_status', __('lang_v1.shipping_status') . ':') !!}
                    {!! Form::select('so_list_shipping_status', $shipping_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        @can('so.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('SellController@create')}}?sale_type=sales_order">
                    <i class="fa fa-plus"></i> @lang('lang_v1.add_sales_order')</a>
                </div>
            @endslot
        @endcan
        @if( auth()->user()->can('so.view_own') || auth()->user()->can('so.view_all'))
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view hide-footer dataTable table-styling table-hover table-primary" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('Sno')</th>
                        <th>@lang('messages.date')</th>
                           <th>@lang('Transaction No')</th>
                        
                        <th>Sale Type</th>
                     
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('sale.status')</th>
                        <th>@lang('Remarks')</th>
                        <th>Generated by</th>
                        <th>@lang('sale.location')</th>
                    </tr>
                </thead>
            </table>
        </div>
        @endif
    @endcomponent
    <div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
</section>
<!-- /.content -->
@stop
@section('javascript')
@includeIf('sales_order.common_js')
<script type="text/javascript">
$(document).ready( function(){
    var i = 1;
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        "ajax": {
            "url": '/sells?sale_type=sales_order',
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();
                if($('#sell_list_filter_sale_type').length) {
                    d.type = $('#sell_list_filter_sale_type').val();
                }
                if ($('#so_list_filter_status').length) {
                    d.status = $('#so_list_filter_status').val();
                }
                if ($('#so_list_shipping_status').length) {
                    d.shipping_status = $('#so_list_shipping_status').val();
                }

                if($('#created_by').length) {
                    d.created_by = $('#created_by').val();
                }
            }
        },
        columnDefs: [ {
            "targets": 7,
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'action', name: 'action'},
            {
                    "render": function() {
                        return i++;
                    }, visible: false
                },
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'ref_no', name: 'ref_no'},
            { data: 'sales_type', name: 'sales_type', searchable: false},
            { data: 'conatct_name', name: 'conatct_name'},
            { data: 'status', name: 'status', visible: false},
            { data: 'additional_notes', name: 'additional_notes', visible: false},
            { data: 'created_by', name: 'created_by'},
            { data: 'business_location', name: 'bl.name'},
        ]
    });
    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by, #so_list_filter_status, #so_list_shipping_status, #sell_list_filter_sale_type',  function() {
        sell_table.ajax.reload();
    });
});
</script>
	
@endsection