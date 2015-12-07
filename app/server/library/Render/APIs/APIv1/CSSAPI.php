<?php


namespace Render\APIs\APIv1;

use Render\Exceptions\NodeNotFoundException;
use Render\Nodes\AbstractRenderNode;
use Render\NodeTree;
use Render\RenderContext;
use Render\Unit;

class CSSAPI extends HeadAPI
{

  /**
   * @var NodeTree  Used to access children and other units.
   */
  private $nodeTree;

  /**
   * @param NodeTree      $nodeTree
   * @param RenderContext $renderContext
   */
  public function __construct(NodeTree $nodeTree, RenderContext $renderContext)
  {
    parent::__construct($renderContext);
    $this->nodeTree = $nodeTree;
  }

  /**
   * @return NodeTree
   */
  protected function getNodeTree()
  {
    return $this->nodeTree;
  }

  /**
   * Returns the parent unit for the given unit
   *
   * @param Unit $unit unit array or unit id
   *
   * @return Unit|null
   */
  public function getParentUnit(Unit $unit)
  {
    $node = $this->getAbstractRenderNodeById($unit->getId());
    if (is_null($node)) {
      return null;
    }
    $parentNode = $node->getParent();
    if (!($parentNode instanceof AbstractRenderNode)) {
      return null;
    }
    return $parentNode->getUnit();
  }

  /**
   * Returns the form value of the given unit
   *
   * @param Unit  $unit          unit that holds the form value
   * @param mixed $key           key of the requested form value
   * @param mixed $fallbackValue result if formValue array misses key
   *
   * @return mixed
   */
  public function getFormValue(Unit $unit, $key, $fallbackValue = null)
  {
    $formValues = $unit->getFormValues();
    if (!(is_array($formValues) && array_key_exists($key, $formValues))) {
      return $fallbackValue;
    }

    return $formValues[$key];
  }

  /**
   * Returns a list of all children units of the given unit
   *
   * @param Unit $unit the unit object
   *
   * @return \Render\Unit[]
   */
  public function getChildren(Unit $unit)
  {
    $node = $this->getAbstractRenderNodeById($unit->getId());
    if (is_null($node)) {
      return array();
    }
    $children = array();
    foreach ($node->getChildren() as $childNode) {
      if ($childNode instanceof AbstractRenderNode) {
        $children[] = $childNode->getUnit();
      }
    }
    return $children;
  }

  /**
   * Returns the Unit object for a given unitId, null if not found
   *
   * @param $unitId
   *
   * @return null|\Render\Unit
   */
  public function getUnitById($unitId)
  {
    $node = $this->getAbstractRenderNodeById($unitId);
    if (is_null($node)) {
      return null;
    }
    return $node->getUnit();
  }

  /**
   * Returns the module info object for the given unit, null if not found
   *
   * @param \Render\Unit $unit
   *
   * @return null|\Render\ModuleInfo
   */
  public function getModuleInfo(Unit $unit)
  {
    $node = $this->getAbstractRenderNodeById($unit->getId());
    if (is_null($node)) {
      return null;
    }
    return $node->getModuleInfo();
  }

  /**
   * Returns the Node object for a given unitId, null if not found
   *
   * @param $unitId
   *
   * @return \Render\Nodes\AbstractRenderNode
   */
  protected function getNodeByUnitId($unitId)
  {
    return $this->getNodeTree()->getNodeByUnitId($unitId);
  }

  /**
   * Returns the Node object for a given unitId, null if not found
   *
   * @param $unitId
   *
   * @return null|\Render\Nodes\AbstractRenderNode
   */
  protected function getAbstractRenderNodeById($unitId)
  {
    try {
      $node = $this->getNodeByUnitId($unitId);
      if ($node instanceof AbstractRenderNode) {
        return $node;
      }
    } catch (NodeNotFoundException $doNothing) {
    }
    return null;
  }

  /**
   * Get values from the permanent unit cache
   *
   * @param Unit   $unit
   * @param string $key unique key
   *
   * @throws \Exception
   * @returns array returns empty array if key was not found
   */
  public function getUnitCache(Unit $unit, $key)
  {
    if (!is_string($key)) {
      throw new \Exception(__METHOD__ . ' $key is not a string');
    }

    $unitCache = $this->getRenderContext()->getCache();
    try {
      $cachedValue = $unitCache->getUnitValue($unit, $key);
    } catch (\Exception $e) {
      return array();
    }

    return $cachedValue;
  }

  /**
   * Set values in the permanent unit cache
   *
   * @param Unit   $unit
   * @param string $key   unique key
   * @param array  $value any typ of array containing only primitive types
   *
   * @throws \Exception
   */
  public function setUnitCache(Unit $unit, $key, $value)
  {

    if (!is_string($key)) {
      throw new \Exception(__METHOD__ . ' $key is not a string');
    }

    if (!is_array($value)) {
      throw new \Exception(__METHOD__ . ' $value is not an array');
    }

    $unitCache = $this->getRenderContext()->getCache();
    $unitCache->setUnitValue($unit, $key, $value);

  }
}
