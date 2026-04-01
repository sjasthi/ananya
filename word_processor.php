<?php
//require("telugu_parser.php");

/*
 * This is PHP version of Telugu WordProcessor.java
 * The function names and arguments are identical.
 * Contact Siva.Jasthi@metrostate.edu for Java version
 *
 *  NOTE: WordProcessor would be the same for English as well as other
 *  Indic Languages. It would not change as we are manipulating 
 *  these three variables
 * 	 "input string" 
 *   "logical characters"
 *   "codepoints"
 * 
 *   The logic of splitting an "input string" to a set of logical characters
 *   would be different for each Language
 *   For example, "telugu_parser.php" focuses on Telugu parser
 */
class wordProcessor
{
	// since all three of these properties are reliant on the others being in exact sync,
	// we only allow them to be accessed via mutators and accessors

	// the basic word, dispayable as UTF-8
	protected $word = "";

	// the word, broken into an array of characters
	protected $logical_chars = array();

	// the array of characters, stored as unicode values
	// All standard functions treat code points as logical characters
	// That may work for English, but not for other multi-byte languages
	protected $code_points = array();

	protected $language;

	// constructor
	function __construct($word, $language)
	{
		$this->language = $this->normalizeLanguage($language);
		if (is_string($word)) return $this->setWord($word);
	}

	private function normalizeLanguage($language)
	{
		if (!is_string($language)) {
			return 'telugu';
		}

		$lang = strtolower(trim($language));
		if ($lang === '') {
			return 'telugu';
		}

		$aliases = array(
			'english' => 'english',
			'telugu' => 'telugu',
			'hindi' => 'hindi',
			'gujarati' => 'gujarati',
			'malayalam' => 'malayalam'
		);

		return $aliases[$lang] ?? 'telugu';
	}

	private function includeParserForLanguage($language = null)
	{
		$lang = $this->normalizeLanguage($language ?? $this->language);

		switch ($lang) {
			case 'hindi':
				include_once 'hindi_parser.php';
				break;
			case 'gujarati':
				include_once 'gujarati_parser.php';
				break;
			case 'malayalam':
				include_once 'malayalam_parser.php';
				break;
			default:
				include_once 'telugu_parser.php';
				break;
		}
	}

	private function parserParseToCodePoints($word, $language = null)
	{
		$lang = $this->normalizeLanguage($language ?? $this->language);
		$this->includeParserForLanguage($lang);

		switch ($lang) {
			case 'hindi':
				if (function_exists('hindi_parseToCodePoints')) return hindi_parseToCodePoints($word);
				break;
			case 'gujarati':
				if (function_exists('gujarati_parseToCodePoints')) return gujarati_parseToCodePoints($word);
				break;
			case 'malayalam':
				if (function_exists('malayalam_parseToCodePoints')) return malayalam_parseToCodePoints($word);
				break;
			default:
				if (function_exists('parseToCodePoints')) return parseToCodePoints($word);
				break;
		}

		if (!is_string($word)) {
			return array();
		}

		$chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
		$fallback = array();
		foreach ($chars as $char) {
			$fallback[] = array(ord($char));
		}
		return $fallback;
	}

	private function parserParseToLogicalCharacters($word, $language = null)
	{
		$lang = $this->normalizeLanguage($language ?? $this->language);
		$this->includeParserForLanguage($lang);

		switch ($lang) {
			case 'hindi':
				if (function_exists('hindi_parseToLogicalCharacters')) return hindi_parseToLogicalCharacters($word);
				break;
			case 'gujarati':
				if (function_exists('gujarati_parseToLogicalCharacters')) return gujarati_parseToLogicalCharacters($word);
				break;
			case 'malayalam':
				if (function_exists('malayalam_parseToLogicalCharacters')) return malayalam_parseToLogicalCharacters($word);
				break;
			default:
				if (function_exists('parseToLogicalCharacters')) return parseToLogicalCharacters($word);
				break;
		}

		if (is_array($word)) {
			return $word;
		}

		if (!is_string($word)) {
			return array();
		}

		return preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
	}

	private function parserStripSpaces($logicalChars, $language = null)
	{
		$lang = $this->normalizeLanguage($language ?? $this->language);
		$this->includeParserForLanguage($lang);

		switch ($lang) {
			case 'hindi':
				if (function_exists('hindi_stripSpaces')) return hindi_stripSpaces($logicalChars);
				break;
			case 'gujarati':
				if (function_exists('gujarati_stripSpaces')) return gujarati_stripSpaces($logicalChars);
				break;
			case 'malayalam':
				if (function_exists('malayalam_stripSpaces')) return malayalam_stripSpaces($logicalChars);
				break;
			default:
				if (function_exists('stripSpacesTelugu')) return stripSpacesTelugu($logicalChars);
				break;
		}

		return array_values(array_filter($logicalChars, function ($char) {
			return trim((string)$char) !== '';
		}));
	}

	private function parserIsBlankHex($hexVal, $language = null)
	{
		$lang = $this->normalizeLanguage($language ?? $this->language);
		$this->includeParserForLanguage($lang);

		switch ($lang) {
			case 'gujarati':
				return function_exists('gujarati_is_blank') ? gujarati_is_blank($hexVal) : false;
			case 'malayalam':
				return function_exists('malayalam_is_blank') ? malayalam_is_blank($hexVal) : false;
			case 'telugu':
				return function_exists('is_blank_Telugu') ? is_blank_Telugu($hexVal) : false;
			default:
				return false;
		}
	}

	private function getSeedFilePath($language)
	{
		$lang = $this->normalizeLanguage($language);
		if (!in_array($lang, array('telugu', 'hindi', 'gujarati', 'malayalam'))) {
			return null;
		}

		return __DIR__ . DIRECTORY_SEPARATOR . $lang . '_seed.txt';
	}

	private function getCorpusFilePath($language)
	{
		$lang = $this->normalizeLanguage($language);
		$map = array(
			'english' => 'english.txt',
			'telugu' => 'telugu.txt',
			'hindi' => 'hindi.txt',
			'gujarati' => 'gujarati.txt',
			'malayalam' => 'malayalam.txt',
		);

		if (!isset($map[$lang])) {
			return null;
		}

		return __DIR__ . DIRECTORY_SEPARATOR . $map[$lang];
	}

