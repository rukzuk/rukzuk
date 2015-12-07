<?php
namespace Application\Controller\Template;

use Test\Seitenbau\EditTemplateControllerTestCase as EditTemplateControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * TemplateController EditMeta-Test
 *
 * @package      Test
 * @subpackage   Controller
 */
class EditMetaTest extends EditTemplateControllerTestCase
{
  protected $websiteId = 'SITE-30490289-dddb-4501-879f-9c6c7965f871-SITE';
  protected $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
  protected $serviceUrl = '/template/editMeta/params/%s';

  
  /**
   * @return array
   */
  public function invalidIdsProvider()
  {
    return array(
      array('15'),
      array('some_test_value'),
      array('MODUL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-MODUL'),
    );
  }

  /**
   * @return array
   */
  public function requiredParamsProvider()
  {
    return array(
      array( array('runid', 'id', 'websiteid') )
    );
  }
  
  /**
   * @return array
   */
  public function editDataProvider()
  {
    return array(
      array(
        false,
        'params' => array(
          'id' => 'TPL-4mrap53m-2al2-4g1f-a49b-4a93in3f70pd-TPL',
          'name' => 'name_Edit',
          'content' => array(
            (object) array(
              'id' => 'MUNIT-00000000-0000-0000-0000-000000000000-MUNIT',
              'name' => 'Test-Basismodul',
              'moduleId' => 'MODUL-00000000-0000-0000-0000-000000000000-MODUL',
            )
          )
        ),
        'expectedData' => array(
          'name' => 'name_Edit',
        )
      )
    );
  }
}