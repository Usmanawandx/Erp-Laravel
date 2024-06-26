@extends('layouts.app')
@if(request('type') == 'cash_received_voucher')
    @section('title', 'Cash Received Voucher')
@else
   @section('title', __('Reciept Voucher'))
@endif

@section('content')
<section class="content-header">
    <h1>
        @if(request('type') == 'cash_received_voucher')
            Cash Received Voucher
        @else
            Reciept Vouchers
        @endif
    </h1>
</section>
<section class="content">
    
       @component('components.widget', ['class' => 'box-primary', 'title' => 'All VOUCHERS'])
        @if (auth()->user()->can('account.receiept_vouchers.add'))
            @slot('tool')
                <div class="box-tools">
                    @if(request('type') == 'cash_received_voucher')
                        <a class="btn btn-block btn-primary" href="{{action('AccountController@credit_voucher',['type' => 'cash_received_voucher'])}}">
                    @else
                        <a class="btn btn-block btn-primary" href="{{action('AccountController@credit_voucher')}}">
                    @endif
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endif
      <table class="table table-bordered table-striped ajax_view hide-footer dataTable table-styling table-hover table-primary" id="purchase_order_table" style="width: 100%;">
         <thead>
            <tr>
               <th>SNo#</th>
               <th>Voucher No#</th>
               <th>Transaction Date</th>
               <th>Description</th>
               <th>Amount</th>
               <th>Action</th>
            </tr>
         </thead>
         <tbody>
            @foreach($list as $v)
            <tr>
               <td>{{$loop->iteration}}</td>
               <td>{{$v->reff_no}}</td>
               <td>{{date('d-m-Y', strtotime($v->operation_date))}}</td>
               <td>{{$v->note}}</td>
               <td>{{ number_format($v->total_amount,2) }}</td>
               <td>
                  <a href="{{route('show.Invoiceprt',$v->reff_no)}}" class="btn btn-sm btn-primary" id=""><i class="fas fa-eye"></i></a>
                  @if (auth()->user()->can('account.receiept_vouchers.edit'))
                    <a href="/account/cv_edit/{{$v->reff_no}}" class="btn btn-sm btn-success" ><i class="fas fa-edit"></i></a>
                  @endif
                  @if (auth()->user()->can('account.receiept_vouchers.delete'))
                    <a href="/account/cv_delete/{{$v->reff_no}}" class="btn btn-sm btn-danger del_btn" ><i class="fas fa-trash"></i></a>
                  @endif
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
      @endcomponent
</section>

@endsection
@section('javascript')
<script>
$(document).ready( function () {
    $('.ajax_view').DataTable();
});

$(document).on('click', '.del_btn', function(e){
   e.preventDefault();
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete)=>{
            if(willDelete){
               window.location = this.href;
            }
        });
    })
</script>
@endsection