	private function getMissingSeedFilesMessage($language)
	{
		$lang = $this->normalizeLanguage($language);
		if (!in_array($lang, array('telugu', 'hindi', 'gujarati', 'malayalam'))) {
			return 'Seed files are not required for this language.';
		}

		return 'Missing dataset files for ' . $lang . '. Please provide ' . $lang . '_seed.txt and ' . $lang . '_seed_words.txt.';
	}

	private function getRandomFromPool($pool)
	{
		if (!is_array($pool) || empty($pool)) {
			return null;
		}

		return $pool[array_rand($pool)];
	}

	private function loadIndicSeedPools($language)
	{
		$seedFile = $this->getSeedFilePath($language);
		if ($seedFile === null || !file_exists($seedFile)) {
			return array(
				'ok' => false,
				'message' => $this->getMissingSeedFilesMessage($language),
			);
		}

		$lines = @file($seedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) {
			return array(
				'ok' => false,
				'message' => 'Unable to read ' . basename($seedFile),
			);
		}

		$pools = array(
			'constants' => array(),
			'vowels' => array(),
			'vowelMixers' => array(),
			'singleConstantBlends' => array(),
			'doubleConstantBlends' => array(),
			'tripleConstantBlends' => array(),
			'constantBlendsAndVowels' => array(),
		);

		foreach ($lines as $line) {
			$word = preg_split('/\s+/', trim($line));
			if (count($word) < 2) {
				continue;
			}

			$tag = strtoupper($word[0]);
			$value = $word[1];

			switch ($tag) {
				case 'CONSONANTS':
					$pools['constants'][] = $value;
					break;
				case 'VOWELS':
					$pools['vowels'][] = $value;
					break;
				case 'VOWELMIXERS':
					$pools['vowelMixers'][] = $value;
					break;
				case 'SINGLECONSONANTBLENDS':
					$pools['singleConstantBlends'][] = $value;
					break;
				case 'DOUBLECONSONANTBLENDS':
					$pools['doubleConstantBlends'][] = $value;
					break;
				case 'TRIPLECONSONANTBLENDS':
					$pools['tripleConstantBlends'][] = $value;
					break;
				case 'CONSONANTBLENDSANDVOWELS':
					$pools['constantBlendsAndVowels'][] = $value;
					break;
			}
		}

		return array(
			'ok' => true,
			'pools' => $pools,
		);
	}

	// setter for the word
	// this also parses the word to logical characters
	function setWord($a_word)
	{
		if (!is_string($a_word)) return;
		$this->word = $a_word;
		return $this->parseToLogicalChars($this->word);
	}

	/*// all mutators need to call this, since it keeps all three properties in sync
	function parseToLogicalChars() {
		$this->code_points = parseToCodePoints($this->getWord());
		$this->logical_chars = parseToLogicalCharacters($this->getCodePoints());
		return $this->getLogicalChars();
	}*/

	// all mutators need to call this, since it keeps all three properties in sync
	function parseToLogicalChars($word)
	{
		$this->code_points = $this->parserParseToCodePoints($word, $this->language);
		$this->logical_chars = $this->parserParseToLogicalCharacters($this->getCodePoints(), $this->language);
		return $this->getLogicalChars();
	}

	// a wrapper for the underlying telugu_parser version with the same name
	function parseToLogicalCharacters($word)
	{
		return $this->parserParseToLogicalCharacters($word, $this->language);
	}

	// accepts an array of logical characters and sets the word to the value of chars
	function setLogicalChars($some_logical_chars)
	{
		if (!is_array($some_logical_chars)) return;
		return $this->setWord(implode("", $some_logical_chars));
	}

	function getWord()
	{
		return $this->word;
	}

	function getLogicalChars()
	{
		return $this->logical_chars;
	}

	function getLogicalChars2()
	{
		$invalidCharacters = array("!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", "{", "}", "[", "]", ":", ";", " \" ", " ' ", "<", ",", "<", ",", ">", ".", "?", "/", "|", '\\', " ");
		$parsedLogicalChars = $this->parseToLogicalChars($this->word);
		$newParsedLogicalChars = array();

		foreach($parsedLogicalChars as $char) {
			if(!in_array($char, $invalidCharacters)) {
				array_push($newParsedLogicalChars, $char);
			}	
		}


		// var_dump($parsedLogicalChars);
		// var_dump($newParsedLogicalChars);
		// $this->setLogicalChars($newParsedLogicalChars);   
		
		

		return $newParsedLogicalChars;
	}

	function parseToLogicalChars2() {
		return $this->getLogicalChars2();
	}

	function getLength2() {
		return count($this->getLogicalChars2());
	}

	function getCodePoints()
	{
		return $this->code_points;
	}

	// Standard getLength( ) functions operate on code points
	// We are overtaking and providing a meaningful length
	// based on the number of logical characters
	function getLength()
	{
		return count($this->getLogicalChars());
	}

	// returns the total number of code points for the word
	function getCodePointLength()
	{
		$len = 0;
		foreach ($this->getCodePoints() as $chars) {
			$len += count($chars);
		}
		return $len;
	}

	function startsWith($start_chars)
	{
		if (strncmp($start_chars, $this->getWord(), strlen($start_chars)) == 0) return true;
		else return false;
	}

	function endsWith($end_chars)
	{
		if (strcmp($end_chars, substr($this->getWord(), -strlen($end_chars))) == 0) return true;
		else return false;
	}

	function containsString($to_find)
	{
		if (strpos($this->getWord(), $to_find) === false) return false;
		else return true;
	}

	function containsChar($to_find)
	{
		foreach ($this->getLogicalChars() as $char)
			if ($to_find === $char) return true;
		return false;
	}

	function containsLogicalChars($to_find)
	{
		foreach ($to_find as $char) {
			if ($this->containsChar($char)) continue;
			return false;
		}
		return true;
	}

	function containsAllLogicalChars($to_find)
	{
		return $this->containsLogicalChars($to_find);
	}

	function containsLogicalCharSequence($to_find)
	{
		if (strpos($this->getWord(), $to_find) === false) return false;
		else return true;
	}

	function canMakeWord($a_word)
	{
		$parsed_word = $this->parseToLogicalCharacters($a_word);
		return $this->containsLogicalChars($parsed_word);
	}

	function canMakeAllWords($some_words)
	{
		foreach ($some_words as $word) {
			if ($this->canMakeWord($word)) continue;
			return false;
		}
		return true;
	}

	function containsSpace()
	{
		foreach ($this->getLogicalChars() as $char)
			if ($char === " ") return true;
		return false;
	}

