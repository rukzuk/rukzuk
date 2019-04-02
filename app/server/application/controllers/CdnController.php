<?php
use Cms\Controller\Action as Action;
use Cms\Request\Base as BaseRequest;
use Cms\Service\Cdn as CdnService;
use Seitenbau\Mimetype as Mimetype;
use Seitenbau\Registry as Registry;
use Cms\Render\RequestHelper\ZendBasedHttpRequest;

/**
 * CdnController
 *
 * @package      Application
 * @subpackage   Controller
 */
class CdnController extends Action
{
  public function init()
  {
    $this->_helper->viewRenderer->setNoRender();
    $this->initBusiness('Cdn');
    $this->getFrontController()
         ->getDispatcher()
         ->setParam('disableOutputBuffering', 1);
  }

  public function postDispatch()
  {
  }

  public function exportAction()
  {
    $data = $this->getRequest()->getParam(BaseRequest::REQUEST_PARAMETER);

    if ($data == null) {
      $error['success'] = false;
      $error['errors'] = array(
        'code' => 1,
        'message' => 'Unvollstaendiger Request'
      );
      $this->view->error = json_encode($error);
      $this->_helper->viewRenderer->setNoRender(false);
      $this->_helper->viewRenderer('error');
    } else {
      $dataParams = new ArrayObject(json_decode($data));
      $exportName = $dataParams['name'];

      /** @var $exportBusiness \Cms\Business\Export */
      $exportBusiness = $this->getBusiness('Export');

      $config = Registry::getConfig();
      $exportBaseDirectory = $config->export->directory;
      $exportDirectory = $exportBaseDirectory
        . DIRECTORY_SEPARATOR . md5($exportName);
      $exportZipFile = $exportBaseDirectory
        . DIRECTORY_SEPARATOR . md5($exportName)
        . DIRECTORY_SEPARATOR . md5($exportName)
        . '.' . $exportBusiness::EXPORT_FILE_EXTENSION;
      $exportZipFileName = $exportName . '.' . $exportBusiness::EXPORT_FILE_EXTENSION;
      if (!is_dir($exportDirectory) || !file_exists($exportZipFile)) {
        $error['success'] = false;
        $error['errors'] = array(
          'code' => 1,
          'message' => null
        );

        if (!is_dir($exportDirectory)) {
          $error['errors']['message'] = sprintf(
              "Export Verzeichniss '%s' existiert nicht",
              $exportDirectory
          );
        } elseif (!file_exists($exportZipFile)) {
          $error['errors']['message'] = sprintf(
              "Export Datei '%s' existiert nicht",
              $exportZipFile
          );
        }

        $this->view->error = json_encode($error);
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_helper->viewRenderer('error');
      } else {
        $fileModificationTime = filemtime($exportZipFile);
        $fileModificationTimeForHeader  = gmdate(
            'D, j M Y H:i:s T',
            $fileModificationTime
        );

        $response = $this->getResponse();
        $response->setHeader(
            'Content-Type',
            Mimetype::getMimetype($exportZipFile)
        );
        $response->setHeader(
            'Content-Disposition',
            'attachment; filename="' . $exportZipFileName . '"'
        );
        $response->setHeader('Last-Modified', $fileModificationTimeForHeader);
        $response->setHeader('Content-Length', filesize($exportZipFile));

        $response->setOutputBodyCallback(
            function ($response, $chunkSize) use ($exportZipFile, $exportDirectory) {
              if (is_file($exportZipFile) && is_readable($exportZipFile)) {
                $fd=fopen($exportZipFile, 'rb');
                if ($fd !== false) {
                  while ((!feof($fd)) && (!connection_aborted())) {
                    print(fread($fd, $chunkSize));
                    $response->flushOutput();
                  }
                  fclose($fd);
                }
              }
              if (is_file($exportZipFile)) {
                @unlink($exportZipFile);
              }
              if (is_dir($exportDirectory)) {
                @rmdir($exportDirectory);
              }
            }
        );
      }
    }
  }

