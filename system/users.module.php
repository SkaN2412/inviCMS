<?php

/*
 * inviUsers
 * Users management tool for inviCMS
 */

class inviUsers
{
    //Metadata
    const NAME = "inviUsers";
    const VER = "0.2.1beta";
    
    protected $_mysqli;
    protected $login = NULL;
    protected $group = NULL;
    protected $stat = false;
    protected static $ext_rows = array();
    protected $ext_data = array();
    
    public static function __init($rows)
    {
        if (is_array($rows))
        {
            if (array_key_exists("login", $rows) || array_key_exists("group", $rows))
            {
                throw new inviException(002, "Unable to create ext data row called \"login\" or \"group\"");
            }
            self::$ext_rows = $rows;
        } else {
            throw new inviException(001, "\$data parameter must be an array, it isn't.");
        }
        return true;
    }

    public function __construct($login = null, $pass = null)
    {
        foreach (self::$ext_rows as $key)
        {
            $this->ext_data[$key] = NULL;
        }
        $this->_mysqli = new inviMysql("localhost", "root", "q24u12e19rt94i", "test");
        if ($login != null && $pass != null)
        {
            $password = md5(md5($login).md5($pass)."ilybgsd7fvbv876");
            $rows = "password, group";
            $sravn = array(
                "password" => NULL,
                "group" => NULL,
            );
            if (self::$ext_rows != array())
            {
                foreach (self::$ext_rows as $row)
                {
                    $rows .= ", ".$row;
                    $sravn[$row] = NULL;
                }
            }
            $data = $this->_mysqli->selectEntry("users", array( 'login' => $login ), $rows);
            if ($data != $sravn)
            {
                if ($data['password'] != $password)
                {
                    throw new inviException(101, "Wrong password!");
                }
                $this->login = $login;
                $this->group = $data['group'];
                $this->stat = true;
                if (self::$ext_rows != array())
                {
                    foreach (self::$ext_rows as $key)
                    {
                        if (!array_key_exists($key, $data))
                        {
                            throw new inviException(103, "No {$key} row in database");
                        }
                        $this->ext_data[$key] = $data[$key];
                    }
                }
            } else {
                throw new inviException(102, "Wrong data");
            }
        }
        return true;
    }
    
    public function getUserData($return = null)
    {
        if ($this->stat == true)
        {
            if ($return != null)
            {
                switch ($return)
                {
                    case "login":
                        return $this->login;
                        break;
                    case "group":
                        return $this->group;
                        break;
                    default:
                        if (in_array($return, self::$ext_rows))
                        {
                            return $this->ext_data[$return];
                        } else {
                            throw new inviException(202, "Unknown data to return");
                        }
                }
            } else {
                $data = array(
                    'login' => $this->login,
                    'group' => $this->group
                );
                if (self::$ext_rows != array())
                {
                    foreach (self::$ext_rows as $key => $value)
                    {
                        $data[$key] = $value;
                    }
                }
                return $data;
            }
        } else {
            throw new inviException(201, "Trying to get user data while non-authorized");
        }
        return true;
    }
    
    public function register($login, $pass, $group, $ext_data)
    {
        $check = $this->_mysqli->selectEntry("users", array( 'login' => $login ), "login");
        if ($check == array('login'=>null))
        {
            $password = md5(md5($login).md5($pass)."ilybgsd7fvbv876");
            $toinsert = array( 'login' => $login, 'password' => $password, 'group' => $group );
            foreach ($ext_data as $key => $value)
            {
                if (in_array($key, self::$ext_rows))
                {
                    $toinsert[$key] .= $value;
                }
            }
            $result = $this->_mysqli->insertData("users", $toinsert);
            if ($result == "ok")
            {
                $this->login = $login;
                $this->group = $group;
                $this->stat = true;
                foreach ($ext_data as $key => $value)
                {
                    if (in_array($key, self::$ext_rows))
                    {
                        $this->ext_data[$key] = $value;
                    }
                }
            } else {
                throw new inviException(301, "MySQL error (".$result.")");
            }
        } else {
            throw new inviException(302, "User already exists");
        }
        return true;
    }
    
    public function is_authed()
    {
        return $this->stat;
    }
    
    public function getMetadata()
    {
        return self::NAME." ".self::VER;
    }
}
?>