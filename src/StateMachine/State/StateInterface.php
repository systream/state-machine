<?php

namespace Systream\StateMachine\State;


interface StateInterface
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getId();
}