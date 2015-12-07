<?php


namespace Cms\Creator;

interface CreatorStorageInterface
{
  /**
   * @return array
   */
  public function toArray();

  /**
   * Finalizing the creator storage
   */
  public function finalize();

  /**
   * @return \Cms\Data\Creator
   */
  public function getCreatorData();
}
