<?php
namespace Cms\Validator;

use Cms\Validator\UnitArray as UnitArrayValidator;

/**
 * Komponententest für Cms\Validator\UnitArray
 *
 * @package      Cms
 * @subpackage   Validator
 */
class UnitArrayTest extends \PHPUnit_Framework_TestCase
{
 
  /**
	* 
	* @return \Cms\Validator\UnitArray
	*/
  protected function getTemplateValidator() {
  	$templateMUnit = new \Orm\Data\Template\MUnit();
  	return $this->getValidator($templateMUnit);
  }
  
  /**
   * 
   * @return \Cms\Validator\UnitArray
   */
  protected function getPageValidator() {
  	$templateMUnit = new \Orm\Data\Page\MUnit();
  	return $this->getValidator($templateMUnit);
  }
  
  /**
   * @param $munit the MUnit type that should be uses
   * @return \Cms\Validator\UnitArray
   */
  protected function getValidator($munit) {
  	return new UnitArrayValidator($munit);
  }
	
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider validTemplateJSON
   */
  public function testValidTemplateUnits($unit)
  {
  	$unitArrayValidator = $this->getTemplateValidator();
    $this->assertTrue($unitArrayValidator->isValid($unit));
  }
  

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider invalidTemplateJSON
   */
  public function testInvalidTemplateUnits($unit)
  {
  	$unitArrayValidator = $this->getTemplateValidator();
  	// PHP5.4 will raise a notice in Zend here
  	\PHPUnit_Framework_Error_Notice::$enabled = FALSE;
  	$this->assertFalse($unitArrayValidator->isValid($unit));
  }
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider validPageJSON
   */
  public function testValidPageUnits($unit)
  {
  	$unitArrayValidator = $this->getPageValidator();
  	$this->assertTrue($unitArrayValidator->isValid($unit));
  }
  
  
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider invalidPageJSON
   */
  public function testInvalidPageUnits($unit)
  {
  	$unitArrayValidator = $this->getPageValidator();
  	// PHP5.4 will raise a notice in Zend here 
  	\PHPUnit_Framework_Error_Notice::$enabled = FALSE;
  	$this->assertFalse($unitArrayValidator->isValid($unit));
  }
  
  
  /**
   * @return array
   */
  public function validTemplateJSON()
  {
  	$json=<<<EOF
[ {
"id": "MUNIT-723444f2-cb21-44d4-8136-54b2fcab99be-MUNIT",
"moduleId": "MODUL-23258527-f9bb-41c6-865a-a8ed23314dcc-MODUL",
"name": "Basismodul",
"description": "",
"formValues": {
	"titleSource": "navigation",
	"title": "",
	"description": "",
	"commentintern": "",
	"titlePrefix": "",
	"titleSuffix": " | Sonora",
	"resolutionLabel0": "Desktop",
	"resolution0": 960,
	"enableResolutions": true,
	"resolutionLabel1": "Tablet",
	"resolution1": 768,
	"resolutionLabel2": "Smartphone quer",
	"resolution2": 480,
	"resolutionLabel3": "Smartphone",
	"resolution3": 320,
	"cssCommon": "",
	"enablePHPSession": false,
	"debugRefreshAllCss": null,
	"debugShowPhpErrors": false },
"expanded": true,
"children": [
 { "id": "MUNIT-fecdbec2-8bfb-4b22-a851-f25b59143272-MUNIT",
   "moduleId": "MODUL-fddbab66-ae19-4750-a956-261d8c4dee7e-MODUL",
   "name": "Content Ⓢ②",
   "description": "",
   "formValues": {
   		"cssChildWidth0": "100%",
   		"cssHSpace0": 10,
   		"cssVSpace0": 40,
   		"cssHeight0": 0,
   		"cssDisplayNone0": false,
   		"cssData": "[{\"global\":[[\"display: table-cell; width: 10px;\",\" > .boxSpacer:nth-child(1n)\",true],[\"height: auto; display: table;\",null,true]],\"live\":[[\"display: table-cell; width: 100%;\",\" > .r0cell1\",true],[\"display: table-cell; width: 10px;\",\" > .r0cell1 + .boxSpacer\",true],[\"display: table-row; width: 0; height: 40px;\",\" > .r0cell1 + .boxSpacer\",true]],\"edit\":[[\"display: table-cell;\",\" > .boxPreview:nth-child(1n)\",true],[\"display: table-cell;\",\" > .boxPreviewSpacer:nth-child(1n)\",true],[\"display: table-cell; width: 100%; caption-side: left;\",\" > .isCell:nth-child(2n+1)\",true],[\"display: none;\",\" > .boxPreview:nth-child(1n+3)\",true],[\"display: none;\",\" > .boxSpacer.boxPreviewSpacer:nth-child(1n+2)\",true],[\"display: table-row; width: 0; height: 40px;\",\" > .boxSpacer:nth-child(2n)\",true]]},{},{\"global\":[[\"display: table-cell; width: 10px;\",\" > .boxSpacer:nth-child(1n)\",true],[\"height: 70px; display: table;\",null,true]],\"live\":[[\"display: table-cell; width: 100%;\",\" > .r2cell1\",true],[\"display: table-cell; width: 10px;\",\" > .r2cell1 + .boxSpacer\",true],[\"display: table-row; width: 0; height: 20px;\",\" > .r2cell1 + .boxSpacer\",true]],\"edit\":[[\"display: table-cell;\",\" > .boxPreview:nth-child(1n)\",true],[\"display: table-cell;\",\" > .boxPreviewSpacer:nth-child(1n)\",true],[\"display: table-cell; width: 100%; caption-side: left;\",\" > .isCell:nth-child(2n+1)\",true],[\"display: none;\",\" > .boxPreview:nth-child(1n+3)\",true],[\"display: none;\",\" > .boxSpacer.boxPreviewSpacer:nth-child(1n+2)\",true],[\"display: table-row; width: 0; height: 20px;\",\" > .boxSpacer:nth-child(2n)\",true]]},{}]",
   		"cssEnableResolution1": false,
   		"cssChildWidth1": "100%",
   		"cssHSpace1": 10,
   		"cssVSpace1": 10,
   		"cssHeight1": 70,
   		"cssDisplayNone1": false,
   		"cssEnableResolution2": true,
   		"cssChildWidth2": "100%",
   		"cssHSpace2": 10,
   		"cssVSpace2": 20,
   		"cssHeight2": 70,
   		"cssDisplayNone2": false,
   		"cssEnableResolution3": false,
   		"cssChildWidth3": "100%",
   		"cssHSpace3": 10,
   		"cssVSpace3": 10,
   		"cssHeight3": 70,
   		"cssDisplayNone3": false
       },
   	"expanded": true,
   	"children": [],
   	"deletable": false,
   	"htmlClass": "theHtlmClass",
   	"ghostContainer": false,
   	"visibleFormGroups": [] } ],
   "deletable": false,
   "ghostContainer": false,
   "visibleFormGroups": []
  } ]
EOF;
	  	
    return array(
      array(json_decode($json, true)),
    );
  }
  
