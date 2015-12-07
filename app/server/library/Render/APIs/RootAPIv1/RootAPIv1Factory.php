<?php


namespace Render\APIs\RootAPIv1;

use Render\APIs\APIv1\APIv1Factory;
use Render\Visitors\AbstractVisitor;

class RootAPIv1Factory extends APIv1Factory
{

  /**
   * @param AbstractVisitor $renderingVisitor
   *
   * @return RootRenderAPI
   */
  public function getRenderAPI(AbstractVisitor $renderingVisitor)
  {
    return new RootRenderAPI($renderingVisitor, $this->getNodeTree(), $this->getRenderContext());
  }

  /**
   * @return RootCssAPI
   */
  public function getCSSAPI()
  {
    return new RootCssAPI($this->getNodeTree(), $this->getRenderContext());
  }
}
