<?php
require_once '../hypersistence/DB.php';
require_once '../hypersistence/Hypersistence.php';
require_once './Person.php';
require_once './Book.php';

$s = new Student(19);
$s->load();

$s->delete();

DB::getDBConnection()->commit();
?>
