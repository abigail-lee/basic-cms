<?php

/** Class to handle articles **/

  class Article {
  	public $id = null;
  	public $publicationDate = null;
  	public $title = null;
  	public $summary = null;
  	public $content = null;

  	/* Sets the object's properties using the values in the supplied array */

  	public function __construct($data=array()) {
  		if (isset($data['id'])) $this->id = (int) $data['id'];
  		if (isset($data['publicationDate'])) $this->publicationDate = (int) $data['publicationDate'];
  		if (isset($data['title'])) $this->title = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['title']);
  		if (isset($data['summary'])) $this->summary = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['summary']);
  		if (isset($data['content'])) $this->content = $data['content'];
  	}


  	/* Sets the object's properties using the edit form post values in the supplied array */
  	public function storeFormValues($params) {
  		// Store all the parameters
  		$this->__construct($params);

  		// Parse and store the publication date
  		if (isset($params['publicationDate'])) {
  			$publicationDate = explode('-', $params['publicationDate']);

  			if (count($publicationDate) == 3) {
  				list($y, $m, $d) = $publicationDate;
  				$this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
  			}
  		}
  	}


  	// Returns an article object matching the given article ID
  	public static function getById($id){
  		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  		$sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles WHERE id = :id";
  		$st = $conn->prepare($sql);
  		$st->bindValue(":id", $id, PDO::PARAM_INT);
  		$st->execute();
  		$row = $st->fetch();
  		$conn = null;
  		if ($row) return new Article($row);
  	}


  	// Returns all (or a range of) Article objects in the DB
  	public static function getList($numRows=1000000) {
  		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  		$sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles
  		ORDER BY publicationDate DESC LIMIT :numRows";
  		$st = $conn->prepare($sql);
  		$st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
  		$st->execute();
  		$list = array();

  		while ($row = $st->fetch()) {
  			$article = new Article($row);
  			$list[] = $article;
  		}

  		// Gets the total number of articles that matched the criteria
  		$sql = "SELECT FOUND_ROWS() AS totalRows";
  		$totalRows = $conn->query($sql)->fetch();
  		$conn = null;
  		return (array("results" => $list, "totalRows" => $totalRows[0]));
  	}


  	// Inserts the current Article object into the database and sets its ID property
  	public function insert(){
  		// first, check if the object already has an ID
  		if(!is_null($this->id)) trigger_error("Article::insert(): Attempted to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR);

  		// if not, let's insert the article
  		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  		$sql = "INSERT INTO articles (publicationDate, title, summary, content) VALUES (FROM_UNIXTIME(:publicationDate), :title, :summary, :content)";
  		$st = $conn->prepare($sql);
  		$st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
  		$st->bindValue(":title", $this->title, PDO::PARAM_STR);
  		$st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
  		$st->bindValue(":content", $this->content, PDO::PARAM_STR);
  		$st->execute();
  		$this->id = $conn->lastInsertId();
  		$conn = null;
  	}


  	// Updates the current Article object in the database
  	public function update(){
  		// first, check if this object already has an ID
  		if(is_null($this->id)) trigger_error("Article::update(): Attempted to update an article object that does not have its ID property set.", E_USER_ERROR);

  		// if it does, let's update the existing article
  		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  		$sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate), title=:title, summary=:summary, content=:content WHERE id=:id";
  		$st = $conn->prepare($sql);
  		$st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
  		$st->bindValue(":title", $this->title, PDO::PARAM_STR);
  		$st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
  		$st->bindValue(":content", $this->content, PDO::PARAM_STR);
  		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
  		$st->execute();
  		$conn = null;
  	}


  	// Deletes the current Article object in the database
  	public function delete(){
  		// first, check that the object has an ID
  		if(is_null($this->id)) trigger_error("Article::delete(): Attempted to delete an object that doesn't have its ID property set.", E_USER_ERROR);

  		// if it does, let's delete the article
  		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  		$sql = "DELETE FROM articles WHERE id = :id LIMIT 1";
  		$st = $conn->prepare($sql);
  		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
  		$st->execute();
  		$conn = null;
  	}
  }
?>