<?php

namespace Systream\StateMachine;


use Systream\StateMachine;
use Tests\Systream\Unit\DummyStateObject;

class DotFileGenerator
{
	/**
	 * @var array
	 */
	protected $descriptorSpecification = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	/**
	 * @var array
	 */
	protected $nodeLabels;

	/**
	 * @var string
	 */
	protected $nodeIndex;

	/**
	 * @param StateMachine $stateMachine
	 * @return string
	 */
	public function generateDotFileData(StateMachine $stateMachine)
	{
		$this->nodeLabels = array();
		$this->nodeIndex = 'a';

		$return = "digraph {
			\r\n";

		$states = $stateMachine->getStates();
		$object = new DummyStateObject();

		foreach ($states as $state) {
			$object->setState($state);
			$nextStates = $stateMachine->getNextStates($object);
			foreach ($nextStates as $nextState) {
				$return .= $this->getNodeIndex($state->getName()) .
					' -> ' .
					$this->getNodeIndex($nextState->getName()) .
					//' [label="'.$state->getName().'"]' .
					";\r\n";
			}
		}

		$return .= "\r\n";
		foreach ($this->nodeLabels as $nodeIndex => $nodeLabel) {
			$return .= '"' . $nodeIndex . '" [label="' . $nodeLabel . '"]' . "\r\n";
		}

		$return .= "\r\n";
		$return .= '}';

		return $return;
	}

	/**
	 * @param string $nodeLabel
	 * @return string
	 */
	protected function getNodeIndex($nodeLabel)
	{
		$nodeIndex = array_search($nodeLabel, $this->nodeLabels);
		if ($nodeIndex === false) {
			$this->nodeIndex++;
			$nodeIndex = $this->nodeIndex;
			$this->nodeLabels[$nodeIndex] = $nodeLabel;
		}
		return $nodeIndex;
	}

	/**
	 * @param StateMachine $stateMachine
	 * @return string
	 * @throws \Exception
	 */
	public function getImage(StateMachine $stateMachine)
	{
		$cmd = 'dot -Tpng';
		$process = proc_open(
			$cmd,
			$this->descriptorSpecification,
			$pipes,
			sys_get_temp_dir(),
			array('PATH' => getenv('PATH'))
		);

		if (!is_resource($process)) {
			throw new \Exception('Failed to execute: ' . $cmd);
		}

		fwrite($pipes[0], $this->generateDotFileData($stateMachine));
		fclose($pipes[0]);

		$output = stream_get_contents($pipes[1]);

		$err = stream_get_contents($pipes[2]);
		if (!empty($err)) {
			throw new \Exception("failed to execute cmd: \"$cmd\". stderr: `$err'\n");
		}

		fclose($pipes[2]);
		fclose($pipes[1]);
		proc_close($process);
		return $output;
	}
}