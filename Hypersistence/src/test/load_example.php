<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';
require_once './Course.php';



$p = new Person(1);
$p->load();
var_dump($p->getName());
foreach ($p->getBooks()->fetchAll() as $r){
	echo "{$r->getTitle()}\n";
}


$b = new Book(3);
$b->load();
var_dump($b->getTitle());

$s = new Student(47);
$s->load();
var_dump($s->getName());
foreach ($s->getCourses()->fetchAll() as $c){
	echo "{$c->getId()}\n";
}
?>
