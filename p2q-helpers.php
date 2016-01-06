<?php

class P2q {
	// split text at all language comments and quick tags
	static function qtranxf_get_language_blocks($text) {
		$split_regex = "#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\])#ism";
		return preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
	}

	static function qtranxf_split($text, $quicktags = true, $default_language = false) {
		$blocks = static::qtranxf_get_language_blocks($text);
		return static::qtranxf_split_blocks($blocks,$quicktags, $default_language);
	}

	static function qtranxf_split_blocks($blocks, $quicktags = true, $default_language = false) {
		$result = array();

		$current_language = 'default';
		foreach($blocks as $block) {
			# detect language tags
			if(preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
				$current_language = $matches[1];
				//if(!qtranxf_isEnabled($current_language)) $current_language = false;//still need it
				continue;
			// detect quicktags
			} elseif($quicktags && preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
				$current_language = $matches[1];
				//if(!qtranxf_isEnabled($current_language)) $current_language = false;//still need it
				continue;
			// detect ending tags
			} elseif(preg_match("#^<!--:-->$#ism", $block, $matches)) {
				$current_language = false;
				continue;
			}
			// correctly categorize text block
			if($current_language){
				if(!isset($result[$current_language])) $result[$current_language]='';
				$result[$current_language] .= $block;
				$current_language = false;
			}
		}
		//it gets trimmed later in qtranxf_use() anyway, better to do it here
		foreach($result as $lang => $text){
			$result[$lang]=trim($text);
		}

		//We don't know if the original string has tags or not (whether it already was translated). If not, only default language will have a value
		if (isset($result['default'])) {
			if ($default_language && ! isset($result[$default_language] ) || $result[$default_language] == "" )
				$result[$default_language] = $result['default'];
			unset($result['default']);
		}

		return $result;
	}

	static function qtranxf_join($texts) {
		if(!is_array($texts)) $texts = static::qtranxf_split($texts, false);
		return static::qtranxf_join_c($texts);
	}

	static function qtranxf_join_c($texts) {
		$text = '';
		foreach($texts as $lang => $lang_text) {
			if(empty($lang_text)) continue;
			$text .= '<!--:'.$lang.'-->'.$lang_text.'<!--:-->';
		}

		return $text;
	}

}

//var_dump( W2q::qtranxf_split("jos is hier[:en]Jos is here[:es]Jos esta aqui[:nl] l ", true, "nl") );
