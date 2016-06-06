<?php

namespace Systream;

use Systream\EventDispatcher\Subscription;
use Systream\StateMachine\Exception\CantSetStatusException;
use Systream\StateMachine\State\StateInterface;
use Systream\StateMachine\State\StateObjectInterface;
use Systream\StateMachine\Transition\TransitionEvent;
use Systream\StateMachine\Transition\TransitionInterface;

class StateMachine
{

	/**
	 * @var EventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * @var StateInterface[]
	 */
	protected $states = array();

	/**
	 * @var array
	 */
	protected $transitionStates = array();

	/**
	 * @var TransitionInterface[]
	 */
	protected $transitions;

	/**
	 * StateMachine constructor.
	 * @param EventDispatcher $eventDispatcher
	 */
	public function __construct(EventDispatcher $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @param TransitionInterface $transition
	 * @param StateInterface $sourceState
	 * @param StateInterface $targetState
	 */
	public function addTransition(TransitionInterface $transition, StateInterface $sourceState, StateInterface $targetState)
	{
		$this->addState($sourceState);
		$this->addState($targetState);

		$this->transitions[] = $transition;

		if (!isset($this->transitionStates[$sourceState->getId()])) {
			$this->transitionStates[$sourceState->getId()] = array();
		}

		$this->transitionStates[$sourceState->getId()][] = $targetState->getId();

		$transitionSubscription = new Subscription(
			$this->getEventKey($sourceState, $targetState),
			function (TransitionEvent $event) use ($transition) {
				$transition->processTransaction($event->getModel(), $event->getTargetState());
			}
		);
		$this->eventDispatcher->subscribe($transitionSubscription);
	}

	/**
	 * @param StateObjectInterface $model
	 * @param StateInterface $targetStatus
	 * @return void
	 */
	public function process(StateObjectInterface $model, StateInterface $targetStatus)
	{
		if (!$this->can($model, $targetStatus)) {
			throw new CantSetStatusException(sprintf('There is no transaction like "%s" to "%s"', $model->getState()->getName(), $targetStatus->getName()));
		}

		$event = new TransitionEvent($model, $targetStatus, $this->getEventKey($model->getState(), $targetStatus));
		$this->eventDispatcher->emit($event);
		$model->setState($targetStatus);
	}

	/**
	 * @param StateObjectInterface $model
	 * @param StateInterface $targetStatus
	 * @return bool
	 */
	public function can(StateObjectInterface $model, StateInterface $targetStatus)
	{
		$modelStateId = $model->getState()->getId();
		return isset($this->transitionStates[$modelStateId]) &&
			in_array($targetStatus->getId(), $this->transitionStates[$modelStateId]);
	}

	/**
	 * @return StateInterface[]
	 */
	public function getStates()
	{
		return array_values($this->states);
	}

	/**
	 * @param StateInterface $state
	 */
	protected function addState(StateInterface $state)
	{
		$id = $state->getId();
		if (!isset($this->states[$id])) {
			$this->states[$id] = $state;
		}
	}

	/**
	 * @param StateInterface $sourceState
	 * @param StateInterface $targetState
	 * @return string
	 */
	protected function getEventKey(StateInterface $sourceState, StateInterface $targetState)
	{
		return $sourceState->getId() . '|' . $targetState->getId();
	}

	/**
	 * @param StateObjectInterface $model
	 * @return StateInterface[]
	 */
	public function getNextStates(StateObjectInterface $model)
	{
		$result = array();
		$modelStateId = $model->getState()->getId();
		if (isset($this->transitionStates[$modelStateId])) {
			foreach ($this->transitionStates[$modelStateId] as $transitionStateId) {
				$result[] = $this->states[$transitionStateId];
			}
		}
		return $result;
	}

}