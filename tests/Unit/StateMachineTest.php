<?php

namespace Tests\Systream\Unit;


use Systream\EventDispatcher;
use Systream\StateMachine;

class StateMachineTest extends AbstractStateMachineTest
{

	/**
	 * @test
	 */
	public function addStatuses_empty()
	{
		$stateMachine = $this->getStateMachine();
		$this->assertInternalType('array', $stateMachine->getStates());
		$this->assertEmpty($stateMachine->getStates());
	}

	/**
	 * @test
	 */
	public function addStatuses_simple()
	{
		$stateMachine = $this->getStateMachine();

		$transition = $this->getTransitionMock('test');
		$state1 = new StateMachine\State('s1');
		$state2 = new StateMachine\State('s2');

		$stateMachine->addTransition($transition, $state1, $state2);
		
		$this->assertInternalType('array', $stateMachine->getStates());
		$this->assertEquals(
			array(
				$state1,
				$state2
			),
			$stateMachine->getStates()
		);
	}

	/**
	 * @test
	 */
	public function addStatuses_DoubleState()
	{
		$stateMachine = $this->getStateMachine();

		$transition = $this->getTransitionMock('test');
		$state1 = new StateMachine\State('s1');
		$state2 = new StateMachine\State('s2');

		$stateMachine->addTransition($transition, $state1, $state2);

		$transition2 = $this->getTransitionMock('test2');
		$state3 = new StateMachine\State('s3');
		$stateMachine->addTransition($transition2, $state1, $state3);
		
		$this->assertEquals(
			array(
				$state1,
				$state2,
				$state3
			),
			$stateMachine->getStates()
		);
	}

	/**
	 * @test
	 */
	public function processStatus_simple()
	{
		$stateMachine = $this->getStateMachine();

		$transition = $this->getTransitionMock('test');

		$transition->expects($this->atLeastOnce())
			->method('processTransaction');

		$state1 = new StateMachine\State('s1');
		$state2 = new StateMachine\State('s2');
		

		$stateMachine->addTransition($transition, $state1, $state2);

		$transition2 = $this->getTransitionMock('test2');
		$transition2->expects($this->never())
			->method('processTransaction');
		$state3 = new StateMachine\State('s3');
		
		$stateMachine->addTransition($transition2, $state1, $state3);

		$object = new DummyStateObject();
		$object->setState($state1);

		$stateMachine->process($object, $state2);

		$this->assertEquals($state2, $object->getState());
	}

	/**
	 * @test
	 */
	public function processStatus_MultiEvent()
	{
		$stateMachine = $this->getStateMachine();

		$transition = $this->getTransitionMock('test');
		$transition->expects($this->atLeastOnce())
			->method('processTransaction');
		$state1 = new StateMachine\State('s1');
		$state2 = new StateMachine\State('s2');
		$stateMachine->addTransition($transition, $state1, $state2);

		$transition2 = $this->getTransitionMock('test2');
		$transition2->expects($this->atLeastOnce())
			->method('processTransaction');
		$stateMachine->addTransition($transition2, $state1, $state2);

		$object = new DummyStateObject();
		$object->setState($state1);

		$stateMachine->process($object, $state2);

		$this->assertEquals($state2, $object->getState());
	}

	/**
	 * @test
	 * @expectedException \Systream\StateMachine\Exception\CantSetStatusException
	 */
	public function processStatus_CantSetStateToThat()
	{
		$stateMachine = $this->getStateMachine();

		$transition = $this->getTransitionMock('test');
		$transition->expects($this->never())
			->method('processTransaction');
		$state1 = new StateMachine\State('s1');
		$state2 = new StateMachine\State('s2');

		$stateMachine->addTransition($transition, $state1, $state2);

		$transition2 = $this->getTransitionMock('test2');
		$transition2->expects($this->never())
			->method('processTransaction');
		$state3 = new StateMachine\State('s3');
		$stateMachine->addTransition($transition2, $state2, $state3);

		$object = new DummyStateObject();
		$object->setState($state1);

		$stateMachine->process($object, $state3);
	}

	/**
	 * @test
	 */
	public function processStatus_Flow()
	{
		$statuses = $this->getOrderStates();
		$stateMachine = $this->initOrderStateMachine($this->atLeastOnce());

		$product = new DummyStateObject();
		$product->setState($statuses['inStock']);

		$stateMachine->process($product, $statuses['ordered']);
		$stateMachine->process($product, $statuses['backOrder']);
		$stateMachine->process($product, $statuses['shippingInProcess']);
		$stateMachine->process($product, $statuses['deliveredToClient']);
		$stateMachine->process($product, $statuses['inStock']);

		$stateMachine->process($product, $statuses['ordered']);
		$stateMachine->process($product, $statuses['shippingInProcess']);
		$stateMachine->process($product, $statuses['deliveredToClient']);
		$stateMachine->process($product, $statuses['inStock']);
	}

	/**
	 * @test
	 * @dataProvider processStatus_Complex_DateProvider
	 * @param StateMachine\State $initialState
	 * @param StateMachine\State $targetState
	 */
	public function processStatus_Complex(StateMachine\State $initialState, StateMachine\State $targetState)
	{
		$stateMachine = $this->initOrderStateMachine($this->any());

		$product = new DummyStateObject();
		$product->setState($initialState);

		$stateMachine->process($product, $targetState);
	}

