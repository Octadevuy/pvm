<?php
namespace Formapro\Pvm;

use function Formapro\Values\get_value;
use function Formapro\Values\set_value;

class Node
{
    const SCHEMA = 'http://pvm.forma-pro.com/schemas/Node.json';

    protected $values = [];

    use CreateTrait;

    /**
     * @var Process
     */
    private $_process;

    public function getProcess(): Process
    {
        return $this->_process;
    }

    public function setProcess(Process $process): void
    {
        $this->_process = $process;
    }

    public function getId(): string
    {
        return get_value($this, 'id');
    }

    public function setId(string $id): void
    {
        set_value($this, 'id', $id);
    }

    public function getLabel(): string
    {
        return get_value($this, 'label', '');
    }

    public function setLabel(string $label): void
    {
        set_value($this, 'label', $label);
    }

    public function getBehavior(): ?string
    {
        return get_value($this, 'behavior');
    }

    public function setBehavior(?string $behavior): void
    {
        set_value($this, 'behavior', $behavior);
    }

    public function setOption(string $key, $value): void
    {
        set_value($this, 'option.'.$key, $value);
    }

    public function getOption(string $key)
    {
        return get_value($this, 'option.'.$key);
    }
}
