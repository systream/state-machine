<?php

namespace Tests\Systream\Unit\StateMachine;


use Systream\EventDispatcher;
use Systream\StateMachine;

class DotFileGeneratorTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 * @param StateMachine $stateMachine
	 * @dataProvider generateFile_dataProvider
	 */
	public function generateFile(StateMachine $stateMachine)
	{
		$doFileGenerator = new StateMachine\DotFileGenerator();
		$image = $doFileGenerator->getImage($stateMachine);
		//$this->imageToASCII($image);

	}

	/**
	 * @return array
	 */
	public function generateFile_dataProvider()
	{
		$sm = new StateMachine(new EventDispatcher());
		$sm->addTransition(new StateMachine\Transition\GenericTransition('foo'), new StateMachine\State('test'), new StateMachine\State('test2'));
		$sm->addTransition(new StateMachine\Transition\GenericTransition('foo2'), new StateMachine\State('test2'), new StateMachine\State('test'));

		return array(
			array($sm)
		);
	}

	protected function imageToASCII($image)
	{
		$img = imagecreatefromstring($image);

		$width = imagesx($img);
		$height = imagesy($img);

		$scale = 10;
		$chars = array(
			' ', '\'', '.', ':',
			'|', 'T',  'X', '0',
			'#',
		);
		$chars = array_reverse($chars);
		$c_count = count($chars);
		for($y = 0; $y <= $height - $scale - 1; $y += $scale) {
			for($x = 0; $x <= $width - ($scale / 2) - 1; $x += ($scale / 2)) {
				$rgb = imagecolorat($img, $x, $y);
				$r = (($rgb >> 16) & 0xFF);
				$g = (($rgb >> 8) & 0xFF);
				$b = ($rgb & 0xFF);
				$sat = ($r + $g + $b) / (255 * 3);
				echo $chars[ (int)( $sat * ($c_count - 1) ) ];
			}
			echo PHP_EOL;
		}
	}
}
