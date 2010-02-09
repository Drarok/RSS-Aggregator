<?php

class AdvancedXMLElement extends SimpleXMLElement {
	public function addElement($source) {
		if ($source->getName() == 'content' AND $source['type'] == 'xhtml')
			return;

		$name = htmlspecialchars($source->getName());
		$value = htmlspecialchars($source[0]);
		$new_child = $this->addChild($name, $value);

		foreach ($source->attributes() as $attrname => $attrval) {
			$new_child[$attrname] = (string) $attrval;
		}

		foreach ($source->children() as $child) {
			$new_child->addElement($child);
		}
	}
}
