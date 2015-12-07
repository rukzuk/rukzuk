<?php
namespace Cms\Reparser;

use Cms\Reparser\UnitExtractor as CmsUnitExtractor;
use \Test\Rukzuk\AbstractTestCase;

/**
 * Komponententest für Cms\Reparser\UnitExtractor
 *
 * @package      Cms
 * @subpackage   Reparser
 */

class UnitExtractorTest extends AbstractTestCase
{
  /**
   * @test
   * @group library
   * @dataProvider pageContentProvider
   */
  public function getUnitsFromPageContentSuccess($content)
  {
    $units = CmsUnitExtractor::getUnitsFromPageContent($content);

    $this->assertInternalType('array', $units);
    $this->assertSame(10, count($units['units']));
    $this->assertSame(10, count($units['tunits']));
    $this->assertArrayHasKey('MUNIT-1624dee4-9e5e-4701-ac5b-bfad188aeb05-MUNIT', $units['units']);
    $this->assertArrayHasKey('MUNIT-f5235e6c-208a-4525-a8e7-7c1f64639a01-MUNIT', $units['units']);
    $this->assertArrayHasKey('MUNIT-4c40334a-86c6-4b79-a2c9-876589c9902d-MUNIT', $units['tunits']);
    $this->assertArrayHasKey('MUNIT-9bc4a3c3-5bcd-4dbb-89c6-97b20fd93a07-MUNIT', $units['tunits']);
    $this->assertSame('TT Basismodul', $units['units']['MUNIT-1624dee4-9e5e-4701-ac5b-bfad188aeb05-MUNIT']['name']);
    $this->assertSame('TT Container', $units['units']['MUNIT-f5235e6c-208a-4525-a8e7-7c1f64639a01-MUNIT']['name']);
  }

  /**
   * @test
   * @group library
   * @dataProvider templateContentProvider
   */
  public function getUnitsFromTemplateContentSuccess($content)
  {
    $units = CmsUnitExtractor::getUnitsFromTemplateContent($content);

    $this->assertInternalType('array', $units);
    $this->assertSame(6, count($units['units']));
    $this->assertArrayHasKey('MUNIT-7c470156-7a6c-4075-90ab-e8d8637ab3b5-MUNIT', $units['units']);
    $this->assertArrayHasKey('MUNIT-371aeeb6-903b-4d43-876d-78497475d716-MUNIT', $units['units']);
    $this->assertSame('UVK Basismodul', $units['units']['MUNIT-7c470156-7a6c-4075-90ab-e8d8637ab3b5-MUNIT']['name']);
    $this->assertSame('UVK Bildgalerie', $units['units']['MUNIT-371aeeb6-903b-4d43-876d-78497475d716-MUNIT']['name']);
  }

