<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';
require_once './Course.php';

$p = new Person();
$p->setName('Fornari');
$search = $p->search();
$search->setRows(10);
$search->setOffset(0);
$search->execute();
$people = $search->getResultList();
foreach ($people as $p){
	var_dump($p->getName());
}

$p = new Person(1);

$b = new Book();
$b->setAuthor($p);
$search = $b->search();
$search->execute();
$books = $search->getResultList();
foreach ($books as $b){
	var_dump($b->getTitle());
}

$s = new Student();
$s->setName('%mat%');
$search = $s->search();
foreach ($search->fetchAll() as $s){
	var_dump($s->getId());
}

DB::getDBConnection()->commit();
?>
