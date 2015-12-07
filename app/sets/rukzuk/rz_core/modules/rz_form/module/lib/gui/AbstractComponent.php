<?php
/**
 * @package      Rukzuk\Modules\rz_form_field
 */
abstract class AbstractComponent {

	const HTML_FORMAT_NORMAL = '<%s %s>%s</%1$s>';
	const HTML_FORMAT_VOID   = '<%s %s/>';

	private $content = null;

	/**
	 * @var array
	 * @see http://www.w3.org/html/wg/drafts/html/master/syntax.html#void-elements
	 */
	private $html5VoidElements = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr');

	/**
	 * @return string
	 */
	abstract protected function getElementTag();

	/**
	 * @return IElementProperties
	 */
	abstract protected function getElementProperties();

	/**
	 * @param string $children[optional]
	 * @return string
	 */
	public function renderElement( $children = null ){
		if($this->isVoidElement()){
		 	return sprintf(self::HTML_FORMAT_VOID, $this->getElementTag(), $this->getElementProperties()->render());
		}else{
			return sprintf(self::HTML_FORMAT_NORMAL, $this->getElementTag(), $this->getElementProperties()->render(), ($this->content)?$this->content:$children);
		}
	}

	public function renderElementProgressive( $renderApi, $unit ){
		if($this->isVoidElement()){
			echo "<{$this->getElementTag()}{$this->getElementProperties()->render()} />";
		}else{
			echo "<{$this->getElementTag()}{$this->getElementProperties()->render()}>";
			echo ($this->content)?$this->content:'';
			$renderApi->renderChildren( $unit );
			echo "</{$this->getElementTag()}>";
		}
	}

	protected function setContent( $content ){
		if( !is_null( $this->content ) ) {
			$this->content .= $content;
		}else{
			$this->content = $content;
		}
	}

	/**
	 * @return bool
	 */
	public function isVoidElement() {
		return in_array($this->getElementTag(), $this->html5VoidElements);
	}


} 
