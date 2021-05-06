<?php

namespace Formapro\Pvm\State;

class ObjectState implements StatefulInterface
{

  public function getValue(string $key, $default = null)
  {
    if (!property_exists($this, $key)) {
      return $default;
    }

    return $this->{$key};
  }

  public function setValue(string $key, $value): void
  {
    $this->{$key} = $value;
  }

  public function hydrate(array $values): void
  {
    foreach ($values as $key => $value)
    {
      $this->{$key} = $value;
    }
  }

  public function toArray(): array
  {
    return get_object_vars($this);
  }

}
