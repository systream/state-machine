<?php

namespace Systream\StateMachine\State;


interface StateObjectInterface
{
	/**
	 * @param StateInterface $state
	 * @return void
	 */
	public function setState(StateInterface $state);

	/**
	 * @return StateInterface
	 */
	public function getState();
}