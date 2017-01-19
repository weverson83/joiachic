<?php

$this->startSetup();

$column 	= 'cc_installments';
$hasColumn 	= $this->_conn->query("SHOW COLUMNS FROM `{$this->getTable('sales/quote_payment')}` WHERE field = '".$column."';");

if (count($hasColumn) == 0) { // Se não existir, adiciona a coluna.
	$this->run("ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `cc_installments` INT NULL DEFAULT NULL;");
}

$hasColumn 	= $this->_conn->query("SHOW COLUMNS FROM `{$this->getTable('sales/order_payment')}` WHERE field = '".$column."';");

if (count($hasColumn) == 0) { // Se não existir, adiciona a coluna.
	$this->run("ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `cc_installments` INT NULL DEFAULT NULL;");
}

$this->run("REPLACE INTO `{$this->getTable('sales/order_status')}` (status, label) VALUES ('authorized', 'Autorizado');");

$this->endSetup();