<?php

namespace Formapro\Pvm;

use Bridit\Workflows\ValueObjects\TaskNode;
use Formapro\Values\ValuesTrait;
use function Formapro\Values\get_value;
use function Formapro\Values\set_value;
use function Formapro\Values\get_values;
use function Formapro\Values\get_object;
use function Formapro\Values\get_objects;
use function Formapro\Values\build_object;

class Process
{
//  const SCHEMA = 'http://pvm.forma-pro.com/schemas/Process.json';

  use ValuesTrait {
    getValue as public;
    setValue as public;
  }

  protected $objects = [];

  /**
   * @param array $data
   *
   * @return self|object
   */
  public static function create(array $data = [])
  {
    return build_object(static::class, $data);
  }

  public function setId(string $id): void
  {
    set_value($this, 'id', $id);
  }

  public function getId(): string
  {
    return get_value($this, 'id');
  }

  public function setExecutionId(string $id): void
  {
    set_value($this, 'executionId', $id);
  }

  public function getExecutionId(): string
  {
    return get_value($this, 'executionId');
  }

  public function getNode(string $id): Node
  {
    /** @var Node $node */
    if (null === $node = get_object($this, 'nodes.' . $id, Node::class)) {
      throw new \LogicException('Not found');
    }

    $node->setProcess($this);

    return $node;
  }

  /**
   * @return Node[]
   */
  public function getNodes(): array
  {
    $nodes = [];
    foreach (get_objects($this, 'nodes', Node::class) as $node) {
      /** @var Node $node */

      $node->setProcess($this);

      $nodes[] = $node;
    }

    return $nodes;
  }

  /**
   * @return Transition[]
   */
  public function getTransitions(): array
  {
    $transitions = [];
    foreach (get_objects($this, 'transitions', Transition::class) as $transition) {
      /** @var Transition $transition */

      $transition->setProcess($this);

      $transitions[] = $transition;
    }

    return $transitions;
  }

  public function getStartTransition(): Transition
  {
    $startTransitions = $this->getStartTransitions();
    if (count($startTransitions) !== 1) {
      throw new \LogicException(sprintf('There is one start transition expected but got "%s"', count($startTransitions)));
    }

    return $startTransitions[0];
  }

  /**
   * @return array|Transition[]
   */
  public function getStartTransitions(): array
  {
    $startTransitions = [];
    foreach ($this->getTransitions() as $transition) {
      if (null === $transition->getFrom()) {
        $startTransitions[] = $transition;
      }
    }

    return $startTransitions;
  }

  public function getTransition(string $id): Transition
  {
    /** @var Transition $transition */
    if (null === $transition = get_object($this, 'transitions.' . $id, Transition::class)) {
      throw new \LogicException(sprintf('Transition "%s" could not be found', $id));
    }

    $transition->setProcess($this);

    return $transition;
  }

  /**
   * @return Transition[]
   */
  public function getInTransitions(Node $node): array
  {
    $inTransitions = get_value($this, 'inTransitions.' . $node->getId(), []);

    $transitions = [];
    foreach ($inTransitions as $id) {
      $transitions[] = $this->getTransition($id);
    }

    return $transitions;
  }

  /**
   * @return Transition[]
   */
  public function getOutTransitions(Node $node): array
  {
    $outTransitions = get_value($this, 'outTransitions.' . $node->getId(), []);

    $transitions = [];
    foreach ($outTransitions as $id) {
      $transitions[] = $this->getTransition($id);
    }

    return $transitions;
  }

  /**
   * @return Transition[]
   */
  public function getOutTransitionsWithName(Node $node, string $name): array
  {
    $outTransitions = get_value($this, 'outTransitions.' . $node->getId(), []);

    $transitions = [];
    foreach ($outTransitions as $id) {
      $transition = $this->getTransition($id);
      if ($transition->getName() == $name) {
        $transitions[] = $transition;
      }
    }

    return $transitions;
  }

  public function toArray(): array
  {
    return get_values($this);
  }

}