  /**
   * @return array
   */
  public function invalidTemplateJSON()
  {
  	$json=<<<EOF
[ {
"id": "MUNIT-723444f2-cb21-44d4-8136-54b2fcab99be-MUNIT",
"moduleId": "MODUL-23258527-f9bb-41c6-865a-a8ed23314dcc-MODUL",
"name": "Basismodul",
"description": "",
"formValues": {
	"titleSource": "navigation",
	"title": "",
	"description": "",
	"commentintern": "",
	"titlePrefix": "",
	"titleSuffix": " | Sonora",
	"resolutionLabel0": "Desktop",
	"resolution0": 960,
	"enableResolutions": true,
	"resolutionLabel1": "Tablet",
	"resolution1": 768,
	"resolutionLabel2": "Smartphone quer",
	"resolution2": 480,
	"resolutionLabel3": "Smartphone",
	"resolution3": 320,
	"cssCommon": "",
	"enablePHPSession": false,
	"debugRefreshAllCss": null,
	"debugShowPhpErrors": false },
"expanded": true,
"children": [
 { "id": "MUNIT-fecdbec2-8bfb-4b22-a851-f25b59143272-MUNIT",
   "moduleId": "MODUL-fddbab66-ae19-4750-a956-261d8c4dee7e-MODUL",
   "name": "Content Ⓢ②",
   "description": "",
   "VeryBadKey" : "I am bad, very bad",
   "formValues": {
   		"cssChildWidth0": "100%",
   		"cssHSpace0": 10,
   		"cssVSpace0": 40,
   		"cssHeight0": 0,
   		"cssDisplayNone0": false,
   		"cssData": "[{\"global\":[[\"display: table-cell; width: 10px;\",\" > .boxSpacer:nth-child(1n)\",true],[\"height: auto; display: table;\",null,true]],\"live\":[[\"display: table-cell; width: 100%;\",\" > .r0cell1\",true],[\"display: table-cell; width: 10px;\",\" > .r0cell1 + .boxSpacer\",true],[\"display: table-row; width: 0; height: 40px;\",\" > .r0cell1 + .boxSpacer\",true]],\"edit\":[[\"display: table-cell;\",\" > .boxPreview:nth-child(1n)\",true],[\"display: table-cell;\",\" > .boxPreviewSpacer:nth-child(1n)\",true],[\"display: table-cell; width: 100%; caption-side: left;\",\" > .isCell:nth-child(2n+1)\",true],[\"display: none;\",\" > .boxPreview:nth-child(1n+3)\",true],[\"display: none;\",\" > .boxSpacer.boxPreviewSpacer:nth-child(1n+2)\",true],[\"display: table-row; width: 0; height: 40px;\",\" > .boxSpacer:nth-child(2n)\",true]]},{},{\"global\":[[\"display: table-cell; width: 10px;\",\" > .boxSpacer:nth-child(1n)\",true],[\"height: 70px; display: table;\",null,true]],\"live\":[[\"display: table-cell; width: 100%;\",\" > .r2cell1\",true],[\"display: table-cell; width: 10px;\",\" > .r2cell1 + .boxSpacer\",true],[\"display: table-row; width: 0; height: 20px;\",\" > .r2cell1 + .boxSpacer\",true]],\"edit\":[[\"display: table-cell;\",\" > .boxPreview:nth-child(1n)\",true],[\"display: table-cell;\",\" > .boxPreviewSpacer:nth-child(1n)\",true],[\"display: table-cell; width: 100%; caption-side: left;\",\" > .isCell:nth-child(2n+1)\",true],[\"display: none;\",\" > .boxPreview:nth-child(1n+3)\",true],[\"display: none;\",\" > .boxSpacer.boxPreviewSpacer:nth-child(1n+2)\",true],[\"display: table-row; width: 0; height: 20px;\",\" > .boxSpacer:nth-child(2n)\",true]]},{}]",
   		"cssEnableResolution1": false,
   		"cssChildWidth1": "100%",
   		"cssHSpace1": 10,
   		"cssVSpace1": 10,
   		"cssHeight1": 70,
   		"cssDisplayNone1": false,
   		"cssEnableResolution2": true,
   		"cssChildWidth2": "100%",
   		"cssHSpace2": 10,
   		"cssVSpace2": 20,
   		"cssHeight2": 70,
   		"cssDisplayNone2": false,
   		"cssEnableResolution3": false,
   		"cssChildWidth3": "100%",
   		"cssHSpace3": 10,
   		"cssVSpace3": 10,
   		"cssHeight3": 70,
   		"cssDisplayNone3": false
       },
   	"expanded": true,
   	"children": [],
   	"deletable": false,
   	"ghostContainer": false,
   	"visibleFormGroups": [] } ],
   "deletable": false,
   "ghostContainer": false,
   "visibleFormGroups": []
  } ]
EOF;
  
    return array(
  	  array(json_decode($json, true)),
  	);
  }
  
