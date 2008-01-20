<?php

if (!defined('e107_INIT')) { exit; }

// e107 requires PHP > 4.3.0, all functions that are used in e107, introduced in newer
// versions than that should be recreated in here for compatabilty reasons..

// file_put_contents - introduced in PHP5
if (!function_exists('file_put_contents'))
{
	/**
	* @return int
	* @param string $filename
	* @param mixed $data
	* @desc Write a string to a file
	*/
	define('FILE_APPEND', 1);
	function file_put_contents($filename, $data, $flag = false)
	{
		$mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
		if (($h = @fopen($filename, $mode)) === false)
		{
			return false;
		}
		if (is_array($data)) $data = implode($data);
		if (($bytes = @fwrite($h, $data)) === false)
		{
			return false;
		}
		fclose($h);
		return $bytes;
	}
}



if (!function_exists('stripos'))
{
	function stripos($str,$needle,$offset=0)
	{
		return strpos(strtolower($str), strtolower($needle), $offset);
	}
}

if(!function_exists('simplexml_load_string'))
{

	//CXml class code found on php.net
	class CXml
	{
		var $xml_data;
		var $obj_data;
		var $pointer;

		function CXml() { }

		function Set_xml_data( &$xml_data )
		{
			$this->index = 0;
			$this->pointer[] = &$this->obj_data;

			//strip white space between tags
			$this->xml_data = preg_replace("/>[[:space:]]+</i", "><", $xml_data);
			$this->xml_parser = xml_parser_create( "UTF-8" );

			xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, false );
			xml_set_object( $this->xml_parser, $this );
			xml_set_element_handler( $this->xml_parser, "_startElement", "_endElement");
			xml_set_character_data_handler( $this->xml_parser, "_cData" );

			xml_parse( $this->xml_parser, $this->xml_data, true );
			xml_parser_free( $this->xml_parser );
		}

		function _startElement( $parser, $tag, $attributeList )
		{
			$attributes = '@attributes';
			foreach( $attributeList as $name => $value )
			{
				$value = $this->_cleanString( $value );
				$object->{$attributes}[$name] = $value;
				//           $object->$name = $value;
			}
			//replaces the special characters with the underscore (_) in tag name
			$tag = preg_replace("/[:\-\. ]/", "_", $tag);
			eval( "\$this->pointer[\$this->index]->" . $tag . "[] = \$object;" );
			eval( "\$size = sizeof( \$this->pointer[\$this->index]->" . $tag . " );" );
			eval( "\$this->pointer[] = &\$this->pointer[\$this->index]->" . $tag . "[\$size-1];" );

			$this->index++;
		}

		function _endElement( $parser, $tag )
		{
			array_pop( $this->pointer );
			$this->index--;
		}

		function _cData( $parser, $data )
		{
			if (empty($this->pointer[$this->index]))
			{
				if (rtrim($data, "\n"))
				{
					$this->pointer[$this->index] = $data;
				}
			}
			else
			{
				$this->pointer[$this->index] .= $data;
			}
		}

		function _cleanString( $string )
		{
			return utf8_decode( trim( $string ) );
		}
	}
	
	function simplexml_load_string($xml)
	{
		$xmlClass = new CXml;
		$xmlClass->Set_xml_data($xml);
		$data = (array)$xmlClass->obj_data;
		$tmp = array_keys($data);
		$data = $data[$tmp[0]][0];
		return $data;
	}

	
}
