<?php

require_once './DivineComedy.php';

class DivineComedyTest extends PHPUnit_Framework_TestCase {

	public function testNumberOfLines() {
		$inferno = new Cantica( 'Inferno' );
		$inferno18 = $inferno->getCanto( 18 );
		$this->assertSame( $inferno18->numberOfLines(), 136 );

		$paradise = new Cantica( 'Paradiso', 'en' );
		$paradise3 = $paradise->getCanto( 3 );
		$this->assertSame( $paradise3->numberOfLines(), 130 );
	}

	public function testItalian() {
		$inferno = new Cantica( 'Inferno' );
		$first_canto = $inferno->getCanto( 1 );
		$result = $first_canto->getLines( 1, 3 );
		$expected = [
			'Nel mezzo del cammin di nostra vita',
			'mi ritrovai per una selva oscura,',
			'ché la diritta via era smarrita.'
		];
		$this->assertSame( $result, $expected );
	}

	public function testEnglish() {
		$inferno = new Cantica( 'Inferno', 'en' );
		$first_canto = $inferno->getCanto( 1 );
		$result = $first_canto->getLines( 1, 3 );
		$expected = [
			'Midway upon the journey of our life',
			'I found myself within a forest dark,',
			'For the straight-forward pathway had been lost.'
		];
		$this->assertSame( $result, $expected );
	}

}
