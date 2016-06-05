<?php

namespace Systream\StateMachine;


use Systream\StateMachine\State\StateInterface;

class State implements StateInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var null|string
	 */
	protected $id;

	/**
	 * State constructor.
	 * @param string $name
	 * @param null|string $id
	 */
	public function __construct($name, $id = null)
	{
		$this->name = $name;
		$this->id = $id !== null ? $id : md5($name);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
}