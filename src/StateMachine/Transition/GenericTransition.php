<?php

namespace Systream\StateMachine\Transition;


use Systream\StateMachine\State\StateInterface;
use Systream\StateMachine\State\StateObjectInterface;

class GenericTransition implements TransitionInterface
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * GenericTransition constructor.
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param StateObjectInterface $stateObject
	 * @param StateInterface $targetState
	 */
	public function processTransaction(StateObjectInterface $stateObject, StateInterface $targetState)
	{
		$stateObject->setState($targetState);
	}
}