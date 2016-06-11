<?php

namespace Tests\Systream\Unit\StateMachine;


use Systream\StateMachine\State;

class StateTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @tests
	 */
	public function idFromName_same()
	{
		$state = new State('foo');
		$state2 = new State('foo');
		
		$this->assertEquals($state->getId(), $state2->getId());
	}

	/**
	 * @tests
	 */
	public function idFromName_different()
	{
		$state = new State('foo2');
		$state2 = new State('foo');

		$this->assertNotEquals($state->getId(), $state2->getId());
	}

	/**
	 * @tests
	 */
	public function setID()
	{
		$state = new State('foo2', 10);
		$this->assertEquals(10, $state->getId());
	}
}