	function isPalindrome()
	{
		$l_count = count($this->getLogicalChars());
		if ($l_count < 2) return true; // a one letter word is always a palindrome (a zero length word? sure, it's one too)
		for ($i = 0; $i < $l_count / 2; $i++) {
			if ($this->logicalCharAt($i) !== $this->logicalCharAt($l_count - $i - 1)) return false;
		}
		return true;
	}

	// accepts both a string word or an array of logical characters
	function isAnagram($word)
	{
		if (is_array($word))
			return ((count($this->getLogicalChars()) == count($word)) && $this->containsLogicalChars($word));
		else return $this->isAnagram($this->parseToLogicalCharacters($word));
	}

	function trim()
	{
		$this->setWord(trim($this->getWord()));
		return $this->getWord();
	}

	function toCaps()
	{
		$this->setWord(strtoupper($this->getWord()));
		return $this->getWord();
	}

	function stripSpaces()
	{
		$this->setLogicalChars($this->parserStripSpaces($this->getLogicalChars(), $this->language));

		return $this->getWord();
	}

	function stripAllSymbols()
	{
		$build = array();
		$build_i = 0;
		for ($i = 0; $i < count($this->getLogicalChars()); $i++) {
			$chr = $this->getCodePoints()[$i][0];
			// this is not perfect, it only checks ASCII special symbols
			// but could easily be expanded to cover other ranges
			if ($this->is_symbol($chr)) continue;
			$build[$build_i++] = $this->logicalCharAt($i);
		}
		$this->setLogicalChars($build);
		return $this->getWord();
	}

	function is_symbol($chr)
	{
		return (($chr > 32 && $chr < 48) || ($chr > 57 && $chr < 65) ||
			($chr > 90 && $chr < 97) || ($chr > 122 && $chr < 127));
	}

	function reverse()
	{
		return implode(array_reverse($this->getLogicalChars()));
	}

	function replace($sub_string, $substitute_string)
	{
		return str_replace($sub_string, $substitute_string, $this->getWord());
	}

	function addCharacterAt($index, $log_char)
	{
		$logical_chars = $this->getLogicalChars(); // Cache the result
		$char_count = count($logical_chars);
		
		// Validate index - reject negative indices
		if ($index < 0) {
			return $this->getWord(); // Return unchanged for negative index
		}
		
		// Handle index beyond array bounds
		if ($index >= $char_count) {
			return $this->addCharacterAtEnd($log_char);
		}
		
		// Use array_splice for clean insertion
		array_splice($logical_chars, $index, 0, [$log_char]);
		
		return implode('', $logical_chars);
	}

	function addCharacterAtEnd($a_logical_char)
	{
		return $this->getWord() . $a_logical_char;
	}

	function getIntersectingRank($word_2)
	{
		return count(array_intersect($this->getLogicalChars(), $this->parserParseToLogicalCharacters($word_2, $this->language)));
	}

	function isIntersecting($word_2)
	{
		return ($this->getIntersectingRank($word_2) > 0);
	}

	function getUniqueIntersectingLogicalChars($list)
	{
		$intersecting = array();
		foreach ($this->getLogicalChars() as $char) {
			if ($char == " ") continue; // we don't want to match spaces
			$found = array_search(strtolower($char), $list);
			if ($found !== FALSE) {
				array_push($intersecting, $char);
				$list[$found] = NULL;
			} else {
				$found = array_search(strtoupper($char), $list);
				if ($found !== FALSE) {
					array_push($intersecting, $char);
					$list[$found] = NULL;
				}
			}
		}
		return $intersecting;
	}

	function getUniqueIntersectingRank($list)
	{
		return count($this->getUniqueIntersectingLogicalChars($list));
	}

	function logicalCharAt($index)
	{
		return $this->logical_chars[$index];
	}

	function codePointAt($index)
	{
		foreach ($this->getCodePoints() as $code_points)
			foreach ($code_points as $cp)
				if ($index-- == 0) return $cp;
	}

	function indexOf($char)
	{
		$index = 0;
		foreach ($this->getLogicalChars() as $logical_char) {
			if ($logical_char == $char) return $index;
			$index++;
		}
		return -1;
	}

	function compareTo($word_2)
	{
		return strcmp($this->getWord(), $word_2);
	}

	function compareToIgnoreCase($word_2)
	{
		return strcasecmp($this->getWord(), $word_2);
	}

	function randomize($some_strings)
	{
		shuffle($some_strings);
		return $some_strings;
	}

	function splitWord($cols)
	{
		$split_word = array();
		for ($row = 0; $row < count($this->getLogicalChars()); $row += 2) {
			for ($col = 0; $col < $cols; $col++) {
				@$split_word[$row][$col] = $this->getLogicalChars()[$row + $col];
			}
		}
		return $split_word;
	}

	function __toString()
	{
		return $this->getWord() . ", " .
			var_export($this->getLogicalChars()) . ", " .
			var_export($this->getCodePoints());
	}

	function equals($word_2)
	{
		return $this->getWord() === $word_2;
	}

	function reverseEquals($word_2)
	{
		return $this->reverse() == $word_2;
	}

	function getWordStrength()
	{
		$len = $this->getLength();

		// non-Telugu word, return the length as strength
		if (!isTelugu($this->getCodePoints()[0][0])) return $len;

		$strength = 1;
		foreach ($this->getCodePoints() as $char)
			$strength = ($strength > count($char) ? $strength : count($char));

		return $strength;
	}

	function getWordWeight()
	{
		$len = $this->getLength();

		// non-Telugu
		if (!isTelugu($this->getCodePoints()[0][0])) return $len;

		$weight = 0;
		foreach ($this->getCodePoints() as $char)
			$weight += count($char);

		return $weight;
	}

	function isCharConsonant($char)
	{
		$retVal = true;
		$englishVowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U");

		$TeluguVstart = hexdec("0x0C05");
		$TeluguVend = hexdec("0x0C14");

		$HindiVstart = hexdec("0x0904");
		$HindiVend = hexdec("0x0914");

		$GujaratiVstart = hexdec("0x0A85");
		$GujaratiVend = hexdec("0x0A94");

		$MalayalamVstart = hexdec("0x0D05");
		$MalayalamVend = hexdec("0x0D14");


		$lang = strtolower($this->language);
		switch ($lang) {

			case "english":
				if (in_array($char, $englishVowels)) {
					$retVal = false;
				}
				break;

			case "telugu":
				$TeluguChar = mb_ord($char, 'UTF-8');
				if ($TeluguChar >= $TeluguVstart && $TeluguChar <= $TeluguVend) {
					$retVal = false;
				}
				break;

			case "hindi":
				$HindiChar = mb_ord($char, 'UTF-8');
				if ($HindiChar >= $HindiVstart && $HindiChar <= $HindiVend) {
					$retVal = false;
				}
				break;

			case "gujarati":
				$GujaratiChar = mb_ord($char, 'UTF-8');
				if ($GujaratiChar >= $GujaratiVstart && $GujaratiChar <= $GujaratiVend) {
					$retVal = false;
				}
				break;
			case "malayalam":
				$MalayalamChar = mb_ord($char, 'UTF-8');
				if ($MalayalamChar >= $MalayalamVstart && $MalayalamChar <= $MalayalamVend) {
					$retVal = false;
				}
				break;
		}
		return $retVal;
	}

