/* global $, CMS */
define('jquery', function () {
    return $;
});

if (typeof window.CMS !== 'undefined') {
    define('CMS', CMS);
} else {
    define('CMS', {});
}
