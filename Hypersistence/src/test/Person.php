<?php
class Person extends Hypersistence{
	
	protected $id;
	protected $name;
	protected $email;
	
	public function __construct($id = null, $name = null, $email = null) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		
		$e = $this->bindEntity('Person', 'person');
		$e->bindVar($this->id, 'id', true);
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

}
?>