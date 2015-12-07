<?php


namespace Render\APIs\APIv1;


use Test\Render\AbstractAPITestCase;

class EditableHtmlTagTest extends AbstractAPITestCase
{
  /**
   * Checks if href replaced and data-cms attributes exists
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @dataProvider provider_test_getEditableTag
   */
  public function test_getEditableTag($isEditMode, $key, $tag, $attributes, $rawHtml, $expectedHtml)
  {
    // ARRANGE
    $pageId1 = 'PAGE-00000000-0000-0000-0000-000000000001-PAGE';
    $pageId2 = 'PAGE-00000000-0000-0000-0000-000000000002-PAGE';
    $pageId3 = 'PAGE-00000000-0000-0000-0000-000000000003-PAGE';
    $navigationMock = $this->getNavigationMock(array(
      '' => $this->createPageMock(''),
      $pageId1 => $this->createPageMock($pageId1),
      $pageId2 => $this->createPageMock($pageId2),
      $pageId3 => $this->createPageMock($pageId3),
    ));

    $mediaId = 'MDB-00000000-0000-0000-0000-000000000001-MDB';
    $mediaItemMock = $this->createMediaItemMock($mediaId);

    $renderAPIMock = $this->createRenderAPIMock();
    $renderAPIMock->expects($this->any())
      ->method('isEditMode')
      ->will($this->returnValue($isEditMode));
    $renderAPIMock->expects($this->any())
      ->method('getNavigation')
      ->will($this->returnValue($navigationMock));
    $renderAPIMock->expects($this->any())
      ->method('getMediaItem')
      ->with($this->equalTo($mediaId))
      ->will($this->returnValue($mediaItemMock));

    $editableHtmlTag = new EditableHtmlTag($renderAPIMock);

    // ACT
    $actualHtml = $editableHtmlTag->getEditableTag($key, $tag, $attributes, $rawHtml);

    // ASSERT
    $this->assertEquals($expectedHtml, $actualHtml);
  }

  /**
   * @return array
   */
  public function provider_test_getEditableTag()
  {
    return array(
      array(true, 'text', 'div', 'class="text"', $this->getRawHtmlCode(), $this->getExpectedHtmlForEditMode()),
      array(false, 'text', 'div', 'class="text"', $this->getRawHtmlCode(), $this->getExpectedHtmlForNoneEditMode()),
    );
  }

  /**
   * @return string
   */
  protected function getRawHtmlCode()
  {
    return <<<EOTESTDATA
<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>
<p>
  <a href="http://www.rukzuk.com/this_link_should_not_be_replaced" target="_blank">None TinyMCE Link - link should not be modify</a>
</p>
<p>
  <a href="this_external_link_will_be_replaced" data-cms-link="http://www.rukzuk.com" data-cms-link-type="external">External Link</a>
</p>
<p>
  <a data-cms-link="heiko.schwarz@rukzuk.com" href="this_mailto_link_will_be_replaced" data-cms-link-type="mail">Mail Link</a>
</p>
<p>
  <a data-cms-link="PAGE-00000000-0000-0000-0000-000000000001-PAGE" data-cms-link-type="internalPage" href="this_internalpage_link_will_be_replaced">Internal Link</a>
</p>
<p>
  <a href="this_internalpage_link_with_anchar_will_be_replaced" data-cms-link-anchor="#sprungmarke" data-cms-link="PAGE-00000000-0000-0000-0000-000000000002-PAGE" data-cms-link-type="internalPage">Internal Link</a>
</p>
<p>
  <a data-cms-link-type="internalMediaDownload" href="this_internalmediadownload_link_will_be_replaced" data-cms-link="MDB-00000000-0000-0000-0000-000000000001-MDB">Download Link</a>
</p>
<p>
  <a href="this_internalmedia_link_will_be_replaced" target="_blank" data-cms-link-type="internalMedia" data-cms-link="MDB-00000000-0000-0000-0000-000000000001-MDB">Stream Link</a>
</p>
<p>
  <a data-cms-link-type="internalPage" href="this_internalpage_link_will_be_replaced">Internal Link - missing data-cms-link</a>
</p>
<p>
  <a data-cms-link="PAGE-00000000-0000-0000-0000-000000000003-PAGE" href="http://www.rukzuk.com/this_link_should_not_be_replaced/2" target="_blank">missing link type - link should not be modify</a>
</p>
EOTESTDATA;
  }

