<?php
use Cms\Business\Builder as BuilderBusiness;
use Cms\Controller as Controller;
use Cms\Response as Response;
use Cms\Exception as CmsException;
use Cms\Data\Build as BuildData;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;

/**
 * BuilderController
 *
 * @package      Application
 * @subpackage   Controller
 */
class BuilderController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Builder');
    parent::init();
  }
  
  public function getwebsitebuildsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Builder', 'GetWebsiteBuilds');
    
    $this->getBusiness()->checkUserRights('getWebsiteBuilds', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));
    
    $builds = $this->getBusiness()->getWebsiteBuilds(
        $validatedRequest->getWebsiteId()
    );
    
    $this->responseData->setData(new Response\Builder\GetByWebsiteId($builds));
  }
  
  public function getwebsitebuildbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('Builder', 'GetWebsiteBuildById');

    $this->getBusiness()->checkUserRights('getWebsiteBuildById', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    $build = $this->getBusiness()->getWebsiteBuildById(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getBuildId()
    );
    
    $this->responseData->setData(new Response\Build($build));
  }

  public function publishAction()
  {
    $validatedRequest = $this->getValidatedRequest('Builder', 'PublishWebsite');

    $this->getBusiness()->checkUserRights('publishWebsite', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    $publishedStatus = $this->publishWebsiteByBuildId(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getBuildId()
    );
    
    $build = $this->getBusiness()->getWebsiteBuildById(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getBuildId()
    );
    
    Registry::getActionLogger()->logAction(BuilderBusiness::BUILDER_PUBLISH_ACTION, array(
      'websiteId'   => $validatedRequest->getWebsiteId(),
      'id'          => $validatedRequest->getBuildId(),
      'publisherId' => $publishedStatus->getId(),
    ));

    $this->responseData->setData(new Response\Build($build));
  }
  
  public function buildandpublishwebsiteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Builder', 'BuildWebsite');

    $this->getBusiness()->checkUserRights('buildAndPublishWebsite', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));

    $build = $this->getBusiness()->buildWebsite(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getComment()
    );

    Registry::getActionLogger()->logAction(BuilderBusiness::BUILDER_BUILD_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $build->getId(),
    ));

    if ($build instanceof Cms\Data\Build) {
      $publishedStatus = $this->publishWebsiteByBuildId(
          $validatedRequest->getWebsiteId(),
          $build->getId()
      );

      Registry::getActionLogger()->logAction(BuilderBusiness::BUILDER_PUBLISH_ACTION, array(
        'websiteId'   => $validatedRequest->getWebsiteId(),
        'id'          => $build->getId(),
        'publisherId' => $publishedStatus->getId(),
      ));
    
      $build = $this->getBusiness()->getWebsiteBuildById(
          $validatedRequest->getWebsiteId(),
          $build->getId()
      );
    
      $this->responseData->setData(new Response\Build($build));
    }
  }
  
  public function buildwebsiteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Builder', 'BuildWebsite');

    $this->getBusiness()->checkUserRights('buildWebsite', array(
      'websiteId' => $validatedRequest->getWebsiteId()
    ));
    
    $build = $this->getBusiness()->buildWebsite(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getComment()
    );

    Registry::getActionLogger()->logAction(BuilderBusiness::BUILDER_BUILD_ACTION, array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $build->getId(),
    ));
    
    $this->responseData->setData(new Response\Build($build));
  }
  
  public function publisherstatuschangedAction()
  {
    try {
      $validatedRequest = $this->getValidatedRequest('Builder', 'PublisherStatusChanged');

      $this->getBusiness()->checkUserRights('publisherStatusChanged', array(
        'websiteId' => $validatedRequest->getWebsiteId()
      ));

      $this->getBusiness()->getWebsiteBuildById(
          $validatedRequest->getWebsiteId(),
          $validatedRequest->getBuildId()
      );
    } catch (\Exception $logOnly) {
      // log only to make hacking more complicating
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), Log::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }
  }
  
  private function publishWebsiteByBuildId($website, $buildId)
  {
    $this->throwExceptionOnActivePublishingJob($website);
    return $this->getBusiness('Publisher')->publishWebsite($website, $buildId);
  }
  
  private function throwExceptionOnActivePublishingJob($website)
  {
    $buildData = $this->getBusiness()->getActualPublishingBuild($website);
    if ($buildData instanceof BuildData) {
      throw new CmsException('903', __METHOD__, __LINE__, array('buildversion' => $buildData->getVersion()));
    }
  }
}
