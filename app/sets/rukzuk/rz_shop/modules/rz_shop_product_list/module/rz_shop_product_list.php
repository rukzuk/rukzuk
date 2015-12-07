<?php
namespace Rukzuk\Modules;

class rz_shop_product_list extends SimpleModule {

    public function renderContent($renderApi, $unit, $moduleInfo) {
        $listTag = new HtmlTagBuilder('ul', array(
            'class' => 'isProductList'
        ));
        echo $listTag->getOpenString();

        // show hint when no children inserted
        if ($renderApi->isEditMode() && count($renderApi->getChildren($unit)) === 0) {
            $i18n = new Translator($renderApi, $moduleInfo, $renderApi->getInterfaceLocale());
            $msg = $i18n->translate('error.pleaseInsertModules');
            $errorTag = new HtmlTagBuilder('div', array(
                'class' => 'RUKZUKmissingInputHint'
            ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
            echo $errorTag->toString();
        } else {
            $this->renderTeaserList($renderApi, $unit);
        }

        echo $listTag->getCloseString();
    }

    private function renderTeaserList ($renderApi, $unit) {
        $startPage = $renderApi->getFormValue($unit, 'teaserStartPage');
        $enableRecursive = $renderApi->getFormValue($unit, 'enableRecursive');

        $teaserItems = $this->getTeaserItemsRecursive($renderApi, $startPage, $enableRecursive);

        // sorting
        $teaserItems = $this->sortTeaserItems($renderApi, $unit, $teaserItems);

        // limit
        $teaserItems = $this->limitTeaserItems($renderApi, $unit, $teaserItems);

        $listItemTag = new HtmlTagBuilder('li', array(
            'class' => 'productTeaserItem'
        ));

        // TODO find better solution to communicate with rz_shop_product_detail
        global $currentProductPageId;

        if(count($teaserItems) === 0 && $renderApi->isEditMode()){
          echo $listItemTag->getOpenString();
          $renderApi->renderChildren($unit);
          echo $listItemTag->getCloseString();
        }else {
          foreach ($teaserItems as $item) {
            $currentProductPageId = $item['pageId'];

            echo $listItemTag->getOpenString();
            $renderApi->renderChildren($unit);
            echo $listItemTag->getCloseString();

            // show only one page due to multiple unit id problem
            if ($renderApi->isEditMode()) {
              break;
            }
          }
            // TODO fill teaser placeholders
            //echo '<div class="RUKZUKmissingInputHint"><button style="cursor: default;">(Teaser ' . ($iTeaserItemPos + 1) . ')</button></div>';
        }
        $currentProductPageId = null;
    }

    private function limitTeaserItems ($renderApi, $unit, &$teaserItems) {
        if ($renderApi->getFormValue($unit, 'enableLimit')) {
            $teaserItems = array_slice($teaserItems, $renderApi->getFormValue($unit, 'limitStart') - 1, $renderApi->getFormValue($unit, 'limit'));
        }

        return $teaserItems;
    }

    private function sortTeaserItems ($renderApi, $unit, &$teaserItems) {
        if ($renderApi->getFormValue($unit, 'enableSort')) {
            if ($renderApi->getFormValue($unit, 'sortBy') == 'field') {
                $sortField = $renderApi->getFormValue($unit, 'sortByField');

                usort($teaserItems, function ($a, $b) use (&$sortField) {
                    return strnatcasecmp($a[$sortField], $b[$sortField]);
                });

                if ($renderApi->getFormValue($unit, 'sortByFieldDirection') === 'desc') {
                    $teaserItems = array_reverse($teaserItems);
                }
            } else {
                shuffle($teaserItems);
            }
        }

        return $teaserItems;
    }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $renderApi
   * @param $startPageId
   * @param $enableRecursive
   * @return array
   */
    private function getTeaserItemsRecursive ($renderApi, $startPageId, $enableRecursive) {
        $items = array();
        $navigation = $renderApi->getNavigation();

        $currentPageId = $renderApi->getNavigation()->getCurrentPageId();
        $childrenIds = $navigation->getChildrenIds($startPageId);
        if (!empty($childrenIds)) {
            foreach ($childrenIds as $pageId) {
                // skip current page
                if ($currentPageId === $pageId) {
                  continue;
                }

                $page = $navigation->getPage($pageId);

                // page needs to be of type rz_shop_product
                if ($page->getPageType() === 'rz_shop_product') {
                  $attr = $page->getPageAttributes();
                  $price = isset($attr['price']) ? floatval($attr['price']) : 0;
                  $items[] = array(
                    'pageId' => $pageId,
                    'pageUrl' => $page->getUrl(),
                    'pageNavigationTitle' => $page->getNavigationTitle(),
                    'pageTitle' => $page->getTitle(),
                    'pageDate' => $page->getDate(),
                    'pageDescription' => $page->getDescription(),
                    'productPrice' => $price
                  );
                }

                //iterate recursively?
                if ($enableRecursive) {
                    $items = array_merge($items, $this->getTeaserItemsRecursive($renderApi, $pageId, $enableRecursive));
                }
            }
        }

        return $items;
    }
}