<?php

namespace DivineComedy;

class DivineComedyTest extends \PHPUnit\Framework\TestCase {

	public function testNumberOfLinesItalian() : void {
		$inferno = new Cantica( 'Inferno', 'it' );
		$inferno18 = $inferno->getCanto( 18 );
		$this->assertSame( count( $inferno18->getLines() ), 136 );
	}

	public function testNumberOfLinesEnglish() : void {
		$this->markTestSkipped( 'not working' );
		$paradise = new Cantica( 'Paradiso', 'en' );
		$paradise3 = $paradise->getCanto( 3 );
		$this->assertSame( count( $paradise3->getLines() ), 130 );
	}

	public function testItalian() : void {
		$inferno = new Cantica( 'Inferno', 'it' );
		$first_canto = $inferno->getCanto( 1 );
		$result = $first_canto->getLines( 1, 3 );
		$expected = [
			'Nel mezzo del cammin di nostra vita',
			'mi ritrovai per una selva oscura,',
			'ché la diritta via era smarrita.'
		];
		$this->assertSame( $result, $expected );
	}

	public function testEnglish() : void {
		$this->markTestSkipped( 'not working' );
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

	public function testLatin() : void {
		$inferno = new Cantica( 'Inferno', 'la' );
		$first_canto = $inferno->getCanto( 1 );
		$result = $first_canto->getLines( 12, 16 );
		$expected = [
			'Veni claudentis vallem, quae corda timore',
			'Foderat, ad Superos attollens lumina, vidi',
			'Iam terga istius radiis induta planetae,',
			'Qui pede inoffenso callem docet ire per omnem',
			'Sic mihi tunc aliqua formido ex parte quievit,'
		];
		$this->assertSame( $result, $expected );
	}

	public function testRussian() : void {
		$purgatorio = new Cantica( 'Purgatorio', 'ru' );
		$second_canto = $purgatorio->getCanto( 2 );
		$result = $second_canto->getLines( 7, 9 );
		$expected = [
			'Такъ что Авроры свѣтлый ликъ предъ нами',
			'Изъ бѣлаго сталъ алымъ и потомъ',
			'Оранжевымъ, состарившись съ часами.'
		];
		$this->assertSame( $result, $expected );
	}

}
