<?php
class Course extends Hypersistence{
	private $id;
	private $description;
	
	function __construct($id = null, $description = null) {
		$this->id = $id;
		$this->description = $description;
		
		$e = $this->bindEntity('Course', 'course');
		$e->bindPk($this->id, 'id');
		$e->bindVar($this->description, 'description');
	}
	public function getId() {
		return $this->id;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDescription($description) {
		$this->description = $description;
	}


}
?>