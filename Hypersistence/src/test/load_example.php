<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';


$b = new Book(3);
$b->load();
echo $b->getAuthor()."\n";

$p = new Person(3);
$p->load();
var_dump($p->getName());

$s = new Student(19);
$s->load();
var_dump($s->getName());
?>