  /**
   * @return string
   */
  protected function getExpectedHtmlForEditMode()
  {
    return <<<EOTESTDATA
<div class="text" data-cms-editable="text"><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>
<p>
  <a href="http://www.rukzuk.com/this_link_should_not_be_replaced" target="_blank">None TinyMCE Link - link should not be modify</a>
</p>
<p>
  <a href="http://www.rukzuk.com" data-cms-link="http://www.rukzuk.com" data-cms-link-type="external">External Link</a>
</p>
<p>
  <a data-cms-link="heiko.schwarz@rukzuk.com" href="mailto:heiko.schwarz@rukzuk.com" data-cms-link-type="mail">Mail Link</a>
</p>
<p>
  <a data-cms-link="PAGE-00000000-0000-0000-0000-000000000001-PAGE" data-cms-link-type="internalPage" href="replaced_with_internalpage_link:PAGE-00000000-0000-0000-0000-000000000001-PAGE">Internal Link</a>
</p>
<p>
  <a href="replaced_with_internalpage_link:PAGE-00000000-0000-0000-0000-000000000002-PAGE#sprungmarke" data-cms-link-anchor="#sprungmarke" data-cms-link="PAGE-00000000-0000-0000-0000-000000000002-PAGE" data-cms-link-type="internalPage">Internal Link</a>
</p>
<p>
  <a data-cms-link-type="internalMediaDownload" href="replaced_with_internalmediadownload_link:MDB-00000000-0000-0000-0000-000000000001-MDB" data-cms-link="MDB-00000000-0000-0000-0000-000000000001-MDB">Download Link</a>
</p>
<p>
  <a href="replaced_with_internalmedia_link:MDB-00000000-0000-0000-0000-000000000001-MDB" target="_blank" data-cms-link-type="internalMedia" data-cms-link="MDB-00000000-0000-0000-0000-000000000001-MDB">Stream Link</a>
</p>
<p>
  <a data-cms-link-type="internalPage" href="replaced_with_internalpage_link:">Internal Link - missing data-cms-link</a>
</p>
<p>
  <a data-cms-link="PAGE-00000000-0000-0000-0000-000000000003-PAGE" href="http://www.rukzuk.com/this_link_should_not_be_replaced/2" target="_blank">missing link type - link should not be modify</a>
</p></div>
EOTESTDATA;
  }

  /**
   * @return string
   */
  protected function getExpectedHtmlForNoneEditMode()
  {
    return <<<EOTESTDATA
<div class="text"><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.&nbsp;Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>
<p>
  <a href="http://www.rukzuk.com/this_link_should_not_be_replaced" target="_blank">None TinyMCE Link - link should not be modify</a>
</p>
<p>
  <a href="http://www.rukzuk.com">External Link</a>
</p>
<p>
  <a href="mailto:heiko.schwarz@rukzuk.com">Mail Link</a>
</p>
<p>
  <a href="replaced_with_internalpage_link:PAGE-00000000-0000-0000-0000-000000000001-PAGE">Internal Link</a>
</p>
<p>
  <a href="replaced_with_internalpage_link:PAGE-00000000-0000-0000-0000-000000000002-PAGE#sprungmarke">Internal Link</a>
</p>
<p>
  <a href="replaced_with_internalmediadownload_link:MDB-00000000-0000-0000-0000-000000000001-MDB">Download Link</a>
</p>
<p>
  <a href="replaced_with_internalmedia_link:MDB-00000000-0000-0000-0000-000000000001-MDB" target="_blank">Stream Link</a>
</p>
<p>
  <a href="replaced_with_internalpage_link:">Internal Link - missing data-cms-link</a>
</p>
<p>
  <a data-cms-link="PAGE-00000000-0000-0000-0000-000000000003-PAGE" href="http://www.rukzuk.com/this_link_should_not_be_replaced/2" target="_blank">missing link type - link should not be modify</a>
</p></div>
EOTESTDATA;
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\APIs\APIv1\RenderAPI
   */
  protected function createRenderAPIMock()
  {
    return $this->getMockBuilder('\Render\APIs\APIv1\RenderAPI')
      ->disableOriginalConstructor()->getMock();
  }

  /**
   * @param $mediaId
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\APIs\APIv1\MediaItem
   */
  protected function createMediaItemMock($mediaId)
  {
    $mediaItemMock = $this->getMockBuilder('\Render\APIs\APIv1\MediaItem')
      ->disableOriginalConstructor()->getMock();
    $mediaItemMock->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('replaced_with_internalmedia_link:'.$mediaId));
    $mediaItemMock->expects($this->any())
      ->method('getDownloadUrl')
      ->will($this->returnValue('replaced_with_internalmediadownload_link:'.$mediaId));
    return $mediaItemMock;
  }

  /**
   * @param array $pages
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\APIs\APIv1\Navigation
   */
  private function getNavigationMock(array $pages)
  {
    $navigationMock = $this->getMockBuilder('\Render\APIs\APIv1\Navigation')
      ->disableOriginalConstructor()->getMock();
    $navigationMock->expects($this->any())
      ->method('getPage')
      ->will($this->returnCallback(function ($pageId) use (&$pages) {
        if (!isset($pages[$pageId])) {
          throw new \Exception('Missing page mock for page id "'.$pageId.'"');
        }
        return $pages[$pageId];
      }));
    return $navigationMock;
  }

  /**
   * @param string $pageId
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Render\APIs\APIv1\Page
   */
  private function createPageMock($pageId)
  {
    $pageMock = $this->getMockBuilder('\Render\APIs\APIv1\Page')
      ->disableOriginalConstructor()->getMock();
    $pageMock->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('replaced_with_internalpage_link:'.$pageId));
    return $pageMock;
  }
}
 