<?php

/**
 * form field to select answer for question.
 * 
 * 
 */ 


class ProductQuestionImageSelectorField extends OptionsetField {

	private static $number_per_row = 1;

	protected $folderID = 0;

	protected $options = array();

	protected $objects = null;

	protected $width = 50;

	protected $height = 50;

	function __construct($name, $title = '', $options = array(), $value = '', $folderID) {
		$this->setOptions($options);
		$this->setFolderID($folderID);
		$this->createObjects();
		parent::__construct($name, $title, $options, $value);
	}

	function setOptions($options) {
		$this->options = $options;
	}

	function setFolderID($folderID) {
		$this->folderID = $folderID;
	}

	function setWidth($width) {
		$this->width = $width;
	}

	function setHeight($height) {
		$this->height = $height;
	}

	function Field($properties = array()) {
		$options = '';
		$source = $this->getSource();
		$count = 0;
		if($this->objects && $this->objects->count()) {
			foreach($this->objects as $image) {
				$key = $image->Key;
				$itemID = $this->id() . "_" . $image->ID;
				$value = $image->Value;
				$labelHTML = $value;
				$resizedImageObject = $image->getFormattedImage("CroppedImage", $this->width,$this->height);
				if($resizedImageObject) {
					$labelHTML = '<img src="'.$resizedImageObject->Link().'" alt="'.$value.'" />';
				}
				if($key == $this->value/* || $useValue */) {
					$useValue = false;
					$checked = " checked=\"checked\"";
				}
				else {
					$checked="";
				}
				$odd = ($count + 1) % 2;
				$oddEven = $odd ? "odd" : "even";
				$extraClass = " val" . preg_replace('/[^a-zA-Z0-9\-\_]/','_', $key);
				$position = " pos".$count;
				$disabled = $this->disabled ? 'disabled="disabled"' : '';
				$options .= "<li class=\"".$oddEven.$extraClass.$position."\"><input id=\"$itemID\" name=\"$this->name\" type=\"radio\" value=\"$key\"$checked $disabled class=\"radio\" /> <label for=\"$itemID\">$labelHTML</label></li>\n";
				$count++;
			}
			$id = $this->id();
		}
		if(empty($id)) {
			$id = 0;
		}
		return "
			<ul id=\"$id\" class=\"optionset {$this->extraClass()}\">
			\n$options
			</ul>\n";
	}

	protected function createObjects(){
		$this->objects = new ArrayList();
		if($this->options && is_array($this->options) && count($this->options)) {
			foreach($this->options as $option) {
				$imageOptions = ProductQuestion::create_file_array_from_option($option);
				$image = Image::get()->filter(array("ParentID" => $this->folderID, "Name" => $imageOptions))->First();
				if($image) {
					$image->Key = $option;
					$image->Value = $option;
					$this->objects->push($image);
				}
			}
		}
	}

}
