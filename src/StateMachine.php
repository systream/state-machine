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
	protected $transitionStatesFlow = array();

	/**
	 * @var TransitionInterface[]
	 */
	protected $transitions = array();

	/**
	 * @var array
	 */
	protected $transitionsStates = array();

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

		if (!isset($this->transitionStatesFlow[$sourceState->getId()])) {
			$this->transitionStatesFlow[$sourceState->getId()] = array();
		}

		$this->transitionStatesFlow[$sourceState->getId()][] = $targetState->getId();
		$this->transitionsStates[$transition->getName()] = array($sourceState->getId(), $targetState->getId());

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
			throw new CantSetStatusException(sprintf('There is no transition from "%s" to "%s"', $model->getState()->getName(), $targetStatus->getName()));
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
		return isset($this->transitionStatesFlow[$modelStateId]) &&
			in_array($targetStatus->getId(), $this->transitionStatesFlow[$modelStateId]);
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
		if (isset($this->transitionStatesFlow[$modelStateId])) {
			foreach ($this->transitionStatesFlow[$modelStateId] as $transitionStateId) {
				$result[] = $this->states[$transitionStateId];
			}
		}
		return $result;
	}

	/**
	 * @param StateInterface $sourceState
	 * @param StateInterface $targetState
	 * @return string
	 */
	public function getTransitionName(StateInterface $sourceState, StateInterface $targetState)
	{
		foreach ($this->transitionsStates as $transitionsStateName => $states) {
			if ($sourceState->getId() == $states[0] && $targetState->getId() == $states[1]) {
				return $transitionsStateName;
			}
		}
		return '';
	}

}