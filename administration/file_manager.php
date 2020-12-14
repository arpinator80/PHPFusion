<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: file_manager.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once '../maincore.php';
if (!checkrights('FM') || !defined('iAUTH') || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {redirect('../index.php');}

require_once THEMES.'templates/admin_header.php';
include LOCALE.LOCALESET.'admin/image_uploads.php';

opentable($locale['100']);
add_to_head('<script src="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.css">');
add_to_head('<script src="'.INCLUDES.'elFinder/js/elfinder.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/elfinder.min.css">');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/theme.css">');

$lang = '';
if (file_exists(INCLUDES.'elFinder/js/i18n/elFinder.'.$locale['filemanager'].'.js')) {
    $lang = ',lang: "'.$locale['filemanager'].'"';
}

add_to_jquery('
var elfinder_path = "//" + window.location.host + window.location.pathname.replace(/[\\\/][^\\\/]*$/, "") + "/";
$("#elfinder").elfinder({
    baseUrl: "'.fusion_get_settings('siteurl').'includes/elFinder/",
    url: "'.fusion_get_settings('siteurl').'includes/elFinder/php/connector.php'.fusion_get_aidlink().'"
    '.$lang.',
    themes: {
        "material-light": "themes/manifests/material-light.json",
        "material": "themes/manifests/material-default.json",
        "material-gray": "themes/manifests/material-gray.json"
    },
    ui: ["toolbar", "tree", "path", "stat"],
    uiOptions: {
        toolbar: [
            ["home", "back", "forward", "up", "reload"],
            ["mkdir", "mkfile", "upload"],
            ["open"],
            ["copy", "cut", "paste", "rm", "empty"],
            ["duplicate", "rename", "edit", "resize", "chmod"],
            ["quicklook", "info"],
            ["extract", "archive"],
            ["search"],
            ["view", "sort"],
            ["preference", "help"]
        ]
    }
});
');

echo '<div id="elfinder"></div>';
closetable();

require_once THEMES."templates/footer.php";
