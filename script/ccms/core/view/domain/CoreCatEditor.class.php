<?

class CoreCatEditor extends GenericEditor {

	protected function getFkOptions(CCMSDomainField $field) {
		if ($field->name!=="parent_id") return parent::getFkOptions($field);
		return $this->getManager()->getParentOptions($this->line);
	}

}

//end