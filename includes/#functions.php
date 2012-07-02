<?php
function parseDate($input_date)
{
	$datetemp = explode(".", $input_date);
	$date = $datetemp[2]." ";
	switch ($datetemp[1])
	{
		case "01":
			$date .= "января ";
			break;
		case "02":
			$date .= "февраля ";
			break;
		case "03":
			$date .= "марта ";
			break;
		case "04":
			$date .= "апреля ";
			break;
		case "05":
			$date .= "мая ";
			break;
		case "06":
			$date .= "июня ";
			break;
		case "07":
			$date .= "июля ";
			break;
		case "08":
			$date .= "августа ";
			break;
		case "09":
			$date .= "сентября ";
			break;
		case "10":
			$date .= "октября ";
			break;
		case "11":
			$date .= "ноября ";
			break;
		case "12":
			$date .= "декабря ";
			break;
	}
	$date .= $datetemp[0]." года, в ".$datetemp[3];
	return $date;
}

function parseBBcode($input)
{
	$search = array(
	"/\[more\]/is"
	);
	$replace = array(
	"<a href=\"?id=article&article={id}#more\">Читать далее &rarr;</a>"
	);
	$input = preg_replace($search, $replace, $input);
	return $input;
}

function parseToID($input)
{
	$input = strtolower($input);
	$search = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я", " ");
	$replace = array("a", "b", "v", "g", "d", "e", "yo", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "kh", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya", "a", "b", "v", "g", "d", "e", "yo", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "kh", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya", "_");
	return str_ireplace($search, $replace, $input);
}
?>
