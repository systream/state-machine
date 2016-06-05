<?php

namespace Systream\StateMachine;


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
	 */
	public function __construct(StateObjectInterface $model, StateInterface $targetState)
	{
		$this->model = $model;
		$this->targetState = $targetState;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		if (!$this->key) {
			$this->key = $this->model->getState()->getId() . '|' . $this->targetState->getId();
		}
		return $this->key;
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