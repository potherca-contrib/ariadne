<?php
	class pinp_xml {
		function _parser() {
			$parser = new xml_parser($this);
			return $parser;
		}
	};

	class xml_parser {

		function xml_parser(&$object) {
			$this->object = $object;
		}

		function _set_element_handler($tag_open, $tag_close) {
			$this->tag_open_template = $tag_open;
			$this->tag_close_template = $tag_close;
		}

		function _set_character_data_handler($tag_data) {
			$this->tag_data_template = $tag_data;
		}

		function _parse($string) {
			$parser = xml_parser_create();
			xml_set_object($parser, $this);
			xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
			xml_set_character_data_handler($parser, "call_tag_data");
			if (!xml_parse($parser, $string)) {
				$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
						xml_get_current_line_number($parser));
			}
		}

		function _parse_url($url) {
			if (!eregi('^https?://', $url)) {
				$this->error = "Not a valid URL ($url)";
			} else {
				$parser = xml_parser_create();
				xml_set_object($parser, $this);
				xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
				xml_set_character_data_handler($parser, "call_tag_data");
				$fp = fopen($url, "r");
				if (!$fp) {
					$this->error = "Could not open ($url)";
				} else {
					while (!$this->error && !feof($fp)) {
						$string = fread($fp, 4096);
						if (!xml_parse($parser, $string)) {
							$this->error = sprintf("XML error: %s at line %d",
								xml_error_string(xml_get_error_code($parser)),
									xml_get_current_line_number($parser));
						}
					}
					fclose($fp);
				}
			}
		}

		function _parse_curl($url) {
			if (!eregi('^https?://', $url)) {
				$this->error = "Not a valid URL ($url)";
			} else {
				$parser = xml_parser_create();
				xml_set_object($parser, $this);
				xml_set_element_handler($parser, "call_tag_open", "call_tag_close");
				xml_set_character_data_handler($parser, "call_tag_data");

				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		
				$string = curl_exec($ch);
				curl_close($ch);
				if (!xml_parse($parser, $string)) {
					$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser));
				}

			}
		}


		function call_tag_open($parser, $tag, $attributes) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_open_template) {
				$this->object->call($this->tag_open_template, Array("tag" => $tag, "attributes" => $attributes));
			}
		}

		function call_tag_close($parser, $tag) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_close_template) {
				$this->object->call($this->tag_close_template, Array("tag" => $tag));
			}
		}

		function call_tag_data($parser, $data) {
		global $ARBeenHere;
			$ARBeenHere = Array();
			if ($this->tag_data_template) {
				$this->object->call($this->tag_data_template, Array("tag_data" => $data));
			}
		}

	}
?>