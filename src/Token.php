<?php

namespace Formapro\Pvm;

use Formapro\Values\ValuesTrait;
use Formapro\Pvm\State\ObjectState;
use Formapro\Pvm\State\StatefulInterface;
use function Formapro\Values\get_value;
use function Formapro\Values\get_values;
use function Formapro\Values\set_value;
use function Formapro\Values\add_object;
use function Formapro\Values\get_objects;

class Token
{
  const SCHEMA = 'http://pvm.forma-pro.com/schemas/Token.json';

  use CreateTrait;
  use ValuesTrait {
    getValue as public;
    setValue as public;
  }

  /**
   * @var StatefulInterface|null
   */
  private ?StatefulInterface $state;

  /**
   * @var Process
   */
  private Process $_process;

  /**
   * @var TokenTransition
   */
  private TokenTransition $_currentTokenTransition;

  /**
   * Token constructor.
   * @param \Formapro\Pvm\State\StatefulInterface|null $state
   */
  public function __construct(StatefulInterface $state = null)
  {
    $this->state = $state ?? new ObjectState();
  }

  /**
   * @param string $id
   */
  public function setId(string $id)
  {
    set_value($this, 'id', $id);
  }

  /**
   * @return string
   */
  public function getId(): string
  {
    return get_value($this, 'id');
  }

  /**
   * @return Process
   */
  public function getProcess(): Process
  {
    return $this->_process;
  }

  /**
   * @param Process $process
   */
  public function setProcess(Process $process)
  {
    $this->_process = $process;
  }

  /**
   * @param TokenTransition $transition
   */
  public function addTransition(TokenTransition $transition)
  {
    $transition->setProcess($this->getProcess());
    $transition->setContext($this->getContext());

    add_object($this, 'transitions', $transition);

    $this->_currentTokenTransition = $transition;
  }

  /**
   * @param TokenTransition $transition
   */
  public function setCurrentTransition(TokenTransition $transition): void
  {
    $this->_currentTokenTransition = $transition;
  }

  /**
   * @return TokenTransition
   */
  public function getCurrentTransition(): TokenTransition
  {
    if (false == $this->_currentTokenTransition) {
      $transitions = $this->getTransitions();

      $this->_currentTokenTransition = array_pop($transitions);
    }

    return $this->_currentTokenTransition;
  }

  /**
   * @return TokenTransition[]
   */
  public function getTransitions(): array
  {
    $transitions = [];
    foreach (get_objects($this, 'transitions', TokenTransition::class) as $transition) {
      /** @var TokenTransition $transition */

      $transition->setProcess($this->getProcess());
      $transitions[] = $transition;
    }

    usort($transitions, function (TokenTransition $left, TokenTransition $right) {
      return $left->getTime() <=> $right->getTime();
    });

    return $transitions;
  }

  /**
   * @return Node
   */
  public function getTo(): Node
  {
    return $this->getCurrentTransition()->getTransition()->getTo();
  }

  /**
   * @return Node
   */
  public function getFrom(): Node
  {
    return $this->getCurrentTransition()->getTransition()->getFrom();
  }

  /**
   * @param array|null $context
   */
  public function setContext(?array $context): void
  {
    set_value($this, 'context', $context);
  }

  /**
   * @return array|null
   */
  public function getContext(): ?array
  {
    return get_value($this, 'context');
  }

  /**
   * @param StatefulInterface $state
   * @return void
   */
  public function replaceState(StatefulInterface $state): void
  {
    $this->state = $state;
  }

  /**
   * @return StatefulInterface
   */
  public function getStateObject(): StatefulInterface
  {
    return $this->state;
  }

  /**
   * @param array $values
   * @return void
   */
  public function hydrateState(array $values): void
  {
    $this->state->hydrate($values);
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public function setState(string $key, mixed $value): void
  {
    $this->state->setValue($key, $value);
  }

  /**
   * @param string $key
   * @param mixed|null $default
   * @return mixed|null
   */
  public function getState(string $key, mixed $default = null)
  {
    return $this->state->getValue($key, $default);
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    return array_merge(get_values($this), ['state' => $this->state->toArray()]);
  }

}
