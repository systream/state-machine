<?php

namespace Tests\Systream\Unit;


use Systream\EventDispatcher;
use Systream\StateMachine;

abstract class AbstractStateMachineTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @param string $name
	 * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $processTransactionState
	 * @return \PHPUnit_Framework_MockObject_MockObject|StateMachine\Transition\TransitionInterface
	 */
	protected function getTransitionMock($name = '', \PHPUnit_Framework_MockObject_Matcher_Invocation $processTransactionState = null)
	{
		$transition = $this->getMockBuilder(StateMachine\Transition\TransitionInterface::class)
			->getMock();
		if ($name) {
			$transition
				->expects($this->any())
				->method('getName')
				->will($this->returnValue($name));
		}

		if ($processTransactionState) {
			$transition
				->expects($processTransactionState)
				->method('processTransaction');
		}


		return $transition;
	}

	/**
	 * @return StateMachine
	 */
	protected function getStateMachine()
	{
		$stateMachine = new StateMachine(new EventDispatcher());
		return $stateMachine;
	}

	/**
	 * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $processTransactionState
	 * @return StateMachine
	 */
	protected function initOrderStateMachine(\PHPUnit_Framework_MockObject_Matcher_Invocation $processTransactionState = null)
	{
		$stateMachine = $this->getStateMachine();

		$orderStatuses = $this->getOrderStates();

		$orderTransition = $this->getTransitionMock('Order', $processTransactionState);
		$backOrderTransition = $this->getTransitionMock('Back Order', $processTransactionState);
		$addToCourierTransition = $this->getTransitionMock('Add to courier', $processTransactionState);
		$handoverToClientTransition = $this->getTransitionMock('Package delivered to client', $processTransactionState);
		$clientSendBackTransition = $this->getTransitionMock('Client send back to shop.', $processTransactionState);

		$stateMachine->addTransition(
			$orderTransition, $orderStatuses['inStock'], $orderStatuses['ordered']
		);

		$stateMachine->addTransition(
			$backOrderTransition, $orderStatuses['ordered'], $orderStatuses['backOrder']
		);

		$stateMachine->addTransition(
			$addToCourierTransition, $orderStatuses['backOrder'], $orderStatuses['shippingInProcess']
		);

		$stateMachine->addTransition(
			$addToCourierTransition, $orderStatuses['ordered'], $orderStatuses['shippingInProcess']
		);

		$stateMachine->addTransition(
			$handoverToClientTransition, $orderStatuses['shippingInProcess'], $orderStatuses['deliveredToClient']
		);

		$stateMachine->addTransition(
			$clientSendBackTransition, $orderStatuses['deliveredToClient'], $orderStatuses['inStock']
		);

		return $stateMachine;
	}

	/**
	 * @return StateMachine\State[]
	 */
	protected function getOrderStates()
	{
		return array(
			'inStock' => new StateMachine\State('In stock'),
			'backOrder' => new StateMachine\State('Back ordered'),
			'ordered' => new StateMachine\State('Ordered'),
			'shippingInProcess' => new StateMachine\State('Shipping in process'),
			'deliveredToClient' => new StateMachine\State('Order is at client'),
		);
	}
}