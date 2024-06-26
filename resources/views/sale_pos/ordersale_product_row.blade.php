@php
	$common_settings = session()->get('business.common_settings');
	 //dd($transaction);
	 //dd($product);
@endphp
<tr class="product_row" data-row_index="{{$row_count}}" @if(!empty($so_line)) data-so_id="{{$so_line->transaction_id}}" @endif>
	<td><button class="btn btn-danger remove" type="button" onclick="remove_row(this)" 
									style="padding: 0px 5px 2px 5px;"><i class="fa fa-trash" aria-hidden="true"></i></button>
									</td>
	<td>
	<input type="hidden" class="change_name_old_qty" name="products[{{$row_count}}][old_quantity]" value="{{$transaction->custom_field_4=='convert_dn' ? 0 : $product->quantity_ordered}}" >
		
	<span class='sr_number'>{{$row_count}}</span>
</td>
<td class="hide">
{{-- <div class="col-sm-3"> --}}
				<div class="form-group">
                

                    <select name="products[{{ $row_count }}][store]" class="form-control  input-sm purchase_line_tax_id select2" id="store_s" placeholder="'Please Select'">
                        <option value="" >@lang('lang_v1.none')</option>
                    @foreach ($store as $store)
						@if($store->id == $product->store)
						<option value="{{ $store->id }}" selected >{{ $store->name  }}</option>
						@else
						<option value="{{ $store->id }}" >{{ $store->name  }}</option>
						@endif
                    @endforeach
                    </select>
                </div>
			{{-- </div> --}}
</td>
<td>
	{{-- {!! $product->sub_sku !!}	 --}}
	<input class="form-control product_code" readonly="" id="item_code" name="products[0][item_code]" type="text">
	<input type="hidden" name="base_unit" class="base_unit">
