<?php
class inviMysql {
	//Metadata
	public $name = "inviMysql";
	public $version = "3.0";
	
	private $mysqli;
	
	function __construct($server = "", $login = "", $password = "", $database = "")
	{
		if ($server == "" && $login == "" && $password == "" && $database == "")
		{
			$_config = new SconfigQueries();
			$server = $_config->get("db_server");
			$login = $_config->get("db_login");
			$password = $_config->get("db_password");
			$database = $_config->get("db");
		}
		$this->mysqli = new mysqli($server, $login, $password, $database);
		return true;
	}

	public function selectEntries($table, $optionals = null)
	{
		$query = "SELECT ";
		if (isset($optionals))
		{
			if (isset($optionals['distinct']))
			{
				$query .= "DISTINCT ";
			}
			if (isset($optionals['rows']))
			{
				$rows = explode(", ", $optionals['rows']);
				foreach ($rows as $value)
				{
					if (end($rows) != $value) $query .= "`".$value."`, ";
					if (end($rows) == $value) $query .= "`".$value."` ";
				}
			} else {
				$query .= "* ";
			}
			$query .= "FROM `".$table."`";
			if (isset($optionals['cases']))
			{
				preg_match_all("/`([A-z0-9_-]*)`(==|%=)<([^>]+)>(, )?/s", $optionals['cases'], $matches);
				$query .= " WHERE ";
				for ($i=0; $i<count($matches[0]); $i++)
				{
					switch ($matches[2][$i])
					{
						case "==":
							$query .= "`".$matches[1][$i]."` = ?";
							break;
						case "%=":
							$query .= "`".$matches[1][$i]."` LIKE ?";
							$matches[3][$i] = "%".$matches[3][$i]."%";
							break;
					}
					if ($matches[4][$i] == ", ")
					{
						$query .= " AND ";
					}
				}
			}
			if (isset($optionals['order']))
			{
				$order = explode(" ", $optionals['order']);
				$query .= " ORDER BY `$order[0]`";
				if ($order[1] == "asc") $query .= " ASC";
				if ($order[1] == "desc") $query .= " DESC";
			}
			if (isset($optionals['limit'])) $query .= " LIMIT ".$optionals['limit'];
		} else {
			$query .= "* FROM `$table`";
		}
		$stmt = $this->mysqli->prepare($query);
		$params = array();
		$types = "";
		if (isset($matches))
		{
			foreach ($matches[3] as $key=>$value)
			{
				if (is_int($value) == TRUE) $types .= "i";
				elseif(is_float($value) == TRUE) $types .= "d";
				else $types .= "s";
				$params[$key] = &$matches[3][$key];
			}
			array_unshift($params, $types);
			call_user_func_array(array($stmt, "bind_param"), $params);
		}
		$stmt->execute();
		$results = array();
		$meta = $stmt->result_metadata();
		$data = array();
		while ($field = $meta->fetch_field())
		{
			$data[$field->name] = "";
			$results[] = &$data[$field->name];
		}
		$stmt->store_result();
		call_user_func_array(array($stmt, "bind_result"), $results);
		$results = array();
		$i=0;
		while ($stmt->fetch())
		{
			$results[$i] = array();
			foreach ($data as $key=>$value)
			{
				$results[$i][$key] = $value;
			}
			$i++;
		}
		$stmt->close();
		return $results;
	}