	/**
	 * @return array
	 */
	public function processStatus_Complex_DateProvider()
	{
		$statuses = $this->getOrderStates();

		return array(
			array($statuses['inStock'], $statuses['ordered']),
			array($statuses['ordered'], $statuses['backOrder']),
			array($statuses['ordered'], $statuses['shippingInProcess']),
			array($statuses['backOrder'], $statuses['shippingInProcess']),
			array($statuses['shippingInProcess'], $statuses['deliveredToClient']),
			array($statuses['deliveredToClient'], $statuses['inStock']),
		);
	}

	/**
	 * @test
	 * @dataProvider processStatus_Cant_DateProvider
	 * @param StateMachine\State $initialState
	 * @param StateMachine\State $targetState
	 * @expectedException \Systream\StateMachine\Exception\CantSetStatusException
	 */
	public function processStatus_Cant(StateMachine\State $initialState, StateMachine\State $targetState)
	{
		$stateMachine = $this->initOrderStateMachine($this->any());

		$product = new DummyStateObject();
		$product->setState($initialState);

		$stateMachine->process($product, $targetState);
	}

	/**
	 * @return array
	 */
	public function processStatus_Cant_DateProvider()
	{
		$statuses = $this->getOrderStates();

		return array(

			array($statuses['inStock'], $statuses['backOrder']),
			array($statuses['inStock'], $statuses['shippingInProcess']),
			array($statuses['inStock'], $statuses['deliveredToClient']),
			array($statuses['inStock'], $statuses['inStock']),
			
			array($statuses['ordered'], $statuses['inStock']),
			array($statuses['ordered'], $statuses['deliveredToClient']),
			array($statuses['ordered'], $statuses['ordered']),

			array($statuses['backOrder'], $statuses['inStock']),
			array($statuses['backOrder'], $statuses['backOrder']),
			array($statuses['backOrder'], $statuses['deliveredToClient']),
			array($statuses['backOrder'], $statuses['ordered']),

			array($statuses['shippingInProcess'], $statuses['inStock']),
			array($statuses['shippingInProcess'], $statuses['ordered']),
			array($statuses['shippingInProcess'], $statuses['backOrder']),
			array($statuses['shippingInProcess'], $statuses['shippingInProcess']),

			array($statuses['deliveredToClient'], $statuses['ordered']),
			array($statuses['deliveredToClient'], $statuses['backOrder']),
			array($statuses['deliveredToClient'], $statuses['shippingInProcess']),
			array($statuses['deliveredToClient'], $statuses['deliveredToClient']),
		);
	}

	/**
	 * @tests
	 * @dataProvider getNextStates_DateProvider
	 * @param StateMachine\State $initState
	 * @param array $nextStates
	 */
	public function getNextStates(StateMachine\State $initState, array $nextStates)
	{
		$stateMachine = $this->initOrderStateMachine($this->any());

		$product = new DummyStateObject();
		$product->setState($initState);

		$this->assertEquals(
			$nextStates,
			$stateMachine->getNextStates($product)
		);
	}

	/**
	 * @return array
	 */
	public function getNextStates_DateProvider()
	{
		$statuses = $this->getOrderStates();

		return array(
			array($statuses['inStock'], array($statuses['ordered'])),
			array($statuses['ordered'], array($statuses['backOrder'], $statuses['shippingInProcess'])),
		);
	}

	/**
	 * @tests
	 * @param StateMachine\State $initialState
	 * @param StateMachine\State $expectedState
	 * @dataProvider GenericTransition_dateProvider
	 */
	public function GenericTransition(StateMachine\State $initialState, StateMachine\State $expectedState)
	{
		$sm = $this->getStateMachine();
		$inStock = new StateMachine\State('In stock');
		$ordered = new StateMachine\State('Ordered');
		$completed = new StateMachine\State('Order completed');

		$sm->addTransition(
			new StateMachine\Transition\GenericTransition('Order on website'),
			$inStock,
			$ordered
		);

		$sm->addTransition(
			new StateMachine\Transition\GenericTransition('Deliver to client'),
			$ordered,
			$completed
		);

		$sm->addTransition(
			new StateMachine\Transition\GenericTransition('Cancel order'),
			$ordered,
			$inStock
		);

		$sm->addTransition(
			new StateMachine\Transition\GenericTransition('Cancel order'),
			$completed,
			$inStock
		);

		$product = new DummyStateObject();
		$product->setState($initialState);

		$sm->process($product, $expectedState);

		$this->assertEquals(
			$expectedState, $product->getState()
		);
	}


	/**
	 * @return array
	 */
	public function GenericTransition_dateProvider()
	{
		$inStock = new StateMachine\State('In stock');
		$ordered = new StateMachine\State('Ordered');
		$completed = new StateMachine\State('Order completed');

		return array(
			array($inStock, $ordered),
			array($ordered, $completed),
			array($ordered, $inStock),
			array($completed, $inStock),
		);
	}
}