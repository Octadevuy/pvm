<?php

namespace Formapro\Pvm\Builder;

use Ramsey\Uuid\Uuid;
use Formapro\Pvm\Node;
use Formapro\Pvm\ProcessBuilder;
use function Formapro\Values\get_value;
use function Formapro\Values\set_value;

class NodeBuilder
{
  /**
   * @var ProcessBuilder
   */
  private ProcessBuilder $processBuilder;

  /**
   * @var Node
   */
  private Node $node;

  /**
   * NodeBuilder constructor.
   * @param \Formapro\Pvm\ProcessBuilder $processBuilder
   * @param \Formapro\Pvm\Node $node
   */
  public function __construct(ProcessBuilder $processBuilder, Node $node)
  {
    $this->processBuilder = $processBuilder;

    $this->node = $node;

    if (false == get_value($this->node, 'id')) {
      $this->node->setId(Uuid::uuid4()->toString());
    }
  }

  /**
   * @param string $id
   * @return $this
   */
  public function setId(string $id): self
  {
    $this->node->setId($id);

    return $this;
  }

  /**
   * @param string $label
   * @return $this
   */
  public function setLabel(string $label): self
  {
    $this->node->setLabel($label);

    return $this;
  }

  /**
   * @param string $behavior
   * @return $this
   */
  public function setBehavior(string $behavior): self
  {
    $this->node->setBehavior($behavior);

    return $this;
  }

  /**
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setOption(string $key, mixed $value): self
  {
    $this->node->setOption($key, $value);

    return $this;
  }

  /**
   * @param mixed $value
   * @return $this
   */
  public function replaceConfig(mixed $value): self
  {
    $this->node->replaceConfig($value);

    return $this;
  }

  /**
   * @param string $key
   * @param mixed|null $value
   * @return $this
   */
  public function setConfig(string $key, mixed $value = null): self
  {
    $this->node->setConfig($key, $value);

    return $this;
  }

  /**
   * @param string $key
   * @param mixed|null $value
   * @return $this
   */
  public function setState(string $key, mixed $value = null): self
  {
    $this->node->setState($key, $value);

    return $this;
  }

  public function end(): ProcessBuilder
  {
    return $this->processBuilder;
  }

  public function getNode(): Node
  {
    return $this->node;
  }
}
