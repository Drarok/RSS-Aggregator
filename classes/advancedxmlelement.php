<?php

class AdvancedXMLElement extends SimpleXMLElement {
	public function addElement($source) {
		if ($source->getName() == 'content' AND $source['type'] == 'xhtml')
			return;

		$new_child = $this->addChild($source->getName(), $source[0]);

		foreach ($source->attributes() as $attrname => $attrval) {
			$new_child[$attrname] = (string) $attrval;
		}

		foreach ($source->children() as $child) {
			$new_child->addElement($child);
		}
	}
}
