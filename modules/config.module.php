<?php
define("DS", DIRECTORY_SEPARATOR);
include_once("./exceptions.module.php");
/*
 * inviConfig 1.0alpha
 * Config tool for inviCMS
 */
include_once("..".DS."system".DS."config.php");

class inviConfig {
    //Metadata
    const NAME = "inviConfig";
    const VER = "1.0alpha";
    
    public static function get($what)
    {
        $tree = explode("->", $what);
        if (!isset(Config::${$tree[0]}))
        {
            throw new inviException(10001, "Config value $${tree[0]} does not exist.");
        }
        $value = Config::${$tree[0]};
        for ($i=1; $i<count($tree); $i++)
        {
            if (!isset($value[$tree[$i]]))
            {
                throw new inviException(10001, "Config value $${tree[$i]} does not exist.");
            }
            $value = $value[$tree[$i]];
        }
        return $value;
    }
    
    public function set($what)
    {
        $tree = explode("->", $what);
        if (!isset(Config::${$tree[0]}))
        {
            throw new inviException(10001, "Config value $${tree[0]} does not exist.");
        }
        $value = Config::${$tree[0]};
        for ($i=1; $i<count($tree); $i++)
        {
            if (!isset($value[$tree[$i]]))
            {
                throw new inviException(10001, "Config value $${tree[$i]} does not exist.");
            }
            $value = $value[$tree[$i]];
        }
    }
}
?>
<?php
//var_dump(inviConfig::get("plugins->blog->integrated"));
?>