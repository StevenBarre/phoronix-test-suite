<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009, Phoronix Media
	Copyright (C) 2009, Michael Larabel

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

class gui_gtk implements pts_option_interface
{
	public static function run($r)
	{
		pts_load_function_set("gtk");

		if(!extension_loaded("gtk") && !extension_loaded("php-gtk"))
		{
			echo "\nThe PHP GTK module must be loaded for the GUI.\nThis module can be found @ http://gtk.php.net/\n\n";
			return;
		}

		gui_gtk::show_main_interface();
	}
	public static function kill_gtk_window($window = "")
	{
		Gtk::main_quit();
	}
	public static function show_main_interface()
	{
		$window = new GtkWindow();
		$window->set_title("Phoronix Test Suite v" . PTS_VERSION);
		$window->set_size_request(620, 400);
		$window->set_resizable(false);
		$window->connect_simple("destroy", array("Gtk", "main_quit"));
		pts_set_assignment("GTK_OBJ_WINDOW", $window);
		$vbox = new GtkVBox();
		$window->add($vbox);

		// Menu Setup
		$view_menu = array();
		array_push($view_menu, new pts_gtk_menu_item("System Information", array("gui_gtk", "show_system_info_interface")));
		array_push($view_menu, null);
		array_push($view_menu, new pts_gtk_menu_item(array("Tests", "Suites"), array("gui_gtk", "radio_test_suite_select"), "RADIO_BUTTON"));
		array_push($view_menu, null);

		foreach(pts_subsystem_test_types() as $subsystem)
		{
			array_push($view_menu, new pts_gtk_menu_item($subsystem, array("gui_gtk", "check_test_type_select"), "CHECK_BUTTON", null, true));
		}

		$main_menu_items = array(
		"File" => array(//new pts_gtk_menu_item("Install / Update All"), new pts_gtk_menu_item("Merge Results"), 
		new pts_gtk_menu_item("Quit", array("gui_gtk", "kill_gtk_window"), "STRING", Gtk::STOCK_QUIT)),
		"Edit" => array(new pts_gtk_menu_item("Preferences", array("gui_gtk", "show_preferences_interface"), "STRING", Gtk::STOCK_PREFERENCES)),
		"View" => $view_menu,
		"Help" => array(new pts_gtk_menu_item("About", array("gui_gtk", "show_about_interface"), "STRING", Gtk::STOCK_ABOUT))
		);
		pts_gtk_add_menu($vbox, $main_menu_items);

		// Main Area
		$window_fixed = new GtkFixed();
			
		$vbox->pack_start($window_fixed);

		// Details Frame
		$main_frame = new GtkFrame("Welcome");
		$main_frame->set_size_request(280, 330);
		pts_set_assignment("GTK_OBJ_MAIN_FRAME", $main_frame);

		$main_frame_vbox = new GtkVBox();
		$main_frame->add($main_frame_vbox);
		pts_set_assignment("GTK_OBJ_MAIN_FRAME_BOX", $main_frame_vbox);

		$logo = GtkImage::new_from_file(RESULTS_VIEWER_DIR . "pts-logo.png");
		$logo->set_size_request(158, 82);
		$main_frame_vbox->pack_start($logo);

		$label_welcome = new GtkLabel("The Phoronix Test Suite is the most comprehensive testing and benchmarking platform available for the Linux operating system. This software is designed to effectively carry out both qualitative and quantitative benchmarks in a clean, reproducible, and easy-to-use manner.");
		$label_welcome->set_line_wrap(true);
		$label_welcome->set_size_request(260, 200);
		$main_frame_vbox->pack_start($label_welcome);

		$window_fixed->put($main_frame, 10, 10);

		// Notebook Area
		$main_notebook = new GtkNotebook();
		$main_notebook->set_size_request(310, 330);
		pts_set_assignment("GTK_OBJ_MAIN_NOTEBOOK", $main_notebook);

		$window_fixed->put($main_notebook, 305, 10);

		gui_gtk::update_main_notebook();

		// Bottom Line

		$check_mode_batch = new GtkCheckButton("Batch Mode");
		$window_fixed->put($check_mode_batch, 20, 345);

		$defaults_mode_batch = new GtkCheckButton("Defaults Mode");
		$window_fixed->put($defaults_mode_batch, 155, 345);

		$details_img = GtkImage::new_from_stock(Gtk::STOCK_FIND, Gtk::ICON_SIZE_SMALL_TOOLBAR);
		$details_button = new GtkButton();
		$details_button->connect_simple("clicked", array("gui_gtk", "details_button_clicked"));
		$details_button->set_image($details_img);
		$details_button->set_size_request(150, 30);
		$window_fixed->put($details_button, 350, 342);
		$details_button->set_label("More Information");
		pts_set_assignment("GTK_OBJ_DETAILS_BUTTON", $details_button);

		$run_img = GtkImage::new_from_stock(Gtk::STOCK_EXECUTE, Gtk::ICON_SIZE_SMALL_TOOLBAR);
		$run_button = new GtkButton("Run");
		$run_button->connect_simple("clicked", array("gui_gtk", "show_run_confirmation_interface"));
		$run_button->set_image($run_img);
		$run_button->set_size_request(100, 30);
		$window_fixed->put($run_button, 510, 342);
		pts_set_assignment("GTK_OBJ_RUN_BUTTON", $run_button);

		$window->show_all();
		Gtk::main();
	}
	public static function update_details_frame_from_select($object)
	{
		$identifier = pts_gtk_selected_item($object);

		pts_set_assignment("GTK_SELECTED_ITEM", $identifier);
		$gtk_obj_main_frame = pts_read_assignment("GTK_OBJ_MAIN_FRAME");
		$gtk_obj_main_frame->set_label($identifier);

		if(pts_is_assignment("GTK_OBJ_MAIN_FRAME_BOX"))
		{
			$gtk_obj_main_frame_box = pts_read_assignment("GTK_OBJ_MAIN_FRAME_BOX");
			$gtk_obj_main_frame->remove($gtk_obj_main_frame_box);
		}

		$root_vbox = new GtkVBox();
		$gtk_obj_main_frame->add($root_vbox);
		pts_set_assignment("GTK_OBJ_MAIN_FRAME_BOX", $root_vbox);

		$hbox = new GtkHBox();
		$root_vbox->add($hbox);

		$vbox_left = new GtkVBox();
		$vbox_right = new GtkVBox();
		$hbox->pack_start($vbox_left);
		$hbox->pack_start($vbox_right);

		$info_r = array();

		// PTS Test
		if(pts_read_assignment("GTK_MAIN_NOTEBOOK_SELECTED") == "Test Results")
		{
			$result_file = new pts_test_result_info_details(SAVE_RESULTS_DIR . $identifier . "/composite.xml");

			$info_r["Title"] = $result_file->get_title();
			$info_r["Test"] = $result_file->get_suite();
		}
		else if(pts_read_assignment("GTK_TEST_OR_SUITE") == "TEST")
		{
			$identifier = pts_test_name_to_identifier($identifier);
			$test_profile = new pts_test_profile_details($identifier);

			$info_r["Maintainer"] = $test_profile->get_maintainer();
			$info_r["Test Type"] = $test_profile->get_test_hardware_type();
			$info_r["Software Type"] = $test_profile->get_test_software_type();
			$info_r["License"] = $test_profile->get_license();

			if($test_profile->get_download_size() > 0)
			{
				$info_r["Download Size"] = $test_profile->get_download_size() . " MB";
			}
			if($test_profile->get_environment_size() > 0)
			{
				$info_r["Environment Size"] = $test_profile->get_environment_size() . " MB";
			}
			$label_description = new GtkLabel($test_profile->get_description());
			$label_description->set_line_wrap(true);
			$label_description->set_size_request(260, 10);
			$root_vbox->add($label_description);
		}
		else if(pts_read_assignment("GTK_TEST_OR_SUITE") == "SUITE")
		{
			$identifier = pts_suite_name_to_identifier($identifier);
			$test_suite = new pts_test_suite_details($identifier);

			$info_r["Maintainer"] = $test_suite->get_maintainer();
			$info_r["Suite Type"] = $test_suite->get_suite_type();

			$label_description = new GtkLabel($test_suite->get_description());
			$label_description->set_line_wrap(true);
			$label_description->set_size_request(260, 10);
			$root_vbox->add($label_description);

			$tests = pts_contained_tests($identifier, false, false, true);

			$label_all = new GtkLabel("Tests: " . implode(", ", $tests));
			$label_all->set_line_wrap(true);
			$label_all->set_size_request(260, 10);
			$root_vbox->add($label_all);
		}

		foreach($info_r as $head => $show)
		{
			$label_head = new GtkLabel($head);
			$vbox_left->pack_start($label_head);

			$label_show = new GtkLabel($show);
			$vbox_right->pack_start($label_show);
		}

		gui_gtk::update_run_button();
		gui_gtk::redraw_main_window();
	}
	public static function update_main_notebook()
	{
		$main_notebook = pts_read_assignment("GTK_OBJ_MAIN_NOTEBOOK");

		if($main_notebook == null)
		{
			return;
		}

		foreach($main_notebook->get_children() as $child)
		{
			$main_notebook->remove($child);
		}

		if(pts_read_assignment("GTK_TEST_OR_SUITE") == "SUITE")
		{
			// Installed Suites
			if(count(pts_installed_tests_array()) > 0)
			{
				$installed_suites = array();

				foreach(pts_available_suites_array() as $suite)
				{
					if(!pts_suite_needs_updated_install($suite))
					{
						array_push($installed_suites, $suite);
					}
				}

				$installed_suites = array_map("pts_suite_identifier_to_name", $installed_suites);
				sort($installed_suites);
				$installed_suites = pts_gtk_add_table(array("Suite"), $installed_suites, array("gui_gtk", "update_details_frame_from_select"));
				pts_gtk_add_notebook_tab($main_notebook, $installed_suites, "Installed Suites");
			}

			// Available Suites
			$test_suites = pts_supported_suites_array();
			$to_show_names = array();
			$to_show_types = pts_read_assignment("GTK_TEST_TYPES_TO_SHOW");

			foreach($test_suites as $name)
			{
				$ts = new pts_test_suite_details($name);
				$hw_type = $ts->get_suite_type();

				if(empty($hw_type) || in_array($hw_type, $to_show_types))
				{
					array_push($to_show_names, $name);
				}
			}

			$test_suites = array_map("pts_suite_identifier_to_name", $to_show_names);
			sort($test_suites);
			$available_suites = pts_gtk_add_table(array("Suite"), $test_suites, array("gui_gtk", "update_details_frame_from_select"));
			pts_gtk_add_notebook_tab($main_notebook, $available_suites, "Available Suites");
		}
		else
		{
			// Installed Tests
			if(count(($installed = pts_installed_tests_array())) > 0)
			{
				$installed_tests = array();

				foreach($installed as $test)
				{
					if(($n = pts_test_identifier_to_name($test)) != "")
					{
						array_push($installed_tests, $n);
					}
				}

				sort($installed_tests);
				$installed_tests = pts_gtk_add_table(array("Test"), $installed_tests, array("gui_gtk", "update_details_frame_from_select"));
				pts_gtk_add_notebook_tab($main_notebook, $installed_tests, "Installed Tests");
			}

			// Available Tests
			$test_names = pts_supported_tests_array();
			$to_show_names = array();
			$to_show_types = pts_read_assignment("GTK_TEST_TYPES_TO_SHOW");

			foreach($test_names as $name)
			{
				$tp = new pts_test_profile_details($name);
				$hw_type = $tp->get_test_hardware_type();

				if((empty($hw_type) || in_array($hw_type, $to_show_types)) && $tp->verified_state())
				{
					array_push($to_show_names, $name);
				}
			}

			$test_names = array_map("pts_test_identifier_to_name", $to_show_names);
			sort($test_names);
			$available_tests = pts_gtk_add_table(array("Test"), $test_names, array("gui_gtk", "update_details_frame_from_select"));
			pts_gtk_add_notebook_tab($main_notebook, $available_tests, "Available Tests");
		}

		$saved_results = glob(SAVE_RESULTS_DIR . "*/composite.xml");

		if(count($saved_results) > 0)
		{
			$results = array();

			foreach($saved_results as $result_file)
			{
				//$rf = new pts_test_results_details($result_file);
				//array_push($results, $rf->get_title());
				array_push($results, array_pop(explode("/", dirname($result_file))));
			}

			$test_results = pts_gtk_add_table(array("Test Result"), $results, array("gui_gtk", "update_details_frame_from_select"));
			pts_gtk_add_notebook_tab($main_notebook, $test_results, "Test Results");
		}

		/*
		if(($no = pts_read_assignment("GTK_MAIN_NOTEBOOK_NUM")) >= 0)
		{
			$main_notebook->set_current_page($no);
		}
		*/
	}
	public static function radio_test_suite_select($object)
	{
		if($object->get_active())
		{
			$item = $object->child->get_label();
			pts_set_assignment("GTK_TEST_OR_SUITE", ($item == "Tests" ? "TEST" : "SUITE"));

			gui_gtk::update_main_notebook();
			gui_gtk::redraw_main_window();
		}
	}
	public static function confirmation_button_clicked($button_call, $identifier)
	{
		$window = pts_read_assignment("GTK_OBJ_CONFIRMATION_WINDOW");
		$window->destroy();

		switch($button_call)
		{
			case "return":
				gui_gtk::show_main_interface();
				break;
			case "install":
				pts_run_option_next("install_test", $identifier, array("SILENCE_MESSAGES" => true));
				pts_run_option_next("gui_gtk");
				break;
		}
	}
	public static function show_run_confirmation_interface()
	{
		$identifier = gui_gtk::notebook_selected_to_identifier();

		if(empty($identifier))
		{
			echo "DEBUG: Null identifier in gtk_gui::show_run_confirmation_interface()\n";
			return;
		}

		$main_window = pts_read_assignment("GTK_OBJ_WINDOW");
		$main_window->destroy();

		switch(pts_read_assignment("GTK_RUN_BUTTON_TASK"))
		{
			case "UPDATE":
				$title_cmd = "install";
				$message = "The Phoronix Test Suite will now proceed to update your " . $identifier . " installation.";
				break;
			case "INSTALL":
				$title_cmd = "install";
				$message = "The Phoronix Test Suite will now proceed to install " . $identifier . ".";
				break;
		//	default:
		//		return;
		//		break;
		}

		$window = new GtkWindow();
		$window->set_title("phoronix-test-suite " . $title_cmd . " " . $identifier);
		$window->set_size_request(500, 200);
		$window->set_resizable(false);
		$window->connect_simple("destroy", array("Gtk", "main_quit"));
		$vbox = new GtkVBox();
		$window->add($vbox);

		$label_temp = new GtkLabel($message);
		$label_temp->set_size_request(480, 150);
		$label_temp->set_line_wrap(true);
		$vbox->pack_start($label_temp);

		$button_box = new GtkHBox();
		$vbox->pack_start($button_box);

		$return_img = GtkImage::new_from_stock(Gtk::STOCK_CANCEL, Gtk::ICON_SIZE_SMALL_TOOLBAR);
		$return_button = new GtkButton("Return");
		$return_button->connect_simple("clicked", array("gui_gtk", "confirmation_button_clicked"), "return", $identifier);
		$return_button->set_image($return_img);
		$return_button->set_size_request(100, 30);
		$button_box->pack_start($return_button);

		$continue_img = GtkImage::new_from_stock(Gtk::STOCK_APPLY, Gtk::ICON_SIZE_SMALL_TOOLBAR);
		$continue_button = new GtkButton("Continue");
		$continue_button->connect_simple("clicked", array("gui_gtk", "confirmation_button_clicked"), $title_cmd, $identifier);
		$continue_button->set_image($continue_img);
		$continue_button->set_size_request(100, 30);
		$button_box->pack_start($continue_button);

		$window->show_all();
		pts_set_assignment("GTK_OBJ_CONFIRMATION_WINDOW", $window);
		Gtk::main();
	}
	public static function details_button_clicked()
	{
		if(pts_read_assignment("GTK_MAIN_NOTEBOOK_SELECTED") == "Test Results")
		{
			$result_identifier = pts_read_assignment("GTK_SELECTED_ITEM");
			pts_display_web_browser(SAVE_RESULTS_DIR . $result_identifier . "/index.html", null, true, true);			
		}
	}
	public static function notebook_selected_to_identifier()
	{
		$identifier = pts_read_assignment("GTK_SELECTED_ITEM");

		if(pts_read_assignment("GTK_MAIN_NOTEBOOK_SELECTED") == "Test Results")
		{
			$identifier = $identifier;
		}
		else if(pts_read_assignment("GTK_TEST_OR_SUITE") == "SUITE")
		{
			$identifier = pts_suite_name_to_identifier($identifier);
		}
		else
		{
			$identifier = pts_test_name_to_identifier($identifier);
		}

		return $identifier;
	}
	public static function update_run_button()
	{
		$identifier = gui_gtk::notebook_selected_to_identifier();

		if(pts_is_test($identifier))
		{
			if(!pts_test_installed($identifier))
			{
				$button_string = "Install";

			}
			else if(pts_test_needs_updated_install($identifier))
			{
				$button_string = "Update";
			}
			else
			{
				$button_string = "Run";
			}
		}
		else if(pts_is_suite($identifier) || pts_is_test_result($identifier))
		{
			if(pts_suite_needs_updated_install($identifier))
			{
				$button_string = "Update";
			}
			else
			{
				$button_string = "Run";
			}
		}

		pts_set_assignment("GTK_RUN_BUTTON_TASK", strtoupper($button_string));
		$run_button = pts_read_assignment("GTK_OBJ_RUN_BUTTON");
		$run_button->set_label($button_string);
	}
	public static function notebook_main_page_select($object)
	{
		$selected = $object->child->get_label();
		pts_set_assignment("GTK_MAIN_NOTEBOOK_SELECTED", $selected);

		$details_button = pts_read_assignment("GTK_OBJ_DETAILS_BUTTON");

		switch($selected)
		{
			case "Test Results":
				$details_button->set_label("View Results");
				break;
			default:
				$details_button->set_label("More Information");
				break;
		}

		/*
		$main_notebook = pts_read_assignment("GTK_OBJ_MAIN_NOTEBOOK");
		pts_set_assignment("GTK_MAIN_NOTEBOOK_NUM", $main_notebook->get_current_page());
		*/
	}
	public static function check_test_type_select($object)
	{
		$item = $object->child->get_label();
		//$to_add = $object->get_active();
		$items_to_show = pts_read_assignment("GTK_TEST_TYPES_TO_SHOW");

		if($items_to_show == null)
		{
			$items_to_show = array();
		}

		if(!in_array($item, $items_to_show))
		{
			array_push($items_to_show, $item);
		}
		else
		{
			$items_to_show_1 = $items_to_show;
			$items_to_show = array();

			foreach($items_to_show_1 as $show)
			{
				if($show != $item)
				{
					array_push($items_to_show, $show);
				}
			}
		}

		pts_set_assignment("GTK_TEST_TYPES_TO_SHOW", $items_to_show);

		gui_gtk::update_main_notebook();
		gui_gtk::redraw_main_window();
	}
	public static function show_about_interface()
	{
		$window = new GtkWindow();
		$window->set_title("About");
		$window->set_size_request(200, 240);
		$window->set_resizable(false);
		$window->connect_simple("destroy", array("Gtk", "main_quit"));
		$vbox = new GtkVBox();
		$window->add($vbox);

		$logo = GtkImage::new_from_file(RESULTS_VIEWER_DIR . "pts-logo.png");
		$logo->set_size_request(158, 82);
		$vbox->pack_start($logo);

		$label_codename = new GtkLabel(ucwords(strtolower(PTS_CODENAME)));
		$label_codename->modify_font(new PangoFontDescription("Sans 19"));
		$vbox->pack_start($label_codename);

		$label_version = new GtkLabel("Version " . PTS_VERSION);
		$vbox->pack_start($label_version);

		$label_copyright = new GtkLabel("Copyright By Phoronix Media");
		$vbox->pack_start($label_copyright);


		$window->show_all();
		Gtk::main();
	}
	public static function show_preferences_interface()
	{
		$window = new GtkWindow();
		$window->set_title("Preferences");
		$window->set_size_request(300, 140);
		$window->set_resizable(false);
		$window->connect_simple("destroy", array("Gtk", "main_quit"));
		$vbox = new GtkVBox();
		$window->add($vbox);

		$label_temp = new GtkLabel("This dialog is not yet implemented. For now the configuration can be modified manually at ~/.phoronix-test-suite/user-config.xml");
		$label_temp->set_size_request(300, 200);
		$label_temp->set_line_wrap(true);
		$vbox->pack_start($label_temp);

		$window->show_all();
		Gtk::main();
	}
	public static function show_system_info_interface()
	{
		$window = new GtkWindow();
		$window->set_title("System Information");
		$window->set_size_request(400, 500);
		$window->set_resizable(false);
		$window->connect_simple("destroy", array("Gtk", "main_quit"));
		$vbox = new GtkVBox();
		$window->add($vbox);

		$label_hw = new GtkLabel("Hardware");
		$label_hw->modify_font(new PangoFontDescription("Sans 19"));
		$vbox->pack_start($label_hw);

		$hbox_hw = new GtkHBox();
		$vbox->pack_start($hbox_hw);
		$vbox_hw_headers = new GtkVBox();
		$vbox_hw_values = new GtkVBox();
		$hbox_hw->pack_start($vbox_hw_headers);
		$hbox_hw->pack_start($vbox_hw_values);

		foreach(pts_hw_string(false, true) as $header => $value)
		{
			$label_header = new GtkLabel($header);
			$vbox_hw_headers->pack_start($label_header);
			$label_value = new GtkLabel($value);
			$vbox_hw_values->pack_start($label_value);
		}

		$label_sw = new GtkLabel("Software");
		$label_sw->modify_font(new PangoFontDescription("Sans 19"));
		$vbox->pack_start($label_sw);

		$hbox_sw = new GtkHBox();
		$vbox->pack_start($hbox_sw);
		$vbox_sw_headers = new GtkVBox();
		$vbox_sw_values = new GtkVBox();
		$hbox_sw->pack_start($vbox_sw_headers);
		$hbox_sw->pack_start($vbox_sw_values);

		foreach(pts_sw_string(false, true) as $header => $value)
		{
			$label_header = new GtkLabel($header);
			$vbox_sw_headers->pack_start($label_header);
			$label_value = new GtkLabel($value);
			$vbox_sw_values->pack_start($label_value);
		}

		$window->show_all();
		Gtk::main();
	}
	public static function redraw_main_window()
	{
		$window = pts_read_assignment("GTK_OBJ_WINDOW");
		$window->show_all();
	}
}

?>
