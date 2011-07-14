<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2011, Phoronix Media
	Copyright (C) 2008 - 2011, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_user_io
{
	public static function read_user_input()
	{
		return trim(fgets(STDIN));
	}
	public static function prompt_user_input($question, $allow_null = false)
	{
		do
		{
			echo PHP_EOL . $question . ': ';
			$answer = pts_user_io::read_user_input();
		}
		while(!$allow_null && empty($answer));

		return $answer;
	}
	public static function display_interrupt_message($message)
	{
		if(!empty($message))
		{
			echo $message . PHP_EOL;

			if((pts_c::$test_flags ^ pts_c::batch_mode) && (pts_c::$test_flags ^ pts_c::auto_mode))
			{
				echo PHP_EOL . 'Hit Any Key To Continue...' . PHP_EOL;
				pts_user_io::read_user_input();
			}
		}
	}
	public static function display_text_list($list_items, $line_start = '- ')
	{
		$list = null;

		foreach($list_items as &$item)
		{
			$list .= $line_start . $item . PHP_EOL;
		}

		return $list;
	}
	public static function prompt_bool_input($question, $default = true, $question_id = 'UNKNOWN')
	{
		// Prompt user for yes/no question
		if((pts_c::$test_flags & pts_c::batch_mode))
		{
			switch($question_id)
			{
				default:
					$auto_answer = 'true';
					break;
			}

			$answer = pts_strings::string_bool($auto_answer);
		}
		else
		{
			$question .= ' (' . ($default == true ? 'Y/n' : 'y/N') . '): ';

			do
			{
				pts_client::$display->generic_prompt($question);
				$input = strtolower(pts_user_io::read_user_input());
			}
			while($input != 'y' && $input != 'n' && $input != '');

			switch($input)
			{
				case 'y':
					$answer = true;
					break;
				case 'n':
					$answer = false;
					break;
				default:
					$answer = $default;
					break;
			}
		}

		return $answer;
	}
	public static function prompt_text_menu($user_string, $options_r, $allow_multi_select = false, $return_index = false)
	{
		$option_count = count($options_r);

		if($option_count == 1)
		{
			return $return_index ? pts_arrays::last_element(array_keys($options_r)) : array_pop($options_r);
		}

		$select = array();

		do
		{
			echo PHP_EOL;
			$key_index = array();
			foreach(array_keys($options_r) as $i => $key)
			{
				$key_index[($i + 1)] = $key;
				echo ($i + 1) . ': ' . str_repeat(' ', strlen($option_count) - strlen(($i + 1))) . $options_r[$key] . PHP_EOL;
			}
			echo PHP_EOL . $user_string . ': ';
			$select_choice = pts_user_io::read_user_input();

			foreach(($allow_multi_select ? pts_strings::comma_explode($select_choice) : array($select_choice)) as $choice)
			{
				if(in_array($choice, $options_r))
				{
					array_push($select, array_search($choice, $options_r));
				}
				else if(isset($key_index[$choice]))
				{
					array_push($select, $key_index[$choice]);
				}
			}
		}
		while(!isset($select[0]));

		if($return_index == false)
		{
			foreach($select as &$index)
			{
				$index = $options_r[$index];
			}
		}

		return implode(',', $select);
	}
}

?>
