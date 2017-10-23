<?php

function show_payment_card_account($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking()){
		$type = $data["type"];
		$payment = new BookingPayment;
		$request = $payment->show_payment_card($type);
		unset($payment);
		return $request;
	}
	return FALSE;
}

function register_payment($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking()){
		$type = $data["type"];
		$payment = new BookingPayment;
		$request = $payment->registration_payment($type);
		unset($payment);
		return $request;
	}
}

function success_payment($connect, $data){
//	if(CheckAuthTuristCabinet::check_authorization_booking()){
		$bid_pay = $data["bid"];
		$payment = new BookingPayment;
		$request = $payment->deposit_payment($bid_pay);
		unset($payment);
		return $request;
//	}
}

function show_payment_card_account_module($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking_object()){
		$type = $data["type"];
		$payment = new BookingModuleObjectPayment;
		$request = $payment->show_payment_card($type);
		unset($payment);
		return $request;
	}
	return FALSE;
}

function register_payment_module($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking_object()){
		$type = $data["type"];
		$payment = new BookingModuleObjectPayment;
		$request = $payment->registration_payment($type);
		unset($payment);
		return $request;
	}
}

function success_payment_module($connect, $data){
	if(CheckAuthTuristCabinet::check_authorization_booking_object()){
		$bid_pay = $data["bid"];
		$payment = new BookingModuleObjectPayment;
		$request = $payment->check_status_payment($bid_pay);
		if($request == 2){
			$request = $payment->deposit_payment($bid_pay);
		}
		unset($payment);
		return $request;
	}
}

?>
