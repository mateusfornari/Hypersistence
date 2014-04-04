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
		
		$this->bindEntity('Person', 'person', null);
		$this->bindPk($this->id, 'id');
		$this->bindVar($this->name, 'name');
		$this->bindVar($this->email, 'email');
		$this->bindOneToMany($this->books, 'person_id', 'Book');
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
	
	public function __toString() {
		return $this->name;
	}
	
	public function getBooks() {
		return $this->books;
	}

	public function setBooks($books) {
		$this->books = $books;
	}


	
}

class Student extends Person{
	private $number;
	private $courses;
	
	function __construct($id = null, $name = null, $email = null, $number = null) {
		$this->number = $number;
		parent::__construct($id, $name, $email);
		$this->bindEntity('Student', 'student', 'id');
		$this->bindPk($this->id, 'id');
		$this->bindVar($this->number, 'number');
		$this->bindManyToMany($this->courses, 'student_course', 'student_id', 'course_id', 'Course');
	}
	public function getNumber() {
		return $this->number;
	}

	public function setNumber($number) {
		$this->number = $number;
	}

	public function getCourses() {
		return $this->courses;
	}

	public function setCourses($courses) {
		$this->courses = $courses;
	}


}
?>