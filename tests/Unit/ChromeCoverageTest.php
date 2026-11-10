<?php

require_once __DIR__ . '/../bootstrap-unit.php';
require_once dirname(__DIR__, 2) . '/includes/arrays.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/sources.php';
require_once dirname(__DIR__, 2) . '/includes/dashboard.php';

/*
 * Covers the shared-card-chrome / chart-widget conversion in the standalone
 * render helpers (the full-page topx_overview_dashboard()/topx_drilldown_dashboard()
 * wrappers are view glue and remain @codeCoverageIgnore'd, as before).
 */
describe('topx shared card chrome + primitive rendering', function (): void {
	afterEach(function (): void {
		unset(
			$GLOBALS['__test_db_fetch_assoc'],
			$GLOBALS['__test_db_fetch_assoc_prepared'],
			$GLOBALS['graph_timespans'],
			$GLOBALS['page_refresh_interval']
		);
	});

	it('renders the SVG gauge with min/max ticks on the shared .cc* primitives', function (): void {
		$html = topx_dashboard_gauge(50.0, 10.0, 90.0, 100.0, '50%', 'load');

		expect($html)->toContain('class="ccGauge"')
			->toContain('class="ccGaugeSvg"')
			->toContain('class="ccGaugeTrack"')
			->toContain('class="ccGaugeValue"')
			->toContain('class="ccGaugeTick"')
			->toContain('class="ccGaugeValueText"');
	});

	it('renders the refresh toolbar on the shared .cc* chrome', function (): void {
		$GLOBALS['page_refresh_interval'] = [0 => 'No Refresh', 30 => '30 Seconds', 60 => '1 Minute'];

		ob_start();
		topx_dashboard_toolbar_refresh(30);
		$html = (string) ob_get_clean();

		expect($html)->toContain('class="ccRefresh"')
			->toContain('class="ccToolbarLabel"')
			->toContain('class="ccRefreshInterval"')
			->toContain('ccTool topxDashRefreshNow');
	});

	it('renders the timespan selector label on the shared chrome', function (): void {
		$GLOBALS['graph_timespans'] = [1 => 'Last Hour', 2 => 'Last Day'];

		ob_start();
		topx_dashboard_toolbar_timespan();
		$html = (string) ob_get_clean();

		expect($html)->toContain('class="ccToolbarLabel"')->toContain('Last Hour');
	});

	it('renders the add-card selector on the shared chrome', function (): void {
		ob_start();
		topx_dashboard_toolbar_add(['uptime'], ['uptime' => ['title' => 'Uptime']]);
		$html = (string) ob_get_clean();

		expect($html)->toContain('class="ccAdd"')->toContain('Uptime');
	});

	it('renders the overview summary tiles and distribution bar on the shared primitives', function (): void {
		$GLOBALS['__test_db_fetch_assoc'] = function ($sql) {
			if (strpos($sql, 'plugin_topx_source') !== false) {
				return [['dsname' => 'CPU', 'dsid' => '7', 'tplid' => '12', 'sorting' => 'desc', 'dscount' => '2', 'sup' => 'true']];
			}

			return [['dsname' => 'unsupported - X', 'dsid' => '9', 'dscount' => '1', 'sup' => 'false']];
		};

		expect(topx_overview_card_body('summary'))->toContain('class="ccTiles"')->toContain('ccTile');
		expect(topx_overview_card_body('distribution'))->toContain('class="ccStack"')->toContain('ccStackSeg')->toContain('ccStackChip');
	});

	it('renders overview empty states on the shared .ccEmpty primitive', function (): void {
		$GLOBALS['__test_db_fetch_assoc'] = function ($sql) {
			return [];
		};

		expect(topx_overview_card_body('distribution'))->toContain('ccEmpty');
		expect(topx_overview_card_body('types'))->toContain('ccEmpty');
	});

	it('renders an empty drilldown distribution body on the shared .ccEmpty primitive', function (): void {
		$GLOBALS['__test_db_fetch_assoc_prepared'] = function ($sql, $p) {
			return [];
		};

		$ds = ['key' => 5, 'supported' => true, 'entry' => ['key' => 5, 'name' => 'x', 'count' => '2', 'supported' => 'true', 'sorting' => 'desc']];
		$filter = ['records' => 10, 'sort' => 'desc', 'what' => 'current'];

		expect(topx_drilldown_card_body('distribution', $ds, $filter))->toContain('ccEmpty');
	});

	it('renders the expand/collapse chrome for an expandable, expanded card', function (): void {
		$html = topx_dashboard_card_chrome('latest', 'Latest', 4, true, true, '<p>body</p>');

		expect($html)->toContain('class="ccCard ccW4')
			->toContain('data-severity="none"')
			->toContain('data-expandable="1"')
			->toContain('data-expanded="1"')
			->toContain('ccCardExpandable')
			->toContain('ccCardExpanded')
			->toContain('data-tool="expand"')
			->toContain('data-tool="collapse"');
	});

	it('prints a DS Stats guidance message on the shared .ccEmpty primitive when disabled', function (): void {
		// The unit harness read_config_option() returns '' (DS Stats disabled).
		ob_start();
		$ready = topx_dashboard_dsstats_ready();
		$html  = (string) ob_get_clean();

		expect($ready)->toBeFalse();
		expect($html)->toContain('class="ccEmpty"');
	});
});
