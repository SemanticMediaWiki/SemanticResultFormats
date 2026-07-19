<?php

declare( strict_types=1 );

namespace SRF\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use SRF\Math\MathFormats;

/**
 * @covers \SRF\Math\MathFormats
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class MathFormatsTest extends TestCase {

	public function testMaxReturnsTheLargestNumber() {
		$this->assertSame( 4, MathFormats::maxFunction( [ 3, 1, 4, 2 ] ) );
	}

	public function testMinReturnsTheSmallestNumber() {
		$this->assertSame( 1, MathFormats::minFunction( [ 3, 1, 4, 2 ] ) );
	}

	public function testSumAddsAllNumbers() {
		$this->assertSame( 10, MathFormats::sumFunction( [ 3, 1, 4, 2 ] ) );
	}

	public function testProductMultipliesAllNumbers() {
		$this->assertSame( 24, MathFormats::productFunction( [ 3, 1, 4, 2 ] ) );
	}

	public function testAverageOfUnsortedNumbers() {
		$this->assertSame( 2.5, MathFormats::averageFunction( [ 4, 1, 3, 2 ] ) );
	}

	public function testMedianOfAnOddSizedListIsTheMiddleValueAfterSorting() {
		$this->assertEqualsWithDelta( 3, MathFormats::medianFunction( [ 7, 1, 3 ] ), 1e-9 );
	}

	public function testMedianOfAnEvenSizedListIsTheMeanOfTheTwoMiddleValues() {
		$this->assertEqualsWithDelta( 4, MathFormats::medianFunction( [ 1, 9, 3, 5 ] ), 1e-9 );
	}

	public function testRangeIsMaxMinusMin() {
		$this->assertSame( 8, MathFormats::rangeFunction( [ 4, 9, 1, 5 ] ) );
	}

	public function testVarianceIsThePopulationVariance() {
		$this->assertEqualsWithDelta( 4, MathFormats::varianceFunction( [ 2, 4, 4, 4, 5, 5, 7, 9 ] ), 1e-9 );
	}

	public function testSampleVarianceUsesTheBesselCorrectedDivisor() {
		$this->assertEqualsWithDelta( 4.571428571428571, MathFormats::samplevarianceFunction( [ 2, 4, 4, 4, 5, 5, 7, 9 ] ), 1e-9 );
	}

	public function testStandardDeviationIsThePopulationStandardDeviation() {
		$this->assertEqualsWithDelta( 2, MathFormats::standarddeviationFunction( [ 2, 4, 4, 4, 5, 5, 7, 9 ] ), 1e-9 );
	}

	public function testSampleStandardDeviationUsesTheBesselCorrectedDivisor() {
		$this->assertEqualsWithDelta( 2.138089935299395, MathFormats::samplestandarddeviationFunction( [ 2, 4, 4, 4, 5, 5, 7, 9 ] ), 1e-9 );
	}

	public function testLowerQuartileOnAWholePositionPicksTheValue() {
		$this->assertEqualsWithDelta( 2, MathFormats::quartillowerIncFunction( [ 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testUpperQuartileOnAWholePositionPicksTheValue() {
		$this->assertEqualsWithDelta( 4, MathFormats::quartilupperIncFunction( [ 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testExclusiveLowerQuartileOnAWholeRankPicksTheValue() {
		$this->assertEqualsWithDelta( 2, MathFormats::quartillowerExcFunction( [ 7, 6, 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testExclusiveUpperQuartileOnAWholeRankPicksTheValue() {
		$this->assertEqualsWithDelta( 6, MathFormats::quartilupperExcFunction( [ 7, 6, 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testInterquartileRangeOnWholePositions() {
		$this->assertEqualsWithDelta( 2, MathFormats::interquartilerangeIncFunction( [ 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testLowerQuartileInterpolatesByTheFractionalPosition() {
		$this->assertEqualsWithDelta( 1.75, MathFormats::quartillowerIncFunction( [ 4, 2, 1, 3 ] ), 1e-9 );
	}

	public function testUpperQuartileInterpolatesByTheFractionalPosition() {
		$this->assertEqualsWithDelta( 3.25, MathFormats::quartilupperIncFunction( [ 4, 2, 1, 3 ] ), 1e-9 );
	}

	public function testExclusiveLowerQuartileInterpolatesByTheFractionalRank() {
		$this->assertEqualsWithDelta( 2.5, MathFormats::quartillowerExcFunction( [ 9, 1, 8, 2, 7, 3, 6, 4, 5 ] ), 1e-9 );
	}

	public function testExclusiveUpperQuartileInterpolatesByTheFractionalRank() {
		$this->assertEqualsWithDelta( 7.5, MathFormats::quartilupperExcFunction( [ 9, 1, 8, 2, 7, 3, 6, 4, 5 ] ), 1e-9 );
	}

	public function testInterquartileRangeInterpolatesByTheFractionalPosition() {
		$this->assertEqualsWithDelta( 1.5, MathFormats::interquartilerangeIncFunction( [ 4, 2, 1, 3 ] ), 1e-9 );
	}

	public function testExclusiveInterquartileRangeInterpolatesByTheFractionalRank() {
		$this->assertEqualsWithDelta( 5, MathFormats::interquartilerangeExcFunction( [ 9, 1, 8, 2, 7, 3, 6, 4, 5 ] ), 1e-9 );
	}

	public function testInterquartileMeanOfAListDivisibleByFour() {
		$this->assertEqualsWithDelta( 4.5, MathFormats::interquartilemeanFunction( [ 8, 7, 6, 5, 4, 3, 2, 1 ] ), 1e-9 );
	}

	public function testInterquartileMeanInterpolatesWhenNotDivisibleByFour() {
		$this->assertEqualsWithDelta( 9, MathFormats::interquartilemeanFunction( [ 17, 1, 15, 3, 13, 5, 11, 7, 9 ] ), 1e-9 );
	}

	public function testModeIsNullWhenNoSingleValueIsMostFrequent() {
		$this->assertNull( MathFormats::modeFunction( [ 1, 1, 2, 2 ] ) );
	}

	public function testModeIsTheMostFrequentValue() {
		$this->assertSame( 3, MathFormats::modeFunction( [ 5, 3, 1, 3 ] ) );
	}

	public function testModeOfDecimalValues() {
		$this->assertSame( 2.5, MathFormats::modeFunction( [ 2.5, 7, 2.5 ] ) );
	}

}
