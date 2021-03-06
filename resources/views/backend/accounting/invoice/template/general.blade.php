@extends('layouts.app')

@section('content')
<style type="text/css">
@media all {
	.table th {
		background-color: #2a77d6 !important;
		color: #ffffff;
	}
	
	.base_color{
		background: #2a77d6 !important;
	}
}
</style>  

<div class="row">
	<div class="col-12">
	
	@include('backend.accounting.invoice.invoice-actions')
	@php $date_format = get_company_option('date_format','Y-m-d'); @endphp	
	
	<div class="card clearfix">
	
	<span class="panel-title d-none">{{ _lang('View Invoice') }}</span>
	
    @php $base_currency = base_currency(); @endphp	
	@php $currency = currency(); @endphp

	@if($invoice->related_to == 'contacts' && isset($invoice->client))
		@php $client_currency = $invoice->client->currency; @endphp
		@php $client = $invoice->client; @endphp
	@else 
		@php $client_currency = $invoice->project->client->currency; @endphp
		@php $client = $invoice->project->client; @endphp
	@endif
			 
	<div class="card-body">
		<div class="invoice-box" id="invoice-view">
			<div class="col-md-12">
				<table cellpadding="0" cellspacing="0">
					<tbody>
						 <tr class="top">
							<td colspan="2">
								<table>
									<tbody>
										 <tr>
											<td>
												<b>{{ _lang('Invoice') }} #: </b>  {{ $invoice->invoice_number }}<br>
												<b>{{ _lang('Created') }}: </b>{{ date($date_format, strtotime( $invoice->invoice_date)) }}<br>
												<b>{{ _lang('Due Date') }}: </b>{{ date($date_format, strtotime( $invoice->due_date)) }}							
												<div class="invoice-status {{ strtolower($invoice->status) }}">{{ _dlang(str_replace('_',' ',$invoice->status)) }}</div>
											</td>
											<td class="invoice-logo">
												<img src="{{ get_company_logo() }}" class="wp-100">
											</td>
										 </tr>
									</tbody>
								</table>
							</td>
						 </tr>
						 <tr class="information">
							<td colspan="2">
								 
								<div class="row">
									<div class="invoice-col-6 pt-3">
										 <h5><b>{{ _lang('Invoice To') }}</b></h5>
										 {{ $client->contact_name }}<br>
										 {{ $client->contact_email }}<br>
										 {!! $client->company_name != '' ? clean($client->company_name).'<br>' : '' !!}
										 {!! $client->address != '' ? clean($client->address).'<br>' : '' !!}
										 {!! $client->vat_id != '' ? _lang('VAT ID').': '.clean($client->vat_id).'<br>' : '' !!}
										 {!! $client->reg_no != '' ? _lang('REG NO').': '.clean($client->reg_no).'<br>' : '' !!}
									                       
									</div>

									<!--Company Address-->
									<div class="invoice-col-6 pt-3">
										<div class="d-inline-block float-md-right">	 
											 <h5><b>{{ _lang('Company Details') }}</b></h5>
											 {{ get_company_option('company_name') }}<br>
											 {{ get_company_option('address') }}<br>
											 {{ get_company_option('email') }}<br>
											 {!! get_company_option('vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_option('vat_id')).'<br>' : '' !!}
												{!! get_company_option('reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_option('reg_no')).'<br>' : '' !!}
											 <br>
											 <!--Invoice Payment Information-->
											 <h4>{{ _lang('Invoice Total') }}: &nbsp;{{ decimalPlace($invoice->grand_total,$currency) }}</h4>
											 @if($invoice->client->currency != $base_currency)
												<h4>{{ decimalPlace($invoice->converted_total, currency($invoice->client->currency)) }}</h4>	
											 @endif
										</div>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				 </table>
			 </div>
			 <!--End Invoice Information-->
			 
			 <!--Invoice Product-->
			 <div class="col-md-12">
			 	<div class="table-responsive">
					<table class="table">
						 <thead class="base_color">
							 <tr>
								 <th>{{ _lang('Name') }}</th>
								 <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
								 <th class="text-right">{{ _lang('Unit Cost') }}</th>
								 <th class="text-right wp-100">{{ _lang('Discount') }}</th>
								 <th>{{ _lang('Tax') }}</th>
								 <th class="text-right">{{ _lang('Line Total') }}</th>
							 </tr>
						 </thead>
						 <tbody id="invoice">
							 @foreach($invoice->invoice_items as $item)
								<tr id="product-{{ $item->item_id }}">
									<td>
										<b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
									</td>
									<td class="text-center">{{ $item->quantity }}</td>
									<td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
									<td class="text-right">{{ decimalPlace($item->discount, $currency) }}</td>
									<td>{!! clean(object_to_tax($item->taxes, 'name')) !!}</td>
									<td class="text-right">{{ decimalPlace($item->sub_total, $currency) }}</td>
								</tr>
							 @endforeach
						 </tbody>
					</table>
				</div>
			 </div>
			 <!--End Invoice Product-->	
			 
		
			 <!--Summary Table-->
			 <div class="col-md-3 float-right">
				<table class="table table-bordered">
					<tbody>
						<tr>
							 <td><b>{{ _lang('Sub Total') }}</b></td>
							 <td class="text-right">
								 <b>{{ decimalPlace($invoice->grand_total - $invoice->tax_total, $currency) }}</b>
							 </td>
						</tr>
						@foreach($invoice_taxes as $tax)
						<tr>
							 <td>{{ $tax->name }}</td>
							  <td class="text-right">
								<span>{{ decimalPlace($tax->tax_amount, $currency) }}</span>
							 </td>
						</tr>
						@endforeach
						<tr>
							 <td><b>{{ _lang('Grand Total') }}</b></td>
							 <td class="text-right">
								 <b>{{ decimalPlace($invoice->grand_total, $currency) }}</b>
								 @if($client_currency != $base_currency)
									<br><b>{{ decimalPlace($invoice->converted_total, currency($client_currency)) }}</b>
								 @endif
							 </td>
						</tr>
						<tr>
							 <td>{{ _lang('Total Paid') }}</td>
							 <td class="text-right">
								<span>{{ decimalPlace($invoice->paid, $currency) }}</span>
								@if($client_currency != $base_currency)
									<br><span>{{ decimalPlace(convert_currency($base_currency, $client_currency, $invoice->paid), currency($client_currency)) }}</span>	
								@endif
							 </td>
						</tr>
						@if($invoice->status != 'Paid')
							<tr>
								 <td>{{ _lang('Amount Due') }}</td>
								 <td class="text-right">
									<span>{{ decimalPlace(($invoice->grand_total - $invoice->paid), $currency) }}</span>
									@if($client_currency != $base_currency)
									<br><span>{{ decimalPlace(convert_currency($base_currency, $client_currency, ($invoice->grand_total - $invoice->paid)), currency($client_currency)) }}</span>	
									@endif
								 </td>
							</tr>
						@endif
					</tbody>
				</table>
			 </div>
			 <!--End Summary Table-->
			 
			 <div class="clearfix"></div>
			 
			 <!--Related Transaction-->
			 @if( ! $transactions->isEmpty() )
                <div class="col-md-12">
                	<div class="table-responsive">
						<table class="table table-bordered mt-2">
							<thead>
								<tr>
									<th>{{ _lang('Date') }}</th>
									<th>{{ _lang('Account') }}</th>
									<th class="text-right">{{ _lang('Amount') }}</th>
									<th>{{ _lang('Payment Method') }}</th>
								</tr>
							</thead>
							<tbody>	  
							   @foreach($transactions as $transaction)
									<tr id="transaction-{{ $transaction->id }}">
										<td>{{ date($date_format,strtotime($transaction->trans_date)) }}</td>
										<td>{{ $transaction->account->account_title.' - '.$transaction->account->account_currency }}</td>
										<td class="text-right">{{ decimalPlace($transaction->amount, currency($transaction->account->account_currency)) }}</td>
										<td>{{ $transaction->payment_method->name }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div> 
			 @endif
			 <!--END Related Transaction-->		
			 
			 <!--Invoice Note-->
			 @if($invoice->note  != '')
				<div class="col-md-12">
					<div class="invoice-note">{{ $invoice->note }}</div>
			    </div> 
			 @endif
			 <!--End Invoice Note-->
			 
			 <!--Invoice Footer Text-->
			 @if(get_company_option('invoice_footer')  != '')
				<div class="col-md-12">
					<div class="invoice-note">{!! xss_clean(get_company_option('invoice_footer')) !!}</div>
			    </div> 
			 @endif
			 <!--End Invoice Note-->
		</div>
	 </div>
  </div>
 </div>
</div>
@endsection