	function isCharVowel($char)
	{
		$retVal = false;
		$englishVowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U");

		$TeluguVstart = hexdec("0x0C05");
		$TeluguVend = hexdec("0x0C14");

		$HindiVstart = hexdec("0x0904");
		$HindiVend = hexdec("0x0914");

		$GujaratiVstart = hexdec("0x0A85");
		$GujaratiVend = hexdec("0x0A94");

		$MalayalamVstart = hexdec("0x0D05");
		$MalayalamVend = hexdec("0x0D14");

		$lang = strtolower($this->language);
		switch ($lang) {
			case "english":
				if (in_array($char, $englishVowels)) {
					$retVal = true;
				}
				break;

			case "telugu":
				$TeluguChar = mb_ord($char, 'UTF-8');
				if ($TeluguChar >= $TeluguVstart && $TeluguChar <= $TeluguVend) {
					$retVal = true;
				}
				break;
			case "hindi":
				$HindiChar = mb_ord($char, 'UTF-8');
				if ($HindiChar >= $HindiVstart && $HindiChar <= $HindiVend) {
					$retVal = true;
				}
				break;
			case "gujarati":
				$GujaratiChar = mb_ord($char, 'UTF-8');
				if ($GujaratiChar >= $GujaratiVstart && $GujaratiChar <= $GujaratiVend) {
					$retVal = true;
				}
				break;
			case "malayalam":
				$MalayalamChar = mb_ord($char, 'UTF-8');
				if ($MalayalamChar >= $MalayalamVstart && $MalayalamChar <= $MalayalamVend) {
					$retVal = true;
				}
				break;
		}
		return $retVal;
	}

	function parseList($data)
	{
		//gets word list, creates array of words from it
		//or return false if impossible
		$data['generate_board'] = TRUE;
		//Check to see if word will fit
		foreach ($data['char_bank'] as $wordIndexArray) {
			echo "\n";
			$processor = new wordProcessor($wordIndexArray, $this->language);
			$wordIndex = $processor->parseToLogicalChars($wordIndexArray, $this->language);
		}

		return $data;
	} // end parseList

	/* getfillerCharacters()
		Takes as input the amount of chars to generate and the
		type of chars to be generated. Currently, the possible
		types are vowels and consonants. Based on the type
		a number of logicalCharCount characters will be generated.
		If a type is not of the known variants, a pool from all
		available types is automatically selected.
		@return results - Array with logicalCharCount filler characters
	*/
	function getFillerCharacters($logicalCharCount, $type)
	{
		$language = $this->normalizeLanguage($this->language);
		$logicalCharCount = intval($logicalCharCount);
		$type = strtolower($type);
		$any = [];
		$vowels = [];
		$constants = [];
		$vowelMixers = [];
		$singleConstantBlends = [];
		$doubleConstantBlends = [];
		$tripleConstantBlends = [];
		$constantBlendsAndVowels = [];
		$result = [];

		if ($logicalCharCount <= 0) {
			return ["Input not acceptable integer. Enter a number greater than 0."];
		}

		switch (strtolower($language)) {
			case "english":
				array_push(
					$constants,
					"0x0042",
					"0x0043",
					"0x0044",
					"0x0046",
					"0x0047",
					"0x0048",
					"0x004A",
					"0x004B",
					"0x004C",
					"0x004D",
					"0x004E",
					"0x0050",
					"0x0051",
					"0x0052",
					"0x0053",
					"0x0054",
					"0x0056",
					"0x0057",
					"0x0058",
					"0x0059",
					"0x005A"
				);
				array_push($vowels, "0x0041", "0x0045", "0x0049", "0x004f", "0x0055");
				break;
			case "telugu":
			case "hindi":
			case "gujarati":
			case "malayalam":
				$seedLoadResult = $this->loadIndicSeedPools($language);
				if (!$seedLoadResult['ok']) {
					return [$seedLoadResult['message']];
				}

				$constants = $seedLoadResult['pools']['constants'];
				$vowels = $seedLoadResult['pools']['vowels'];
				$vowelMixers = $seedLoadResult['pools']['vowelMixers'];
				$singleConstantBlends = $seedLoadResult['pools']['singleConstantBlends'];
				$doubleConstantBlends = $seedLoadResult['pools']['doubleConstantBlends'];
				$tripleConstantBlends = $seedLoadResult['pools']['tripleConstantBlends'];
				$constantBlendsAndVowels = $seedLoadResult['pools']['constantBlendsAndVowels'];
				break;
			default:
				return ["Unsupported language"];
		}

		// get N random chars back from each fillerCharType
		$n = $logicalCharCount;
		shuffle($constants);
		shuffle($vowels);
		shuffle($vowelMixers);
		shuffle($singleConstantBlends);
		shuffle($doubleConstantBlends);
		shuffle($tripleConstantBlends);
		shuffle($constantBlendsAndVowels);
		$any = array_merge(
			array_slice($constants, 0, $n),
			array_slice($vowels, 0, $n),
			array_slice($vowelMixers, 0, $n),
			array_slice($singleConstantBlends, 0, $n),
			array_slice($doubleConstantBlends, 0, $n),
			array_slice($tripleConstantBlends, 0, $n),
			array_slice($constantBlendsAndVowels, 0, $n)
		);

		if (empty($constants) && empty($vowels) && empty($any)) {
			return ["No characters found in configured seed data for " . $language . "."];
		}

		switch (strtolower($language)) {
			case "english":
				for ($i = 0; $i < $logicalCharCount; $i++) {
					$english_char = "";

					if ($type == "consonants" || $type == "consonant") {
						$hexcode = $constants[array_rand($constants)];
					} else if ($type == "vowels" || $type == "vowel") {
						$hexcode = $vowels[array_rand($vowels)];
					} else {
						$hexcode = $any[array_rand($any)];
					}

					// Weird unicode and json encoding prompted this
					$hexcode = dechex(hexdec($hexcode));
					$english_char .= "\u00{$hexcode}";
					$english_char = json_decode('"' . $english_char . '"');

					array_push($result, $english_char);
				}
				break;
				case "telugu":
				case "hindi":
				case "gujarati":
				case "malayalam":
				for ($i = 0; $i < $logicalCharCount; $i++) {
						$indic_char = "";

					if ($type == "consonants" || $type == "consonant") {
							$indic_char = $this->getRandomFromPool($constants);
					} else if ($type == "vowels" || $type == "vowel") {
							$indic_char = $this->getRandomFromPool($vowels);
					} else if ($type == "scb") {
							$indic_char = $this->getRandomFromPool($singleConstantBlends);
					} else if ($type == "dcb") {
							$indic_char = $this->getRandomFromPool($doubleConstantBlends);
					} else if ($type == "tcb") {
							$indic_char = $this->getRandomFromPool($tripleConstantBlends);
					} else if ($type == "cdv") {
							$indic_char = $this->getRandomFromPool($constantBlendsAndVowels);
					} else {
							$indic_char = $this->getRandomFromPool($any);
					}

						if ($indic_char === null) {
							return ["Seed data does not contain enough values for type '" . $type . "' in language '" . $language . "'."];
						}

						array_push($result, $indic_char);
				}
				break;
			default:
				return ["Unsupported language"];
				break;
		}

		return $result;
	}

