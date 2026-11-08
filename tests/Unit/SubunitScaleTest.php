<?php

require_once __DIR__ . '/../bootstrap-unit.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

describe('topx_subunit_scale', function (): void {
	it('returns a bare rounded number for zero', function (): void {
		expect(topx_subunit_scale(0.0, 2))->toBe('0');
	});

	it('returns a bare rounded number for values at or above one', function (): void {
		expect(topx_subunit_scale(5.0, 2))->toBe('5');
		expect(topx_subunit_scale(1.0, 2))->toBe('1');
		expect(topx_subunit_scale(12.345, 2))->toBe('12.35');
	});

	it('scales values below one into the milli range', function (): void {
		expect(topx_subunit_scale(0.43388888888889, 2))->toBe('433.89 m');
		expect(topx_subunit_scale(0.053055555555555, 2))->toBe('53.06 m');
	});

	it('scales very small values into the micro range', function (): void {
		expect(topx_subunit_scale(0.00027777777777778, 2))->toBe('277.78 µ');
	});

	it('honours the configured decimal places', function (): void {
		expect(topx_subunit_scale(0.1232774838, 3))->toBe('123.277 m');
	});

	it('preserves the sign of a negative magnitude', function (): void {
		expect(topx_subunit_scale(-0.5, 2))->toBe('-500 m');
	});

	it('clamps extremely small values at the pico prefix', function (): void {
		expect(topx_subunit_scale(0.0000000000001, 2))->toBeString();
	});
});