  /**
   * @return array
   */
  public function invalidPageJSON()
  {
  	$json=<<<EOF
  [ { "moduleId": "MODUL-23258527-f9bb-41c6-865a-a8ed23314dcc-MODUL", "name": "Basismodul", "description": "", "formValues": {}, "expanded": true, "templateUnitId": "MUNIT-67636792-674a-4128-be6f-fe9795dbd4dc-MUNIT", "id": "MUNIT-e4c4b3a0-3817-4d74-a34f-ce4a4b21e914-MUNIT", "children": [ { "moduleId": "MODUL-77a07c5a-e34a-4492-8dee-6f56e0368d1b-MODUL", "name": "Leer", "description": "", "formValues": {}, "expanded": true, "templateUnitId": "MUNIT-6ff513a4-1924-48c8-ba75-df21abd04ac2-MUNIT", "id": "MUNIT-c3d8ab6a-956c-4514-87c1-900457a2c96d-MUNIT", "broken": true, "htmlClass": "theHtlmClass", "children": [] } ] } ]
EOF;
  	return array(
  	  array( json_decode($json, true)),
  			);
  }
  
  /**
   * @return array
   */
  public function validPageJSON()
  {
  	 
  	$json=<<<EOF
  [ { "moduleId": "MODUL-23258527-f9bb-41c6-865a-a8ed23314dcc-MODUL", "name": "Basismodul", "description": "", "formValues": {}, "expanded": true, "templateUnitId": "MUNIT-67636792-674a-4128-be6f-fe9795dbd4dc-MUNIT", "id": "MUNIT-e4c4b3a0-3817-4d74-a34f-ce4a4b21e914-MUNIT", "children": [ { "moduleId": "MODUL-77a07c5a-e34a-4492-8dee-6f56e0368d1b-MODUL", "name": "Leer", "description": "", "formValues": {}, "expanded": true, "templateUnitId": "MUNIT-6ff513a4-1924-48c8-ba75-df21abd04ac2-MUNIT", "id": "MUNIT-c3d8ab6a-956c-4514-87c1-900457a2c96d-MUNIT", "inserted": true, "htmlClass": "theHtlmClass", "children": [] } ] } ]
EOF;
  	return array(
  			array( json_decode($json, true)),
  	);
  }
}