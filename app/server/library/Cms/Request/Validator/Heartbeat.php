<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Heartbeat as Request;
use Orm\Data;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_NotEmpty as NotEmptyValidator;

/**
 * Heartbeat request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */

class Heartbeat extends Base
{
  private $expectedTypes = array('module', 'template', 'page', 'website');

  /**
   * @param Cms\Request\Heartbeat\Poll $actionRequest
   */
  public function validateMethodPoll(Request\Poll $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateOpenItems($actionRequest->getOpenItems());
  }

  /**
   * @param array $openItems
   * @return boolean
   */
  private function validateOpenItems($openItems)
  {
    // init
    $success = true;

    // Alle Items durchlaufen und pruefen
    if (isset($openItems) && is_object($openItems)) {
      foreach ($openItems as $websiteId => $nextOpenItem) {
      // WebsiteId pruefen
        $this->validateWebsiteId($websiteId, 'openItems[websiteId]');
        
        // Items muessen ein Object sein
        if (!is_object($nextOpenItem)) {
          $messages = array('Falsches "openItems"-Format!');
          $this->addError(new Error('openItems', $nextOpenItem, $messages));
          $success = false;
        } else {
          // Pages vorhanden
          if (isset($nextOpenItem->pages)) {
          // Pages muessen als Array angegeben werden
            if (!is_array($nextOpenItem->pages)) {
              $messages = array('Falsches "openItems->pages"-Format!');
              $this->addError(new Error('openItems->pages', $nextOpenItem->pages, $messages));
              $success = false;
            } else {
              // Alle PageIds pruefen
              foreach ($nextOpenItem->pages as $nextPageId) {
                if (!$this->validatePageId($nextPageId, 'openItems->pages[]')) {
                  $success = false;
                }
              }
            }
          }

          // Templates vorhanden
          if (isset($nextOpenItem->templates)) {
          // Templates muessen als Array angegeben werden
            if (!is_array($nextOpenItem->templates)) {
              $messages = array('Falsches "openItems->templates"-Format!');
              $this->addError(new Error('openItems->templates', $nextOpenItem->templates, $messages));
            } else {
              // Alle TemplateIds pruefen
              foreach ($nextOpenItem->templates as $nextTemplateId) {
                if (!$this->validateTemplateId($nextTemplateId, 'openItems->templates[]')) {
                  $success = false;
                }
              }
            }
          }

          // Module vorhanden
          if (isset($nextOpenItem->modules)) {
          // Module muessen als Array modules werden
            if (!is_array($nextOpenItem->modules)) {
              $messages = array('Falsches "openItems->modules"-Format!');
              $this->addError(new Error('openItems->modules', $nextOpenItem->modules, $messages));
            } else {
              // Alle ModulIds pruefen
              foreach ($nextOpenItem->modules as $nextModuleId) {
                if (!$this->validateModuleId($nextModuleId, 'openItems->modules[]')) {
                  $success = false;
                }
              }
            }
          }
        }
      }
    } // Items muss ein Array sein
    elseif (isset($openItems)) {
      $messages = array('Falsches "openItems"-Format!');
      $this->addError(new Error('openItems', $openItems, $messages));
      $success = false;
    }

    return $success;
  }

  /**
   * @param  string $pageId
   * @param  string $field
   * @return boolean
   */
  private function validatePageId($pageId, $field)
  {
    $idValidator = new UniqueIdValidator(
        Data\Page::ID_PREFIX,
        Data\Page::ID_SUFFIX
    );

    if (!$idValidator->isValid($pageId)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error($field, $pageId, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string $templateId
   * @param  string $field
   * @return boolean
   */
  private function validateTemplateId($templateId, $field)
  {
    $idValidator = new UniqueIdValidator(
        Data\Template::ID_PREFIX,
        Data\Template::ID_SUFFIX
    );

    if (!$idValidator->isValid($templateId)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error($field, $templateId, $messages));
      return false;
    }
    return true;
  }
  
  /**
   * @param  string $modulId
   * @param  string $field
   * @return boolean
   */
  private function validateModuleId($modulId, $field)
  {
    $idValidator = new ModuleIdValidator(true);
    if (!$idValidator->isValid($modulId)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error($field, $modulId, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  boolean $value
   * @param  string  $field
   * @return boolean
   */
  private function validateIsBoolean($value, $field)
  {
    $booleanValidator = new BooleanValidator();

    if (!$booleanValidator->isValid($value)) {
      $messages = array_values($booleanValidator->getMessages());
      $this->addError(new Error($field, $value, $messages));
      return false;
    }

    return true;
  }
}
