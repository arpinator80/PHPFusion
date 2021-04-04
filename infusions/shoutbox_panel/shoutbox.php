<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: shoutbox.php
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

class ShoutBox {
    protected static $sb_settings = [];
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 4;
    private static $arch_limit = 20;
    private static $default_params = [
        'sbform_name' => 'sbform',
        'sb_db'       => '',
        'sb_limit'    => ''
    ];
    private $postLink;
    private $sep;
    private $token_limit = 3600;
    private $data = [
        'shout_id'        => 0,
        'shout_name'      => '',
        'shout_message'   => '',
        'shout_datestamp' => '',
        'shout_ip'        => '',
        'shout_ip_type'   => '4',
        'shout_hidden'    => '',
        'shout_language'  => LANGUAGE
    ];

    public function __construct() {
        require_once INCLUDES."infusions_include.php";
        $site_path = fusion_get_settings("sitepath");

        $current_path = pathinfo(FUSION_REQUEST);
        $current_path = $site_path.str_replace("/", DIRECTORY_SEPARATOR, $current_path["dirname"].DIRECTORY_SEPARATOR.$current_path["basename"]);

        if (!session_get("shout_token_hash") || (session_get("shout_page") !== $current_path) && (FUSION_SELF !== "shoutbox_archive.php")) {
            session_add("shout_token_hash", "shoutbox_".random_string());
            session_add("shout_page", $current_path);
        }

        self::$locale = fusion_get_locale("", SHOUTBOX_LOCALE);

        self::$sb_settings = get_settings("shoutbox_panel");

        self::$limit = self::$sb_settings['visible_shouts'];
        $_GET['s_action'] = isset($_GET['s_action']) ? $_GET['s_action'] : '';

        // Just use this. You do not want "s_action" and "shoutbox_id"
        $this->postLink = clean_request("", ["s_action", "shout_id"], FALSE);

        $this->sep = stristr($this->postLink, "?") ? "&" : "?";

        switch (get("s_action")) {
            case 'delete':
                $id = defined('ADMIN_PANEL') ? get("shout_id") : $this->getSecureShoutID(get("shout_id"));
                self::delete_select($id);
                break;
            case 'delete_select':
                if (empty($_POST['shoutid'])) {
                    fusion_stop(self::$locale["SB_noentries"]);
                    redirect(clean_request("", ["section=shoutbox", "aid"], TRUE));
                }

                if (isset($_POST['shoutid'])) {
                    self::delete_select($_POST['shoutid']);
                }
                break;
            case 'edit':
                $id = defined('ADMIN_PANEL') ? get("shout_id") : $this->getSecureShoutID(get("shout_id"));
                $this->data = self::_selectedSB($id);
                break;
            default:
                break;
        }
    }

    private function delete_select($id) {
        if (!empty($id)) {
            $i = 0;
            if (is_array($id)) {
                foreach ($id as $key => $right) {
                    if (self::verify_sbdb($key)) {
                        dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($key)."'");
                        $i++;
                    }
                }
            } else {
                if (self::verify_sbdb($id)) {
                    dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_id='".intval($id)."'");
                }
            }

            addNotice('success', self::$locale['SB_shout_deleted']);
        }