  /**
   * @return string
   */
  public function pageContentProvider()
  {
    return array(array('[{"id":"MUNIT-1624dee4-9e5e-4701-ac5b-bfad188aeb05-MUNIT","name":"TT Basismodul","moduleId":"MODUL-0c1e62c1-023c-42c7-8628-f2cb4236eb08-MODUL","formValues":{"title":"","description":"","bgcolor":"#eeeeee","bgrepeat":"repeat","bgattachment":"nein","cbgcolor":"#ffffff","cbgrepeat":"repeat","cbgattachment":"scroll"},"deletable":false,"readonly":true,"expanded":true,"children":[{"id":"MUNIT-f5235e6c-208a-4525-a8e7-7c1f64639a01-MUNIT","name":"TT Container","moduleId":"MODUL-b9d676f5-b381-4a21-8410-59cd8eb8faf3-MODUL","formValues":{"containerwidth":16,"enabledim":true,"height":"200px","minheight":"100px","enablepadding":false,"paddingleft":0,"paddingright":0,"paddingtop":0,"paddingbottom":0,"enablemargin":true,"marginleft":10,"marginright":10,"margintop":10,"marginbottom":10,"showbgimage":true,"bgimage":"MDB-1d221a78-f001-4382-b5ea-465f81b3a036-MDB","bgrepeat":"no-repeat","bgposition":"0 0","enablebgcolor":false,"bgcolor":"transparent","enableborderleft":false,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":false,"borderwidthright":0,"bordercolorright":"transparent","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":true,"borderradius":10,"enablecustomcss":false},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[],"templateUnitId":"MUNIT-4c40334a-86c6-4b79-a2c9-876589c9902d-MUNIT"},{"id":"MUNIT-c7621328-4170-4dab-a210-c28a3ede90fc-MUNIT","name":"Hauptnavigation","moduleId":"MODUL-b9d676f5-b381-4a21-8410-59cd8eb8faf3-MODUL","formValues":{"containerwidth":16,"enabledim":true,"height":"auto","minheight":"50px","enablepadding":false,"paddingleft":0,"paddingright":0,"paddingtop":0,"paddingbottom":0,"enablemargin":true,"marginleft":10,"marginright":10,"margintop":0,"marginbottom":11,"showbgimage":false,"bgrepeat":"repeat","bgposition":"0 0","enablebgcolor":true,"bgcolor":"#ccc","enableborderleft":false,"borderwidthleft":1,"bordercolorleft":"#aaa","borderstyleleft":"solid","enableborderright":false,"borderwidthright":2,"bordercolorright":"#bbb","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":true,"borderradius":10,"enablecustomcss":false},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[{"id":"MUNIT-4fc4aa5c-6b1c-4606-9918-8fa945576351-MUNIT","name":"TT Hauptnavigation","moduleId":"MODUL-f7c2586f-9655-44d6-9c09-8d296694c26c-MODUL","formValues":{"enablefont":true,"fontfamily":"arial, helvetica","fontcolor":"#fff","fontsize":"20px","enablepadding":true,"paddingleft":25,"paddingright":20,"paddingtop":0,"paddingbottom":0,"enablemargin":true,"marginleft":10,"marginright":10,"margintop":10,"marginbottom":0,"showbgimage":true,"bgimage":"MDB-39102310-f49b-400d-811f-e4bc91844e69-MDB","bgrepeat":"no-repeat","bgposition":"0 5px","enablebgcolor":false,"bgcolor":"transparent","enableborderleft":false,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":true,"borderwidthright":1,"bordercolorright":"#aaa","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":false,"borderradius":0,"enablecustomcss":false,"customcss":"","enabletest":true,"testitems":"Produkte,Service,\u00dcber uns,Kontakt"},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[],"templateUnitId":"MUNIT-9bc4a3c3-5bcd-4dbb-89c6-97b20fd93a07-MUNIT"}],"templateUnitId":"MUNIT-fadfb5c2-9cbf-4685-8313-cce971284e64-MUNIT"},{"id":"MUNIT-059f18e1-4ee1-41cb-b9d2-124f07b821c3-MUNIT","name":"Inhalt","moduleId":"MODUL-b9d676f5-b381-4a21-8410-59cd8eb8faf3-MODUL","formValues":{"containerwidth":12,"minheight":"150px","bgcolor":"#eeeeee","padding":"10px 0 10px 10px","margin":"10px 10px 10px 0","showbgimage":false,"bgrepeat":"repeat","bgposition":"0 0","csscode":""},"expanded":true,"children":[{"id":"MUNIT-6360e1ab-ecf2-4d93-a890-ba7e7f79545f-MUNIT","name":"TT \u00dcberschrift","moduleId":"MODUL-205f7cd8-19f7-47e0-9058-5f39dda3173c-MODUL","formValues":{"headline":"Titel"},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[],"templateUnitId":"MUNIT-9dd6ffc3-fb8e-4c31-aa31-276ee3803935-MUNIT"},{"id":"MUNIT-e285d913-a02a-4261-bf92-51e5f96bc7a1-MUNIT","name":"TT Text","moduleId":"MODUL-6568753a-3525-478d-9787-db76c7011ea5-MODUL","formValues":{"text":"<p>Geben Sie hier den Artikeltext ein<\/p>","enabledim":true,"width":"auto","height":"auto","minheight":"100px","enablepadding":true,"paddingleft":0,"paddingright":0,"paddingtop":0,"paddingbottom":0,"enablemargin":true,"marginleft":0,"marginright":0,"margintop":0,"marginbottom":0,"showbgimage":true,"bgrepeat":"repeat","bgposition":"0 0","enablebgcolor":true,"bgcolor":"transparent","enableborderleft":true,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":true,"borderwidthright":0,"bordercolorright":"transparent","borderstyleright":"solid","enablebordertop":true,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":true,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":true,"borderradius":0,"enablefont":true,"fontfamily":"arial, helvetica","fontcolor":"#000000","fontsize":"12px","enablecustomcss":true},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":false,"children":[],"templateUnitId":"MUNIT-6cca0a99-482e-4bcb-b8b6-81dd34609f72-MUNIT"}],"templateUnitId":"MUNIT-c5799ab7-358f-4476-b631-389036a32bab-MUNIT"},{"id":"MUNIT-27f95a80-4617-47c3-90bc-294bcb8c68b5-MUNIT","name":"TT Container","moduleId":"MODUL-b9d676f5-b381-4a21-8410-59cd8eb8faf3-MODUL","formValues":{"containerwidth":4,"enabledim":true,"height":"auto","minheight":"100px","enablepadding":false,"paddingleft":0,"paddingright":0,"paddingtop":0,"paddingbottom":0,"enablemargin":false,"marginleft":0,"marginright":0,"margintop":0,"marginbottom":0,"showbgimage":false,"bgrepeat":"repeat","bgposition":"0 0","enablebgcolor":true,"bgcolor":"#eee","enableborderleft":false,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":false,"borderwidthright":0,"bordercolorright":"transparent","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":true,"borderradius":10,"enablecustomcss":false},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[],"templateUnitId":"MUNIT-1c6ac91b-fb79-45c7-90ae-a894f3be6def-MUNIT"},{"id":"MUNIT-9984cb87-4dfd-4082-8742-e6e52002947c-MUNIT","name":"Footer","moduleId":"MODUL-b9d676f5-b381-4a21-8410-59cd8eb8faf3-MODUL","formValues":{"containerwidth":16,"enabledim":true,"height":"auto","minheight":"0","enablepadding":false,"paddingleft":0,"paddingright":0,"paddingtop":0,"paddingbottom":0,"enablemargin":false,"marginleft":0,"marginright":0,"margintop":0,"marginbottom":0,"showbgimage":false,"bgrepeat":"repeat","bgposition":"0 0","enablebgcolor":false,"bgcolor":"#eeeeee","enableborderleft":false,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":false,"borderwidthright":0,"bordercolorright":"transparent","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":false,"borderradius":0,"enablecustomcss":false},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[{"id":"MUNIT-1d89b199-218e-4b5f-87d0-1a76b2eed5d7-MUNIT","name":"TT Text","moduleId":"MODUL-6568753a-3525-478d-9787-db76c7011ea5-MODUL","formValues":{"text":"<p style=\"text-align: right;\">&copy; 2010<\/p>","enabledim":true,"width":"auto","height":"auto","minheight":"20px","enablepadding":true,"paddingleft":0,"paddingright":9,"paddingtop":0,"paddingbottom":0,"enablemargin":true,"marginleft":0,"marginright":0,"margintop":0,"marginbottom":10,"showbgimage":false,"bgrepeat":"repeat","bgposition":"0 0","enablebgcolor":true,"bgcolor":"#96bcdf","enableborderleft":false,"borderwidthleft":0,"bordercolorleft":"transparent","borderstyleleft":"solid","enableborderright":false,"borderwidthright":0,"bordercolorright":"transparent","borderstyleright":"solid","enablebordertop":false,"borderwidthtop":0,"bordercolortop":"transparent","borderstyletop":"solid","enableborderbottom":false,"borderwidthbottom":0,"bordercolorbottom":"transparent","borderstylebottom":"solid","enablecorners":true,"borderradius":5,"enablefont":true,"fontfamily":"arial, helvetica","fontcolor":"#fff","fontsize":"12px","enablecustomcss":false},"deletable":false,"readonly":false,"ghostContainer":false,"expanded":true,"children":[],"templateUnitId":"MUNIT-e94a443b-84d9-4f87-a97f-1cf943d5f459-MUNIT"}],"templateUnitId":"xnode-1202"}],"templateUnitId":"MUNIT-396cd60c-cefb-4b42-9cfd-4d4ba02b4433-MUNIT"}]'));
  }