	public function countEntries($table, $optionals = null)
	{
		$query = "SELECT ";
		if (isset($optionals))
		{
			if (isset($optionals['distinct']))
			{
				$query .= "DISTINCT ";
			}
			if (isset($optionals['rows']))
			{
				$rows = explode(", ", $optionals['rows']);
				$query .= "COUNT(";
				foreach ($rows as $value)
				{
					if (end($rows) != $value) $query .= "`".$value."`, ";
					if (end($rows) == $value) $query .= "`".$value."`) ";
				}
			} else {
				$query .= "COUNT(*) ";
			}
			$query .= "FROM `".$table."`";
			if (isset($optionals['cases']))
			{
				preg_match_all("/`([A-z0-9_-]*)`(==|%=)<([^>]+)>(, )?/s", $optionals['cases'], $matches);
				$query .= " WHERE ";
				for ($i=0; $i<count($matches[0]); $i++)
				{
					switch ($matches[2][$i])
					{
						case "==":
							$query .= "`".$matches[1][$i].'` = ?';
							break;
						case "%=":
							$query .= "`".$matches[1][$i]."` LIKE ?";
							$matches[3][$i] = "%".$matches[3][$i]."%";
							break;
					}
					if ($matches[4][$i] == ", ")
					{
						$query .= " AND ";
					}
				}
			}
		} else {
			$query .= "COUNT(*) FROM `$table`";
		}
		$stmt = $this->mysqli->prepare($query);
		if (isset($matches))
		{
			$params = array();
			$types = "";
			foreach ($matches[3] as $key=>$value)
			{
				if (is_int($value) == TRUE) $types .= "i";
				elseif(is_float($value) == TRUE) $types .= "d";
				else $types .= "s";
				$params[$key] = &$matches[3][$key];
			}
			array_unshift($params, $types);
			call_user_func_array(array($stmt, "bind_param"), $params);
		}
		$stmt->execute();
		$stmt->bind_result($result);
		$stmt->fetch();
		$stmt->close();
		return $result;
	}

	public function selectEntry($table, $cases, $rows = null) {
		$query = "SELECT ";
		if (isset($rows))
		{
			$rows = explode(", ", $rows);
			foreach ($rows as $value)
			{
				if (end($rows) != $value) $query .= "`".$value."`, ";
				if (end($rows) == $value) $query .= "`".$value."` ";
			}
		} else {
			$query .= "* ";
		}
		$query .= "FROM `".$table."` WHERE ";
		preg_match_all("/`([A-z0-9_-]*)`(==|%=)<([^>]+)>(, )?/s", $cases, $matches);
		for ($i=0; $i<count($matches[0]); $i++)
		{
			switch ($matches[2][$i])
			{
				case "==":
					$query .= "`".$matches[1][$i]."` = ?";
					break;
				case "%=":
					$query .= "`".$matches[1][$i]."` LIKE ?";
					break;
			}
			if ($matches[4][$i] == ", ")
			{
				$query .= " AND ";
			}
		}
		$stmt = $this->mysqli->prepare($query);
		$types = "";
		$params = array();
		foreach ($matches[3] as $k=>$v)
		{
			if (is_int($v) == TRUE) $types .= "i";
			elseif(is_float($v) == TRUE) $types .= "d";
			else $types .= "s";
			$params[$k] = &$matches[3][$k];
		}
		array_unshift($params, $types);
		call_user_func_array(array($stmt, "bind_param"), $params);
		$stmt->execute();
		$meta = $stmt->result_metadata();
		$result = array();
		while($field = $meta->fetch_field())
		{
			$result[] = &$data[$field->name];
		}
		$stmt->store_result();
		call_user_func_array(array($stmt, "bind_result"), $result);
		$stmt->fetch();
		$stmt->close();
		return $data;
	}
	
	public function insertData($table, $values)
	{
		$query = "INSERT INTO `".$table."` ";
		preg_match_all("/(`([A-Za-z0-9_-]+)`=)?<([^>]+)>(, )?/s", $values, $matches);
		if ($matches[1][1] != "")
		{
			$query .= "(";
			for ($i=0; $i<count($matches[0]); $i++)
			{
				$query .= "`".$matches[2][$i]."`";
				if ($matches[4][$i] == ", ")
				{
					$query .= ", ";
				}
			}
			$query .= ") ";
		}
		$query .= "VALUES (";
		for ($i=0; $i<count($matches[2]); $i++)
		{
			$query .= "?";
			if ($matches[4][$i] == ", ")
			{
				$query .= ", ";
			}
		}
		$query .= ")";
		$stmt = $this->mysqli->prepare($query);
		$nvalues = array();
		$types = "";
		foreach ($matches[3] as $key=>$value)
		{
			if (is_int($value)) $types .= "i";
			elseif(is_float($value)) $types .= "d";
			else $types .= "s";
			$nvalues[$key] = &$matches[3][$key];
		}
		array_unshift($nvalues, $types);
		call_user_func_array(array($stmt, "bind_param"), $nvalues);
		if ($stmt->execute() != FALSE) return "ok";
		else return "fail: ".$this->mysqli->error;
	}

