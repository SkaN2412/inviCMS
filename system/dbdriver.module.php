<?php
class inviDBDriver {
	//Metadata
	public $name = "inviDBDriver";
	public $version = "4.3beta";
	
    // Variable for connection
	private $conn;
    // Statement variable
    private $stat;
	
	function __construct($server = "", $login = "", $password = "", $db = "")
	{
        // If no parameters given, watch connection data from config
		if ($server == "" || $login == "" || $password == "" || $db == "")
		{
			$conn_data = inviConfig::get("database");
		} else {
            $conn_data = array(
                'server' => $server,
                'login' => $login,
                'password' => $password,
                'db' => $db
            );
        }
        try { // Try to connect
            $this->conn = new PDO("mysql:host={$conn_data['server']};dbname={$conn_data['db']}", $conn_data['login'], $conn_data['password']);
        } catch (PDOException $e) {
            throw new inviException($e->getCode(), $e->getMessage());
        } // Errors will throw exceptions
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
    // <ethod for getting entries from database
	public function selectEntries($table, $optionals = NULL)
	{
		$query = "SELECT ";
        // If there's optional parameters given
		if ($optionals != NULL)
		{
			if (isset($optionals['distinct']))
			{
				$query .= "DISTINCT ";
                unset($optionals['distinct']);
			}
			if (isset($optionals['rows']))
			{ // Rows to return
				$rows = explode(", ", $optionals['rows']);
				foreach ($rows as $row)
				{
                    $query .= "`{$row}`";
					if (end($rows) != $row)
                    {
                        $query .= ", ";
                    }
				}
                unset($rows, $row, $optionals['rows']);
			} else {
				$query .= "* ";
			}
			$query .= " FROM `".$table."`";
			if (isset($optionals['cases']))
			{ // Cases
				$query .= " WHERE ";
                $data = array();
				foreach ($optionals['cases'] as $row => $v)
                {
                    $query .= "`{$row}` = ?";
                    $data[] = $v;
                    end($optionals['cases']);
                    if ($row != key($optionals['cases']))
                    {
                        $query .= " AND ";
                    }
                }
                unset($v, $row, $optionals['cases']);
            }
			if (isset($optionals['order']))
			{
				$optionals['order'] = explode(" ", $optionals['order']);
				$query .= " ORDER BY `{$optionals['order']}`";
				switch ($optionals['order'][1])
                {
                    case 'desc':
                        $query .= " DESC";
                        break;
                    case 'asc':
                    default:
                        $query .= " ASC";
                }
                unset($optionals['order']);
			}
			if (isset($optionals['limit']))
            {
                $query .= " LIMIT ".$optionals['limit'];
                unset($optionals['limit']);
            }
		} else {
			$query .= "* FROM `$table`";
            unset($table);
		}
		$this->query($query, $data);
        return $this->getReturnedData();
	}

	public function countEntries($table, $optionals = null)
	{
		$query = "SELECT ";
		if ($optionals != NULL)
		{
			if (isset($optionals['distinct']))
			{
				$query .= "DISTINCT ";
                unset($optionals['distinct']);
			}
			if (isset($optionals['rows']))
			{
				$rows = explode(", ", $optionals['rows']);
                $query .= "COUNT(";
				foreach ($rows as $row)
				{
                    $query .= "`{$row}`";
					if (end($rows) != $row)
                    {
                        $query .= ", ";
                    } else {
                        $query .= ") ";
                    }
				}
                unset($rows, $row, $optionals['rows']);
			} else {
				$query .= "* ";
			}
			$query .= "FROM `".$table."`";
			if (isset($optionals['cases']))
			{
				$query .= " WHERE ";
                $data = array();
				foreach ($optionals['cases'] as $row => $v)
                {
                    $query .= "`{$row}` = ?";
                    $data[] = $v;
                    end($optionals['cases']);
                    if ($row != key($optionals['cases']))
                    {
                        $query .= " AND ";
                    }
                }
                unset($v, $row, $optionals['cases']);
            }
			if (isset($optionals['order']))
			{
				$optionals['order'] = explode(" ", $optionals['order']);
				$query .= " ORDER BY `{$optionals['order']}`";
				switch ($optionals['order'][1])
                {
                    case 'desc':
                        $query .= " DESC";
                        break;
                    case 'asc':
                    default:
                        $query .= " ASC";
                }
                unset($optionals['order']);
			}
			if (isset($optionals['limit']))
            {
                $query .= " LIMIT ".$optionals['limit'];
                unset($optionals['limit']);
            }
		} else {
			$query .= "COUNT(*) FROM `$table`";
            $optionals['cases'] = array();
            unset($table);
		}
		$this->query($query, $data);
        $data = $this->getReturnedData("num");
        return intval($data[0][0]);
	}

	public function selectEntry($table, $cases, $rows = NULL) {
        $opt = array(
            'cases' => $cases,
            'limit' => "1"
        );
        if ($rows != NULL)
        {
            $opt['rows'] = $rows;
        }
		$data = $this->selectEntries($table, $opt);
        if ($data !== NULL)
        {
            return $data[0];
        } else {
            return NULL;
        }
	}
	
	public function insertData($table, $values)
	{
		$query = "INSERT INTO `".$table."` ";
		if (!isset($values[0]))
		{
			$query .= "(";
			foreach ($values as $row => $v)
			{
				$query .= "`".$row."`";
                end($values);
				if ($row != key($values))
				{
					$query .= ", ";
				}
			}
			$query .= ") ";
            unset($row, $v, $table);
		}
        $query .= "VALUES (";
        $nvalues = array();
        foreach ($values as $k=>$value)
        {
            $nvalues[] = $value;
            $query .= "?";
            end($values);
            if ($k != key($values))
            {
                $query .= ", ";
            }
        }
        $values = $nvalues;
        unset($nvalues);
        $query .= ")";
        return $this->query($query, $values);
	}

	public function updateData($table, $values, $cases = null)
	{
		$query = "UPDATE ".$table." SET ";
        $data = array();
		foreach ($values as $row => $v)
        {
            $data[] = $v;
            $query .= "`{$row}` = ?";
            end($values);
            if ($row != key($values))
            {
                $query .= ", ";
            }
            unset($values);
        }
		if ($cases != null)
		{
			$query .= " WHERE ";
			foreach ($cases as $row => $v)
            {
                $data[] = $v;
                $query .= "`{$row}` = ?";
                end($cases);
                if ($row != key($cases))
                {
                    $query .= " AND ";
                }
            }
            unset($v, $row);
		}
		return $this->query($query, $data);
	}
	
	public function deleteData($table, $cases = array())
	{
		$query = "DELETE FROM `".$table."`";
		if ($cases != array())
		{
			$query .= " WHERE ";
            $data = array();
			foreach ($cases as $row => $v)
            {
                $data[] = $v;
                $query .= "`{$row}` = ?";
                end($cases);
                if ($row != key($cases))
                {
                    $query .= " AND ";
                }
            }
            unset($v, $row);
		}
		return $this->query($query, $cases);
	}
        
        public function query($query, $data = array())
        {
            try { // Prepare statement
                $this->stat = $this->conn->prepare($query);
                if (!is_array($data))
                { // If data isn't array, make it an empty array
                    $data = array();
                }
                // Execute statement with data given
                $this->stat->execute($data);
            } catch (PDOException $e) {
                throw new inviException($e->getCode(), $e->getMessage());
            }
            return true;
        }
        
        public function getReturnedData($fetch_mode = "assoc")
        {
            try {
                switch ($fetch_mode)
                { // Set fetch mode, default is assoc.
                    case "num":
                        $this->stat->setFetchMode(PDO::FETCH_NUM);
                        break;
                    case "assoc":
                    default:
                        $this->stat->setFetchMode(PDO::FETCH_ASSOC);
                }
                if ($this->stat->rowCount() == 0)
                {
                    return NULL;
                }
                $data = array();
                while ($row = $this->stat->fetch())
                { // Fetch $data array with rows
                    $data[] = $row;
                }
            } catch (PDOException $e) {
                throw new inviException($e->getCode, $e->getMessage);
            }
            return $data;
        }
}
?>