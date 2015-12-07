<?php


namespace Render\ImageToolFactory;

class LiveImageToolFactory extends SimpleImageToolFactory
{

  public function __construct(
      $libraryPath,
      $imageAdapterName = 'Phpgd',
      array $imageAdapterConfig = array()
  ) {
    $this->initMemoryLimit();
    parent::__construct($libraryPath, $imageAdapterName, $imageAdapterConfig);
  }

  private function initMemoryLimit($newMemoryLimit = 267386880)
  {
    $memoryLimit = $this->convertToBytes(@ini_get('memory_limit'));
    if ($memoryLimit < $newMemoryLimit) {
      @ini_set('memory_limit', (int)$newMemoryLimit);
    }
  }

  private function convertToBytes($val)
  {
    if (is_null($val)) {
      return 0;
    }
    if (!preg_match('#([0-9]+)[\s]*([a-z]+)#i', trim($val), $matches)) {
      return (int)$val;
    }
    $last = $matches[2];
    $resultValue = (int)$matches[1];
    switch (strtolower($last)) {
      case 'g':
      case 'gb':
            return $resultValue * 1024 * 1024 * 1024;
      case 'm':
      case 'mb':
            return $resultValue * 1024 * 1024;
      case 'k':
      case 'kb':
            return $resultValue * 1024;
    }
    return $resultValue;
  }
}
