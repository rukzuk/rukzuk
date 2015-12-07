<?php

namespace Dual\Render;

/**
 * Interface IRenderModule
 * @package Dual\Render
 *
 * Interface needed for RenderObject to render a module.
 */
interface IRenderModule
{

  public function html();

  public function head();

  public function css(&$css);
}
