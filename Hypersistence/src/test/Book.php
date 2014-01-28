<?php
class Book extends Hypersistence{
	private $id;
	private $author;
	private $title;
	
	function __construct($id = null, $author = null, $title = null) {
		$this->id = $id;
		$this->author = $author;
		$this->title = $title;
		
		$e = $this->bindEntity('Book', 'book');
		$e->bindPk($this->id, 'id');
		$e->bindManyToOne($this->author, 'person_id', 'Person');
		$e->bindVar($this->title, 'title');
	}

	public function getId() {
		return $this->id;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

}
?>