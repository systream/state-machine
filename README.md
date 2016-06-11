# State machine

## Installation

You can install this package via [packagist.org](https://packagist.org/packages/systream/state-machine) with [composer](https://getcomposer.org/).

`composer require systream/state-machine`

composer.json:

```json
"require": {
    "systream/state-machine": "1.*"
}
```

## Usage examples

### Setup

You need to add transitions to state machine.

```php
$stateMachine = new StateMachine(new \Systream\EventDispatcher());

$inStock = new StateMachine\State('In stock');
$ordered = new StateMachine\State('Ordered');
$shippingInProcess = new StateMachine\State('Shipping in process');
$deliveredToClient = new StateMachine\State('Order is at client');

$stateMachine->addTransition(
	new GenericTransition('Order'), $inStock, $ordered
);

$stateMachine->addTransition(
	new GenericTransition('Cancel order'), $ordered, $inStock
);

$stateMachine->addTransition(
	new GenericTransition('Shipping'), $ordered, $shippingInProcess
);

$stateMachine->addTransition(
	new GenericTransition('Handover to client'), $shippingInProcess, $deliveredToClient
);

```

### Custom transitions
You have to implement the ```\Systream\StateMachine\TransitionInterface``` interface to create custom transition


### Process Transition

#### StateObject

To use state machine you need an object which has state.
```process``` method expect ```\Systream\StateMachine\State\StateObjectInterface``` interface.

So you need to implement it, or just use ```\Systream\StateMachine\State\StateObjectTrait```.

#### Can

Testing, whether it can change the status to the target state

```php
$product = new DummyStateObject();
$product->setState($inStock);
$stateMachine->can($product, $ordered); // will return true
$stateMachine->can($product, $deliveredToClient); // will return false

```

#### Process

Set project state to ```In Stock``` and process it to ```Ordered```.

```php
$product = new DummyStateObject();
$product->setState($inStock);
$stateMachine->process($product, $ordered);

```

Set state without transition will trow an ```\Systream\StateMachine\Exception\CantSetStatusException``` exception.

```php
$product = new DummyStateObject();
$product->setState($inStock);
$stateMachine->process($product, $deliveredToClient);

```

### Get available states

```php
$states = $stateMachine->getStates();

```
It will return array of ```\Systream\StateMachine\State\StateInterface``` objects

#### Get next states

This method will return of the next possible states of an state object:
```php
$product = new DummyStateObject();
$states = $stateMachine->getNextStates($product);

```

It will return array of ```\Systream\StateMachine\State\StateInterface``` objects

# Visualization
With this library you are able to generate an image with the states and transitions.
To get this work you need to install ```graphviz```.

```php
$doFileGenerator = new StateMachine\DotFileGenerator();
$image = $doFileGenerator->getImage($stateMachine);
file_put_contents('my_flow_chart.png', $image);

```
[[https://github.com/systream/state-machine/blob/master/assets/my_flow_chart.png|alt=example-flow]]

## Test

[![Build Status](https://travis-ci.org/systream/state-machine.svg?branch=master)](https://travis-ci.org/systream/state-machine)