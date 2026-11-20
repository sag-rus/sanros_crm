<?php

/**
 * Request-specific counterparty account labels and numbers for contracts.
 *
 * Returning null keeps the payer details from the CRM unchanged.
 */
function contract_counterparty_accounts($requestId){
	if((int)$requestId !== 199437) return null;

	return array(
		array(
			'label' => 'Казначейский счет',
			'number' => '0321464300000015109',
		),
		array(
			'label' => 'Единый казначейский счет',
			'number' => '40102810445370000043',
		),
	);
}
