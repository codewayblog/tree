<?php

require_once('Item.php');
require_once('Tree.php');

$items = [];
$items[] = new Item(1, 'А', 0);
$items[] = new Item(2, 'А-1', 1);
$items[] = new Item(3, 'А-2', 1);
$items[] = new Item(4, 'А-3', 1);
$items[] = new Item(5, 'Б', 0);
$items[] = new Item(6, 'Б-1', 5);
$items[] = new Item(7, 'Б-2', 5);
$items[] = new Item(8, 'Б-3', 5);
$items[] = new Item(9, 'А-2-1', 3);
$items[] = new Item(10, 'А-2-2', 3);
$items[] = new Item(11, 'А-2-1-1', 9);
$items[] = new Item(12, 'Б-3-1', 8);

$tree = new Tree($items);
echo '<pre>';

echo "Максимальный уровень вложенности: ".$tree->getMaxLevel().PHP_EOL;

echo "Потомки элемента A".PHP_EOL;
$itemA = $tree->findItem(1);
var_dump($tree->getDescendants($itemA));

echo "Предки элемента A-2-1-1".PHP_EOL;
$itemA211 = $itemA = $tree->findItem(11);
var_dump($tree->getAncestors($itemA211));

echo "Братья элемента Б-3".PHP_EOL;
$itemB3 = $tree->findItem(8);
var_dump($tree->getSiblings($itemB3));

echo '</pre>';
