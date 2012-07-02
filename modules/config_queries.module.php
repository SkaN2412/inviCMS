<?php
include(".".DS."etc".DS."config.php");

class SconfigQueries {
	private $config;

	public function __construct() {
		$this->config = new Sconfig;
	}

	public function get($name) {
		return $this->config->$name;
	}
	
	public function set($name, $nvalue) {
		$this->config->$name = $nvalue;
		$text = "<?php\nclass config {\n";
		foreach ($this->config as $key=>$value) {
			$text .= "\tpublic $".$key." = '".$value."';\n";
		}
		$text .= "}\n?>";
		file_put_contents("config.class.php", $text);
		return true;
	}

	public function add($name, $nvalue) {
		$text = "<?php\nclass config {\n";
		foreach ($this->config as $key=>$value) {
			$text .= "\tpublic $".$key." = '".$value."';\n";
		}
		$text .= "\tpublic $".$name." = '".$nvalue."';\n";
		$text .= "}\n?>";
		file_put_contents("config.class.php", $text);
	}
}
?>