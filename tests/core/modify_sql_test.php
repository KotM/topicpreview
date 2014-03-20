<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace vse\topicpreview\tests\core;

class modify_sql_test extends topic_preview_base
{
	public static function topic_preview_data()
	{
		return array(
			array(
				array(
					// First post by user 1, no last post
					1 => array(
						'First message', 'avatar1.jpg', 0,
						'First message', 'avatar1.jpg', 0,
					),
					// First post by user 2, last post by user 3
					2 => array(
						'Second message', 'avatar2.jpg', 0,
						'Third message', '', 0,
					),
					// First post by user 3, no last post
					3 => array(
						'Fourth message', '', 0,
						'Fourth message', '', 0,
					),
				),
			),
		);
	}

	/**
	* @dataProvider topic_preview_data
	*/
	public function test_modify_sql_array($expected)
	{
		// We need a basic SQL query to begin with
		$sql_array = array(
			'SELECT'	=> 't.*',
			'FROM'		=> array(
				'phpbb_topics'		=> 't'
			),
			'LEFT_JOIN'	=> array(),
			'WHERE'		=> 't.forum_id = 2',
			'ORDER_BY'	=> 't.topic_time ASC',
		);

		// Get an instance of topic preview class
		$topic_preview_manager = $this->topic_preview_manager();

		// Modify the sql_array for topic previews
		$sql_array = $topic_preview_manager->modify_sql($sql_array);

		// Build the SQL query
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		// Test that we get the expected result
		$this->assertEquals($expected, $this->run_query_helper($sql));
	}

	/**
	* @dataProvider topic_preview_data
	*/
	public function test_modify_sql_string($expected)
	{
		// We need a basic SQL query to begin with
		$sql_select = 't.*';
		$sql_from = 'phpbb_topics t';
		$sql_where = 't.forum_id = 2 ORDER BY t.topic_time ASC';

		// Get an instance of topic preview class
		$topic_preview_manager = $this->topic_preview_manager();

		// Modify the sql strings for topic previews
		$sql_select = $topic_preview_manager->modify_sql($sql_select, 'SELECT');
		$sql_from = $topic_preview_manager->modify_sql($sql_from, 'JOIN');

		// Build the SQL query
		$sql = "SELECT $sql_select
			FROM $sql_from
			WHERE $sql_where";

		// Test that we get the expected result
		$this->assertEquals($expected, $this->run_query_helper($sql));
	}

	public function run_query_helper($sql)
	{
		$rowset = array();
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['topic_id']] = array(
				$row['first_post_text'],
				$row['first_poster_avatar'],
				$row['first_poster_avatar_type'],
				$row['last_post_text'],
				$row['last_poster_avatar'],
				$row['last_poster_avatar_type'],
			);
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}
}