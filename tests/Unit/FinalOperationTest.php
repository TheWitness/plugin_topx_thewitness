<?php

require_once __DIR__ . '/../bootstrap-unit.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

describe('topx_final_operation', function (): void {
	it('rounds and appends the unit for the strip operation', function (): void {
		expect(topx_final_operation(12.345, 'strip', '%', '', '1'))->toBe('12.3 %');
	});

	it('rounds to the configured decimal places', function (): void {
		expect(topx_final_operation(2.5, 'strip', 'load', '', '0'))->toBe('3 load');
	});

	it('defaults to two decimal places when Auto', function (): void {
		expect(topx_final_operation(12.345, 'strip', '%', '', ''))->toBe('12.35 %');
	});

	it('scales to the largest divider whose value exceeds one', function (): void {
		expect(topx_final_operation(5000, '/', 'bit/Kbit/Mbit', '1/1000/1000000'))->toBe('5 Kbit');
	});

	it('keeps the base unit when no larger divider applies', function (): void {
		expect(topx_final_operation(42, '/', 'bit/Kbit/Mbit', '1/1000/1000000'))->toBe('42 bit');
	});

	it('rounds the divide result to the configured decimal places', function (): void {
		expect(topx_final_operation(5125, '/', 'bit/Kbit/Mbit', '1/1000/1000000', '1'))->toBe('5.1 Kbit');
	});

	it('scales a sub-unit value down with an SI prefix instead of printing a raw float', function (): void {
		// Smaller than the smallest ladder divider, so no divider ever exceeds
		// one. The remainder is scaled down (milli) rather than printed as the
		// raw 0.1232774838 or rounded away to 0.
		expect(topx_final_operation(0.1232774838, '/', 'bit/Kbit/Mbit', '1/1000/1000000'))->toBe('123.28 m bit');
	});

	it('honours configured decimals for an SI-scaled sub-unit value', function (): void {
		expect(topx_final_operation(0.1232774838, '/', 'bit/Kbit/Mbit', '1/1000/1000000', '3'))->toBe('123.277 m bit');
	});

	it('scales a sub-unit value into the micro range (matches the reported errors panel)', function (): void {
		expect(topx_final_operation(0.00027777777777778, '/', 'errors', '1'))->toBe('277.78 µ errors');
		expect(topx_final_operation(0.00055555555555555, '/', 'errors', '1'))->toBe('555.56 µ errors');
		expect(topx_final_operation(0.053055555555555, '/', 'errors', '1'))->toBe('53.06 m errors');
		expect(topx_final_operation(0.43388888888889, '/', 'errors', '1'))->toBe('433.89 m errors');
	});

	it('leaves an above-unit value unscaled (matches the reported errors panel)', function (): void {
		expect(topx_final_operation(14.87, '/', 'errors', '1'))->toBe('14.87 errors');
	});

	it('scales a sub-unit value when the ladder has no base-unit divider', function (): void {
		// Ladder starts at Kbit (1000); a value of a few base units is below it,
		// so it falls back to the smallest divider and scales the remainder.
		expect(topx_final_operation(2.1232774838, '/', 'Kbit/Mbit', '1000/1000000'))->toBe('2.12 m Kbit');
	});

	it('returns the value as a string for an unknown operation', function (): void {
		expect(topx_final_operation(7, '', '', ''))->toBe('7');
	});

	it('never divides by a zero divider', function (): void {
		expect(topx_final_operation(10, '/', 'a/b', '0/5'))->toBeString();
	});
});
