<?php
class Stemplater {
	//Metadata
	public $name = "inviTemplater";
	public $version = "3.1";
	
	private $dir;
	private $content;
	public $vars;

	public function __construct($dir) {
		$this->dir = $dir.DS;
		if (!is_dir($this->dir)) die("Directory ".$this->dir." does not exist");
		return $this->name." ".$this->version;
	}

	public function load($page) {
		if (file_exists($this->dir.$page.".htm")) $file = $this->dir.$page.".htm";
        elseif (file_exists($this->dir.$page.".html")) $file = $this->dir.$page.".html";
        else die("File ".$this->dir.$page.".htm(l) does not exist");
		if (!filesize($file)) die("File ".$file." is empty");
		$this->content = file_get_contents($file);
	}
	
	public function parse($vars) {
		if (!is_array($vars)) die("$vars is not array");
		$this->vars = $vars;
		unset($vars);
		$this->content = $this->parseCode($this->content);
		$code = $this->content;
		$this->vars = NULL;
		$this->consts = NULL;
		$this->content = NULL;
		return $code;
	}

	public function parseCode($code) {
		preg_match_all('/\{(case|array|var|include)=`(.*?)`\}(((?:(?!\{\/?\1=`\2`).)*)\{\/\1=`\2`\})?/s', $code, $matches);
		for ($i=0; $i<count($matches[0]); $i++) {
			switch ($matches[1][$i]) {
				case "case":
					$code = $this->parseCase($matches[0][$i], $code);
					break;
				case "array":
					$code = $this->parseCycle($matches[0][$i], $code);
					break;
				case "var":
					$code = $this->parseVar($matches[0][$i], $code);
					break;
				case "include":
					$code = $this->parseInclude($matches[0][$i], $code);
					break;
			}
		}
		return $code;
	}

	private function parseVar($match, $code) {
		preg_match('/\{var=`(.*?)`\}/', $match, $var);
		if (!isset($this->vars[$var[1]])) die("Variable $".$var[1]." does not exist");
		$code = str_replace($match, $this->vars[$var[1]], $code);
		return $code;
	}

	private function parseInclude($match, $code) {
		preg_match('/\{include=`(.*?)`\}/', $match, $matches);
		if (file_exists($this->dir.$matches[1].".htm")) $file = $this->dir.$matches[1].".htm";
        elseif (file_exists($this->dir.$matches[1].".html")) $file = $this->dir.$matches[1].".html";
        else die("File ".$this->dir.$matches[1].".htm(l) does not exist");
		$content = file_get_contents($file);
		$content = $this->parseCode($content);
		$code = str_replace($matches[0], $content, $code);
		return $code;
	}

	private function parseCycle($match, $code) {
		preg_match('|\{array=`(\w+)`\}((?:(?!\{/?array).)*){/array=`\1`}|s', $match, $matches);
		if (!isset($this->vars[$matches[1]])) die("Array $".$matches[1]." does not exist");
		if (!is_array($this->vars[$matches[1]])) die("Variable $".$matches[1]." is not array");
		if (!isset($this->vars[$matches[1]][0]) || !is_array($this->vars[$matches[1]][0])) die("Array $".$matches[1]." is not multi-dimensional");
		$coden = "";
		for ($c=0; $c<count($this->vars[$matches[1]]); $c++) {
			$temp = $matches[2];
			foreach ($this->vars[$matches[1]][$c] as $key=>$value) {
				$temp = str_replace("{".$key."}", $value, $temp);
			}
			$temp = $this->parseCode($temp);
			$coden .= $temp;
		}
		$code = str_replace($matches[0], $coden, $code);
		$code = $this->parseCode($code);
		return $code;
	}

	private function parseCase($match, $code) {
		preg_match('/\{case=`([0-9A-Za-z_]+)(==|!=|<=|>=|<|>|\|isset\|)([0-9A-Za-z_]+)`\}((?:(?!\{\/?case=`\1\2\3`).)*)\{\/case=`\1\2\3`\}/s', $match, $matches);
		switch ($matches[2]) {
			case "==":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] == $this->vars[$matches[3]]) {
						$matches[4] = parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] == $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "!=":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] != $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] != $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "<=":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] <= $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] <= $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case ">=":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] >= $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] >= $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "<":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] < $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] < $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case ">":
				if (!isset($this->vars[$matches[1]])) die("Variable $".$matches[1]." does not exist");
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] > $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] > $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "|isset|":
				if ($matches[3] == "true") {
					if (isset($this->vars[$matches[1]])) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				if ($matches[3] == "false") {
					if (!isset($this->vars[$matches[1]])) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			default:
				die($matches[2]." is not a valid case");
		}
		return $code;
	}
}
?>