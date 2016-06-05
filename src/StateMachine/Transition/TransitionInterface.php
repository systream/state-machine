<?php

namespace Systream\StateMachine\Transition;


use Systream\StateMachine\State\StateInterface;
use Systream\StateMachine\State\StateObjectInterface;

interface TransitionInterface
{
	
	public function getName();
	
	/**
	 * @param StateObjectInterface $stateObject
	 * @param StateInterface $targetState
	 */
	public function processTransaction(StateObjectInterface $stateObject, StateInterface $targetState);
}