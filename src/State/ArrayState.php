<?php

namespace Formapro\Pvm\State;

class ArrayState implements StatefulInterface
{

  /**
   * @var array
   */
  protected array $state = [];

  /**
   * @param string $key
   * @param null $default
   * @return array|mixed|null
   */
  public function getValue(string $key, $default = null)
  {

    if (array_key_exists($key, $this->state)) {
      return $this->state[$key];
    }

    if (false === strpos($key, '.')) {
      return $default;
    }

    $items = $this->state;

    foreach (explode('.', $key) as $segment) {
      if (!is_array($items) || !array_key_exists($segment, $items)) {
        return $default;
      }

      $items = &$items[$segment];
    }

    return $items;
  }

  /**
   * @param string $key
   * @param $value
   * @return void
   */
  public function setValue(string $key, $value): void
  {
    $items = &$this->state;

    foreach (explode('.', $key) as $k) {
      if (!isset($items[$k]) || !is_array($items[$k])) {
        $items[$k] = [];
      }

      $items = &$items[$k];
    }

    $items = $value;
  }

  public function hydrate(array $values): void
  {
    $this->state = $values;
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    return $this->state;
  }

}
