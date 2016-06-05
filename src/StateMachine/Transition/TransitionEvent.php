<?php

namespace Systream\StateMachine\Transition;


use Systream\EventDispatcher\AbstractEvent;
use Systream\StateMachine\State\StateInterface;
use Systream\StateMachine\State\StateObjectInterface;

class TransitionEvent extends AbstractEvent
{
	/**
	 * @var StateObjectInterface
	 */
	protected $model;

	/**
	 * @var StateInterface
	 */
	protected $targetState;

	/**
	 * TransitionEvent constructor.
	 * @param StateObjectInterface $model
	 * @param StateInterface $targetState
	 * @param string $key
	 */
	public function __construct(StateObjectInterface $model, StateInterface $targetState, $key)
	{
		$this->model = $model;
		$this->targetState = $targetState;
		$this->key = $key;
	}

	/**
	 * @return StateObjectInterface
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @return StateInterface
	 */
	public function getTargetState()
	{
		return $this->targetState;
	}
}