	public function updateData($table, $values, $cases = null)
	{
		$query = "UPDATE ".$table." SET ";
		preg_match_all("/`([A-z0-9_-]+)`=<([^>]+)>(, )?/s", $values, $valuesar);
		for ($i=0; $i<count($valuesar[0]); $i++)
		{
			$query .= "`".$valuesar[1][$i]."` = ?";
			if ($valuesar[3][$i] == ", ") $query .= ", ";
		}
		if ($cases != null)
		{
			preg_match_all("/`([A-z0-9_-]*)`(==|%=)<([^>]+)>(, )?/s", $cases, $casesar);
			$query .= " WHERE ";
			for ($i=0; $i<count($casesar[0]); $i++)
			{
				switch ($casesar[2][$i])
				{
					case "==":
						$query .= "`".$casesar[1][$i]."` = ?";
						break;
					case "%=":
						$query .= "`".$casesar[1][$i]."` LIKE ?";
						$casesar[3][$i] = "%".$casesar[3][$i]."%";
						break;
				}
				if ($casesar[4][$i] == ", ")
				{
					$query .= " AND ";
				}
			}
		}
		$stmt = $this->mysqli->prepare($query);
		$values = array();
		$types = "";
		foreach ($valuesar[2] as $key=>$value)
		{
			if (is_int($value) == TRUE) $types .= "i";
			elseif(is_float($value) == TRUE) $types .= "d";
			else $types .= "s";
			$values[$key] = &$valuesar[2][$key];
		}
		$cases = array();
		foreach ($casesar[3] as $key=>$value)
		{
			if (is_int($value) == TRUE) $types .= "i";
			elseif(is_float($value) == TRUE) $types .= "d";
			else $types .= "s";
			$cases[$key] = &$casesar[3][$key];
			$values[] = &$cases[$key];
		}
		array_unshift($values, $types);
		call_user_func_array(array($stmt, "bind_param"), $values);
		if ($stmt->execute() != FALSE) return "ok";
		else return "error: ".$this->mysqli->error;
	}
	
	public function deleteData($table, $cases = null)
	{
		$query = "DELETE FROM `".$table."`";
		if ($cases != null)
		{
			$query .= " WHERE ";
			preg_match_all("/`([A-z0-9_-]*)`(==|%=)<([^>]+)>(, )?/s", $cases, $matches);
			for ($i=0; $i<count($matches[0]); $i++)
			{
				switch ($matches[2][$i])
				{
					case "==":
						$query .= "`".$matches[1][$i]."` = ?";
						break;
					case "%=":
						$query .= "`".$matches[1][$i]."` LIKE ?";
						break;
				}
				if ($matches[4][$i] == ", ")
				{
					$query .= " AND ";
				}
			}
		}
		$stmt = $this->mysqli->prepare($query);
		if (isset($matches))
		{
			$cases = array();
			$types = "";
			foreach ($matches[3] as $key=>$value)
			{
				if (is_int($value) == TRUE) $types .= "i";
				elseif(is_float($value) == TRUE) $types .= "d";
				else $types .= "s";
				$cases[$key] = &$matches[3][$key];
			}
			array_unshift($cases, $types);
			call_user_func_array(array($stmt, "bind_param"), $cases);
		}
		if ($stmt->execute() != FALSE) return "ok";
		else return "error: ".$mysqli->error;
	}
}
?>