        defined('ADMIN_PANEL') ?
            redirect(clean_request("section=shoutbox", ["", "aid"], TRUE)) :
            redirect($this->postLink);
    }

    static function verify_sbdb($id) {
        if (isnum($id)) {
            if ((iADMIN && checkrights("S")) ||
                (iMEMBER && dbcount("(shout_id)", DB_SHOUTBOX, "shout_id='".$id."' AND shout_name='".fusion_get_userdata("user_id")."'".(multilang_table("SB") ? " AND ".in_group('shout_language', LANGUAGE) : "").""))
            ) {
                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    /**
     * @param $shout_hash - shoutout id hash
     *
     * @return int
     */
    private function getSecureShoutID($shout_hash) {
        $userdata = fusion_get_userdata();
        $hash = session_get("shout_token_hash");
        $decrypted_hash = fusion_decrypt($shout_hash, $hash);
        if (!empty($decrypted_hash)) {
            $hash = explode(".", $decrypted_hash);
            if (count($hash) === 3) {
                list($shout_token_id, $user_token_id, $time_token) = $hash;
                if (($userdata["user_lastvisit"] + $this->token_limit) >= $time_token && $userdata["user_id"] === $user_token_id) {
                    return (int)$shout_token_id;
                }
            }
        }
        return (int)0;
    }

    public function _selectedSB($ids) {
        if (self::verify_sbdb($ids)) {
            $result = dbquery("SELECT shout_id, shout_name, shout_message, shout_datestamp, shout_ip, shout_ip_type, shout_hidden, shout_language
                FROM ".DB_SHOUTBOX."
                WHERE shout_id=".intval($ids).(multilang_table("SB") ? " AND ".in_group('shout_language', LANGUAGE) : "")
            );

            if (dbrows($result) > 0) {
                return $this->data = dbarray($result);
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->set_shoutboxdb();
        }

        return self::$instance;
    }

    private function set_shoutboxdb() {
        $shout_group = 0;
        $shout_group_n = "";
        foreach (fusion_get_groups() as $key => $grups) {
            if (!empty($_POST[$grups])) {
                $shout_group = $key;
                $shout_group_n = $grups;
            }
        }

        if (isset($_POST[$shout_group_n]) or isset($_POST['shout_box'])) {
            $shout_group = !empty($_POST['shout_box']) ? (isset($_POST['shout_hidden']) ? form_sanitizer($_POST['shout_hidden'], 0, "shout_hidden") : 0) : $shout_group;

            if (iGUEST && self::$sb_settings['guest_shouts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES."captchas/".fusion_get_settings('captcha')."/captcha_check.php";
                $sb_name = form_sanitizer($_POST['shout_name'], '', 'shout_name');
                if (!$_CAPTCHA_IS_VALID) {
                    fusion_stop(self::$locale['SB_warning_validation_code']);
                    redirect(clean_request("section=shoutbox", ["", "aid"], TRUE));
                }
            }

            $this->data = [
                'shout_id'       => form_sanitizer($_POST['shout_id'], 0, "shout_id"),
                'shout_name'     => !empty($sb_name) ? $sb_name : fusion_get_userdata("user_id"),
                'shout_message'  => form_sanitizer($_POST['shout_message'], '', 'shout_message'),
                'shout_hidden'   => $shout_group,
                'shout_language' => form_sanitizer($_POST['shout_language'], LANGUAGE, "shout_language")
            ];

            if (empty($this->data['shout_id'])) {
                $this->data += [
                    'shout_datestamp' => time(),
                    'shout_ip'        => USER_IP,
                    'shout_ip_type'   => USER_IP_TYPE,
                    'shout_hidden'    => $shout_group
                ];
            }

            require_once INCLUDES."flood_include.php";
            if (!flood_control("shout_datestamp", DB_SHOUTBOX, "shout_name='".$this->data['shout_name']."'")) {
                if (\defender::safe()) {
                    dbquery_insert(DB_SHOUTBOX, $this->data, empty($this->data['shout_id']) ? "save" : "update");
                    addNotice("success", empty($this->data['shout_id']) ? self::$locale['SB_shout_added'] : self::$locale['SB_shout_updated']);
                }
            } else {
                fusion_stop(sprintf(self::$locale['SB_flood'], fusion_get_settings("flood_interval")));
            }
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", ["", "aid"], TRUE)) :
                redirect($this->postLink);
        }

        if (isset($_POST['sb_settings'])) {
            $inputArray = [
                'visible_shouts' => form_sanitizer($_POST['visible_shouts'], 5, "visible_shouts"),
                'guest_shouts'   => form_sanitizer($_POST['guest_shouts'], 0, "guest_shouts"),
                'hidden_shouts'  => form_sanitizer($_POST['hidden_shouts'], 0, "hidden_shouts")
            ];

            if (fusion_safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "shoutbox_panel",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", ["primary_key" => "settings_name"]);
                }
                addNotice("success", self::$locale['SB_update_ok']);
                redirect(clean_request("section=shoutbox_settings", ["", "aid"], TRUE));
            }
        }

        if (isset($_POST['sb_delete_old']) && isset($_POST['num_days']) && isnum($_POST['num_days'])) {
            $deletetime = time() - (intval($_POST['num_days']) * 86400);
            $numrows = dbcount("(shout_id)", DB_SHOUTBOX, "shout_datestamp < '".$deletetime."'");
            dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_datestamp < '".$deletetime."'");
            addNotice("warning", number_format(intval($numrows))." / ".$_POST['num_days'].self::$locale['SB_delete_old']);
            defined('ADMIN_PANEL') ?
                redirect(clean_request("section=shoutbox", ["", "aid"], TRUE)) :
                redirect($this->postLink);
        }
    }

    public function DisplayAdmin() {
        $allowed_section = ["shoutbox", "shoutbox_form", "shoutbox_settings"];
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'shoutbox';
        $edit = (isset($_GET['s_action']) && $_GET['s_action'] == 'edit') && isset($_GET['shout_id']);
        $_GET['shout_id'] = isset($_GET['shout_id']) && isnum($_GET['shout_id']) ? $_GET['shout_id'] : 0;

        opentable(self::$locale['SB_admin1']);
        $master_tab_title['title'][] = self::$locale['SB_admin1'];
        $master_tab_title['id'][] = "shoutbox";
        $master_tab_title['icon'][] = "";

        $master_tab_title['title'][] = $edit ? self::$locale['edit'] : self::$locale['SB_add'];
        $master_tab_title['id'][] = "shoutbox_form";
        $master_tab_title['icon'][] = "";

        $master_tab_title['title'][] = self::$locale['SB_settings'];
        $master_tab_title['id'][] = "shoutbox_settings";
        $master_tab_title['icon'][] = "";

        echo opentab($master_tab_title, $_GET['section'], "shoutbox", TRUE, 'nav-tabs');
        switch ($_GET['section']) {
            case "shoutbox_form":
                add_to_title($edit ? self::$locale['edit'] : self::$locale['SB_add']);
                self::$default_params['sbform_name'] = 'sbform';
                echo $this->sbForm();
                add_breadcrumb(['link' => FUSION_REQUEST, "title" => $edit ? self::$locale['edit'] : self::$locale['SB_add']]);
                break;
            case "shoutbox_settings":
                add_to_title(self::$locale['SB_settings']);
                $this->settings_Form();
                add_breadcrumb(['link' => FUSION_REQUEST, "title" => self::$locale['SB_settings']]);
                break;
            default:
                add_to_title(self::$locale['SB_title']);
                $this->ShoutsAdminListing();
                add_breadcrumb(['link' => INFUSIONS.'shoutbox_panel/shoutbox_admin.php'.fusion_get_aidlink(), "title" => self::$locale['SB_title']]);
                break;
        }
        echo closetab();
        closetable();
    }

    public function sbForm() {
        if (defined('ADMIN_PANEL')) {
            fusion_confirm_exit();
        }

        $html = '';

        if (iGUEST && !self::$sb_settings['guest_shouts'] && empty(self::$sb_settings['hidden_shouts'])) {
            $html .= "<div class='text-center'>".self::$locale['SB_login_req']."</div>\n";
        } else {
            $html .= openform(self::$default_params['sbform_name'], 'post', $this->postLink);
            $html .= form_hidden('shout_id', '', $this->data['shout_id']);
            $html .= form_hidden('shout_hidden', '', $this->data['shout_hidden']);
            $html .= form_textarea('shout_message', self::$locale['SB_message'], $this->data['shout_message'], [
                'required'     => TRUE,
                'autosize'     => TRUE,
                'form_name'    => self::$default_params['sbform_name'],
                'wordcount'    => TRUE,
                'maxlength'    => '200',
                'type'         => 'bbcode',
                'input_bbcode' => 'smiley|b|u|url|color'
            ]);

            if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
                $_CAPTCHA_HIDE_INPUT = FALSE;

                $html .= form_text('shout_name', self::$locale['SB_name'], '', ["required" => TRUE, 'max_length' => 30]);

                include INCLUDES.'captchas/'.fusion_get_settings('captcha').'/captcha_display.php';

                $html .= display_captcha([
                    'captcha_id' => 'captcha_shoutbox',
                    'input_id'   => 'captcha_code_shoutbox',
                    'image_id'   => 'captcha_image_shoutbox'
                ]);

                if (!$_CAPTCHA_HIDE_INPUT) {
                    $html .= form_text('captcha_code', self::$locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_shoutbox']);
                }
            }

            if ((count(fusion_get_enabled_languages()) > 1) && multilang_table("SB")) {
                $html .= form_select('shout_language[]', self::$locale['global_ML100'], $this->data['shout_language'], [
                    "inner_width" => "100%",
                    'required'    => TRUE,
                    'options'     => fusion_get_enabled_languages(),
                    'multiple'    => TRUE
                ]);
            } else {
                $html .= form_hidden('shout_language', '', $this->data['shout_language']);
            }

            if (iMEMBER && self::$sb_settings['hidden_shouts']) {
                $html .= "<div class='btn-group dropup'>
                ".form_button('shout_box', empty($_GET['shout_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], empty($_GET['blacklist_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], ['class' => 'btn-primary'])."
                <button id='ddsg' type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>\n";
                $html .= "<span class='caret'></span>\n";
                $html .= "</button>\n";
                $html .= "<ul class='dropdown-menu' aria-labelledby='ddsg'>\n";
                foreach (fusion_get_groups() as $key => $glink) {
                    if (!iADMIN && $key > 0) {
                        $html .= users_groupaccess($key) ? "<li>".form_button($glink, $glink, $glink, ['class' => 'btn-primary'])."</li>\n" : "";
                    } else {
                        $html .= "<li>".form_button($glink, $glink, $glink, ['class' => 'btn-primary'])."</li>\n";
                    }
                }
                $html .= "</ul>\n";
                $html .= "</div>\n";
            } else {
                $html .= form_button('shout_box', empty($_GET['shout_id']) ? self::$locale['send_message'] : self::$locale['SB_update_shout'], empty($_GET['blacklist_id']) ? self::$locale['SB_save_shout'] : self::$locale['SB_update_shout'], ['class' => 'btn-primary btn-block']);
            }

            $html .= closeform();
        }

        return $html;
    }

    public function settings_Form() {

        add_to_jquery("$('#sb_delete_old').bind('click', function() { return confirm('".self::$locale['SB_warning_shouts']."'); });");
        echo openform('shoutbox', 'post', $this->postLink);

        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-6">';
        openside('');
        echo form_text('visible_shouts', self::$locale['SB_visible_shouts'], self::$sb_settings['visible_shouts'], ['required' => TRUE, 'inline' => TRUE, 'inner_width' => '100px', "type" => "number"]);
        $opts = ['1' => self::$locale['yes'], '0' => self::$locale['no']];
        echo form_select('guest_shouts', self::$locale['SB_guest_shouts'], self::$sb_settings['guest_shouts'], ['inline' => TRUE, 'inner_width' => '100px', 'options' => $opts]);
        echo form_select('hidden_shouts', self::$locale['SB_hidden_shouts'], self::$sb_settings['hidden_shouts'], ['inline' => TRUE, 'inner_width' => '100px', 'options' => $opts]);
        echo form_button('sb_settings', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success']);
        closeside();
        echo '</div>';
        echo '<div class="col-xs-12 col-sm-6">';
        openside('');
        echo form_select('num_days', self::$locale['SB_delete_old'], '', [
            'inner_width' => '200px',
            'options'     => [
                '90' => "90 ".self::$locale['SB_days'],
                '60' => "60 ".self::$locale['SB_days'],
                '30' => "30 ".self::$locale['SB_days'],
                '20' => "20 ".self::$locale['SB_days'],
                '10' => "10 ".self::$locale['SB_days']
            ]
        ]);
        echo form_button('sb_delete_old', self::$locale['delete'], self::$locale['delete'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
        closeside();
        echo '</div>';

        echo '</div>';
    }

    private function ShoutsAdminListing() {
        $total_rows = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? in_group('shout_language', LANGUAGE)." AND " : "").groupaccess('shout_hidden'));
        $rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;
        $result = $this->_selectDB($rowstart, self::$limit);
        $rows = dbrows($result);

        echo '<div class="m-t-10 m-b-10">';
        echo "<div class='display-inline'><span class='pull-right m-t-10'>".sprintf(self::$locale['SB_entries'], $rows, $total_rows)."</span></div>\n";
        echo ($total_rows > $rows) ? '<div>'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ["aid", "section"], TRUE)."&amp;").'</div>' : "";
        echo '</div>';

        if ($rows > 0) {
            echo openform('sb_form', 'post', $this->postLink."&amp;section=shoutbox&amp;s_action=delete_select");
            echo "<div class='list-group'>\n";

            if (!defined("SHOUTBOXJS")) {
                add_to_jquery("$('.shoutbox-delete-btn').on('click', function(evt) {
                return confirm('".self::$locale['SB_warning_shout']."');
                });");
                define("SHOUTBOXJS", TRUE);
            }

            while ($data = dbarray($result)) {
                $online = !empty($data['user_lastvisit']) ? "<span style='color:#5CB85C'> <i class='".($data['user_lastvisit'] >= time() - 300 ? "fa fa-circle" : "fa fa-circle-thin")."'></i></span>" : '';
                echo "<div class='list-group-item clearfix'>\n";
                echo '<div class="row">';
                echo '<div class="col-sm-3">';
                echo display_avatar($data, '30px', '', TRUE, 'img-rounded pull-left m-r-10');
                echo "<div class='overflow-hide m-r-20'>";
                echo $data['user_name'] ? profile_link($data['user_id'], $data['user_name'], $data['user_status']) : $data['shout_name'];
                echo $online;
                echo '<br/>'.self::$locale['SB_userip'].' '.$data['shout_ip'].'<br/>';
                echo "<small>".showdate("longdate", $data['shout_datestamp'])."</small><br/>";
                echo self::$sb_settings['hidden_shouts'] ? self::$locale['SB_visbility'].': '.getgroupname($data['shout_hidden']).'<br/>' : '';
                echo self::$locale['SB_lang'].': '.translate_lang_names($data['shout_language']).'<br/>';
                echo "</div>\n";
                echo '</div>';
                echo '<div class="col-sm-6">';
                echo parse_textarea($data['shout_message'], TRUE, TRUE, FALSE, NULL, TRUE);
                echo '</div>';
                echo '<div class="col-sm-3">';
                echo '<div class="btn-group btn-group-sm pull-left m-r-20">';
                echo "<a class='btn btn-default' href='".$this->postLink."&amp;section=shoutbox_form&amp;s_action=edit&amp;shout_id=".$data['shout_id']."'>";
                echo "<i class='fa fa-edit fa-fw'></i> ".self::$locale['edit'];
                echo "</a>";
                echo "<a class='btn btn-danger shoutbox-delete-btn' href='".$this->postLink."&amp;section=shoutbox&amp;s_action=delete&amp;shout_id=".$data['shout_id']."'>";
                echo "<i class='fa fa-trash fa-fw'></i> ".self::$locale['delete'];
                echo "</a>";
                echo '</div>';
                echo form_checkbox("shoutid[".$data['shout_id']."]", '', '');
                echo '</div>';
                echo '</div>';
                echo "</div>\n";
            }
            echo "</div>\n";
            echo form_button('sb_admins', self::$locale['SB_selected_shout'], self::$locale['SB_selected_shout'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
            echo closeform();

            echo ($total_rows > $rows) ? '<div class="text-center">'.makepagenav($rowstart, self::$limit, $total_rows, self::$limit, clean_request("", ["aid", "section"], TRUE)."&amp;").'</div>' : "";
        } else {
            echo "<div class='text-center m-t-10'>".self::$locale['SB_no_msgs']."</div>\n";
        }
    }

    public function _selectDB($rows, $min) {
        return dbquery("SELECT s.shout_id, s.shout_name, s.shout_message, s.shout_datestamp, s.shout_language, s.shout_ip, s.shout_hidden,
            u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_lastvisit
            FROM ".DB_SHOUTBOX." s
            LEFT JOIN ".DB_USERS." u ON s.shout_name=u.user_id
            WHERE ".(multilang_table("SB") ? in_group('shout_language', LANGUAGE)." AND " : "")."
            ".groupaccess('s.shout_hidden')."
            ORDER BY shout_datestamp DESC
            LIMIT ".intval($rows).", ".$min
        );
    }

    public function DisplayShouts() {
        $sdata = $this->getShoutboxData();

        $info = [
            'form'    => self::sbForm(),
            'items'   => $sdata['items'],
            'archive' => $sdata['archive'],
        ];

        if (!defined("SHOUTBOXJS")) {
            add_to_jquery("$('.shoutbox-delete-btn').on('click', function(evt) {
                return confirm('".self::$locale['SB_warning_shout']."');
                });");
            define("SHOUTBOXJS", TRUE);
        }

        render_shoutbox($info);
    }

    public function getShoutboxData() {
        $total_rows = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? in_group('shout_language', LANGUAGE)." AND " : "").groupaccess('shout_hidden'));
        $_GET['rows'] = isset($_GET['rows']) && $_GET['rows'] <= $total_rows ? $_GET['rows'] : 0;
        $rows = $_GET['rows'];
        $result = $this->_selectDB($rows, self::$limit);
        $rows = dbrows($result);

        $sdata = [
            'items'   => [],
            'archive' => []
        ];

        if ($rows > 0) {
            while ($data = dbarray($result)) {
                if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == fusion_get_userdata('user_id') && isset($data['user_name']))) {
                    $shout_id = $this->doSecureShoutID($data['shout_id']);
                    $data['edit_link'] = INFUSIONS.'shoutbox_panel/shoutbox_archive.php?s_action=edit&shout_id='.$shout_id;
                    $data['edit_title'] = self::$locale['edit'];
                    $data['delete_link'] = INFUSIONS.'shoutbox_panel/shoutbox_archive.php?s_action=delete&shout_id='.$shout_id;
                    $data['delete_title'] = self::$locale['delete'];
                }

                $data['profile_link'] = profile_link($data['shout_name'], $data['user_name'], $data['user_status']);
                $data['message'] = parse_textarea($data['shout_message'], TRUE, TRUE, FALSE, NULL, TRUE);

                $sdata['items'][] = $data;
            }
        }

        if ($total_rows > self::$sb_settings['visible_shouts']) {
            $sdata['archive'] = [
                'link'  => INFUSIONS.'shoutbox_panel/shoutbox_archive.php',
                'title' => self::$locale['SB_archive']
            ];
        }

        return $sdata;
    }

    public function ShoutsListing($info) {
        echo self::sbForm();

        $total_rows = dbcount("(shout_id)", DB_SHOUTBOX, (multilang_table("SB") ? in_group('shout_language', LANGUAGE)." AND " : "").groupaccess('shout_hidden'));
        $_GET['rows'] = isset($_GET['rows']) && $_GET['rows'] <= $total_rows ? $_GET['rows'] : 0;
        $rows = $_GET['rows'];
        $result = $this->_selectDB($rows, $info['sb_limit']);
        $rows = dbrows($result);

        if ($rows > 0) {
            if (!defined("SHOUTBOXJS")) {
                add_to_jquery("$('.shoutbox-delete-btn').on('click', function(evt) {
                return confirm('".self::$locale['SB_warning_shout']."');
                });");
                define("SHOUTBOXJS", TRUE);
            }

            while ($data = dbarray($result)) {
                echo "<div class='display-block shoutboxwrapper clearfix'>\n";
                echo "<div class='shoutboxavatar pull-left m-r-10 m-t-5'>\n";
                echo display_avatar($data, '25px', '', TRUE, 'img-rounded');
                echo "</div>\n";

                if ((iADMIN && checkrights("S")) || (iMEMBER && $data['shout_name'] == fusion_get_userdata('user_id') && isset($data['user_name']))) {
                    $shout_id = $this->doSecureShoutID($data["shout_id"]);
                    echo "<div class='pull-right btn-group'>\n";
                    echo "<a class='btn btn-default btn-xs side' title='".self::$locale['edit']."' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php?s_action=edit&shout_id=$shout_id'><i class='fa fa-edit'></i></a>\n";
                    echo "<a class='btn btn-default btn-xs side shoutbox-delete-btn' title='".self::$locale['delete']."' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php?s_action=delete&shout_id=$shout_id'><i class='fa fa-trash'></i></a>\n";
                    echo "</div>\n";
                }

                $online = !empty($data['user_lastvisit']) ? "<span style='color: #5CB85C; font-size: 10px;'><i class='m-l-5 m-r-5 fa fa-".($data['user_lastvisit'] >= time() - 300 ? "circle" : "circle-thin")."'></i></span>" : '';

                echo "<div class='clearfix'>";
                echo '<b>'.($data['user_name'] ? profile_link($data['shout_name'], $data['user_name'], $data['user_status']).$online : '<span class="m-r-5">'.$data['shout_name'].'</span>').'</b>';
                echo timer($data['shout_datestamp']);
                echo "</div>\n";

                $shout_message = parse_textarea($data['shout_message'], TRUE, TRUE, FALSE, NULL, TRUE);
                echo "<div class='shoutbox overflow-hide'>".$shout_message."</div>\n";
                echo "</div>\n";
            }

            if ($info['sbform_name'] == 'sarchive') {
                echo $total_rows > $rows ? '<div class="text-center m-t-10">'.makepagenav($_GET['rows'], $info['sb_limit'], $total_rows, $info['sb_limit'], FUSION_SELF.$info['sb_db'], FALSE).'</div>' : '';
            } else {
                echo $total_rows > self::$sb_settings['visible_shouts'] ? "<div class='text-center m-t-20'><a class='btn btn-default btn-xs side' href='".INFUSIONS."shoutbox_panel/shoutbox_archive.php'>".self::$locale['SB_archive']."</a>\n</div>\n" : "";
            }
        } else {
            echo "<div class='text-center m-t-10'>".self::$locale['SB_no_msgs']."</div>\n";
        }
    }

    /**
     * @param $shout_id - int
     *
     * @return string
     */
    private function doSecureShoutID($shout_id) {
        $userdata = fusion_get_userdata();
        $value = $shout_id.".".$userdata["user_id"].".".time();
        $hash = session_get("shout_token_hash");

        return urlencode(fusion_encrypt($value, $hash));
    }

    public function ArchiveListing() {
        self::$default_params = [
            'sbform_name' => 'sarchive',
            'sb_db'       => '?rows',
            'sb_limit'    => self::$arch_limit
        ];

        add_to_title(self::$locale['SB_archive']);

        openside(self::$locale['SB_archive']);
        self::ShoutsListing(self::$default_params);
        closeside();
    }
}
