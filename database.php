<?php

/**
 * @package Board Sorting Method
 * @version 1.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

if (!isset($smcFunc['db_create_table']))
	db_extend('packages');

// Add sorting column
$smcFunc['db_add_column'](
	'{db_prefix}boards', 
	[
		'name' => 'sorting_method',
		'type' => 'varchar',
		'size' => 50,
		'not_null' => false,
	]
);
// Add order column
$smcFunc['db_add_column'](
	'{db_prefix}boards', 
	[
		'name' => 'sorting_order',
		'type' => 'varchar',
		'size' => 4,
		'not_null' => false,
	]
);