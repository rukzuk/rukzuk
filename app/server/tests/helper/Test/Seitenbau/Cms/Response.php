<?php

namespace Test\Seitenbau\Cms;

use Cms\Response as CmsResponse;

/**
 * Response
 *
 * @package      Cms
 */
class Response extends CmsResponse
{
  /**
   * @var array
   */
  private $expectedResponseBodySectionKeys;
  /**
   * @var string
   */
  private $rawResponseBody;

  /**
   * @string
   */
  public function __construct($rawResponseBody)
  {
    $this->rawResponseBody = $rawResponseBody;
    $this->expectedResponseBodySectionKeys = array(
      'success', 
      'error',
      'data'
    );
    $this->dissolveSections();
  }

  /**
   * @return string
   */
  public function getRawResponseBody()
  {
    return $this->rawResponseBody;
  }

  /**
   * @throws \Exception
   */
  private function dissolveSections()
  {
    $responseBodySections = json_decode($this->rawResponseBody);

    if (is_object($responseBodySections))
    {
      if ($this->areAllSectionsAvailable($responseBodySections))
      {
        $this->setSuccess($responseBodySections->success);
        if (is_array($responseBodySections->error)
            && count($responseBodySections->error) > 0)
        {
          $this->setError($responseBodySections->error);
        }
        $this->setData($responseBodySections->data);
      }
      else
      {
        $exceptionMessage = sprintf(
          "Not all Response body sections '%s' available",
          implode(', ', $this->expectedResponseBodySectionKeys)
        );
        throw new \Exception($exceptionMessage);
      }
    } else {
      throw new \Exception('No Response body sections found to dissolve');
    }
  }

  /**
   * @return boolean
   */
  private function areAllSectionsAvailable($responseBodySections)
  {
    $responseBodyValues = get_object_vars($responseBodySections);
    $responseBodySectionKeys = array_keys($responseBodyValues);

    sort($this->expectedResponseBodySectionKeys);
    sort($responseBodySectionKeys);

    return $this->expectedResponseBodySectionKeys === $responseBodySectionKeys;
  }
}