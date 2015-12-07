<?php


namespace Render\ImageToolFactory;

class SimpleImageToolFactory implements IImageToolFactory
{
  const ADAPTER_NAMESPACE = '\\Seitenbau\\Image\\Adapter\\';
  const ADAPTER_SUBDIR = '/Seitenbau/Image/Adapter/';

  /**
   * @var string
   */
  protected $imageAdapterClassName;

  /**
   * @var array
   */
  protected $imageAdapterConfig;

  /**
   * @param        $libraryPath
   * @param string $imageAdapterName
   * @param array  $imageAdapterConfig
   */
  public function __construct(
      $libraryPath,
      $imageAdapterName = 'Phpgd',
      array $imageAdapterConfig = array()
  ) {
    $this->imageAdapterName = self::ADAPTER_NAMESPACE
            . ucfirst($imageAdapterName);
    $imageAdapterClassFile = $libraryPath . self::ADAPTER_SUBDIR
            . ucfirst($imageAdapterName) . '.php';
    require_once($imageAdapterClassFile);
    $this->imageAdapterConfig = $imageAdapterConfig;
  }

  /**
   * @return ImageTool
   */
  public function createImageTool()
  {
    $imageAdapter = new $this->imageAdapterName($this->imageAdapterConfig);
    return new ImageTool($imageAdapter);
  }
}