</td>
<td>
	<select name="products[0][product_id]" class="form-control  prd_select select2" id="prd_select" onchange="get_product_code(this)">
		<option value="">Please Select</option>
		@foreach ($prd as $p)
		<option value="{{$p->id}}" {{$product->product_id == $p->id ? 'selected' : ''}}>{{$p->name}}</option>
		@endforeach
	</select>

		{{-- @if(!empty($so_line))
			<input type="hidden" 
			name="products[{{$row_count}}][so_line_id]" 
			value="{{$so_line->id}}">
		@endif
		@php
			$product_name = $product->product_name  ;
		@endphp

		@if( ($edit_price || $edit_discount) && empty($is_direct_sell) )
		<div title="@lang('lang_v1.pos_edit_product_price_help')">
		<span class="text-link text-info cursor-pointer" data-toggle="modal" data-target="#row_edit_product_price_modal_{{$row_count}}">
			{!! $product_name !!}
			&nbsp;<i class="fa fa-info-circle"></i>
		</span>
		</div>
		@else
			{!! $product_name !!}
		@endif
		<input type="hidden" class="enable_sr_no" value="{{$product->enable_sr_no}}"> --}}
		<input type="hidden" class="product_type" name="products[{{$row_count}}][product_type]" value="{{$product->product_type}}">

		@php
			$hide_tax = 'hide';
	        if(session()->get('business.enable_inline_tax') == 1){
	            $hide_tax = '';
	        }
	        
			$tax_id = $product->tax_id;
			$item_tax = !empty($product->item_tax) ? $product->item_tax : 0;
			$unit_price_inc_tax = $product->sell_price_inc_tax;

			if(!empty($so_line)) {
				$tax_id = $so_line->tax_id;
				$item_tax = $so_line->item_tax;
			}

			if($hide_tax == 'hide'){
				$tax_id = null;
				$unit_price_inc_tax = $product->default_sell_price;
			}

			$discount_type = !empty($product->line_discount_type) ? $product->line_discount_type : 'fixed';
			$discount_amount = !empty($product->line_discount_amount) ? $product->line_discount_amount : 0;
			
			if(!empty($discount)) {
				$discount_type = $discount->discount_type;
				$discount_amount = $discount->discount_amount;
			}

			if(!empty($so_line)) {
				$discount_type = $so_line->line_discount_type;
				$discount_amount = $so_line->line_discount_amount;
			}

  			$sell_line_note = '';
  			if(!empty($product->sell_line_note)){
  				$sell_line_note = $product->sell_line_note;
  			}
  		@endphp

		@if(!empty($discount))
			{!! Form::hidden("products[$row_count][discount_id]", $discount->id); !!}
		@endif

		@php
			$warranty_id = !empty($action) && $action == 'edit' && !empty($product->warranties->first())  ? $product->warranties->first()->id : $product->warranty_id;
		@endphp

		@if(empty($is_direct_sell))
		<div class="modal fade row_edit_product_price_model" id="row_edit_product_price_modal_{{$row_count}}" tabindex="-1" role="dialog">
			@include('sale_pos.partials.row_edit_product_price_modal')
		</div>
		@endif

		<!-- Description modal end -->
		@if(in_array('modifiers' , $enabled_modules))
			<div class="modifiers_html">
				@if(!empty($product->product_ms))
					@include('restaurant.product_modifier_set.modifier_for_product', array('edit_modifiers' => true, 'row_count' => $loop->index, 'product_ms' => $product->product_ms ) )
				@endif
			</div>
		@endif

		@php
			$max_quantity = $product->qty_available;
			$formatted_max_quantity = $product->formatted_qty_available;

			if(!empty($action) && $action == 'edit') {
				if(!empty($so_line)) {
					$qty_available = $so_line->quantity - $so_line->so_quantity_invoiced + $product->quantity_ordered;
					$max_quantity = $qty_available;
					$formatted_max_quantity = number_format($qty_available, config('constants.currency_precision', 2), session('currency')['decimal_separator'], session('currency')['thousand_separator']);
				}
			} else {
				if(!empty($so_line) && $so_line->qty_available <= $max_quantity) {
					$max_quantity = $so_line->qty_available;
					$formatted_max_quantity = $so_line->formatted_qty_available;
				}
			}
			

			$max_qty_rule = $max_quantity;
			$max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty'=> $formatted_max_quantity, 'unit' => $product->unit  ]);
		@endphp

		@if( session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
		@php
			$lot_enabled = session()->get('business.enable_lot_number');
			$exp_enabled = session()->get('business.enable_product_expiry');
			$lot_no_line_id = '';
			if(!empty($product->lot_no_line_id)){
				$lot_no_line_id = $product->lot_no_line_id;
			}
		@endphp
		@if(!empty($product->lot_numbers) && empty($is_sales_order))
			<select class="form-control lot_number input-sm" name="products[{{$row_count}}][lot_no_line_id]" @if(!empty($product->transaction_sell_lines_id)) disabled @endif>
				<option value="">@lang('lang_v1.lot_n_expiry')</option>
				@foreach($product->lot_numbers as $lot_number)
					@php
						$selected = "";
						if($lot_number->purchase_line_id == $lot_no_line_id){
							$selected = "selected";

							$max_qty_rule = $lot_number->qty_available;
							$max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ]);
						}

						$expiry_text = '';
						if($exp_enabled == 1 && !empty($lot_number->exp_date)){
							if( \Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)) ){
								$expiry_text = '(' . __('report.expired') . ')';
							}
						}

						//preselected lot number if product searched by lot number
						if(!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
							$selected = "selected";

							$max_qty_rule = $lot_number->qty_available;
							$max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ]);
						}
					@endphp
					<option value="{{$lot_number->purchase_line_id}}" data-qty_available="{{$lot_number->qty_available}}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ])" {{$selected}}>@if(!empty($lot_number->lot_number) && $lot_enabled == 1){{$lot_number->lot_number}} @endif @if($lot_enabled == 1 && $exp_enabled == 1) - @endif @if($exp_enabled == 1 && !empty($lot_number->exp_date)) @lang('product.exp_date'): {{@format_date($lot_number->exp_date)}} @endif {{$expiry_text}}</option>
				@endforeach
			</select>
		@endif
	@endif
	<input type="hidden" name="gross_weight" class="gross__weight">
	<input type="hidden" name="net_weight" class="net__weight">
	</td>
	<td>
		{!! Form::select('products[{{$row_count}}][brand_id]', ['' => 'Select'] + $brands->pluck('name','id')->all(), $product->brand_id, ['class' => 'form-control select2','id' =>'brand_id']) !!}
	</td>
	<td>
	<textarea class="form-control" name="products[{{$row_count}}][sell_line_note]" rows="1">{{$sell_line_note}}</textarea>

					</td>
					<td>
		   <input type="text" class="form-control uom" readonly="">

					{{-- @if(count($sub_units) > 0)

			<select name="products[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit">
                @foreach($sub_units as $key => $value)
                    <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}" data-unit_name="{{$value['name']}}" data-allow_decimal="{{$value['allow_decimal']}}" @if(!empty($product->sub_unit_id) && $product->sub_unit_id == $key) selected @endif>
                        {{$value['name']}}
                    </option>
                @endforeach
           </select>
		@else
			{{$product->unit}}
		@endif --}}
					</td>

	<td>
		{{-- If edit then transaction sell lines will be present --}}
		{{-- @if(!empty($product->transaction_sell_lines_id))
			<input type="hidden" name="products[{{$row_count}}][transaction_sell_lines_id]" class="form-control" value="{{$product->transaction_sell_lines_id}}">
		@endif --}}

		{{-- <input type="hidden" name="products[{{$row_count}}][product_id]" class="form-control product_id" value="{{$product->product_id}}"> --}}

        
        <input type="hidden" name="transporter_rate" class="transporter_rate" />
        <input type="hidden" name="contractor_rate" class="contractor_rate" />

		<input type="hidden" value="{{$product->variation_id}}" 
			name="products[{{$row_count}}][variation_id]" class="row_variation_id">

		<input type="hidden" value="{{$product->enable_stock}}" 
			name="products[{{$row_count}}][enable_stock]">
		
		@if(empty($product->quantity_ordered))
			@php
				$product->quantity_ordered = 1;
			@endphp
		@endif

		@php
			$multiplier = 1;
			$allow_decimal = true;
			if($product->unit_allow_decimal != 1) {
				$allow_decimal = false;
			}
		@endphp
		@foreach($sub_units as $key => $value)
        	@if(!empty($product->sub_unit_id) && $product->sub_unit_id == $key)
        		@php
        			$multiplier = $value['multiplier'];
        			$max_qty_rule = $max_qty_rule / $multiplier;
        			$unit_name = $value['name'];
        			$max_qty_msg = __('validation.custom-messages.quantity_not_available', ['qty'=> $max_qty_rule, 'unit' => $unit_name  ]);

        			if(!empty($product->lot_no_line_id)){
        				$max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $max_qty_rule, 'unit' => $unit_name  ]);
        			}

        			if($value['allow_decimal']) {
        				$allow_decimal = true;
        			}
        		@endphp
        	@endif
        @endforeach
		<div class="input-group input-number">
		
		<input type="text" data-min="1" 
			class="form-control pos_quantity input_number mousetrap input_quantity" autocomplete="disabled"
			value="{{$product->quantity_ordered}}" name="products[{{$row_count}}][quantity]" data-allow-overselling="@if(empty($pos_settings['allow_overselling'])){{'false'}}@else{{'true'}}@endif" onkeyup="calculate_unitprice(this)">
	
		</div>
		
		{{-- <input type="hidden" name="products[{{$row_count}}][product_unit_id]" value="{{$product->unit_id}}"> --}}
		

		{{-- <input type="hidden" class="base_unit_multiplier" name="products[{{$row_count}}][base_unit_multiplier]" value="{{$multiplier}}"> --}}

		<input type="hidden" class="hidden_base_unit_sell_price" value="{{$product->default_sell_price / $multiplier}}">
		
		{{-- Hidden fields for combo products --}}
		@if($product->product_type == 'combo'&& !empty($product->combo_products))

			@foreach($product->combo_products as $k => $combo_product)

				@if(isset($action) && $action == 'edit')
					@php
						$combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

						$qty_total = $combo_product['quantity'];
					@endphp
				@else
					@php
						$qty_total = $combo_product['qty_required'];
					@endphp
				@endif

				<input type="hidden" 
					name="products[{{$row_count}}][combo][{{$k}}][product_id]"
					value="{{$combo_product['product_id']}}">

					<input type="hidden" 
					name="products[{{$row_count}}][combo][{{$k}}][variation_id]"
					value="{{$combo_product['variation_id']}}">

					<input type="hidden"
					class="combo_product_qty" 
					name="products[{{$row_count}}][combo][{{$k}}][quantity]"
					data-unit_quantity="{{$combo_product['qty_required']}}"
					value="{{$qty_total}}">

					@if(isset($action) && $action == 'edit')
						<input type="hidden" 
							name="products[{{$row_count}}][combo][{{$k}}][transaction_sell_lines_id]"
							value="{{$combo_product['id']}}">
					@endif

			@endforeach
		@endif
	</td>
	@if(!empty($is_direct_sell))
		@if(!empty($pos_settings['inline_service_staff']))
			<td>
				<div class="form-group">
					<div class="input-group">
						{!! Form::select("products[" . $row_count . "][res_service_staff_id]", $waiters, !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null, ['class' => 'form-control select2 order_line_service_staff', 'placeholder' => __('restaurant.select_service_staff'), 'required' => (!empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1) ? true : false ]); !!}
					</div>
				</div>
			</td>
		@endif
		@php
			$pos_unit_price = !empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price;

			if(!empty($so_line)) {
				$pos_unit_price = $so_line->unit_price_before_discount;
			}
		@endphp
		<td @if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
			<input type="text" name="products[{{$row_count}}][unit_price]" class="form-control pos_unit_price input_number mousetrap"   onkeyup="calculate_unitprice(this)" value="{{$pos_unit_price}}" @if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$pos_unit_price}}" data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)])}}" @endif>
		</td>
		<td @if(!$edit_discount) hide @endif class="hide">
			{!! Form::text("products[$row_count][line_discount_amount]", @num_format($discount_amount), ['class' => 'form-control input_number row_discount_amount','onkeyup' => 'calculate_discount(this)']); !!}<br>
			{!! Form::select("products[$row_count][line_discount_type]", ['fixed' => __('lang_v1.fixed')], $discount_type , ['class' => 'form-control row_discount_type']); !!}
			@if(!empty($discount))
				<p class="help-block">{!! __('lang_v1.applied_discount_text', ['discount_name' => $discount->name, 'starts_at' => $discount->formated_starts_at, 'ends_at' => $discount->formated_ends_at]) !!}</p>
			@endif
		</td>
		<td class="get_total">
			<input class="calculate_discount row_total_amount form-control" type="text">
		</td>
		<td class="text-center {{$hide_tax}}">
			{!! Form::hidden("products[$row_count][item_tax]", @num_format($item_tax), ['class' => 'item_tax']); !!}
		
			{{-- {!! Form::select("products[$row_count][tax_id]", $tax_dropdown['tax_rates'], $tax_id, ['placeholder' => 'Select', 'class' => 'form-control row_total_amount tax_idd select2','id' => 'tax_id'], $tax_dropdown['attributes']); !!} --}}
			<select name="products[{{$row_count}}][tax_id]" class="form-control select2 input-sm tax_idd" placeholder="'Please Select'" id="tax_id" onchange="calculate_unitprice(this)">
				<option value="0" data-ratee="0">@lang('lang_v1.none')</option>
				@foreach($tax_rate as $tax_ratee)
					<option value="{{ $tax_ratee->id }}" data-ratee="{{ $tax_ratee->amount }}" {{ ($tax_ratee->id == $product->tax_id) ? 'selected' : '' }}>{{ $tax_ratee->name }}</option>
				@endforeach
			</select>
		</td>
		
		
		@if($transaction->type == 'sale_invoice' || $transaction->type == 'sale_return_invoice')
		<td class="text-center">
			<input type="hidden" class="form-control further_tax_hidden" name="products[{{$row_count}}][item_further_tax]" />
			<select name="products[{{$row_count}}][further_taax_id]" class="form-control select2 input-sm further_tax" id="further_tax" onchange="calculate_unitprice(this)" placeholder="Please Select">
				<option value="0" data-rate="0">NONE</option>
				@foreach($further_tax as $further_tax)
				    <option value="{{ $further_tax->id }}" data-rate="{{ $further_tax->amount }}" {{ ($product->further_tax == $further_tax->id) ? 'selected' : '' }}>{{ $further_tax->name }}</option>
				@endforeach
			</select>
		</td>

		<td class="{{$hide_tax}}">
			<input type="text" name="products[{{$row_count}}][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{@num_format($unit_price_inc_tax)}}" readonly @if(!$edit_price) readonly @endif @if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$unit_price_inc_tax}}" data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)])}}" @endif>
		</td>
		
		<td>
            <input type="number" name="products[{{$row_count}}][salesman_commission_rate]" class="form-control salesman_commission_rate" value="{{ $product->salesman_commission }}" onkeyup="calculate_unitprice(this)"/>
		</td>
		
		@endif

	@else
		@if(!empty($warranties))
			{!! Form::select("products[$row_count][warranty_id]", $warranties, $warranty_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control']); !!}
		@endif

		@if(!empty($pos_settings['inline_service_staff']))
			<td>
				<div class="form-group">
					<div class="input-group">
						{!! Form::select("products[" . $row_count . "][res_service_staff_id]", $waiters, !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null, ['class' => 'form-control select2 order_line_service_staff', 'placeholder' => __('restaurant.select_service_staff'), 'required' => (!empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1) ? true : false ]); !!}
					</div>
				</div>
			</td>
		@endif
	@endif
	@if($transaction->type != 'sale_invoice' && $transaction->type != 'sale_return_invoice')
		<td class="{{$hide_tax}}">
			<input type="text" name="products[{{$row_count}}][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{@num_format($unit_price_inc_tax)}}" readonly @if(!$edit_price) readonly @endif @if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$unit_price_inc_tax}}" data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)])}}" @endif>
		</td>
	@endif
	@if(!empty($common_settings['enable_product_warranty']) && !empty($is_direct_sell))
		<td>
			{!! Form::select("products[$row_count][warranty_id]", $warranties, $warranty_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control']); !!}
		</td>
	@endif
	<td class="text-center">
		@php
			$subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

		@endphp
		<input type="text"  class="form-control pos_line_total @if(!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{@num_format($product->quantity_ordered*$unit_price_inc_tax )}}">
		{{-- <span class="display_currency pos_line_total_text @if(!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{$product->quantity_ordered*$unit_price_inc_tax}}</span> --}}
	</td>

</tr>