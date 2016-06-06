<?php

namespace Tests\Systream\Unit\StateMachine;


use Systream\StateMachine\State;
use Systream\StateMachine\State\StateObjectInterface;
use Systream\StateMachine\Transition\GenericTransition;

class GenericTransitionTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function getNameTest()
	{
		$transition = new GenericTransition('foo');
		$this->assertEquals('foo', $transition->getName());
	}

	/**
	 * @test
	 */
	public function processTransaction()
	{
		$transition = new GenericTransition('foo');

		$stateObjectInterface = $this->getMockBuilder(StateObjectInterface::class)->getMock();

		$stateObjectInterface->expects($this->atLeastOnce())->method('setState');
		$foo = new State('foo');
		$transition->processTransaction($stateObjectInterface, $foo);
	}

}
