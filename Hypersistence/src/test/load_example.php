<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';


$b = new Book(1);
$b->load();
var_dump($b->getAuthor()->getName());

$p = new Person(1);
$p->load();
var_dump($p->getName());
?>