	// add extra letters to wordFind board //
	function addFoils($data)
	{

		// filler character types
		$fillerChars = $data['filler_char_types'];
		$any = [];
		$vowels = [];
		$constants = [];
		$vowelMixers = [];
		$singleConstantBlends = [];
		$doubleConstantBlends = [];
		$tripleConstantBlends = [];
		$constantBlendsAndVowels = [];

		// remove double instances of letters
		$inputLetters =  call_user_func_array('array_merge', $data['char_bank']);
		$rawInputLetters = [];
		foreach ($inputLetters as $letter) {
			if (!in_array($letter, $rawInputLetters)) {
				array_push($rawInputLetters, $letter);
			}
		}
		$inputLetters = $rawInputLetters;

		//add random dummy characters to board
		$language = $data['language'];
		global $board;

		// live version, uncomment for live site
		//$myfile = fopen("/home2/icsbinco/public_html/indic-wp/telugu_seed.txt", "r") or die("Unable to open file!");
		// local version, comment out for live site
		$seedFile = __DIR__ . DIRECTORY_SEPARATOR . "telugu_seed.txt";
		$myfile = fopen($seedFile, "r");
		if ($myfile === false) {
			return;
		}

		$lines = [];
		$word = [];
		while (!feof($myfile)) {
			$line = fgets($myfile);
			$lines[] = $line;
		}

		foreach ($lines as $w) {
			$word = explode(" ", trim($w));
			if (in_array("CONSONANTS", $word)) {
				array_push($constants, $word[1]);
			} elseif (in_array("VOWELS", $word)) {
				array_push($vowels, $word[1]);
			} elseif (in_array("VOWELMIXERS", $word)) {
				array_push($vowelMixers, $word[1]);
			} elseif (in_array("SINGLECONSONANTBLENDS", $word)) {
				array_push($singleConstantBlends, $word[1]);
			} elseif (in_array("DOUBLECONSONANTBLENDS", $word)) {
				array_push($doubleConstantBlends, $word[1]);
			} elseif (in_array("TRIPLECONSONANTBLENDS", $word)) {
				array_push($tripleConstantBlends, $word[1]);
			} elseif (in_array("CONSONANTBLENDSANDVOWELS", $word)) {
				array_push($constantBlendsAndVowels, $word[1]);
			}
		}

		// get N random chars back from each fillerCharType
		$n = 15;
		shuffle($constants);
		shuffle($vowels);
		shuffle($vowelMixers);
		shuffle($singleConstantBlends);
		shuffle($doubleConstantBlends);
		shuffle($tripleConstantBlends);
		shuffle($constantBlendsAndVowels);
		$any = array_merge(
			array_slice($constants, 0, $n),
			array_slice($vowels, 0, $n),
			array_slice($vowelMixers, 0, $n),
			array_slice($singleConstantBlends, 0, $n),
			array_slice($doubleConstantBlends, 0, $n),
			array_slice($tripleConstantBlends, 0, $n),
			array_slice($constantBlendsAndVowels, 0, $n)
		);

		fclose($myfile);

		switch ($language) {
			case "English":
				for ($row = 0; $row < $data["height"]; $row++) {
					for ($col = 0; $col < $data["width"]; $col++) {
						if ($board[$row][$col] == ".") {
							$validChar = false;
							while (!$validChar) {
								$english_char = "";
								$startHex = "0x0041";
								$endHex = "0x005A";
								$num = rand(hexdec($startHex), hexdec($endHex));
								$hexcode = dechex($num);
								if ($hexcode == 0x004F) {
									continue;
								}

								if ($fillerChars == "Consonants") {
									if ($this->isCharVowel($hexcode, $language)) {
										continue;
									}
									$english_char .= sprintf("\\u%'04s", dechex($num));
									if (json_decode('"' . $english_char . '"') == "O") {
										continue;
									}
									$board[$row][$col] = json_decode('"' . $english_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "Vowels") {
									if ($this->isCharConsonant($hexcode, $language)) {
										continue;
									}
									$english_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $english_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "LFIW") {
									$k = array_rand($inputLetters);
									$english_char .= $inputLetters[$k];
									$board[$row][$col] = $english_char;
									$validChar = true;
								} else {
									$english_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $english_char . '"');
									$validChar = true;
								}
							}
						} // end if
					} // end col for loop
				} // end row for loop
				break;

			case "Telugu":
				for ($row = 0; $row < $data["height"]; $row++) {
					for ($col = 0; $col < $data["width"]; $col++) {
						if ($board[$row][$col] == ".") {
							//Make sure the character is valid
							$validChar = false;
							while (!$validChar) {
								$telugu_char = "";
								$startHex = "0x0c05";
								$endHex = "0x0c39";
								$num = rand(hexdec($startHex), hexdec($endHex));
								$hexcode = dechex($num);

								if (is_blank_Telugu($hexcode)) {
									continue;
								} elseif ($fillerChars == "Consonants") {
									$k = array_rand($constants);
									$telugu_char .= $constants[$k];
									$board[$row][$col] = $telugu_char;
									$validChar = true;
								} elseif ($fillerChars == "Vowels") {
									$k = array_rand($vowels);
									$telugu_char .= $vowels[$k];
									$board[$row][$col] = $telugu_char;
									$validChar = true;
								} elseif ($fillerChars == "SCB") {
									$k = array_rand($singleConstantBlends);
									$telugu_char .= $singleConstantBlends[$k];
									$board[$row][$col] = "  " . $telugu_char . "  ";
									$validChar = true;
								} elseif ($fillerChars == "DCB") {
									$k = array_rand($doubleConstantBlends);
									$telugu_char .= $doubleConstantBlends[$k];
									$board[$row][$col] = "  " . $telugu_char . "  ";
									$validChar = true;
								} elseif ($fillerChars == "TCB") {
									$k = array_rand($tripleConstantBlends);
									$telugu_char .= $tripleConstantBlends[$k];
									$board[$row][$col] = "  " . $telugu_char . "  ";
									$validChar = true;
								} elseif ($fillerChars == "CDV") {
									$k = array_rand($constantBlendsAndVowels);
									$telugu_char .= $constantBlendsAndVowels[$k];
									$board[$row][$col] = "  " . $telugu_char . "  ";
									$validChar = true;
								} elseif ($fillerChars == "LFIW") {
									$k = array_rand($inputLetters);
									$telugu_char .= $inputLetters[$k];
									$board[$row][$col] = $telugu_char;
									$validChar = true;
								} else {
									$k = array_rand($any);
									$telugu_char .= $any[$k];
									$board[$row][$col] = "  " . $telugu_char . "  ";
									$validChar = true;
								}
							}
						} // end if
					} // end col for loop
				} // end row for loop
				break;

			case "Hindi":
				for ($row = 0; $row < $data["height"]; $row++) {
					for ($col = 0; $col < $data["width"]; $col++) {
						if ($board[$row][$col] == ".") {
							//Make sure the character is valid
							$validChar = false;
							while (!$validChar) {
								$hindi_char = "";
								$startHex = "0x0904";
								$endHex = "0x0939";
								$num = rand(hexdec($startHex), hexdec($endHex));
								$hexcode = dechex($num);

								if ($fillerChars == "Consonants") {
									if ($this->isCharVowel($hexcode, $language)) {
										continue;
									}
									$hindi_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $hindi_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "Vowels") {
									if ($this->isCharConsonant($hexcode, $language)) {
										continue;
									}
									$hindi_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $hindi_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "LFIW") {
									$k = array_rand($inputLetters);
									$hindi_char .= $inputLetters[$k];
									$board[$row][$col] = $hindi_char;
									$validChar = true;
								} else {
									$hindi_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $hindi_char . '"');
									$validChar = true;
								}
							}
						} // end if
					} // end col for loop
				} // end row for loop
				break;

			case "Gujarati":
				for ($row = 0; $row < $data["height"]; $row++) {
					for ($col = 0; $col < $data["width"]; $col++) {
						if ($board[$row][$col] == ".") {
							$validChar = false;
							while (!$validChar) {
								$gujarati_char = "";
								$startHex = "0x0a81";
								$endHex = "0x0acc";
								$num = rand(hexdec($startHex), hexdec($endHex));
								$hexcode = dechex($num);
								$number = (20 * $row) + $col;
								if ($this->parserIsBlankHex($hexcode, 'gujarati')) {
									continue;
								} elseif ($fillerChars == "Consonants") {
									if ($this->isCharVowel($hexcode, $language)) {
										continue;
									}
									$gujarati_char  .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $gujarati_char  . '"');
									$validChar = true;
								} elseif ($fillerChars == "Vowels") {
									if ($this->isCharConsonant($hexcode, $language)) {
										continue;
									}
									$gujarati_char  .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $gujarati_char  . '"');
									$validChar = true;
								} elseif ($fillerChars == "LFIW") {
									$k = array_rand($inputLetters);
									$gujarati_char .= $inputLetters[$k];
									$board[$row][$col] = $gujarati_char;
									$validChar = true;
								} else {
									$gujarati_char  .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $gujarati_char  . '"');
									$validChar = true;
								}
							}
						} // end if
					} // end col for loop
				} // end row for loop
				break;

			case "Malayalam":
				for ($row = 0; $row < $data["height"]; $row++) {
					for ($col = 0; $col < $data["width"]; $col++) {
						if ($board[$row][$col] == ".") {
							$validChar = false;
							while (!$validChar) {
								$malay_char = "";
								$startHex = "0x0d01";
								$endHex = "0x0d3a";
								$num = rand(hexdec($startHex), hexdec($endHex));
								$hexcode = dechex($num);
								if ($this->parserIsBlankHex($hexcode, 'malayalam')) {
									continue;
								} elseif ($fillerChars == "Consonants") {
									if ($this->isCharVowel($hexcode, $language)) {
										continue;
									}
									$malay_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $malay_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "Vowels") {
									if ($this->isCharConsonant($hexcode, $language)) {
										continue;
									}
									$malay_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $malay_char . '"');
									$validChar = true;
								} elseif ($fillerChars == "LFIW") {
									$k = array_rand($inputLetters);
									$malay_char .= $inputLetters[$k];
									$board[$row][$col] = $malay_char;
									$validChar = true;
								} else {
									$malay_char .= sprintf("\\u%'04s", dechex($num));
									$board[$row][$col] = json_decode('"' . $malay_char . '"');
									$validChar = true;
								}
							}
						} // end if
					} // end col for loop
				} // end row for loop
				break;
		}
	} // end addFoils

	// Level is always subjective and requires user/admin intervention
	// For now, Level = Strength
	function getWordLevel()
	{
		return $this->getWordStrength($this->language);
	}

	function getLengthNoSpaces($word)
	{
		return count($this->getLogicalChars()) - substr_count($word, ' ');
	}

	function getLengthNoSpacesNoCommas($word)
	{
		return count($this->getLogicalChars()) - substr_count($word, ' ') - substr_count($word, ',');
	}

	private function getCodePointsForWord($word)
	{
		$processor = new wordProcessor($word, $this->language);
		return $processor->getCodePoints();
	}

	private function getLogicalCharsForWord($word)
	{
		$processor = new wordProcessor($word, $this->language);
		return $processor->getLogicalChars2();
	}

	private function getBaseCharactersForWord($word)
	{
		$processor = new wordProcessor($word, $this->language);
		return $processor->getBaseCharacters();
	}

	private function getConsonantFrequencyMap($baseChars)
	{
		$lang = strtolower($this->normalizeLanguage($this->language));
		$freq = array();

		foreach ($baseChars as $char) {
			$value = $lang === 'english' ? strtolower((string)$char) : $char;
			if ($lang === 'english' && !preg_match('/^[a-z]$/', $value)) {
				continue;
			}
			if (!$this->isCharConsonant($value)) {
				continue;
			}
			$freq[$value] = ($freq[$value] ?? 0) + 1;
		}

		ksort($freq);
		return $freq;
	}

	//Uses word instantiated with class and takes the second word as an argument
	//It compares the inconsistencies within two given words to see if they are ladder words.
	function areLadderWords($string2)
	{
		$lang = strtolower($this->normalizeLanguage($this->language));

		if ($lang == "english") {
			$string = strtolower($this->word);
			$string2 = strtolower($string2);
			if (strlen($string) != strlen($string2)) {
				return false;
			}
			$stringArray = str_split($string);
			$stringArray2 = str_split($string2);
			$inconsistencyCount = 0;
			for ($i = 0; $i < sizeof($stringArray); $i++) {
				if ($stringArray[$i] != $stringArray2[$i]) {
					$inconsistencyCount++;
					if ($inconsistencyCount > 1) {
						return false;
					}
				}
			}
			return $inconsistencyCount == 1;
		}

		$wordCodePoints = $this->getCodePoints();
		$wordCodePoints2 = $this->getCodePointsForWord($string2);
		if (count($wordCodePoints) != count($wordCodePoints2)) {
			return false;
		}

		$differenceCount = 0;
		for ($i = 0; $i < count($wordCodePoints); $i++) {
			if ($wordCodePoints[$i] != $wordCodePoints2[$i]) {
				$differenceCount++;
				if ($differenceCount > 1) {
					return false;
				}
			}
		}

		return $differenceCount == 1;
	}

	//Compares the last letter of the first word and the first letter of the last word.
	function areHeadAndTailWords($string2)
	{
		$lang = strtolower($this->normalizeLanguage($this->language));

		if ($lang == "english") {
			$string = strtolower($this->word);
			$string2 = strtolower($string2);
			if (strlen($string) != strlen($string2)) {
				return false;
			}
			if ($string === '' || $string2 === '') {
				return false;
			}
			$stringArray = str_split($string);
			$stringArray2 = str_split($string2);
			return $stringArray[strlen($string) - 1] == $stringArray2[0];
		}

		$wordCodePoints = $this->getCodePoints();
		$wordCodePoints2 = $this->getCodePointsForWord($string2);
		$length = count($wordCodePoints);
		if ($length != count($wordCodePoints2) || $length === 0) {
			return false;
		}

		return $wordCodePoints[$length - 1] == $wordCodePoints2[0];
	}

	function baseConsonants($firstWord, $secondWord)
	{
		$lang = strtolower($this->normalizeLanguage($this->language));
		if ($lang === 'english') {
			$firstWord = strtolower((string)$firstWord);
			$secondWord = strtolower((string)$secondWord);
		}

		$firstChars = $this->getLogicalCharsForWord($firstWord);
		$secondChars = $this->getLogicalCharsForWord($secondWord);
		if (count($firstChars) != count($secondChars)) {
			return false;
		}

		$firstBase = $this->getBaseCharactersForWord($firstWord);
		$secondBase = $this->getBaseCharactersForWord($secondWord);
		$firstFreq = $this->getConsonantFrequencyMap($firstBase);
		$secondFreq = $this->getConsonantFrequencyMap($secondBase);

		return $firstFreq == $secondFreq;
	}

	//gets base characters of a string stripping spaces and special characters
	function getBaseCharacters () {
	    $logicalCharacters = $this->getLogicalChars2();
	    $baseCharacters = array();

	    foreach ($logicalCharacters as $character) {
	        $this->setWord($character);
	        $result = $this->getCodePoints();
	        $codePoint = $result[0][0];
            $char = mb_chr($codePoint, "utf8");
            array_push($baseCharacters, $char);
        }

	    return $baseCharacters;
    }
	//Splits A string into 15 chunks. 
	function splitInto15Chunks(){
		$logicalChars = $this->getLogicalChars2();
		$charCount = count($logicalChars);
		$result = array();
		
		if ($charCount <= 15) {
			// Case 1: Less than or equal to 15 characters
			// Each character becomes its own chunk, pad with empty strings if needed
			for ($i = 0; $i < 15; $i++) {
				if ($i < $charCount) {
					$result[$i] = $logicalChars[$i];
				} else {
					$result[$i] = "";  // Padding with empty strings
				}
			}
		} else {
			// Case 2: More than 15 characters
			// Distribute characters across 15 chunks
			$baseChunkSize = floor($charCount / 15);
			$extraChars = $charCount % 15;
			
			$index = 0;
			for ($i = 0; $i < 15; $i++) {
				$chunkSize = $baseChunkSize;
				
				// Give extra characters to the first few chunks
				if ($i < $extraChars) {
					$chunkSize++;
				}
				
				$chunk = "";
				for ($j = 0; $j < $chunkSize; $j++) {
					if ($index < $charCount) {
						$chunk .= $logicalChars[$index];
						$index++;
					}
				}
				$result[$i] = $chunk;
			}
		}
		
		return $result;
    }
	//a certain string will be returned if the characters are matched in the same position.
	function get_match_id_string($string1,$string2){
		$lang = strtolower($this->normalizeLanguage($this->language));

		if($lang == "english"){
			$firstString = strtolower((string)$string1);
			$secondString = strtolower((string)$string2);
			$firstArray = $this->getLogicalCharsForWord($firstString);
			$secondArray = $this->getLogicalCharsForWord($secondString);

			if(count($firstArray) != count($secondArray)){
				return "Cannot Solve inputs are different lengths";
			}

			$returnString = '';
			for ($i = 0; $i < count($firstArray); $i++) {
				if(strcmp($firstArray[$i], ($secondArray[$i])) == 0){
					$returnString .= '1';
				} else {
					$returnString .= in_array($secondArray[$i], $firstArray) ? '2' : '5';
				}
			}
			return $returnString;
		}

		$firstArray = $this->getLogicalCharsForWord($string1);
		$firstBase = $this->getBaseCharactersForWord($string1);
		$secondArray = $this->getLogicalCharsForWord($string2);
		$secondBase = $this->getBaseCharactersForWord($string2);

		if(count($firstArray) != count($secondArray) || count($firstBase) != count($secondBase)){
			return "Cannot solve Inputs are different Lengths";
		}

		$returnString = "";
		for ($i = 0; $i < count($firstArray); $i++) {
			switch (true) {
				case (in_array($secondBase[$i], $firstBase)):
					if(strcmp($firstArray[$i], ($secondArray[$i])) == 0){
						if(strcmp($firstBase[$i], ($secondBase[$i])) == 0){
							$returnString .= '1';
						} else {
							$returnString .= '3';
						}
					} else {
						if(strcmp($firstBase[$i], ($secondBase[$i])) == 0){
							$returnString .= '3';
						} else if(in_array($secondArray[$i], $firstArray)){
							$returnString .= '2';
						} else {
							$returnString .= '4';
						}
					}
					break;
				case (!in_array($secondArray[$i], $firstArray)):
					$returnString .= '5';
					break;
				default:
					$returnString .= '2';
					break;
			}
		}

		return $returnString;
	}
	function getLangForString(){
        // Get Unicode code points of the string
        $codePointArrays = $this->getCodePoints();
        
        // Define Unicode ranges for Indian languages
        $languageRanges = [
            'Telugu' => [0x0C00, 0x0C7F],
            'Hindi' => [0x0900, 0x097F],
            'Gujarati' => [0x0A80, 0x0AFF],
            'Malayalam' => [0x0D00, 0x0D7F],
            'Tamil' => [0x0B80, 0x0BFF],
            'Kannada' => [0x0C80, 0x0CFF],
            'Bengali' => [0x0980, 0x09FF],
            'Punjabi' => [0x0A00, 0x0A7F],
            'Oriya' => [0x0B00, 0x0B7F]
        ];
        
        $detectedLanguages = [];
        $hasEnglish = false;
        
        // Flatten the array and check each code point
        foreach ($codePointArrays as $codePointArray) {
            foreach ($codePointArray as $codePoint) {
				if (is_int($codePoint)) {
					$cp = $codePoint;
				} else {
					$codePointStr = trim((string)$codePoint);
					if (preg_match('/^0x/i', $codePointStr)) {
						$cp = hexdec(substr($codePointStr, 2));
					} elseif (ctype_digit($codePointStr)) {
						$cp = (int)$codePointStr;
					} else {
						$cp = hexdec($codePointStr);
					}
				}
                
                // Skip spaces, punctuation, and common symbols (0x0020-0x007F)
                if ($cp >= 0x0020 && $cp <= 0x007F) {
                    // Check if it's an English letter
                    if (($cp >= 0x0041 && $cp <= 0x005A) || ($cp >= 0x0061 && $cp <= 0x007A)) {
                        $hasEnglish = true;
                    }
                    continue;
                }
                
                // Check which language range this code point falls into
                foreach ($languageRanges as $language => $range) {
                    if ($cp >= $range[0] && $cp <= $range[1]) {
                        if (!in_array($language, $detectedLanguages)) {
                            $detectedLanguages[] = $language;
                        }
                        break;
                    }
                }
            }
        }
        
        // Return results based on what was detected
        if (count($detectedLanguages) == 0 && $hasEnglish) {
            return "English";
        } else if (count($detectedLanguages) == 1 && !$hasEnglish) {
            return $detectedLanguages[0];
        } else if (count($detectedLanguages) > 1 || (count($detectedLanguages) >= 1 && $hasEnglish)) {
            return "Mixed languages";
        } else if (count($detectedLanguages) == 0 && !$hasEnglish) {
            return "Unknown";
        } else {
            return "Unknown";
        }
	}
	function getRandomLogicalChars($n){
		$langLower = $this->normalizeLanguage($this->language);
		$int_val = (int)$n;

		if ($int_val <= 0) {
			return "invalid Input please enter a number > 0 and no strings allowed";
		}

		$corpusFile = $this->getCorpusFilePath($langLower);
		if ($corpusFile === null) {
			return "Language has not yet been implemented";
		}

		if (!file_exists($corpusFile)) {
			if (in_array($langLower, array('hindi', 'gujarati', 'malayalam'))) {
				return "Missing corpus file for " . $langLower . ". Please provide " . $langLower . ".txt and also " . $langLower . "_seed.txt and " . $langLower . "_seed_words.txt.";
			}
			return "File not found";
		}

		$fh = fopen($corpusFile, "r");
		if (!$fh) {
			return "Unable to open " . basename($corpusFile);
		}

		$allChars = array();
		while (!feof($fh)) {
			$line = fgets($fh);
			if ($line === false) {
				continue;
			}
			$this->setWord($line);
			$filtered = $this->filterRandomLogicalCharsByLanguage($this->getLogicalChars2(), $langLower);
			$allChars = array_merge($allChars, $filtered);
		}
		fclose($fh);

		shuffle($allChars);
		if ($int_val > count($allChars)) {
			return "Not enough characters in file. Lower N";
		}

		return array_slice($allChars, 0, $int_val);
       
    }

	// Keep only valid, printable logical characters for the selected language.
	function filterRandomLogicalCharsByLanguage($chars, $language)
	{
		if (!is_array($chars)) {
			return array();
		}

		$filtered = array();
		foreach ($chars as $char) {
			if (!is_string($char)) {
				continue;
			}

			$char = trim($char);
			if ($char === "") {
				continue;
			}

			// Drop control characters and malformed fragments.
			if (preg_match('/[\x00-\x1F\x7F]/u', $char)) {
				continue;
			}

			if ($language === "telugu") {
				if (!preg_match('/[\x{0C00}-\x{0C7F}]/u', $char)) {
					continue;
				}
			} elseif ($language === "hindi") {
				if (!preg_match('/[\x{0900}-\x{097F}]/u', $char)) {
					continue;
				}
			} elseif ($language === "gujarati") {
				if (!preg_match('/[\x{0A80}-\x{0AFF}]/u', $char)) {
					continue;
				}
			} elseif ($language === "malayalam") {
				if (!preg_match('/[\x{0D00}-\x{0D7F}]/u', $char)) {
					continue;
				}
			} elseif ($language === "english") {
				if (!preg_match('/^[A-Za-z]$/', $char)) {
					continue;
				}
			}

			$filtered[] = $char;
		}

		return $filtered;
	}

	
}

