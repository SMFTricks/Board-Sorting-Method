<?php

/**
 * @package Board Sorting Method
 * @version 1.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 */

class BoardSorting
{
	/**
	 * @var array The sorting methods
	 */
	private $_sorting_methods = [
		'last_post' => [
			'sort' => 't.id_last_msg',
		],
		'subject' => [ 
			'sort' => 'mf.subject',
		],
		'starter' => [
			'sort' => 'COALESCE(memf.real_name, mf.poster_name)',
			'label' => 'started_by',
		],
		'last_poster' => [
			'sort' => 'COALESCE(meml.real_name, ml.poster_name)',
		],
		'replies' => [
			'sort' => 't.num_replies'
		],
		'views' => [
			'sort' => 't.num_views'
		],
		'first_post' => [
			'sort' => 't.id_topic',
		],
	];

	/**
	 * The hooks
	 * 
	 * @return void
	 */
	public static function hooks() : void
	{
		global $sourcedir;

		// Board Info
		add_integration_function('integrate_load_board', __CLASS__ . '::load_board', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_board_info', __CLASS__ . '::board_info', false, $sourcedir . '/Class-BoardSorting.php');

		// MessageIndex
		add_integration_function('integrate_pre_messageindex', __CLASS__ . '::pre_messageindex#', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_message_index', __CLASS__ . '::message_index#', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_messageindex_buttons', __CLASS__ . '::message_index_buttons#', false, $sourcedir . '/Class-BoardSorting.php');

		// Manage Boards
		add_integration_function('integrate_pre_boardtree', __CLASS__ . '::pre_boardtree#', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_boardtree_board', __CLASS__ . '::boardtree_board#', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_modify_board', __CLASS__ . '::modify_boards#', false, $sourcedir . '/Class-BoardSorting.php');
		add_integration_function('integrate_edit_board', __CLASS__ . '::edit_board#', false, $sourcedir . '/Class-BoardSorting.php');
	}

	/**
	 * Load the board info
	 * 
	 * @param array $columns The board columns
	 */
	public static function load_board(&$columns)
	{
		$columns[] = 'sorting_method';
		$columns[] = 'sorting_order';
	}

	/**
	 * Add the sorting info to the board info
	 * 
	 * @param array $board_info The board info
	 */
	public static function board_info(&$board_info, $row)
	{
		// Sorting method
		$row['sorting_method'] = $row['sorting_method'] ?? 'last_post';
		$board_info['sorting_method'] = $row['sorting_method'];

		// Sorting order
		$row['sorting_order'] = $row['sorting_order'] ?? 'desc';
		$board_info['sorting_order'] = $row['sorting_order'];
	}

	/**
	 * Insert the order before the rest of the logic
	 * 
	 * @return void
	 */
	public function pre_messageindex() : void
	{
		global $board_info;

		// No sorting method, or they selected the default sorting method
		if (!isset($board_info['sorting_order']) || empty($board_info['sorting_order']) || isset($_REQUEST['sort']))
			return;

		// Okay, now we use the sorting order
		if ($board_info['sorting_order'] == 'asc')
		{
			$_REQUEST['asc'] = '';
			unset($_REQUEST['desc']);
		}
		else
		{
			$_REQUEST['desc'] = '';
			unset($_REQUEST['asc']);
		}
	}

	/**
	 * Add the sorting methods to the message index
	 * 
	 * @return void
	 */
	public function message_index() : void
	{
		global $board_info, $context;

		// No sorting method, or they selected the default sorting method
		if (!isset($board_info['sorting_method']) || empty($board_info['sorting_method']) || $board_info['sorting_method'] == 'last_post')
			return;

		// Do the magic?
		if (!isset($_GET['sort']) || !isset($this->_sorting_methods[$_GET['sort']]))
		{
			$context['sort_by'] = $board_info['sorting_method'];
			$_REQUEST['sort'] = $this->_sorting_methods[$board_info['sorting_method']]['sort'];
		}
	}

	/**
	 * Add the sorting methods to the message index
	 * 
	 * @return void
	 */
	public function message_index_buttons() : void
	{
		global $context, $txt;

		// Sorting this again, what a hassle
		if (isset($_GET['sort']) && isset($this->_sorting_methods[$_GET['sort']]))
		{
			$context['sort_by'] = $_GET['sort'];
			$_REQUEST['sort'] = $this->_sorting_methods[$_GET['sort']]['sort'];
		}

		// Loop through the topics_headers after any other mod (hopefully)
		foreach ($context['topics_headers'] as $key => $header)
		{
			// Let's keep the URL, get it using regex, from the a href
			$url = preg_match('/href="(.*?)"/', $header, $matches) ? $matches[1] : '';
			$context['topics_headers'][$key] = '<a href="' . $url . ($context['sort_by'] == $key && $context['sort_direction'] == 'up' ? (isset($_REQUEST['desc']) ? ';desc' : '') : '') . '">' . $txt[$key] . ($context['sort_by'] == $key ? '<span class="main_icons sort_' . $context['sort_direction'] . '"></span>' : '') . '</a>';
		}
	}

	/**
	 * Pre Board Tree
	 * 
	 * @return void
	 */
	public function pre_boardtree(&$boardColumns) : void
	{
		$boardColumns[] = 'b.sorting_method';
		$boardColumns[] = 'b.sorting_order';
	}

	/**
	 * Board Tree
	 * 
	 * @return void
	 */
	public function boardtree_board($row) : void
	{
		global $boards;

		$boards[$row['id_board']]['sorting_method'] = $row['sorting_method'];
		$boards[$row['id_board']]['sorting_order'] = $row['sorting_order'];
	}

	/**
	 * Modify Boards
	 * 
	 * @return void
	 */
	public function modify_boards($id, $boardOptions, &$boardUpdates, &$boardUpdateParameters) : void
	{
		$boardOptions['sorting_method'] = isset($_POST['BoardSortingMethod']) ? $_POST['BoardSortingMethod'] : '';
		$boardOptions['sorting_order'] = isset($_POST['BoardSortingOrder']) ? $_POST['BoardSortingOrder'] : '';

		// Sorting method
		if (isset($boardOptions['sorting_method']))
		{
			$boardUpdates[] = 'sorting_method = {string:sorting_method}';
			$boardUpdateParameters['sorting_method'] = $boardOptions['sorting_method'];
		}

		// Sorting order
		if (isset($boardOptions['sorting_order']))
		{
			$boardUpdates[] = 'sorting_order = {string:sorting_order}';
			$boardUpdateParameters['sorting_order'] = $boardOptions['sorting_order'];
		}
	}

	/**
	 * Edit board
	 * 
	 * @return void
	 */
	public function edit_board()
	{
		global $context, $txt;

		loadLanguage('BoardSorting/');

		$options = '';
		
		foreach ($this->_sorting_methods as $by => $sorting)
		{
			$options .= '
				<option value="' . $by . '"' . ($context['board']['sorting_method'] == $by ? ' selected="selected"' : '') . '>' . $txt[(isset($sorting['label']) ? $sorting['label'] : $by)] . '</option>';
		}

		// Sorting method
		$context['custom_board_settings']['sorting_method'] = [
			'dt' => '<label for="BoardSortingMethod"><strong>'. $txt['BoardSorting_method_default']. '</strong></label>',
			'dd' => '<select name="BoardSortingMethod" id="BoardSortingMethod">' . $options . '</select>',
		];

		// Sorting order
		$context['custom_board_settings']['sorting_order'] = [
			'dt' => '<label for="BoardSortingOrder"><strong>'. $txt['BoardSorting_method_order']. '</strong></label>',
			'dd' => '<select name="BoardSortingOrder" id="BoardSortingOrder">
				<option value="desc"' . ($context['board']['sorting_order'] == 'desc' ? ' selected="selected"' : '') . '>' . $txt['BoardSorting_method_desc'] . '</option>
				<option value="asc"' . ($context['board']['sorting_order'] == 'asc' ? ' selected="selected"' : '') . '>' . $txt['BoardSorting_method_asc'] . '</option>
			</select>',
		];
	}
}