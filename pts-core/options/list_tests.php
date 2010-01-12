<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2010, Phoronix Media
	Copyright (C) 2008 - 2010, Michael Larabel

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

class list_tests implements pts_option_interface
{
	public static function run($r)
	{
		echo pts_string_header("Phoronix Test Suite - Tests");
		$tests_to_display = array();
		foreach(pts_available_tests_array() as $identifier)
		{
			if((pts_is_assignment("LIST_UNSUPPORTED") xor pts_test_supported($identifier)) || pts_is_assignment("LIST_ALL_TESTS"))
			{
				array_push($tests_to_display, $identifier);
			}
		}

		if(count($tests_to_display) == 0)
		{
			echo "\nNo tests found.\n\n";
		}
		else
		{
			foreach($tests_to_display as $identifier)
			{
				$tp = new pts_test_profile($identifier);

				if(getenv("PTS_DEBUG"))
				{
					echo sprintf("%-18ls %-6ls %-6ls %-12ls %-12ls %-4ls %-4ls %-22ls\n", $identifier, $tp->get_test_profile_version(), $tp->get_version(), $tp->get_status(), $tp->get_license(), $tp->get_download_size(), $tp->get_environment_size(), $tp->get_maintainer());
				}
				else if($tp->get_test_title() != null && (pts_is_assignment("LIST_ALL_TESTS") || $tp->is_verified_state()))
				{
					echo sprintf("%-18ls - %-36ls [%s, %10ls]\n", $identifier, $tp->get_test_title(), $tp->get_status(), $tp->get_license());
				}
			}
			echo "\n";
		}
	}
}

?>
