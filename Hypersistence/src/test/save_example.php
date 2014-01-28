<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';

$p = new Person(1);
$p->load();

$b = new Book();
$b->setAuthor($p);
$b->setTitle('My Book');
$b->save();

DB::getDBConnection()->commit();
?>
