<?php

namespace Formapro\Pvm;

use function Formapro\Values\build_object;

trait CreateTrait
{
  /**
   * @param array $data
   *
   * @return self|object
   */
  public static function create(array $data = [])
  {
    return build_object(static::class, array_replace([
      'schema' => static::SCHEMA,
    ], $data));
  }
}
