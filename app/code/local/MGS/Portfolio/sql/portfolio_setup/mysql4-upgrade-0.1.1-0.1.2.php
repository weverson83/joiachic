<?php

$installer = $this;

$installer->startSetup();

$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('mgs_portfolio_items');
$cols = $installer->getConnection()->fetchCol($fieldsSql);

if (!in_array('portfolio_date', $cols))
{
    $installer->run("ALTER TABLE `{$this->getTable('mgs_portfolio_items')}` ADD `portfolio_date` datetime NULL");
}


$installer->endSetup(); 