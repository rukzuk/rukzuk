<?php

use Cms\Controller as Controller;
use Cms\Business\User as UserBusiness;
use Cms\Response\User as Response;
use Seitenbau\Registry as Registry;

/**
 * UserController
 *
 * @package      Application
 * @subpackage   Controller
 */
class UserController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('User');
    parent::init();
  }

  public function changepasswordAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'ChangePassword');
    
    $check = array('id' => $validatedRequest->getUserId());
    $this->getBusiness()->checkUserRights('changePassword', $check);
    
    $this->getBusiness()->changePassword(
        $validatedRequest->getUserId(),
        $validatedRequest->getOldPassword(),
        $validatedRequest->getPassword()
    );
  }

  public function optinAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Optin');
    
    $this->getBusiness()->optin(
        $validatedRequest->getCode(),
        $validatedRequest->getPassword(),
        $validatedRequest->getUsername()
    );
  }

  public function validateoptinAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'ValidateOptin');
    
    $this->getBusiness()->validateOptin($validatedRequest->getCode());
  }

  public function registerAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Register');
    
    $this->getBusiness()->register($validatedRequest->getUserIds());
  }
  
  public function renewpasswordAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'RenewPassword');

    try {
      $this->getBusiness()->renewPassword($validatedRequest->getEmail());
    } catch (UserBusiness\UserIsReadOnlyException $readonlyException) {
      $info = $readonlyException->getUser()->getSourceInfo();
      if (!isset($info['passwordResetUrl']) && !empty($info['passwordResetUrl'])) {
        throw $readonlyException;
      }
      $this->responseData->setData(
          new Response\RenewPassword(array('redirect' => $info['passwordResetUrl']))
      );
    }
  }
  
  public function createAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Create');
    
    $createValues = array(
      'email' => $validatedRequest->getEmail(),
      'lastname' => $validatedRequest->getLastname(),
      'firstname' => $validatedRequest->getFirstname(),
      'gender' => $validatedRequest->getGender(),
      'language' => $validatedRequest->getLanguage(),
      'isSuperuser' => $validatedRequest->getIsSuperuser(),
      'isDeletable' => true,
    );
    
    $check = array('attributes' => $createValues);
    $this->getBusiness()->checkUserRights('create', $check);

    $user = $this->getBusiness()->create($createValues);

    // log user create
    Registry::getActionLogger()->logAction(UserBusiness::USER_CREATE_ACTION, array(
      'id' => $user->getId(),
      'email' => $user->getEmail(),
      'name' => ($user->getFirstname()." ".$user->getLastname()),
      'language' => $user->getLanguage()
    ));

    $this->responseData->setData(new Response\Create($user));
  }

  public function editAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Edit');

    $editValues = array(
      'email' => $validatedRequest->getEmail(),
      'lastname' => $validatedRequest->getLastname(),
      'firstname' => $validatedRequest->getFirstname(),
      'language' => $validatedRequest->getLanguage(),
      'gender' => $validatedRequest->getGender(),
      'password' => $validatedRequest->getPassword(),
      'isSuperuser' => $validatedRequest->getIsSuperuser(),
      'isDeletable' => null,
    );
    
    $check = array('id' => $validatedRequest->getId(), 'attributes' => $editValues);
    $this->getBusiness()->checkUserRights('edit', $check);

    $user = $this->getBusiness()->edit($validatedRequest->getId(), $editValues);
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Delete');
    
    $check = array('id' => $validatedRequest->getId());
    $this->getBusiness()->checkUserRights('delete', $check);

    // log params
    $user = $this->getBusiness()->getById($validatedRequest->getId());
    $logParams = array('id' => $user->getId(), 'email' => $user->getEmail());
    $user = null;

    $this->getBusiness()->delete($validatedRequest->getId());

    // log call
    Registry::getActionLogger()->logAction(UserBusiness::USER_DELETE_ACTION, $logParams);
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'GetAll');

    $all = $this->getBusiness()->getAll($validatedRequest->getWebsiteId());

    $this->responseData->setData(new Response\GetAll($all));
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'GetById');

    $user = $this->getBusiness()->getById($validatedRequest->getId());

    $this->responseData->setData(new Response($user));
  }

  public function addgroupsAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'AddGroups');

    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('addGroups', $check);
    
    $this->getBusiness()->addGroups(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getGroupIds()
    );
  }

  public function removegroupsAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'RemoveGroups');

    $check = array(
      'id'        => $validatedRequest->getId(),
      'websiteId' => $validatedRequest->getWebsiteId()
    );
    $this->getBusiness()->checkUserRights('removeGroups', $check);

    $this->getBusiness()->removeGroups(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getGroupIds()
    );
  }
  
  /**
   * Gibt Informationen zum angemeldeten User zurueck
   */
  public function infoAction()
  {
    $userInfo = $this->getBusiness()->getInfoFromDeclaredUser();
    $this->responseData->setData(new Response\Info($userInfo));
  }
  
  public function loginAction()
  {
    $validatedRequest = $this->getValidatedRequest('User', 'Login');
    
    $this->getBusiness()->login(
        $validatedRequest->getUsername(),
        $validatedRequest->getPassword()
    );
    
    Registry::getActionLogger()->logAction(UserBusiness::USER_LOGIN_ACTION, array());
  }
  
  public function logoutAction()
  {
    $this->getBusiness()->logout();
  }
  
  /**
   * check if the session should be closed
   *
   * @param string $action  Method name of action
   * @return boolean
   */
  protected function shouldTheSessionBeClosedBeforeActionDispatched($action)
  {
    switch ($action) {
      case 'getallAction':
      case 'getbyidAction':
      case 'createAction':
      case 'deleteAction':
      case 'infoAction':
            return true;
        break;
    }
    
    return false;
  }
}
