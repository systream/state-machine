<?php

namespace Tests\Systream\Unit;


use Systream\StateMachine\State\StateObjectInterface;
use Systream\StateMachine\State\StateObjectTrait;

class DummyStateObject implements StateObjectInterface
{
	use StateObjectTrait;
}