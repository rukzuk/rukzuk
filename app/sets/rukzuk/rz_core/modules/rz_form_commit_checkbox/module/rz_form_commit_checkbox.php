<?php
namespace Rukzuk\Modules;
use Render\Unit;
use Rukzuk\Modules\ChildModuleDependency;

class rz_form_commit_checkbox extends SimpleModule{

	/**
	 * @var \FormSubmit
	 */
	private $formSubmit = null;

	/**
	 * @param $renderApi
	 * @param \Render\Unit $unit
	 * @param \Render\ModuleInfo $moduleInfo
	 */
	public function renderContent( $renderApi, $unit, $moduleInfo ) {
		$child = new ChildModuleDependency();
		if ($child->isInsideModule($renderApi, $unit, 'rz_form')) {
			$this->renderFormFieldContent($renderApi, $unit);
		} else {
			$i18n = new Translator($renderApi, $moduleInfo);
			$msg = $i18n->translate('error.moduleOnlyWorkingInForm');
			$errorTag = new HtmlTagBuilder('div', array(
				'class' => 'RUKZUKmissingInputHint'
			), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
			echo $errorTag->toString();
		}
	}

	private function renderFormFieldContent($renderApi, $unit){

		$this->formSubmit 	= new \FormSubmit();
		$fieldId     = 'field' . $unit->getId();
    $labelText   = $renderApi->getEditableTag($unit, 'fieldLabel', 'span', '');

    $choiceField = new \CheckboxField();
    $elementProperties = $choiceField->getElementProperties();
    $elementProperties->addAttribute("id", $fieldId);
    $elementProperties->addAttribute("required", null);

    $label = new \Label();
    $label->add($choiceField);
    $label->add(new \Span(" ".$labelText));

    $wrapper = new \Container();
    $wrapper->add( $label );
    echo $wrapper->renderElement();

		$renderApi->renderChildren( $unit );
	}

}
