<?php

class Sunny_Filter_File_Rename extends Zend_Filter_File_Rename
{
    /**
     * Associative array of characters and their replace values
     * 
     * @var array
     */
	protected $_replaceMap = array(
        ' ' => '_',
        '!' => '',
        '"' => '',
        '№' => '',
        ';' => '',
        '%' => '',
        ':' => '',
        '?' => '',
        '*' => '',
        '(' => '',
        ')' => '',
        '+' => '',
        '@' => '',
        '#' => '',
        '$' => '',
        '&' => '',
        '|' => '',
        '\\' => '',
        '/' => '',
        '~' => '',
        '\'' => '',
        '"' => '',
        '`' => '',
        '+' => '',
        '-' => '',
        '—' => '',
        '«' => '',
        '»' => '',
        'й' => 'i',
        'ц' => 'ts',
        'у' => 'u',
        'к' => 'k',
        'е' => 'e',
        'н' => 'n',
        'г' => 'g',
        'ш' => 'sh',
        'щ' => 'sch',
        'з' => 'z',
        'х' => 'h',
        'ъ' => '_',
        'ф' => 'f',
        'ы' => 'y',
        'в' => 'v',
        'а' => 'a',
        'п' => 'p',
        'р' => 'r',
        'о' => 'o',
        'л' => 'l',
        'д' => 'd',
        'ж' => 'zh',
        'э' => 'z',
        'я' => 'ya',
        'ч' => 'ch',
        'с' => 's',
        'м' => 'm',
        'и' => 'i',
        'т' => 't',
        'ь' => '_',
        'б' => 'b',
        'ю' => 'yu',
        'і' => 'i',
        'ї' => 'yi',
        'є' => 'e',
        'ё' => 'yo',        
        'Й' => 'i',
        'Ц' => 'ts',
        'У' => 'u',
        'К' => 'k',
        'Е' => 'e',
        'Н' => 'n',
        'Г' => 'g',
        'Ш' => 'sh',
        'Щ' => 'sch',
        'З' => 'z',
        'Х' => 'h',
        'Ъ' => '_',
        'Ф' => 'f',
        'Ы' => 'y',
        'В' => 'v',
        'А' => 'a',
        'П' => 'p',
        'Р' => 'r',
        'О' => 'o',
        'Л' => 'l',
        'Д' => 'd',
        'Ж' => 'zh',
        'Э' => 'z',
        'Я' => 'ya',
        'Ч' => 'ch',
        'С' => 's',
        'М' => 'm',
        'И' => 'i',
        'Т' => 't',
        'Ь' => '_',
        'Б' => 'b',
        'Ю' => 'yu',
        'І' => 'i',
        'Ї' => 'yi',
        'Є' => 'e',
        'Ё' => 'yo',
        '.' => '.'
    );
	protected $_useCodebase = false;
	
    /**
     * Rename file name from non latin characters (not include extension)
     * 
     * @param string $filename
     * @throws Zend_Filter_Exception
     */
    
    public function setUseCodebase($useCodebase)
	{
		$this->_useCodebase = $useCodebase;
	}
    
	public function filter($value)
    {
        $file   = $this->getNewName($value, true);
        if (is_string($file)) {
            return $file;
        }

		// Split string to characters
    	if ($this->_useCodebase !== false && is_string($this->_useCodebase)) {
    		$file['source'] = iconv($this->_useCodebase, 'UTF-8', $file['source']);
    	}
    	$parts = str_split($file['source']);    	
    	
    	// Replace characters
    	foreach ($parts as &$letter) {
    		if(preg_match('/^[a-z0-9]+$/', $letter)){
    			// Skip if not need to replace
    			//continue;
    		}
   			
    		// If character in character map replace, otherwise replace to ''
   			if (!array_key_exists($letter, $this->_replaceMap)) {
   				$letter = '';
   			} else {
   				$letter = $this->_replaceMap[$letter];
   			}
    	}
    	
    	// Return filtered string
    	$file['target'] = strtolower(implode('', $parts));

        $result = rename($file['source'], $file['target']);

        if ($result === true) {
            return $file['target'];
        }

        require_once 'Zend/Filter/Exception.php';
        throw new Zend_Filter_Exception(sprintf("File '%s' could not be renamed. An error occured while processing the file.", $value));
    }
	
	
}