  public function getAction()
  {
    $zendResponse = $this->getResponse();
    $httpRequest = new ZendBasedHttpRequest($this->getRequest());
    $mediaResponse = $this->getBusiness()->createMediaResponse($httpRequest);
    $zendResponse->setHttpResponseCode($mediaResponse->getResponseCode());
    foreach ($mediaResponse->getHeaders() as $headerName => $headerValue) {
      $zendResponse->setHeader($headerName, $headerValue, true);
    }
    $zendResponse->setOutputBodyCallback(
        function ($response, $chunkSize) use (&$mediaResponse) {
          $mediaResponse->outputBody();
        }
    );
  }


  public function getbuildAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cdn', 'GetBuild');

    $buildfilePath = $this->getBusiness()->getBuildfilePath(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getId()
    );

    $buildfilePathinfo = pathinfo($buildfilePath);

    if ($validatedRequest->getName() === null) {
      $buildfileName = $buildfilePathinfo['basename'];
    } else {
      $buildfileName = sprintf(
          '%s.%s',
          $validatedRequest->getName(),
          $buildfilePathinfo['extension']
      );
    }

    $buildfileName = $this->getBusiness()
                          ->cleanFileNameForResponse($buildfileName);

    $response = $this->getResponse();
    $response->setHeader('Content-Type', Mimetype::getMimetype($buildfilePath));
    $response->setHeader('Cache-Control', 'max-age=0, must-revalidate, private');
    $response->setHeader('Content-Disposition', 'inline; filename="' . $buildfileName . '"');
    $response->setHeader('Accept-Ranges', 'bytes');
    $response->setOutputBodyCallback(
        function ($response, $chunkSize) use ($buildfilePath) {
          if (is_file($buildfilePath) && is_readable($buildfilePath)) {
            $fd=fopen($buildfilePath, 'rb');
            if ($fd !== false) {
              while ((!feof($fd)) && (!connection_aborted())) {
                print(fread($fd, $chunkSize));
                $response->flushOutput();
              }
              fclose($fd);
            }
          }
        }
    );
  }

  /**
   * Gibt das Vorschaubild eines Screenshots aus
   */
  public function getscreenAction()
  {
    $validatedRequest = $this->getValidatedRequest('Cdn', 'GetScreen', true);
    if ($validatedRequest === false) {
      return;
    }

    $screenStreamFilePath = $this->getBusiness()->getScreenStreamFilePath(
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getId(),
        $validatedRequest->getType(),
        array(
        'width' => $validatedRequest->getWidth(),
        'height' => $validatedRequest->getHeight()
        )
    );

    if (empty($screenStreamFilePath) || !file_exists($screenStreamFilePath)) {
      $screenStreamFilePath = $this->getBusiness()->getDefaultScreenshot();
      $this->getResponse()->setHttpResponseCode(404);
    }

      $screenFileName = $this->getBusiness()->getScreenFileName(
          $validatedRequest->getId(),
          $validatedRequest->getType()
      );

      $response = $this->getResponse();
      $response->setHeader('Content-Type', Mimetype::getMimetype($screenStreamFilePath));
      $response->setHeader('Cache-Control', 'max-age=0, must-revalidate, private');
      $response->setHeader('Content-Disposition', 'inline; filename="' . $screenFileName . '"');
      $response->setOutputBodyCallback(
          function ($response, $chunkSize) use ($screenStreamFilePath) {
            if (is_file($screenStreamFilePath) && is_readable($screenStreamFilePath)) {
              $fd=fopen($screenStreamFilePath, 'rb');
              if ($fd !== false) {
                while ((!feof($fd)) && (!connection_aborted())) {
                  print(fread($fd, $chunkSize));
                  $response->flushOutput();
                }
                fclose($fd);
              }
            }
          }
      );
  }
}
