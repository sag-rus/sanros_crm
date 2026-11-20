<?php
require_once __DIR__.'/../core/document/contract_counterparty_accounts.php';

$expected = array(
	array('label' => 'Казначейский счет', 'number' => '0321464300000015109'),
	array('label' => 'Единый казначейский счет', 'number' => '40102810445370000043'),
);

if(contract_counterparty_accounts(199437) !== $expected){
	throw new RuntimeException('Wrong treasury account override for request 199437');
}

foreach(array(199436, 199438, 0, null) as $requestId){
	if(contract_counterparty_accounts($requestId) !== null){
		throw new RuntimeException('Treasury account override leaked to another request');
	}
}

echo "PASS: treasury account override is limited to request 199437\n";
