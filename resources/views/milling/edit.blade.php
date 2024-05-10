@extends('layouts.app')

@php
	$title = $transaction->type == 'sales_order' ? __('lang_v1.edit_sales_order') : __('Edit Milling');
@endphp
@section('title', $title)

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{$title}} <small>(@if($transaction->type == 'sales_order') @lang('restaurant.order_no') @else @lang('sale.invoice_no') @endif: <span class="text-success">#{{$transaction->invoice_no}})</span></small></h1>
</section>
<!-- Main content -->
<section class="content">
<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? 'none'}}">
@if(!empty($pos_settings['allow_overselling']))
	<input type="hidden" id="is_overselling_allowed">
@endif
@if(session('business.enable_rp') == 1)
    <input type="hidden" id="reward_point_enabled">

@endif
@php
	$custom_labels = json_decode(session('business.custom_labels'), true);
	$common_settings = session()->get('business.common_settings');
@endphp
<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
	{!! Form::open(['url' => action('SellPosController@update', ['id' => $transaction->id ]), 'method' => 'put', 'id' => 'edit_sell_form', 'files' => true ]) !!}

	{!! Form::hidden('location_id', $transaction->location_id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($location_printer_type) ? $location_printer_type : 'browser', 'data-default_payment_accounts' => $transaction->location->default_payment_accounts]); !!}

	@if($transaction->type == 'sales_order')
	 	<input type="hidden" id="sale_type" value="{{$transaction->type}}">
	@endif
	<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-solid'])
				@if(!empty($transaction->selling_price_group_id))
					<div class="col-md-4 col-sm-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
								</span>
								{!! Form::hidden('price_group', $transaction->selling_price_group_id, ['id' => 'price_group']) !!}
								{!! Form::text('price_group_text', $transaction->price_group->name, ['class' => 'form-control', 'readonly']); !!}
								<span class="input-group-addon">
									@show_tooltip(__('lang_v1.price_group_help_text'))
								</span> 
							</div>
						</div>
					</div>
				@endif

				@if(in_array('types_of_service', $enabled_modules) && !empty($transaction->types_of_service))
					<div class="col-md-4 col-sm-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-external-link-square-alt text-primary service_modal_btn"></i>
								</span>
								{!! Form::text('types_of_service_text', $transaction->types_of_service->name, ['class' => 'form-control', 'readonly']); !!}

								{!! Form::hidden('types_of_service_id', $transaction->types_of_service_id, ['id' => 'types_of_service_id']) !!}

								<span class="input-group-addon">
									@show_tooltip(__('lang_v1.types_of_service_help'))
								</span> 
							</div>
							<small><p class="help-block @if(empty($transaction->selling_price_group_id)) hide @endif" id="price_group_text">@lang('lang_v1.price_group'): <span>@if(!empty($transaction->selling_price_group_id)){{$transaction->price_group->name}}@endif</span></p></small>
						</div>
					</div>
					<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
						@if(!empty($transaction->types_of_service))
						@include('types_of_service.pos_form_modal', ['types_of_service' => $transaction->types_of_service])
						@endif
					</div>
				@endif

				@if(in_array('subscription', $enabled_modules))
					<div class="col-md-4 pull-right col-sm-6">
						<div class="checkbox">
							<label>
				              {!! Form::checkbox('is_recurring', 1, $transaction->is_recurring, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
				            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
						</div>
					</div>
				@endif
				<div >
					<div class=" col-sm-4 ">
						<div class="form-group">
						{!! Form::label('Transaction No:', __('Transaction No').':') !!}
						{!! Form::text('ref_no',$transaction->ref_no, ['class' => 'form-control']); !!}
					</div>
				</div>
				<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-4 @endif">
					<div class="form-group">
						{!! Form::label('posting_date', __('Posting Date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('posting_date', @format_date('now'), ['class' => 'form-control', 'readonly','required']); !!}

							<!-- {!! Form::date('posting_date',$transaction->posting_date, ['class' => 'form-control', 'required']); !!} -->
							
						</div>
					</div>
				</div>
				<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-4 @endif">
					<div class="form-group">
						{!! Form::label('efective_date', __('Efective Date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::date('efective_date',$transaction->efective_date, ['class' => 'form-control', 'required']); !!}
							
						</div>
					</div>
				</div>
				<div class="@if(!empty($default_purchase_status)) col-sm-4 @else col-sm-4 @endif">
					<div class="form-group">
					  {!! Form::label('supplier_id', __('Customer Name') . ':*') !!}
					  <div class="input-group">
						<span class="input-group-addon">
						  <i class="fa fa-user"></i>
						</span>
						<!--{!! Form::select('contact_id', [ $transaction->contact_id => $transaction->contact->name??''], $transaction->contact_id, ['class' => 'form-control', 'placeholder' => __('messages.please_select') , 'required', 'id' => 'supplier_id']); !!}-->
					   <select name="contact_id" class="form-control select2" required>
						   <option selected disabled>Please Select</option>
						  @foreach ($supplier as $supplier)
						  @if($transaction->contact_id == $supplier->id)
						  <option value="{{$supplier->id}}" selected>{{$supplier->supplier_business_name}}</option>
						  @else
						  <option value="{{$supplier->id}}">{{$supplier->supplier_business_name}}</option>  
						  @endif
						  @endforeach
						</select>
						<span class="input-group-btn">
						  {{-- <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button> --}}
						</span>
					  </div>
					</div>
					<!--<strong>-->
					<!--  @lang('business.address'):-->
					<!--</strong>-->
					<!--<div id="supplier_address_div">-->
					<!--  {!! $transaction->contact->contact_address??'' !!}-->
					<!--</div>-->
				  </div>

				  <div class="col-sm-4" >
	                <div class="form-group">
	                    {!! Form::label('upload_document', __('purchase.attach_document') . ':') !!}
	                    {!! Form::file('sell_document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
	                    {{-- <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
	                    @includeIf('components.document_help_text')</p> --}}
	                </div>
	            </div>

				<div class="col-md-12">
			    	<div class="form-group">
						{!! Form::label('additional_notes',__('Remarks')) !!}
						{!! Form::textarea('additional_notes', $transaction->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
					</div>
			    </div>
				<div class="col-md-3" style="display: none;">
		          <div class="form-group">
		            <div class="multi-input">
		              {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
		              <br/>
		              {!! Form::number('pay_term_number', $transaction->pay_term_number, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

		              {!! Form::select('pay_term_type', 
		              	['months' => __('lang_v1.months'), 
		              		'days' => __('lang_v1.days')], 
		              		$transaction->pay_term_type, 
		              	['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); !!}
		            </div>
		          </div>
		        </div>

				@if(!empty($commission_agent))
				<div class="col-sm-4" style="display: none;">
					<div class="form-group">
					{!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
					{!! Form::select('commission_agent', 
								$commission_agent, $transaction->commission_agent, ['class' => 'form-control select2']); !!}
					</div>
				</div>
				@endif
				<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-4 @endif" style="display: none;">
					<div class="form-group">
						{!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', $transaction->transaction_date, ['class' => 'form-control', 'readonly', 'required']); !!}
						</div>
					</div>
				</div>
				
				@php
					if($transaction->status == 'draft' && $transaction->is_quotation == 1){
						$status = 'quotation';
					} else if ($transaction->status == 'draft' && $transaction->sub_status == 'proforma') {
						$status = 'proforma';
					} else {
						$status = $transaction->status;
					}
				@endphp
				@if($transaction->type == 'sales_order')
					<input type="hidden" name="status" id="status" value="{{$transaction->status}}">
				@else
					<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-4 @endif" style="display: none;">
						<div class="form-group">
							{!! Form::label('status', __('sale.status') . ':*') !!}
							{!! Form::select('status', $statuses, $status, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
						</div>
					</div>
				@endif
				@if($transaction->status == 'draft')
				<div class="col-sm-4" >
					<div class="form-group">
						{!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':') !!}
						{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				@endif
				@can('edit_invoice_number')
				<div class="col-sm-4" style="display: none;">
					<div class="form-group">
						{!! Form::label('invoice_no', $transaction->type == 'sales_order' ? __('restaurant.order_no'): __('sale.invoice_no') . ':') !!}
						{!! Form::text('invoice_no', $transaction->invoice_no, ['class' => 'form-control', 'placeholder' => $transaction->type == 'sales_order' ? __('restaurant.order_no'): __('sale.invoice_no')]); !!}
					</div>
				</div>
				@endcan
				@php
			        $custom_field_1_label = !empty($custom_labels['sell']['custom_field_1']) ? $custom_labels['sell']['custom_field_1'] : '';

			        $is_custom_field_1_required = !empty($custom_labels['sell']['is_custom_field_1_required']) && $custom_labels['sell']['is_custom_field_1_required'] == 1 ? true : false;

			        $custom_field_2_label = !empty($custom_labels['sell']['custom_field_2']) ? $custom_labels['sell']['custom_field_2'] : '';

			        $is_custom_field_2_required = !empty($custom_labels['sell']['is_custom_field_2_required']) && $custom_labels['sell']['is_custom_field_2_required'] == 1 ? true : false;

			        $custom_field_3_label = !empty($custom_labels['sell']['custom_field_3']) ? $custom_labels['sell']['custom_field_3'] : '';

			        $is_custom_field_3_required = !empty($custom_labels['sell']['is_custom_field_3_required']) && $custom_labels['sell']['is_custom_field_3_required'] == 1 ? true : false;

			        $custom_field_4_label = !empty($custom_labels['sell']['custom_field_4']) ? $custom_labels['sell']['custom_field_4'] : '';

			        $is_custom_field_4_required = !empty($custom_labels['sell']['is_custom_field_4_required']) && $custom_labels['sell']['is_custom_field_4_required'] == 1 ? true : false;
		        @endphp
		        @if(!empty($custom_field_1_label))
		        	@php
		        		$label_1 = $custom_field_1_label . ':';
		        		if($is_custom_field_1_required) {
		        			$label_1 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_1', $label_1 ) !!}
				            {!! Form::text('custom_field_1', $transaction->custom_field_1, ['class' => 'form-control','placeholder' => $custom_field_1_label, 'required' => $is_custom_field_1_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_2_label))
		        	@php
		        		$label_2 = $custom_field_2_label . ':';
		        		if($is_custom_field_2_required) {
		        			$label_2 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_2', $label_2 ) !!}
				            {!! Form::text('custom_field_2', $transaction->custom_field_2, ['class' => 'form-control','placeholder' => $custom_field_2_label, 'required' => $is_custom_field_2_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_3_label))
		        	@php
		        		$label_3 = $custom_field_3_label . ':';
		        		if($is_custom_field_3_required) {
		        			$label_3 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_3', $label_3 ) !!}
				            {!! Form::text('custom_field_3', $transaction->custom_field_3, ['class' => 'form-control','placeholder' => $custom_field_3_label, 'required' => $is_custom_field_3_required]); !!}
				        </div>
				    </div>
		        @endif
		        @if(!empty($custom_field_4_label))
		        	@php
		        		$label_4 = $custom_field_4_label . ':';
		        		if($is_custom_field_4_required) {
		        			$label_4 .= '*';
		        		}
		        	@endphp

		        	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('custom_field_4', $label_4 ) !!}
				            {!! Form::text('custom_field_4', $transaction->custom_field_4, ['class' => 'form-control','placeholder' => $custom_field_4_label, 'required' => $is_custom_field_4_required]); !!}
				        </div>
				    </div>
		        @endif
		        
		        <div class="clearfix"></div>
		        @if((!empty($pos_settings['enable_sales_order']) && $transaction->type != 'sales_order') || $is_order_request_enabled)
					<div class="col-sm-4" style="display: none;">
						<div class="form-group">
							{!! Form::label('sales_order_ids', __('lang_v1.sales_order').':') !!}
							{!! Form::select('sales_order_ids[]', $sales_orders, $transaction->sales_order_ids, ['class' => 'form-control select2 not_loaded', 'multiple', 'id' => 'sales_order_ids']); !!}
						</div>
					</div>
					<div class="clearfix"></div>
				@endif
				<!-- Call restaurant module if defined -->
		        @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
		        	<span id="restaurant_module_span" 
		        		data-transaction_id="{{$transaction->id}}">
		        	</span>
		        @endif
			@endcomponent
			
			@component('components.widget', ['class' => 'box-solid'])
				{{-- <div class="col-sm-10 col-sm-offset-1">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
							</div>
							{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
							'autofocus' => true,
							]); !!}
							<span class="input-group-btn">
								<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
							</span>
						</div>
					</div>
				</div> --}}

				<div class="row col-sm-12 pos_product_div" style="min-height: 0">

					<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

					<!-- Keeps count of product rows -->
					<input type="hidden" id="product_row_count" 
						value="{{count($sell_details)}}">
					@php
						$hide_tax = '';
						if( session()->get('business.enable_inline_tax') == 0){
							$hide_tax = 'hide';
						}
					@endphp
					<div class="table-responsive">
					<table class="table table-condensed table-bordered table-th-green text-center table-striped" id="pos_table">
						<thead>
							<tr>
								<th class="text-center"><i class="fas fa-times" aria-hidden="true"></i></th>
							
								<th>#</th>
								<th width=12% class="hide">Store</th>
								<th>Item Code</th>
								<th>Item</th>
								<th>Item Descriptions</th>	
								<th>UOM</th>
								<th>Qty</th>
								@if(!empty($pos_settings['inline_service_staff']))
									<th class="text-center">
										@lang('restaurant.service_staff')
									</th>
								@endif
								<th @can('edit_product_price_from_sale_screen') hide @endcan>
									@lang('sale.unit_price')
								</th>
							 
								<th  @can('edit_product_discount_from_sale_screen') hide @endcan style="  display: none;">
									@lang('receipt.discount')
								</th>
								<th class="text-center {{$hide_tax}}" style="  display: none;">
									@lang('sale.tax')
								</th>
								<th class="text-center {{$hide_tax}}" style="  display: none;">
									@lang('sale.price_inc_tax')
								</th>
								@if(!empty($common_settings['enable_product_warranty']))
									<th style="  display: none;">@lang('lang_v1.warranty')</th>
								@endif
								<th class="text-center" style="  display: none;">
									@lang('sale.subtotal')
								</th>
					
							</tr>
						</thead>
						<tbody id="tbody">
							@foreach($sell_details as $sell_line)
								@include('sale_pos.milling_product_row', ['product' => $sell_line,'prd'=>$prd , 'row_count' => $loop->index, 'tax_dropdown' => $taxes, 'sub_units' => !empty($sell_line->unit_details) ? $sell_line->unit_details : [], 'action' => 'edit', 'is_direct_sell' => true, 'so_line' => $sell_line->so_line, 'is_sales_order' => $transaction->type == 'sales_order'])
							@endforeach
						</tbody>
					</table>
					</div>
					<button class="btn btn-md btn-primary addBtn" type="button"  onclick="add_row(this)" style="padding: 0px 5px 2px 5px;">
						Add row
					</button>
					<div class="table-responsive">
					<table class="table table-condensed table-bordered table-striped table-responsive">
						<tr>
							<td>
								<div class="pull-right">
									<b>@lang('sale.item'):</b> 
									<span class="total_quantity">0</span>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<b>@lang('sale.total'): </b>
									<span class="price_total">0</span>
								</div>
							</td>
						</tr>
					</table>
					</div>
				</div>
			@endcomponent
<div style="display: none;">
			@component('components.widget', ['class' => 'box-solid'])
				<div class="col-md-4 @if($transaction->type == 'sales_order') hide @endif">
			        <div class="form-group">
			            {!! Form::label('discount_type', __('sale.discount_type') . ':*' ) !!}
			            <div class="input-group">
			                <span class="input-group-addon">
			                    <i class="fa fa-info"></i>
			                </span>
			                {!! Form::select('discount_type', ['fixed' => __('lang_v1.fixed')], $transaction->discount_type , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'percentage']); !!}
			            </div>
			        </div>
			    </div>
			    @php
			    	$max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';
			    @endphp
			    <div class="col-md-4 @if($transaction->type == 'sales_order') hide @endif">
			        <div class="form-group">
			            {!! Form::label('discount_amount', __('sale.discount_amount') . ':*' ) !!}
			            <div class="input-group">
			                <span class="input-group-addon">
			                    <i class="fa fa-info"></i>
			                </span>
			                {!! Form::text('discount_amount', @num_format($transaction->discount_amount), ['class' => 'form-control input_number', 'data-default' => $business_details->default_sales_discount, 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? @num_format($max_discount) : '']) ]); !!}
			            </div>
			        </div>
			    </div>
			    <div class="col-md-4 @if($transaction->type == 'sales_order') hide @endif"><br>
			    	<b>@lang( 'sale.discount_amount' ):</b>(-) 
					<span class="display_currency" id="total_discount">0</span>
			    </div>
			    <div class="clearfix"></div>
			    <div class="col-md-12 well well-sm bg-light-gray @if(session('business.enable_rp') != 1 || $transaction->type == 'sales_order') hide @endif">
			    	<input type="hidden" name="rp_redeemed" id="rp_redeemed" value="{{$transaction->rp_redeemed}}">
			    	<input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="{{$transaction->rp_redeemed_amount}}">
			    	<div class="col-md-12"><h4>{{session('business.rp_name')}}</h4></div>
			    	<div class="col-md-4">
				        <div class="form-group">
				            {!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-gift"></i>
				                </span>
				                {!! Form::number('rp_redeemed_modal', $transaction->rp_redeemed, ['class' => 'form-control direct_sell_rp_input', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'min' => 0, 'data-max_points' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0, 'data-min_order_total' => session('business.min_order_total_for_redeem') ]); !!}
				                <input type="hidden" id="rp_name" value="{{session('business.rp_name')}}">
				            </div>
				        </div>
				    </div>
				    <div class="col-md-4">
				    	<p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">{{$redeem_details['points'] ?? 0}}</span></p>
				    </div>
				    <div class="col-md-4">
				    	<p><strong>@lang('lang_v1.redeemed_amount'):</strong> (-)<span id="rp_redeemed_amount_text">{{@num_format($transaction->rp_redeemed_amount)}}</span></p>
				    </div>
			    </div>
			    <div class="clearfix"></div>
			    <div class="col-md-4 @if($transaction->type == 'sales_order') hide @endif">
			    	<div class="form-group">
			            {!! Form::label('tax_rate_id', __('sale.order_tax') . ':*' ) !!}
			            <div class="input-group">
			                <span class="input-group-addon">
			                    <i class="fa fa-info"></i>
			                </span>
			                {!! Form::select('tax_rate_id', $taxes['tax_rates'], $transaction->tax_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $business_details->default_sales_tax], $taxes['attributes']); !!}

							<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" 
							value="{{@num_format(optional($transaction->tax)->amount)}}" data-default="{{$business_details->tax_calculation_amount}}">
			            </div>
			        </div>
			    </div>
			    <div class="col-md-4 col-md-offset-4 @if($transaction->type == 'sales_order') hide @endif">
			    	<b>@lang( 'sale.order_tax' ):</b>(+) 
					<span class="display_currency" id="order_tax">{{$transaction->tax_amount}}</span>
			    </div>
			    <div class="col-md-12">
			    	<div class="form-group">
						{!! Form::label('sell_note',__('sale.sell_note') . ':') !!}
						{!! Form::textarea('sale_note', $transaction->additional_notes, ['class' => 'form-control', 'rows' => 3]); !!}
					</div>
			    </div>
			    <input type="hidden" name="is_direct_sale" value="1">
			@endcomponent
					</div>
					<div style="display: none;">
			@component('components.widget', ['class' => 'box-solid'])
			<div class="col-md-4">
				<div class="form-group">
		            {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
		            {!! Form::textarea('shipping_details',$transaction->shipping_details, ['class' => 'form-control','placeholder' => __('sale.shipping_details') ,'rows' => '3', 'cols'=>'30']); !!}
		        </div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
		            {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
		            {!! Form::textarea('shipping_address', $transaction->shipping_address, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '3', 'cols'=>'30']); !!}
		        </div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					{!!Form::label('shipping_charges', __('sale.shipping_charges'))!!}
					<div class="input-group">
					<span class="input-group-addon">
					<i class="fa fa-info"></i>
					</span>
					{!!Form::text('shipping_charges',@num_format($transaction->shipping_charges),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-4">
				<div class="form-group">
		            {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
		            {!! Form::select('shipping_status',$shipping_statuses, $transaction->shipping_status, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
		        </div>
			</div>
			<div class="col-md-4">
		        <div class="form-group">
		            {!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ) !!}
		            {!! Form::text('delivered_to', $transaction->delivered_to, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); !!}
		        </div>
		    </div>
		    @php
		        $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : '';

		        $is_shipping_custom_field_1_required = !empty($custom_labels['shipping']['is_custom_field_1_required']) && $custom_labels['shipping']['is_custom_field_1_required'] == 1 ? true : false;

		        $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : '';

		        $is_shipping_custom_field_2_required = !empty($custom_labels['shipping']['is_custom_field_2_required']) && $custom_labels['shipping']['is_custom_field_2_required'] == 1 ? true : false;

		        $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : '';
		        
		        $is_shipping_custom_field_3_required = !empty($custom_labels['shipping']['is_custom_field_3_required']) && $custom_labels['shipping']['is_custom_field_3_required'] == 1 ? true : false;

		        $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : '';
		        
		        $is_shipping_custom_field_4_required = !empty($custom_labels['shipping']['is_custom_field_4_required']) && $custom_labels['shipping']['is_custom_field_4_required'] == 1 ? true : false;

		        $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : '';
		        
		        $is_shipping_custom_field_5_required = !empty($custom_labels['shipping']['is_custom_field_5_required']) && $custom_labels['shipping']['is_custom_field_5_required'] == 1 ? true : false;
	        @endphp

	        @if(!empty($shipping_custom_label_1))
	        	@php
	        		$label_1 = $shipping_custom_label_1 . ':';
	        		if($is_shipping_custom_field_1_required) {
	        			$label_1 .= '*';
	        		}
	        	@endphp

	        	<div class="col-md-4">
			        <div class="form-group">
			            {!! Form::label('shipping_custom_field_1', $label_1 ) !!}
			            {!! Form::text('shipping_custom_field_1', !empty($transaction->shipping_custom_field_1) ? $transaction->shipping_custom_field_1 : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_1, 'required' => $is_shipping_custom_field_1_required]); !!}
			        </div>
			    </div>
	        @endif
	        @if(!empty($shipping_custom_label_2))
	        	@php
	        		$label_2 = $shipping_custom_label_2 . ':';
	        		if($is_shipping_custom_field_2_required) {
	        			$label_2 .= '*';
	        		}
	        	@endphp

	        	<div class="col-md-4">
			        <div class="form-group">
			            {!! Form::label('shipping_custom_field_2', $label_2 ) !!}
			            {!! Form::text('shipping_custom_field_2', !empty($transaction->shipping_custom_field_2) ? $transaction->shipping_custom_field_2 : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_2, 'required' => $is_shipping_custom_field_2_required]); !!}
			        </div>
			    </div>
	        @endif
	        @if(!empty($shipping_custom_label_3))
	        	@php
	        		$label_3 = $shipping_custom_label_3 . ':';
	        		if($is_shipping_custom_field_3_required) {
	        			$label_3 .= '*';
	        		}
	        	@endphp

	        	<div class="col-md-4">
			        <div class="form-group">
			            {!! Form::label('shipping_custom_field_3', $label_3 ) !!}
			            {!! Form::text('shipping_custom_field_3', !empty($transaction->shipping_custom_field_3) ? $transaction->shipping_custom_field_3 : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_3, 'required' => $is_shipping_custom_field_3_required]); !!}
			        </div>
			    </div>
	        @endif
	        @if(!empty($shipping_custom_label_4))
	        	@php
	        		$label_4 = $shipping_custom_label_4 . ':';
	        		if($is_shipping_custom_field_4_required) {
	        			$label_4 .= '*';
	        		}
	        	@endphp

	        	<div class="col-md-4">
			        <div class="form-group">
			            {!! Form::label('shipping_custom_field_4', $label_4 ) !!}
			            {!! Form::text('shipping_custom_field_4', !empty($transaction->shipping_custom_field_4) ? $transaction->shipping_custom_field_4 : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_4, 'required' => $is_shipping_custom_field_4_required]); !!}
			        </div>
			    </div>
	        @endif
	        @if(!empty($shipping_custom_label_5))
	        	@php
	        		$label_5 = $shipping_custom_label_5 . ':';
	        		if($is_shipping_custom_field_5_required) {
	        			$label_5 .= '*';
	        		}
	        	@endphp

	        	<div class="col-md-4">
			        <div class="form-group">
			            {!! Form::label('shipping_custom_field_5', $label_5 ) !!}
			            {!! Form::text('shipping_custom_field_5', !empty($transaction->shipping_custom_field_5) ? $transaction->shipping_custom_field_5 : null, ['class' => 'form-control','placeholder' => $shipping_custom_label_5, 'required' => $is_shipping_custom_field_5_required]); !!}
			        </div>
			    </div>
	        @endif
	        <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
                    {!! Form::file('shipping_documents[]', ['id' => 'shipping_documents', 'multiple', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                    <p class="help-block">
                    	@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                    	@includeIf('components.document_help_text')
                    </p>
                    @php
                    	$medias = $transaction->media->where('model_media_type', 'shipping_document')->all();
                    @endphp
                    @include('sell.partials.media_table', ['medias' => $medias, 'delete' => true])
                </div>
            </div>

			
	        <div class="clearfix"></div>
			
		    <div class="col-md-4 col-md-offset-8">
		    	@if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
		    	<small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
				<br/>
				<input type="hidden" name="round_off_amount" 
					id="round_off_amount" value=0>
				@endif
		    	<div><b>@lang('sale.total_payable'): </b>
					<input type="hidden" name="final_total" id="final_total_input">
					<span id="total_payable">0</span>
				</div>
		    </div>
			@endcomponent
				</div>
			@if(!empty($common_settings['is_enabled_export']) && $transaction->type != 'sales_order')
			<div style="display: none;">
				@component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.export')])
					<div class="col-md-12 mb-12">
		                <div class="form-check">
		                    <input type="checkbox" name="is_export" class="form-check-input" id="is_export" @if(!empty($transaction->is_export)) checked @endif>
		                    <label class="form-check-label" for="is_export">@lang('lang_v1.is_export')</label>
		                </div>
		            </div>
			        @php
	                	$i = 1;
		            @endphp
		            @for($i; $i <= 6 ; $i++)
		                <div class="col-md-4 export_div" @if(empty($transaction->is_export)) style="display: none;" @endif>
		                    <div class="form-group">
		                        {!! Form::label('export_custom_field_'.$i, __('lang_v1.export_custom_field'.$i).':') !!}
		                        {!! Form::text('export_custom_fields_info['.'export_custom_field_'.$i.']', !empty($transaction->export_custom_fields_info['export_custom_field_'.$i]) ? $transaction->export_custom_fields_info['export_custom_field_'.$i] : null, ['class' => 'form-control','placeholder' => __('lang_v1.export_custom_field'.$i), 'id' => 'export_custom_field_'.$i]); !!}
		                    </div>
		                </div>
		            @endfor
				@endcomponent
				</div>
			@endif
		</div>
	</div>
	@php
		$is_enabled_download_pdf = config('constants.enable_download_pdf');
	@endphp
	@if($is_enabled_download_pdf && $transaction->type != 'sales_order')
		@can('sell.payments')
			@component('components.widget', ['class' => 'box-solid', 'title' => __('purchase.add_payment')])
				<div class="well row">
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label("prefer_payment_method" , __('lang_v1.prefer_payment_method') . ':') !!}
							@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
								</span>
								{!! Form::select("prefer_payment_method", $payment_types, $transaction->prefer_payment_method, ['class' => 'form-control','style' => 'width:100%;']); !!}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							{!! Form::label("prefer_payment_account" , __('lang_v1.prefer_payment_account') . ':') !!}
							@show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fas fa-money-bill-alt"></i>
								</span>
								{!! Form::select("prefer_payment_account", $accounts, $transaction->prefer_payment_account, ['class' => 'form-control','style' => 'width:100%;']); !!}
							</div>
						</div>
					</div>
				</div>
			@endcomponent
		@endcan
	@endif
<div style="display: none;">
	@if($transaction->type = 'sell')
	@can('sell.payments')
		@component('components.widget', ['class' => 'box-solid', 'title' => __('purchase.add_payment')])
			<div class="payment_row" id="payment_rows_div">
			@foreach($payment_lines as $payment_line)			
				@if($payment_line['is_return'] == 1)
					@php
						$change_return = $payment_line;
					@endphp

					@continue
				@endif

				@if(!empty($payment_line['id']))
        			{!! Form::hidden("payment[$loop->index][payment_id]", $payment_line['id']); !!}
        		@endif

				@include('sale_pos.partials.payment_row_form', ['row_index' => $loop->index, 'show_date' => true, 'payment_line' => $payment_line])
			@endforeach
			</div>

			<div class="col-md-12">
        		<hr>
        		<strong>
        			@lang('lang_v1.change_return'):
        		</strong>
        		<br/>
        		<span class="lead text-bold change_return_span">0</span>
        		{!! Form::hidden("change_return", $change_return['amount'], ['class' => 'form-control change_return input_number', 'required', 'id' => "change_return"]); !!}
        		<!-- <span class="lead text-bold total_quantity">0</span> -->
        		@if(!empty($change_return['id']))
            		<input type="hidden" name="change_return_id" 
            		value="{{$change_return['id']}}">
            	@endif
			</div>
		@endcomponent
	@endcan
	@endif
				</div>
	<div class="row">
		<div class="col-md-12 text-center">
	    	{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
	    	<button type="button" class="btn btn-primary btn-big" accesskey="s" id="submit-sell">@lang('messages.update')</button>
	    	<button type="button" id="save-and-print" class="btn btn-success btn-big">@lang('lang_v1.update_and_print')</button>
	    </div>
	</div>
	@if(in_array('subscription', $enabled_modules))
		@include('sale_pos.partials.recurring_invoice_modal')
	@endif
	{!! Form::close() !!}
</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	{{-- @include('contact.create', ['quick_add' => true]) --}}
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script type="text/javascript">
    	$(document).ready( function(){
			reIndexTable();
			update_table_sr_number();
    		$('#shipping_documents').fileinput({
		        showUpload: false,
		        showPreview: false,
		        browseLabel: LANG.file_browse_label,
		        removeLabel: LANG.remove,
		    });

		    $('#is_export').on('change', function () {
	            if ($(this).is(':checked')) {
	                $('div.export_div').show();
	            } else {
	                $('div.export_div').hide();
	            }
	        });

	        $('#status').change(function(){
    			if ($(this).val() == 'final') {
    				$('#payment_rows_div').removeClass('hide');
    			} else {
    				$('#payment_rows_div').addClass('hide');
    			}
    		});
    		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

			$('.prd_select').trigger('change');
			


    	});

		function get_product_code(el)
		{
			var terms = $(el).val();
			$.getJSON(
				'/purchases/get_products',
				{ term: terms },
				function(data){
					$(this).val(data[0].unit.actual_name);
					console.log(data[0]);            
					$(el).closest("tr").find(".product_code").val(data[0].sku);
					$(el).closest("tr").find(".uom").val(data[0].unit.actual_name);
			});
		}

		function add_row(el){
			// alert("asdgfsadf");
			$('#pos_table tbody tr').each(function(){
				$(this).find('#prd_select').select2('destroy')
			})
			var tr = $("#pos_table #tbody tr:last").clone();

			tr.find('input').val('');
			tr.find('textarea').val('');
			// console.log(tr);
			$("#pos_table #tbody tr:last").after(tr);
			
			reIndexTable();
			update_table_sr_number();

		}
		function reIndexTable(){
			var j=0;
			$('#pos_table tbody tr').each(function(){
				$(this).find('#prd_select').select2()
				$(this).attr('id',j)


				$(this).find('[name*=store]').attr('name',"products["+j+"][store]")
				$(this).find('[name*=item_code]').attr('name',"products["+j+"][item_code]")
				$(this).find('[name*=product_id]').attr('name',"products["+j+"][product_id]")
				$(this).find('[name*=sell_line_note]').attr('name',"products["+j+"][sell_line_note]")
				$(this).find('[name*=quantity]').attr('name',"products["+j+"][quantity]")
				$(this).find('[name*=pp_without_discount]').attr('name',"products["+j+"][pp_without_discount]")
				$(this).find('[name*=discount_percent]').attr('name',"products["+j+"][discount_percent]")
				$(this).find('[name*=purchase_price]').attr('name',"products["+j+"][purchase_price]")
				$(this).find('[name*=item_tax]').attr('name',"products["+j+"][item_tax]")
				$(this).find('[name*=purchase_price_inc_tax]').attr('name',"products["+j+"][purchase_price_inc_tax]")
				$(this).find('[name*=purchase_line_tax_id]').attr('name',"products["+j+"][purchase_line_tax_id]")
				$(this).find('[name*=unit_price]').attr('name',"products["+j+"][unit_price]")
				$(this).find('.pos_unit_price_inc_tax').attr('name',"products["+j+"][unit_price_inc_tax]")
				$(this).find('[name*=variation_id]').attr('name',"products["+j+"][variation_id]")
				$(this).find('[name*=tax_id]').attr('name',"products["+j+"][tax_id]")
				$(this).find('[name*=enable_stock]').attr('name',"products["+j+"][enable_stock]")
				$(this).find('[name*=product_type]').attr('name',"products["+j+"][product_type]")
				$(this).find('[name*=product_name]').attr('name',"products["+j+"][product_name]")



				// $(this).find('[name*=store]').attr('name',"purchases["+j+"][store]")
				// $(this).find('[name*=item_code]').attr('name',"purchases["+j+"][item_code]")
				// $(this).find('[name*=item_description]').attr('name',"purchases["+j+"][item_description]")
				// $(this).find('[name*=quantity]').attr('name',"purchases["+j+"][quantity]")
				// $(this).find('[name*=product_id]').attr('name',"purchases["+j+"][product_id]")
				// $(this).find('[name*=pp_without_discount]').attr('name',"purchases["+j+"][pp_without_discount]")
				// $(this).find('[name*=discount_percent]').attr('name',"purchases["+j+"][discount_percent]")
				// $(this).find('[name*=purchase_price]').attr('name',"purchases["+j+"][purchase_price]")
				// $(this).find('[name*=purchase_price_inc_tax]').attr('name',"purchases["+j+"][purchase_price_inc_tax]")
				// $(this).find('[name*=purchase_line_tax_id]').attr('name',"purchases["+j+"][purchase_line_tax_id]")
				// $(this).find('[name*=profit_percent]').attr('name',"purchases["+j+"][profit_percent]")
				// $(this).find('[name*=item_tax]').attr('name',"purchases["+j+"][item_tax]")
				// $(this).find('[name*=variation_id]').attr('name',"purchases["+j+"][variation_id]")
				// $(this).find('[name*=purchase_line_id]').attr('name',"purchases["+j+"][purchase_line_id]")
				// $(this).find('[name*=product_unit_id]').attr('name',"purchases["+j+"][product_unit_id]")
				j++;
			});
		}
		function remove_row(el) {
		var tr_length = $("#pos_table #tbody tr").length;
		if(tr_length > 1){
			var tr = $(el).closest("tr").remove();
			reIndexTable();
			update_table_sr_number();
		}else{
			alert("At least one row required");
		}		
	}
	function update_table_sr_number() {
    var sr_number = 1;
    $('table#pos_table tbody')
        .find('.sr_number')
        .each(function() {
            $(this).text(sr_number);
            sr_number++;
        });
}

 // on first focus (bubbles up to document), open the menu
 $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
  $(this).closest(".select2-container").siblings('select:enabled').select2('open');
});

// steal focus during close - only capture once and stop propogation
$('select.select2').on('select2:closing', function (e) {
  $(e.target).data("select2").$selection.one('focus focusin', function (e) {
    e.stopPropagation();
  });
});


    </script>
@endsection