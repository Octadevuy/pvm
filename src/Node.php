<?php

namespace Formapro\Pvm;

use function Formapro\Values\get_value;
use function Formapro\Values\set_value;

class Node
{
  use CreateTrait;

  const SCHEMA = 'http://pvm.forma-pro.com/schemas/Node.json';

  /**
   * @var Process
   */
  private Process $_process;

  /**
   * @var array
   */
  protected array $values = [];

  public function getProcess(): Process
  {
    return $this->_process;
  }

  /**
   * @param \Formapro\Pvm\Process $process
   */
  public function setProcess(Process $process): void
  {
    $this->_process = $process;
  }

  /**
   * @return string
   */
  public function getId(): string
  {
    return get_value($this, 'id');
  }

  /**
   * @param string $id
   */
  public function setId(string $id): void
  {
    set_value($this, 'id', $id);
  }

  /**
   * @return string
   */
  public function getLabel(): string
  {
    return get_value($this, 'label', '');
  }

  /**
   * @param string $label
   */
  public function setLabel(string $label): void
  {
    set_value($this, 'label', $label);
  }

  /**
   * @return string|null
   */
  public function getBehavior(): ?string
  {
    return get_value($this, 'behavior');
  }

  /**
   * @param string|null $behavior
   */
  public function setBehavior(?string $behavior): void
  {
    set_value($this, 'behavior', $behavior);
  }

  /**
   * @param string $key
   * @param $value
   */
  public function setOption(string $key, $value): void
  {
    set_value($this, 'option.' . $key, $value);
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getOption(string $key): mixed
  {
    return get_value($this, 'option.' . $key);
  }

  /**
   * @param mixed $value
   */
  public function replaceState(mixed $value): void
  {
    set_value($this, 'state', $value);
  }

  /**
   * @param string $key
   * @param $value
   */
  public function setState(string $key, $value): void
  {
    set_value($this, 'state.' . $key, $value);
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getState(string $key): mixed
  {
    return get_value($this, 'state.' . $key);
  }

  /**
   * @return mixed
   */
  public function getAllState(): mixed
  {
    return get_value($this, 'state');
  }

  /**
   * @param mixed $value
   */
  public function replaceConfig(mixed $value): void
  {
    set_value($this, 'config', $value);
  }

  /**
   * @param string $key
   * @param $value
   */
  public function setConfig(string $key, $value): void
  {
    set_value($this, 'config.' . $key, $value);
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getConfig(string $key): mixed
  {
    return get_value($this, 'config.' . $key);
  }

  /**
   * @return mixed
   */
  public function getAllConfig(): mixed
  {
    return get_value($this, 'config');
  }

}
