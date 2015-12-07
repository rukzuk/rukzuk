<?php
namespace Rukzuk\Modules;

class rz_google_analytics extends SimpleModule
{

  public function htmlHeadUnit($api, $unit, $moduleInfo)
  {
    $gaCode = '';
    $accountId = htmlentities($api->getFormValue($unit, 'trackingId'));

    if (!($api->isEditMode() || $api->isPreviewMode())) {
      $gaCode = "<script>
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', '".$accountId."' ]);
            _gaq.push(['_trackPageview']);

            (function () {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
                })();
            </script>";
    }
    return $gaCode;
  }
}
