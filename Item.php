<?php

class Item
{
    /**
     * ID
     *
     * @var integer
     */
    public int $id;

    /**
     * Название
     *
     * @var string
     */
    public string $name;

    /**
     * № родительского элемента
     *
     * @var integer
     */
    public int $parent;

    public function __construct(int $id, string $name, int $parent)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent = $parent;
    }
}
