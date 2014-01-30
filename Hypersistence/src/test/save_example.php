<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';

$p = new Person();
$p->setName('Mateus Fornari');
$p->setEmail('mateusfornari@gmail.com');
$p->save();

$b = new Book();
$b->setAuthor($p);
$b->setTitle('My Book');
$b->save();


$s = new Student();
$s->setEmail('mateusfornari@gmail.com');
$s->setName('Mateus Fornari');
$s->setNumber(123456);
$s->save();

Hypersistence::commit();
?>
