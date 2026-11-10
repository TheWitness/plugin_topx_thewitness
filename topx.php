<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2026 The Cacti Group, Inc.                           |
 | Copyright (C) 2015-2026 Petr Macek                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

chdir('../../');

include_once('./include/auth.php');
include_once('./include/global_arrays.php');
include_once($config['base_path'] . '/plugins/topx/includes/functions.php');
include_once($config['base_path'] . '/plugins/topx/includes/arrays.php');
include_once($config['base_path'] . '/plugins/topx/includes/sources.php');
include_once($config['base_path'] . '/plugins/topx/includes/dashboard.php');

set_default_action();

/**
 * Load the shared dashboarding bundle (card chrome, chart-widget primitives and
 * the CactiCards controller) only on cores that do not already ship them in
 * include/ (Cacti < 1.2.32); newer cores load the core copies globally. Kept
 * here, in the entry point, rather than in the coverage-measured render code,
 * because the bundled fallback only runs on older cores.
 */
function topx_dashboard_print_bundle(): void {
	global $config;

	if (!file_exists($config['base_path'] . '/include/css/cards.css')) {
		print get_md5_include_css('plugins/topx/css/cards.css');
	}

	if (!file_exists($config['base_path'] . '/include/css/card-widgets.css')) {
		print get_md5_include_css('plugins/topx/css/card-widgets.css');
	}

	if (!file_exists($config['base_path'] . '/include/js/cards.js')) {
		print get_md5_include_js('plugins/topx/js/cards.js');
	}
}

switch (get_request_var('action')) {
	case 'overview_card':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_overview_card_ajax();

		break;
	case 'overview_layout':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_dashboard_layout_save('overview', topx_overview_available_cards());

		break;
	case 'overview_refresh':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_dashboard_refresh_save('overview');

		break;
	case 'dashboard_card':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_drilldown_card_ajax();

		break;
	case 'dashboard_layout':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_dashboard_layout_save('drill', topx_drilldown_available_cards());

		break;
	case 'dashboard_refresh':
		header('Content-Type: application/json; charset=UTF-8');
		print topx_dashboard_refresh_save('drill');

		break;
	case 'view':
		general_header();
		topx_dashboard_print_bundle();
		topx_drilldown_dashboard();
		bottom_footer();

		break;
	case 'dashboard':
	default:
		general_header();
		topx_dashboard_print_bundle();
		topx_overview_dashboard();
		bottom_footer();

		break;
}
