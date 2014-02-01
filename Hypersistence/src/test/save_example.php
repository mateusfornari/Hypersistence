<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';
require_once './Course.php';

$p = new Person();
$p->setName('Mateus Fornari');
$p->setEmail('mateusfornari@gmail.com');
$p->save();
echo $p->getId()."\n";

$b = new Book();
$b->setAuthor($p);
$b->setTitle('My Book');
$b->save();


$s = new Student();
$s->setEmail('mateusfornari@gmail.com');
$s->setName('Mateus Fornari');
$s->setNumber(123456);
$s->save();
echo $s->getId()."\n";

$c = new Course();
$c->setDescription('PHP Programming');
$c->save();

$s->addManyToManyRelationTo($c, 'student_course');

Hypersistence::commit();
?>
