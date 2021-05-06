<?php

namespace Formapro\Pvm\State;

interface StatefulInterface
{

  public function setValue(string $key, $value): void;
  public function getValue(string $key, $default = null);
  public function hydrate(array $values): void;
  public function toArray(): array;

}
