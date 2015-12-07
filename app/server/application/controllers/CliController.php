<?php
use Cms\Controller as Controller;
use Cms\Business\Cli as CliBusiness;
use Seitenbau\Registry as Registry;
use Cms\Response\Cli as Response;
use Cms\Exception as CmsException;

/**
 * Cli Controller
 *
 * @package      Application
 * @subpackage   Controller
 *
 * @method \Cms\Business\Cli getBusiness
 */

class CliController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Cli');
    parent::init();
  }

  /**
   * @example
   * php cms/server/bin/run.php --docroot="${INSTANCE_PATH}/" --action="cli/checkftplogin" --params="{\"username\":\"${USERNAME}\", \"password\":\"${PASSWORD}\"}"
   */
  public function checkftploginAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cli', 'CheckFtpLogin');
    
    $this->getBusiness()->checkFtpLogin(
        $validatedRequest->getUsername(),
        $validatedRequest->getPassword()
    );
  }

  /**
   * @example
   * php cms/server/bin/run.php --docroot="${INSTANCE_PATH}/" --action="cli/lastlogin"
   */
  public function lastloginAction()
  {
    $lastLogin = $this->getBusiness()->getLastLogin();
    $this->responseData->setData(new Response\LastLogin($lastLogin));
  }

  public function checkloginAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cli', 'CheckLogin');
    
    $identity = $this->getBusiness()->checkLogin(
        $validatedRequest->getUsername(),
        $validatedRequest->getPassword()
    );
    
    $this->responseData->setData(new Response\CheckLogin($identity));
  }

  public function getallsuperusersAction()
  {
    $superusers = $this->getBusiness()->getAllSuperusers();

    $this->responseData->setData(new Response\GetAllSuperusers($superusers));
  }

  public function initsystemAction()
  {
    /** @var $validatedRequest \Cms\Request\Cli\InitSystem */
    $validatedRequest = $this->getValidatedRequest('Cli', 'InitSystem');

    $this->getBusiness()->initSystem();

    if ($validatedRequest->getEmail() !== null) {
      $userCreateValues = array(
        'email' => $validatedRequest->getEmail(),
        'lastname' => $validatedRequest->getLastname(),
        'firstname' => $validatedRequest->getFirstname(),
        'gender' => $validatedRequest->getGender(),
        'language' => $validatedRequest->getLanguage(),
        'isSuperuser' => true,
        'isDeletable' => false,
      );
      $user = $this->getBusiness()->createUser(
          $userCreateValues,
          $validatedRequest->getSendregistermail()
      );

      $this->responseData->setData(new Response\InitSystem($user));
      return;
    }
  }

  public function registeruserAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cli', 'RegisterUser');
    
    $data = $this->getBusiness()->registerUser($validatedRequest->getId());

    $this->responseData->setData(new Response\RegisterUser($data));
  }
  
  public function optinuserAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cli', 'OptinUser');
    
    $this->getBusiness()->optinUser(
        $validatedRequest->getCode(),
        $validatedRequest->getPassword()
    );
  }

  public function updatesystemAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cli', 'UpdateSystem');

    $updateInfo = $this->getBusiness()->updateSystem(
        $validatedRequest->getVersion()
    );

    $this->responseData->setData(new Response\UpdateSystem($updateInfo));
  }

  public function updatedataAction()
  {
    $this->getBusiness()->updateData();
  }

  public function garbagecollectionAction()
  {
    $gbInfo = $this->getBusiness()->garbageCollection();
    $this->responseData->setData(new Response\GarbageCollection($gbInfo));
  }

  public function sendstatisticAction()
  {
    // Graphite
    // CliBusiness\DiskUsageHelper::logDiskUsage();
    // $this->getBusiness()->sendStatisticToGraphite();

    // Segment.io
    $this->getBusiness()->sendStatisticToAnalyticsServices();
  }

  public function predeleteAction()
  {
    // clean up all relations to external systems
    // remove live hosted websites
    $this->getBusiness()->removeAllLiveWebsites();
  }

  /**
   * check if the session should be closed
   *
   * @param string $action  Method name of action
   * @return boolean
   */
  protected function shouldTheSessionBeClosedBeforeActionDispatched($action)
  {
    # do not close session for all action
    return false;
  }

  public function buildthemeAction()
  {
    /** @var \Cms\Request\Cli\BuildTheme $validatedRequest */
    $validatedRequest = $this->getValidatedRequest('Cli', 'BuildTheme');

    // read a file if present
    if ($validatedRequest->hasProperty('file')) {
      $content = json_decode(@file_get_contents($validatedRequest->getProperty('file')), true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new CmsException(2, __METHOD__, __LINE__, array(
          'message' => json_last_error_msg()));
      }
    } elseif ($validatedRequest->hasProperty('content')) {
      $content = json_decode(json_encode($validatedRequest->getProperty('content')), true);
    }
    if (!isset($content['vars']) || !is_array($content['vars'])) {
      throw new CmsException(2, __METHOD__, __LINE__, array(
        'message' => 'no vars key in json file found'));
    }

    $this->getBusiness()->buildTheme($content['vars']);
  }
}