  /**
   * @return string
   */
  public function templateContentProvider()
  {
    return array(array('[{"id":"MUNIT-7c470156-7a6c-4075-90ab-e8d8637ab3b5-MUNIT","name":"UVK Basismodul","moduleId":"MODUL-3832aeaf-ed3c-4175-b8f3-21fb6ff83dab-MODUL","formValues":{},"expanded":true,"children":[{"id":"MUNIT-882ae41f-53bc-45cc-9b70-bdd2920b0290-MUNIT","name":"UVK Amazon Widget","moduleId":"MODUL-8767dd13-29be-4606-9156-070d611789be-MODUL","formValues":{},"expanded":true,"children":[]},{"id":"MUNIT-c015a219-af1b-4ba3-a484-6c08d61fb2ed-MUNIT","name":"UVK rechte Spalte","moduleId":"MODUL-e5d8c010-dc9b-466c-ac4a-87fb6ee21f2d-MODUL","formValues":{"color":"#7cb1da"},"deletable":false,"readonly":false,"ghostContainer":false,"visibleFormGroups":[],"expanded":true,"children":[]},{"id":"MUNIT-22661593-c862-4ae3-9ca2-d8a33c9c6656-MUNIT","name":"UVK Text","moduleId":"MODUL-48b9e33b-8186-49bb-8cef-7acfbf82a08b-MODUL","formValues":{},"expanded":true,"children":[]},{"id":"MUNIT-ff3bc334-d5ff-48f7-8761-de694b2d9187-MUNIT","name":"UVK Text","moduleId":"MODUL-48b9e33b-8186-49bb-8cef-7acfbf82a08b-MODUL","formValues":{},"expanded":true,"children":[]},{"id":"MUNIT-371aeeb6-903b-4d43-876d-78497475d716-MUNIT","name":"UVK Bildgalerie","moduleId":"MODUL-4de3a375-aac7-455b-8858-82f2752cd4bf-MODUL","formValues":{},"expanded":true,"children":[]}]}]'));
  }
}