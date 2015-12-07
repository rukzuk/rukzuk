<?php
namespace Rukzuk\Modules;

/**
 * Class HtmlTagBuilder
 * Outputs valid HTML5
 * @package Rukzuk\Modules
 */
class HtmlTagBuilder
{

  private $tag;
  private $attr;
  private $children;

  /**
   * @var array
   * @see http://www.w3.org/html/wg/drafts/html/master/syntax.html#void-elements
   */
  private $html5VoidElements = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr');

  /**
   * Create a HtmlTagBuilder Instance
   *
   * @param string $tag the tag
   * @param array $attr html attributes of the tag in a key => value manner
   * @param array|HtmlTagBuilder $children an array of HtmlTagBuilder objects or only one HtmlTagBuilder object (or a string)
   */
  public function __construct($tag, $attr = null, $children = null)
  {
    // init
    $this->attr = array();
    $this->children = array();
    $this->setTagName($tag);
    $this->set($attr);
    
    if (is_null($children)) {
      return;
    }

    // append children
    if (is_array($children)) {
      foreach ($children as $child) {
        $this->append($child);
      }
    } else {
      $this->append($children);
    }
  }

  /**
   * Sets the tag
   * @param string $tag
   */
  public function setTagName($tag)
  {
    $this->tag = $tag;
  }

  /**
   * Appends the given html tag as a child
   *
   * @param HtmlTagBuilder|string $tag The new child tag or a string (which will be html encoded)
   * @return HtmlTagBuilder The current instance for chaining
   * @throws \Exception
   */
  public function append($tag)
  {
    if ($this->isVoidElement()) {
      throw new \Exception('HtmlTagBuilder: void elements cant have child elements');
    }

    if (is_string($tag) || $this->hasToStringMagicMethod($tag)) {
      $this->children[] = $tag;
    }
    return $this;
  }

  /**
   * Append raw html which will not be escaped
   * WARNING: THIS MIGHT LEAD TO INJECTION OF UNWANTED CODE
   *
   * @param string $str
   * @return HtmlTagBuilder The current instance for chaining
   */
  public function appendHtml($str)
  {
    // wrap raw html strings
    if (is_string($str)) {
      $this->append(new HtmlTagBuilderRawHtml($str));
    }
    return $this;
  }

  /**
   * Append Text with a nl2br
   * @param $text
   * @return HtmlTagBuilder The current instance for chaining
   */
  public function appendText($text) {
    if (is_string($text)) {
      foreach(explode("\n", $text) as $v) {
        $this->children[] = $v;
        $this->children[] = new static('br');
      }
    }
    return $this;
  }

  /**
   * Adds the given string to the list of css classes
   * @param string $cssClass The new css class name
   * @return HtmlTagBuilder The current instance for chaining
   */
  public function addClass($cssClass)
  {
    // skip empty classes
    if (!is_string($cssClass) || trim($cssClass) == '') {
      return $this;
    }

    $cssClass = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8', false);

    if (!array_key_exists('class', $this->attr)) {
      $this->attr['class'] = $cssClass;
    } else {
      $this->attr['class'] .= ' ' . $cssClass;
    }
    return $this;
  }

  /**
   * Set the given tag attribute(s)
   * @param string|array $attribute The attribute name or an array containing multiple key-value-changes
   * @param string $value The new attribute value or empty
   * @return HtmlTagBuilder The current instance for chaining
   *
   * @example $tag->set('data-foo', 'foo')->set('data-bar', 'bar');
   * @example $tag->set(array('data-foo' => 'foo', 'data-bar' => 'bar'));
   */
  public function set($attribute, $value = null)
  {
    if (is_array($attribute)) {
      $this->attr = array_merge($this->attr, $attribute);
    } else if (is_string($attribute)) {
      $this->attr[$attribute] = $value;
    }
    return $this;
  }

  /**
   * Removes an attribute
   * @param $attribute
   * @example $tag->clear('data-foo')
   */
  public function clear($attribute)
  {
    unset($this->attr[$attribute]);
  }

  /**
   * Returns the value of a given attribute or the empty string if the attribute
   * has no value or has never been set
   * @param string $attribute The name of the attribute
   * @return string
   */
  public function get($attribute)
  {
    if (isset($this->attr[$attribute])) {
      return $this->attr[$attribute];
    } else {
      return '';
    }
  }

  /**
   * Returns an array containing all attributes of the current tag (key -> attribute name,
   * value -> attribute value)
   * @return array
   */
  public function getAll()
  {
    return $this->attr;
  }

  /**
   * Returns the initial tag name
   * @return string
   */
  public function getTagName()
  {
    return $this->tag;
  }

  /**
   * Returns a string containing the complete html out put for the tag
   * (e.g. '<div id="foo" class="bar" data-x="...">...</div>')
   *
   * @return string
   */
  public function toString()
  {

    // open
    $str = $this->getOpenString();

    // children
    foreach ($this->children as $child) {
      if (is_string($child)) {
        $str .= htmlspecialchars($child, ENT_NOQUOTES, 'UTF-8');
      } else {
        $str .= $child->__toString();
      }
    }

    // close
    $str .= $this->getCloseString();

    return $str;
  }

  /**
   * Magic toString function
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

  /**
   * Magic method to create new instances using the API
   * Example: HtmlTagBuilder::h1(child1, child2, ...)
   * @param $name
   * @param $arguments
   * @return HtmlTagBuilder
   */
  public static function __callStatic($name, $arguments)
  {
    if (is_array($arguments) && count($arguments) > 0) {
      return new static($name, null, $arguments);
    }

    return new static($name);
  }

  /**
   * @return string open tag, e.g. <tag attribute="value">
   */
  public function getOpenString()
  {
    $str = '<' . $this->tag;
    foreach ($this->attr as $key => $value) {
      $str .= ' ' . $key;
      if (!is_null($value)) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $str .= '="' . $value . '"';
      }
    }
    $str .= '>';

    return $str;
  }

  /**
   * @return string closing tag, e.g. </tag>
   */
  public function getCloseString()
  {
    if (!$this->isVoidElement()) {
      return '</' . $this->tag . '>';
    }
  }

  /**
   * @return bool
   */
  protected function isVoidElement()
  {
    return in_array($this->tag, $this->html5VoidElements);
  }


  /**
   * Get all child elements
   * @return array of HtmlTagBuilder instances or strings
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Check if object can be converted to a string
   * @param $obj
   * @return bool
   */
  private function hasToStringMagicMethod($obj)
  {
    return (is_object($obj) && method_exists($obj, '__toString'));
  }

}


/**
 * Class HtmlTagBuilderRawHtml
 * @package Rukzuk\Modules
 */
class HtmlTagBuilderRawHtml
{
  /**
   * @var string
   */
  private $html = '';

  /**
   * Possible unsafe raw html
   * @param $html
   */
  public function __construct($html)
  {
    $this->html = $html;
  }

  /**
   * @return string
   */
  public function toString()
  {
    return $this->html;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

}
