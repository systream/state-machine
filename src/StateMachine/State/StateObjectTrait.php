<?php

namespace Systream\StateMachine\State;


trait StateObjectTrait
{

	/**
	 * @var StateInterface
	 */
	protected $state;

	/**
	 * @param StateInterface $state
	 * @return void
	 */
	public function setState(StateInterface $state)
	{
		$this->state = $state;
	}

	/**
	 * @return StateInterface
	 */
	public function getState()
	{
		return $this->state;
	}

}