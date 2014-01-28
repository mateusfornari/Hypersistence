<?php
class Person extends Hypersistence{
	
	protected $id;
	protected $name;
	protected $email;
	protected $books;


	public function __construct($id = null, $name = null, $email = null) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		
		$e = $this->bindEntity('Person', 'person');
		$e->bindPk($this->id, 'id');
		$e->bindVar($this->name, 'name');
		$e->bindVar($this->email, 'email');
	}
	
	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function getBooks() {
		return $this->books;
	}

	public function setBooks($books) {
		$this->books = $books;
	}


}

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