<?php

/**
 * Работает с деревом элементов
 */
class Tree
{
    /**
     * Собственно структура дерева
     *
     * @var array
     */
    protected array $tree;

    /**
     * Общий список элементов
     *
     * @var array<Item>
     */
    protected array $items;

    /**
     * Корневые элементы
     *
     * @var array
     */
    protected array $rootItems;

    /**
     * Индекс категорий по id
     *
     * @var array
     */
    protected array $index;

    /**
     * Максимальный уровень вложенности
     *
     * @var int
     */
    protected int $maxLevel;

    /**
     * Конструктор. Собирает дерево
     *
     * @param array<Item> $items
     */
    public function __construct(array $items)
    {
        $this->tree = [];
        $this->maxLevel = 1;
        $this->items = $items;
        $this->rootItems = array_filter($items, function ($item) {
            return $item->parent == 0;
        });
        foreach ($this->rootItems as $key=>$item) {
            $this->tree[$item->id] = (string) $item->id;
            $this->index[$item->id] = $key;
            $this->getSubTree($item);
        }
    }

    /**
     * Собирает поддерево для данного элемента
     *
     * @param Item $item
     * @return void
     */
    protected function getSubTree(Item $item)
    {
        $children = array_filter($this->items, function ($sub) use ($item) {
            return $sub->parent == $item->id;
        });
        $level = $this->getLevel($item);
        if ($level>$this->maxLevel) {
            $this->maxLevel = $level;
        }
        foreach ($children as $key=>$child) {
            $this->tree[$child->id] = $this->tree[$item->id].'.'.$child->id;
            $this->index[$child->id] = $key;
            $this->getSubTree($child);
        }
    }

    /**
     * Возвращает хэш-путь элемента
     *
     * @param Item $item
     * @return string
     */
    protected function getHash(Item $item): string
    {
        return $this->tree[$item->id];
    }

    /**
     * Возвращает полный массив элементов
     *
     * @return array<Item>
     */
    public function getItems(): array
    {
        return $this->items;
    }


    /**
     * Ищет элемент по id
     *
     * @param integer $id
     * @return Item|null
     */
    public function findItem(int $id): ?Item
    {
        $number = $this->index[$id] ?? null;
        if (is_null($number)) {
            return null;
        }
        return $this->items[$number];
    }

    /**
     * Собирает элементы по ID
     *
     * @param array $ids
     * @return array<Item>
     */
    protected function getItemsByIds(array $ids): array
    {
        $items = [];
        foreach ($ids as $id) {
            $item = $this->findItem($id);
            if ($item instanceof Item) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Возвращает хэши дочерних элементов для данного
     *
     * @param Item $item Целевая категория
     * @return array
     */
    protected function getChildrenHashes(Item $item): array
    {
        $pattern = '/^'.$this->tree[$item->id].'\.[\d]+$/s';
        $search = preg_grep($pattern, $this->tree);
        return $search;
    }

    /**
     * Возвращает дочерние элементы для данного
     *
     * @param Item $item Целевой элемент
     * @return array<Item>
     */
    public function getChildren(Item $item): array
    {
        $search = $this->getChildrenHashes($item);
        $selectedItems = $this->getItemsByIds(array_keys($search));
        return $selectedItems;
    }

    /**
     * Возвращает количество дочерних элементов для данного
     *
     * @param Item $item Целевая категория
     * @return integer
     */
    public function getChildrenCount(Item $item): int
    {
        $search = $this->getChildrenHashes($item);
        return count($search);
    }

    /**
     * Возвращает нижележащие элементы для данного (дочерние и ниже)
     *
     * @param Item $item Целевой элемент
     * @return array<Item>
     */
    public function getDescendants(Item $item): array
    {
        $search = $this->getDescendantHashes($item);
        $keys = array_keys($search);
        $selectedItems = $this->getItemsByIds($keys);
        return $selectedItems;
    }

    /**
     * Возвращает количество нижележащих элементов для данного
     *
     * @param Item $item Целевой элемент
     * @return integer
     */
    public function getDescendantsCount(Item $item): int
    {
        $search = $this->getDescendantHashes($item);
        return count($search);
    }

    /**
     * Ищет хэши нижележащих элементов
     *
     * @param Item $item
     * @return array
     */
    protected function getDescendantHashes(Item $item): array
    {
        $pattern = '/^'.$this->tree[$item->id].'\..+/s';
        $search = preg_grep($pattern, $this->tree);
        return $search;
    }

    /**
     * Отвечает на вопрос, есть ли потомки у данного элемента
     *
     * @param Item $item Целевой элемент
     * @return boolean
     */
    public function hasDescendants(Item $item): bool
    {
        $search = $this->getDescendantHashes($item);
        return (!empty($search));
    }

    /**
     * Возвращает всех предков данного элемента по порядку
     *
     * @param Item $item Целевой элемент
     * @param bool $includeTarget Включать ли последним номером сам целевой элемент
     * @return array<Item>
     */
    public function getAncestors(Item $item, bool $includeTarget = false): array
    {
        $pathItems = explode('.', $this->tree[$item->id]);
        $ancestors = [];

        foreach ($pathItems as $element) {
            $id = (int) $element;
            /* Если это уже сам целевой элемент и его не нужно включать в список, пропускаем */
            if (($id === $item->id) && (!$includeTarget)) {
                continue;
            }
            $ancestors[] = $this->findItem($id);
        }

        return $ancestors;
    }

    /**
     * Возвращает потомков того же родителя (братьев), включая и сам элемент
     *
     * @param Item $item Целевой элемент
     * @return array<Item>
     */
    public function getSiblings(Item $item): array
    {
        if ($item->parent === 0) {
            $siblings = $this->rootItems;
        } else {
            $parent = $this->findItem($item->parent);
            $siblings = $this->getChildren($parent);
        }
        $siblings = array_filter($siblings, function (Item $sibling) use ($item) {
            return $sibling->id !== $item->id;
        });
        return $siblings;
    }

    /**
     * Возвращает уровень вложенности элемента (1 для корневых)
     *
     * @param Item $item Целевой элемент
     * @return integer
     */
    public function getLevel(Item $item): int
    {
        return substr_count($this->tree[$item->id], '.')+1;
    }

    /**
     * Возвращает максимальный уровень вложенности дерева
     *
     * @return integer
     */
    public function getMaxLevel(): int
    {
        return $this->maxLevel;
    }
}
