<?php


namespace Render\ImageToolFactory;

interface IImageToolFactory
{

  /**
   * @return ImageTool
   */
  public function createImageTool();
}
