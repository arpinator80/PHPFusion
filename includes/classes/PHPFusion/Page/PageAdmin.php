<?php
namespace PHPFusion\Page;

// Administration Strictly for Page Creation only
use PHPFusion\QuantumFields;
use PHPFusion\SiteLinks;

class PageAdmin extends PageComposer {

    protected static $page_instance = NULL;
    private static $data = array(
        'page_id' => 0,
        'page_cat' => 0,
        'page_link_cat' => 0,
        'page_title' => '',
        'page_access' => iGUEST,
        'page_content' => '',
        'page_keywords' => '',
        'page_status' => 0,
        'page_user' => 0,
        'page_datestamp' => 0,
        'page_allow_comments' => 0,
        'page_allow_ratings' => 0,
        'page_language' => LANGUAGE,
        'link_id' => 0,
        'link_order' => 0,
    );
    private static $locale = array();
    private static $allowed_admin_pages = array('cp1', 'compose_frm');
    private static $allowed_composer_mode = array('pg_content', 'pg_settings', 'pg_composer');
    private static $current_section = '';
    private static $current_status = '';
    private static $current_action = '';
    private static $current_pageId = 0;
    private static $composerMode = 'pg_content';

    /**
     * Return page composer object
     * @return null|static
     */
    public static function getComposerAdminInstance() {
        if (empty(self::$page_instance)) {
            self::$page_instance = new Static;
            self::set_PageAdminInfo();
        }

        return (object)self::$page_instance;
    }

    // This is the controller page

    public static function set_PageAdminInfo() {

        self::$current_section = isset($_GET['section']) && in_array($_GET['section'],
                                                                     self::$allowed_admin_pages) ? $_GET['section'] : self::$allowed_admin_pages[0];
        self::$current_status = isset($_GET['status']) && isnum($_GET['status']) ? $_GET['status'] : self::$current_status;
        self::$current_action = isset($_GET['action']) ? $_GET['action'] : self::$current_action;
        self::$current_pageId = isset($_GET['cpid']) && isnum($_GET['cpid']) ? intval($_GET['cpid']) : self::$current_pageId;
        self::$composerMode = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                                                                       self::$allowed_composer_mode) ? $_GET['composer_tab'] : self::$allowed_composer_mode[0];
        $_POST['page_id'] = isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : 0;
        self::$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/sitelinks.php');
        self::$locale += fusion_get_locale('', LOCALE.LOCALESET.'admin/custom_pages.php');
        self::$data['page_datestamp'] = time();
    }

    private static function composer_LayoutSettings() {
        add_to_title(fusion_get_locale('global_201')."Page Layout Settings");
        add_breadcrumb(array(
                           'link' => clean_request('compose=layout', array('page_id'), TRUE),
                           'title' => 'Page Layout Settings'
                       ));
        //print_p(self::$info);
        ob_start();
        echo openform('layoutBuilderFrm', 'post', FUSION_SELF);

        echo form_button('add_row', 'Add Row', 'add_row', array('class' => 'btn-primary m-r-10'));
        echo form_button('save_layout', 'Save Layout', 'save_layout', array('class' => 'btn-success'));

        ?>
        <hr/>
        <div class="well">
            <?php echo self::$info['body'][self::$info['rowstart']] ?>
        </div>
        <?php
        echo closeform();
        $info = ob_get_contents();
        ob_end_clean();

        return $info;
    }

    private static function composer_PanelSettings() {
        ob_start();
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Below Header Panel</strong><br/><i>Toggle display of below header
                    panel</i></div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('au_upper_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio',
                                       'class' => 'm-b-0'
                                   )
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Left Panel</strong><br/><i>Toggle display of left side panel</i>
            </div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('left_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio',
                                   )
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Right Panel</strong><br/><i>Toggle display of right side panel</i>
            </div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('right_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio',
                                   )
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Upper Center Panel</strong><br/><i>Toggle display of upper center
                    panel</i></div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('upper_center_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio'
                                   )
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Lower Center Panel</strong><br/><i>Toggle display of lower center
                    panel</i></div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('lower_center_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio',
                                   )
                );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4"><strong>Above Footer Panel</strong><br/><i>Toggle display of above footer
                    panel</i></div>
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_checkbox('above_footer_panel_status', '', '',
                                   array(
                                       'options' => array(
                                           0 => 'Do not display panels',
                                           1 => 'Show panels'
                                       ),
                                       'type' => 'radio',
                                   )
                );
                ?>
            </div>
        </div>
        <?php
        $info = ob_get_contents();
        ob_end_clean();

        return (array)$info;
    }

    private static function composer_PanelAdmin() {
        echo 'do a form and react with something. plan in development';
    }



    // Page coupled with Panels in front end Construction, with TinyMCE inline editor and Drag and Drop Feature
    // Remove opening page and install a non-deletable home page

    public function display_page() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        add_to_title(self::$locale['global_201'].self::$locale['403']);
        add_breadcrumb(array('link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(), 'title' => self::$locale['403']));
        $tree = dbquery_tree_full(DB_CUSTOM_PAGES, 'page_id', 'page_cat');
        $tree_index = tree_index($tree);
        make_page_breadcrumbs($tree_index, $tree, 'page_id', 'page_title', 'pref');

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;
        if (self::$current_section == "cp2") {
            add_breadcrumb(array(
                               'link' => ADMIN.'custom_pages.php'.fusion_get_aidlink(),
                               'title' => $edit ? self::$locale['401'] : self::$locale['400']
                           ));
        }


        $tab_title['title'][] = self::$locale['402'];
        $tab_title['id'][] = 'cp1';
        $tab_title['icon'][] = '';

        if (self::$current_section == 'compose_frm') {
            $tab_title['title'][] = $edit ? self::$locale['401'] : self::$locale['400'];
            $tab_title['id'][] = 'compose_frm';
            $tab_title['icon'][] = '';
        }

        $tab_active = self::$current_section;


        switch (self::$current_action) {
            case 'edit':
                if (!empty(self::$current_pageId)) {
                    self::$data = self::load_customPage(self::$current_pageId);
                    if (empty(self::$data)) {
                        redirect(FUSION_SELF.fusion_get_aidlink());
                    }
                    fusion_confirm_exit();
                    opentable(self::$locale['401']);
                } else {
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }

                break;
            case 'delete':
                if (!empty(self::$current_pageId)) {
                    self::delete_customPage(self::$current_pageId);
                } else {
                    redirect(FUSION_SELF.fusion_get_aidlink());
                }
                break;
            default:
                opentable(self::$locale['403']);
        }

        echo opentab($tab_title, $tab_active, 'cpa', TRUE);
        if (self::$current_section == "compose_frm") {
            self::set_customPage(self::$data);
            self::display_Composer();
        } else {
            self::display_PageList();
        }
        echo closetab();
        echo closetable();

    }

    /**
     * SQL update or save data
     */
    protected static function set_customPage() {

        if (isset($_POST['save']) or isset($_POST['save_and_close'])) {

            // We must identify which page is saving by using get
            // pg_content, pg_composer, pg_settings
            switch (self::$composerMode) {
                case 'pg_composer':
                    break;
                case 'pg_settings':
                    self::$data = array(
                        'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                        'page_header_panel' => !empty($_POST['page_header_panel']) ? 1 : 0,
                        'page_footer_panel' => !empty($_POST['page_footer_panel']) ? 1 : 0,
                        'page_left_panel' => !empty($_POST['page_left_panel']) ? 1 : 0,
                        'page_right_panel' => !empty($_POST['page_right_panel']) ? 1 : 0,
                        'page_top_panel' => !empty($_POST['page_top_panel']) ? 1 : 0,
                        'page_bottom_panel' => !empty($_POST['page_bottom_panel']) ? 1 : 0,
                        'page_link_cat' => self::$data['page_link_cat'],
                        'page_title' => self::$data['page_title'],
                        'page_access' => self::$data['page_access'],
                    );
                    break;
                case 'pg_content';
                    self::$data = array(
                        'page_id' => form_sanitizer($_POST['page_id'], 0, 'page_id'),
                        'page_cat' => form_sanitizer($_POST['page_cat'], 0, 'page_cat'),
                        'page_title' => form_sanitizer($_POST['page_title'], '', 'page_title'),
                        'page_access' => form_sanitizer($_POST['page_access'], 0, 'page_access'),
                        'page_content' => addslash($_POST['page_content']),
                        'page_keywords' => form_sanitizer($_POST['page_keywords'], '', 'page_keywords'),
                        'page_status' => form_sanitizer($_POST['page_status'], '', 'page_status'),
                        'page_datestamp' => form_sanitizer($_POST['page_datestamp'], '', 'page_datestamp'),
                        'page_language' => isset($_POST['page_language']) ? form_sanitizer($_POST['page_language'], "",
                                                                                           "page_language") : LANGUAGE,
                    );
                    break;

            }
            // Debug process
            //\defender::stop();
            //print_p($_POST);
            //print_p(self::$data, 1);

            if (\defender::safe()) {
                if (self::verify_customPage(self::$data['page_id'])) {
                    dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'update');
                    addNotice('success', self::$locale['411']);
                } else {
                    dbquery_insert(DB_CUSTOM_PAGES, self::$data, 'save');
                    self::$data['page_id'] = dblastid();
                    if (!empty($data['add_link'])) {
                        self::set_customPageLinks(self::$data);
                    }
                    addNotice('success', self::$locale['410']);
                }

                if (isset($_POST['save'])) {
                    redirect(clean_request('action=edit&cpid='.self::$data['page_id'],
                                           array('section', 'composer_tab', 'aid'), TRUE));
                } elseif (isset($_POST['save_and_close'])) {
                    redirect(FUSION_SELF.fusion_get_aidlink()."&amp;pid=".self::$data['page_id']);
                }
            }


        }
    }

    /**
     * Display Composer need to echo
     */
    private static function display_Composer() {

        $locale = fusion_get_locale("", LOCALE.LOCALESET."admin/custom_pages.php");

        $textArea_config = array(
            'width' => '100%',
            'height' => '260px',
            'form_name' => 'inputform',
            'type' => "html",
            'class' => 'm-t-20',
        );
        if ((isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1) || fusion_get_settings('tinymce_enabled')) {
            $textArea_config = array(
                "type" => "tinymce",
                "tinymce" => "advanced",
                "class" => "m-t-20",
                "height" => "400px",
            );
        }

        echo openform('inputform', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
        echo form_hidden('page_id', '', self::$data['page_id']);
        // Too much clutter on the middle, there is not enough room to see the design later.
        // Really need 2-3 tabs to control these things, start with basic ones first.
        // have a page description so admin knows what to do with it.

        $composerTab['title'][] = 'Page Content';
        $composerTab['id'][] = 'pg_content';
        if (self::$data['page_id']) { // only available when save
            $composerTab['title'][] = 'Page Composer';
            $composerTab['id'][] = 'pg_composer';

            $composerTab['title'][] = 'Page Settings';
            $composerTab['id'][] = 'pg_settings';
        }

        $currentComposerTab = isset($_GET['composer_tab']) && in_array($_GET['composer_tab'],
                                                                       $composerTab['id']) ? $_GET['composer_tab'] : $composerTab['id'][0];
        self::$composerMode = $currentComposerTab;

        echo opentab($composerTab, self::$composerMode, 'composer_tab', TRUE, 'm-t-10', 'composer_tab');

        echo "<div class='m-t-10'>";
        echo form_button('save', $locale['430'], $locale['430'], array('class' => 'btn-primary m-r-10'));
        if (isset($_POST['edit'])) {
            echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
        }
        echo form_button('save_and_close', 'Save and Close', 'Save and Close', array('class' => 'btn-success m-r-10'));
        echo "</div>\n";
        echo "<hr/>";

        switch ($currentComposerTab) {
            case 'pg_settings':
                // do the settings
                self::Page_Settings();
                break;
            case 'pg_composer':
                self::Page_Composer();
                break;
            default:
                // the composer
                self::Page_Content();
        }
        echo closetab();
        echo closeform();
    }

    private static function Page_Settings() {
        add_to_jquery("
        function checkLinkPosition( val ) {
            if ( val == 4 ) {
                $('#link_position_id').prop('disabled', false).show();
            } else {
                $('#link_position_id').prop('disabled', true).hide();
            }
        }
        ");
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Site Links Attributes</strong></div>
                    <div class="panel-body">
                        <?php

                        $has_link = (!empty(self::$data['page_link_cat']) && SiteLinks::verify_sitelinks(self::$data['page_link_cat'])) ? TRUE : FALSE;

                        if ($has_link === FALSE and !isset($_GET['add_sl'])) : ?>
                            <div class="well text-center">
                                No Site Links defined<br/>
                                <a class="btn btn-primary m-t-20"
                                   href="<?php echo clean_request('add_sl=true', array('add_sl'), FALSE) ?>">
                                    Add Site Links
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Whether has link data or not
                        $data = array(
                            'link_id' => self::$data['page_link_cat'],
                            'link_name' => self::$data['page_title'],
                            'link_url' => 'viewpage.php?page_id='.self::$data['page_id'],
                            'link_icon' => '',
                            'link_cat' => 0,
                            'link_language' => LANGUAGE,
                            'link_visibility' => self::$data['page_access'],
                            'link_order' => 0,
                            'link_position' => 1,
                            'link_window' => 0,
                            'link_position_id' => 0,
                        );

                        if ($has_link) {
                            $data = SiteLinks::get_SiteLinks(self::$data['page_link_cat']);
                        }


                        if (isset($_GET['add_sl']) or $has_link === TRUE) {

                            if (isset($_POST['save_link'])) {

                                $data = array(
                                    "link_id" => $data['link_id'],
                                    "link_cat" => form_sanitizer($_POST['link_cat'], 0, 'link_cat'),
                                    "link_name" => form_sanitizer($_POST['link_name'], '', 'link_name'),
                                    "link_url" => $data['link_url'],
                                    "link_icon" => form_sanitizer($_POST['link_icon'], '', 'link_icon'),
                                    "link_language" => $data['link_language'],
                                    "link_visibility" => $data['link_visibility'],
                                    "link_position" => form_sanitizer($_POST['link_position'], '', 'link_position'),
                                    "link_order" => form_sanitizer($_POST['link_order'], '', 'link_order'),
                                    "link_window" => form_sanitizer(isset($_POST['link_window']) && $_POST['link_window'] == 1 ? 1 : 0,
                                                                    0,
                                                                    'link_window')
                                );
                                if ($data['link_position'] > 3) {
                                    $data['link_position'] = form_sanitizer($_POST['link_position_id'], 3,
                                                                            'link_position_id');
                                }

                                if (empty($data['link_order'])) {
                                    $max_order_query = "SELECT MAX(link_order) 'link_order' FROM ".DB_SITE_LINKS."
                                    ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."
                                    link_cat='".$data['link_cat']."'";

                                    $data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
                                }

                                if (\defender::safe()) {

                                    if (!empty($data['link_id'])) {

                                        dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order",
                                                      $data['link_id'],
                                                      "link_id",
                                                      $data['link_cat'], "link_cat", multilang_table("SL"),
                                                      "link_language",
                                                      "update");

                                        dbquery_insert(DB_SITE_LINKS, $data, 'update');

                                        addNotice("success", 'Site Links updated');

                                    } else {

                                        dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order",
                                                      $data['link_id'],
                                                      "link_id",
                                                      $data['link_cat'], "link_cat", multilang_table("SL"),
                                                      "link_language", "save");

                                        dbquery_insert(DB_SITE_LINKS, $data, 'save');

                                        $id = dblastid();

                                        dbquery("UPDATE ".DB_CUSTOM_PAGES." SET page_link_cat='$id'");

                                        addNotice("success", 'Site Links created');

                                    }

                                    redirect(clean_request('', array('add_sl'), FALSE));
                                }
                            }

                            if ($data['link_position'] > 3) {
                                $data['link_position_id'] = $data['link_position'];
                                $data['link_position'] = 4;
                            }
                            add_to_jquery("
                                checkLinkPosition( ".$data['link_position']." );
                                $('#link_position').bind('change', function(e) {
                                    checkLinkPosition( $(this).val() );
                                });
                                ");

                            echo form_text('link_name', 'Link Name', $data['link_name'],
                                           array('required' => TRUE, 'inline' => TRUE)).
                                form_select('link_position', 'Link Position', $data['link_position'],
                                            array(
                                                'options' => SiteLinks::get_SiteLinksPosition(),
                                                'inline' => TRUE,
                                                'stacked' => form_text('link_position_id', '', '',
                                                    //$this->data['link_position_id'],
                                                                       array(
                                                                           'required' => TRUE,
                                                                           'placeholder' => 'ID',
                                                                           'type' => 'number',
                                                                           'type' => 'number',
                                                                           'width' => '150px',
                                                                           'class' => 'm-b-0'
                                                                       )
                                                )
                                            )).
                                form_text('link_order', 'Link Order', $data['link_order'],
                                          array('type' => 'number', 'width' => '150px', 'inline' => TRUE)).
                                form_text('link_icon', 'Link Icon', $data['link_icon'],
                                          array('width' => '150px', 'inline' => TRUE)).
                                form_select_tree('link_cat', 'Link Category', $data['link_cat'], array(
                                    "parent_value" => self::$locale['parent'],
                                    'inline' => TRUE,
                                    'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
                                    'disable_opts' => self::$data['page_link_cat'],
                                    'hide_disabled' => FALSE,
                                    'class' => 'm-b-0'
                                ), DB_SITE_LINKS, "link_name", "link_id", "link_cat")."<hr/>",
                            form_button('save_link', 'Save Link', 'save_link', array('class' => 'btn-primary'));
                            ?>
                            <a class="btn btn-default" href="<?php echo clean_request('', array('add_sl'), false) ?>">
                                <?php echo self::$locale['cancel'] ?>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Panel Attributes</strong></div>
                    <div class="panel-body">
                        <?php
                        echo form_btngroup("page_left_panel", 'Left Panels', self::$data['page_left_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_right_panel", 'Right Panels', self::$data['page_right_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_header_panel", 'Header Panels', self::$data['page_header_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_top_panel", 'Top Panels', self::$data['page_top_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_bottom_panel", 'Bottom Panels', self::$data['page_bottom_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            )).
                            form_btngroup("page_footer_panel", 'Footer Panels', self::$data['page_footer_panel'], array(
                                'inline' => TRUE,
                                'options' => array(
                                    0 => 'Disabled',
                                    1 => 'Enabled',
                                ),
                                'width' => '100%'
                            ));
                        ?>
                    </div>
                </div>


            </div>
        </div>
        <?php

    }

    private static function Page_Composer() {

        // This is the composer
        echo form_button('add_row', 'Add Row', 'add row', array(
            'class' => 'btn-primary m-r-10'
        ));
        ?>

        <section id='pageComposerLayout' class="m-t-20">

            <div class="well">

                <div class="pull-right sortable btn btn-xs m-r-10 m-b-10 display-inline-block">
                    <i class="fa fa-arrows-alt"></i>
                </div>


                <div class="btn-group btn-group-sm m-b-10">
                    <?php
                    echo form_button('add_compo', '', 'add_compo',
                                     array('icon' => 'fa fa-dashboard', 'alt' => 'Add Component')).
                        form_button('add_col', '', 'add_col',
                                    array('icon' => 'fa fa-plus-circle', 'alt' => 'Add Column')).
                        form_button('set_prop', '', 'set_prop',
                                    array('icon' => 'fa fa-cog', 'alt' => 'Configure Properties'));
                    ?>
                </div>

                <div class="btn-group btn-group-sm m-b-10">
                    <?php
                    echo form_button('copy_row', '', 'copy_row',
                                     array('icon' => 'fa fa-copy', 'alt' => 'Duplicate Row')).
                        form_button('del_col', '', 'del_col',
                                    array('icon' => 'fa fa-minus-circle', 'alt' => 'Remove Column')).
                        form_button('del_row', '', 'del_row',
                                    array('class' => 'btn-danger', 'icon' => 'fa fa-trash', 'alt' => 'Delete Row'));
                    ?>
                </div>


                <div class="list-group-item m-t-10">
                    <div class='text-center'>Add Content</div>
                </div>
            </div>


        </section>

        <?php

        add_to_jquery("
			$('#delete').bind('click', function() { confirm('".self::$locale['450']."'); });
			$('#save').bind('click', function() {
			var page_title = $('#page_title').val();
			if (page_title =='') { alert('".self::$locale['451']."'); return false; }
			});
		");
        if (fusion_get_settings('tinymce_enabled')) {
            add_to_jquery("
			function SetTinyMCE(val) {
			now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);
			expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;
			location.href='".FUSION_SELF.fusion_get_aidlink()."&section=cp2';
			}
		    ");
        }
    }

    private static function Page_Content() {

        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_text('page_title', self::$locale['422'], self::$data['page_title'],
                               array('required' => TRUE)).
                    form_select('page_keywords', 'Page Meta Keywords (Seperate each keywords with Enter key)',
                                self::$data['page_keywords'], array(
                                    'max_length' => 320,
                                    'width' => '100%',
                                    'tags' => 1,
                                    'multiple' => 1,
                                )).
                    form_textarea('page_content', 'Page Description', self::$data['page_content']);

                ?>
                <div class="row m-b-20">
                    <div class="col-xs-12 col-sm-3">
                        <strong>Enabled Languages</strong><br/><i>The language of this page</i>
                    </div>
                    <div class="col-xs-12 col-sm-9">
                        <?php
                        if (multilang_table("CP")) {
                            $page_lang = !empty(self::$data['page_language']) ? explode('.',
                                                                                        self::$data['page_language']) : array();
                            foreach (fusion_get_enabled_languages() as $language => $language_name) {
                                echo form_checkbox('page_language[]', $language_name,
                                                   in_array($language, $page_lang) ? TRUE : FALSE,
                                                   array(
                                                       'class' => 'm-b-0',
                                                       'value' => $language,
                                                       'input_id' => 'page_lang-'.$language,
                                                       "delimiter" => ".",
                                                       'reverse_label' => TRUE,
                                                       'required' => TRUE
                                                   ));
                            }
                        } else {
                            echo form_hidden('page_language', '', self::$data['page_language']);
                        }
                        ?>
                    </div>
                </div>

            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>Publication</strong></div>
                    <div class="panel-body">
                        <?php
                        echo form_select('page_status', 'Page Status', self::$data['page_status'], array(
                                'options' => array('Unpublished', 'Published'),
                                'width' => '100%',
                                'inline' => TRUE
                            )).
                            form_select('page_access', 'Page Access', self::$data['page_access'], array(
                                'options' => fusion_get_groups(),
                                'width' => '100%',
                                'inline' => TRUE,
                            )).
                            form_datepicker('page_datestamp', 'Published On', self::$data['page_datestamp'], array(
                                'width' => '100%',
                                'inline' => TRUE,
                            )).
                            form_select_tree('page_cat', 'Page Category', self::$data['page_cat'], array(
                                'inline' => TRUE,
                                'width' => '100%',
                                'placeholder' => self::$locale['choose'],
                            ), DB_CUSTOM_PAGES, 'page_title', 'page_id', 'page_cat', self::$data['page_id']);

                        ?>

                    </div>
                    <div class="panel-footer">
                        <?php
                        echo form_button('save', self::$locale['save'], self::$locale['save'],
                                         array('class' => 'btn-primary m-r-10'));
                        echo form_button('save_and_close', 'Save and Close', 'Save and Close',
                                         array('class' => 'btn-success m-r-10'));
                        echo form_button('preview', self::$locale['preview'], self::$locale['preview'],
                                         array('class' => 'btn-default m-r-10'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * List custom page administration table
     */
    private static function display_PageList() {

        $aidlink = fusion_get_aidlink();
        $locale = self::$locale;

        if (isset($_POST['page_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        $search_string = array();
        if (isset($_POST['p-submit-page_title'])) {
            $search_string['cp.page_title'] = array(
                "input" => form_sanitizer($_POST['page_title'], "", "page_title"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['page_status']) && isnum($_POST['page_status'])) {
            switch ($_POST['cp.page_status']) {
                case 1: // is a draft
                    $search_string['page_status'] = array("input" => 1, "operator" => "=");
                    break;
                case 2: // is a sticky
                    $search_string['page_status'] = array("input" => 2, "operator" => "=");
                    break;
            }
        }

        if (!empty($_POST['page_access'])) {
            $search_string['cp.page_access'] = array(
                "input" => form_sanitizer($_POST['page_access'], "", "page_access"), "operator" => "="
            );
        }

        if (!empty($_POST['page_cat'])) {
            $search_string['cp.page_cat'] = array(
                "input" => form_sanitizer($_POST['page_cat'], "", "page_cat"), "operator" => "="
            );
        }
        // This one cannot - must be ".in_group()

        if (!empty($_POST['page_language'])) {
            $language = form_sanitizer($_POST['page_language'], '', 'page_language');
            $search_string['cp.page_language'] = array(
                "input" => in_group('page_language', $language), "operator" => ""
            );
        }

        if (!empty($_POST['page_user'])) {
            $search_string['cp.page_user'] = array(
                "input" => form_sanitizer($_POST['page_user'], "", "page_user"), "operator" => "="
            );
        }

        if (isset($_GET['pref']) && isnum($_GET['pref'])) {
            $search_string['cp.page_cat'] = array(
                'input' => intval($_GET['pref']),
                'operator' => '='
            );
        }

        $sql_condition = '';
        if (!empty($search_string)) {
            $i = 0;
            foreach ($search_string as $key => $values) {
                if ($i > 0) {
                    $sql_condition .= " AND ";
                }
                $sql_condition .= " $key ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
                $i++;
            }
        }

        $rowstart = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
        $page_per_query = 20;

        $page_query = "SELECT cp.*, cp2.page_title 'page_cat_title', count(cp2.page_id) 'page_sub_count', u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_CUSTOM_PAGES." cp
        LEFT JOIN ".DB_USERS." u ON u.user_id=cp.page_user
        LEFT JOIN ".DB_CUSTOM_PAGES." cp2 ON cp.page_cat=cp2.page_id
        ".($sql_condition ? "WHERE " : "")." $sql_condition
	    GROUP BY cp.page_id
	    ORDER BY cp.page_status DESC, cp.page_datestamp DESC LIMIT $rowstart, $page_per_query
        ";

        $page_result = dbquery($page_query);
        ?>

        <div class="m-t-15">
            <?php

            echo openform("cp_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";
            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("section=compose_frm",
                                                                                 array("section"),
                                                                                 FALSE)."'>Add New</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".self::$locale['publish']." </a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".self::$locale['unpublish']."</a>";
            echo "<a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".self::$locale['delete']."</a>";
            echo "</div>\n";

            ?>
            <script>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#cp_table').submit();
                }
            </script>

            <?php
            $filter_values = array(
                "page_title" => !empty($_POST['page_title']) ? form_sanitizer($_POST['page_title'], "",
                                                                              "page_title") : "",
                "page_status" => !empty($_POST['page_status']) ? form_sanitizer($_POST['page_status'], "",
                                                                                "page_status") : "",
                "page_cat" => !empty($_POST['page_cat']) ? form_sanitizer($_POST['page_cat'], "", "page_cat") : "",
                "page_access" => !empty($_POST['page_access']) ? form_sanitizer($_POST['page_access'], "",
                                                                                "page_access") : "",
                "page_language" => !empty($_POST['page_language']) ? form_sanitizer($_POST['page_language'], "",
                                                                                    "page_language") : "",
                "page_user" => !empty($_POST['page_user']) ? form_sanitizer($_POST['page_user'], "", "page_user") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }

            echo "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            echo form_text("page_title", "", $filter_values['page_title'], array(
                "placeholder" => "Page Title Subject",
                "append_button" => TRUE,
                "append_value" => "<i class='fa fa-search'></i>",
                "append_form_value" => "search_page",
                "width" => "250px"
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>";
            echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>Search Options
            <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("page_clear", "Clear", "clear");
            echo "</div>\n";
            echo "</div>\n";

            add_to_jquery("
            $('#toggle_options').bind('click', function(e) {
                $('#news_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });

            // Select change
            $('#page_status, #page_access, #page_cat, #page_language, #page_user').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");
            unset($filter_values['page_title']);

            echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("page_status", "", $filter_values['page_status'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Status -", "options" => array(
                    0 => "All Status",
                    1 => "Published",
                    2 => "Unpublished",
                )
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";
            echo form_select("page_access", "", $filter_values['page_access'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Access -", "options" => fusion_get_groups()
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";
            echo form_select_tree("page_cat", "", $filter_values['page_cat'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Category -"
            ), DB_CUSTOM_PAGES, 'page_title', 'page_id', 'page_cat');
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $language_opts = array(0 => "All Language");
            $language_opts += fusion_get_enabled_languages();
            echo form_select("page_language", "", $filter_values['page_language'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Language -", "options" => $language_opts
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $author_opts = array(0 => "All Author");
            $result = dbquery("SELECT u.user_id, u.user_name, u.user_status
          FROM ".DB_CUSTOM_PAGES." cp
          LEFT JOIN ".DB_USERS." u on cp.page_user = u.user_id
          GROUP BY u.user_id
          ORDER BY user_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $author_opts[$data['user_id']] = $data['user_name'];
                }
            }
            echo form_select("page_user", "", $filter_values['page_user'],
                             array(
                                 "allowclear" => TRUE, "placeholder" => "- Select Author -", "options" => $author_opts
                             ));

            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>
        <?php

        add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#cp-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#cp-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		");

        echo "<div class='m-t-20'>\n";
        echo "<table class='table table-responsive".(!empty($data) ? " table-striped " : "")."table-hover'>\n";
        echo "<tr>\n";
        echo "<th></th>\n";
        echo "<th  class='col-xs-4'>".self::$locale['cp_101']."</th>\n";
        echo "<th>".$locale['cp_102']."</th>\n";
        echo "<th>".$locale['cp_103']."</th>\n";
        echo "<th>".$locale['cp_104']."</th>\n";
        echo "<th>".$locale['cp_105']."</th>\n";
        echo "<th>".$locale['cp_106']."</th>\n";
        echo "<th>".$locale['cp_100']."</th>\n";
        echo "</tr>\n";

        if (dbrows($page_result) > 0) {

            echo "<tbody id='custompage-links' class='connected'>\n";

            while ($pageData = dbarray($page_result)) {

                $pageLanguage = '';
                $pageLang = explode(".", $pageData['page_language']);
                foreach ($pageLang as $languages) {
                    $pageLanguage .= "<span class='badge'>".translate_lang_names($languages)."</span>\n";
                }

                $pageParent = $pageData['page_cat'] == 0 ? "Starting Page" : "<a href='".clean_request('pref='.$pageData['page_cat'],
                                                                                                       array('pref'),
                                                                                                       FALSE)."'>".$pageData['page_cat_title']."</a>\n";
                $pageStatus = $pageData['page_status'] == 1 ? 'Published' : 'Unpublished';
                $pageLink = clean_request('pref='.$pageData['page_id'], array('pref'), FALSE);

                echo "<tr id='listItem_".$pageData['page_id']."' data-id='".$pageData['page_id']."' class='list-result pointer'>\n";
                echo "<td>".form_checkbox('cp[]', '', '', array(
                        'value' => $pageData['page_id'], 'input_id' => 'cp-'.$pageData['page_id']
                    ))."</td>";

                echo "<td><a href='$pageLink'>".$pageData['page_title']."</a>\n";
                echo "<div class='actionbar text-smaller' id='cp-".$pageData['page_id']."-actions'>
				<a target='_new' href='".BASEDIR."viewpage.php?page_id=".$pageData['page_id']."'>".$locale['preview']."</a> |
				<a href='".FUSION_SELF.$aidlink."&amp;section=compose_frm&amp;action=edit&amp;cpid=".$pageData['page_id']."'>".$locale['edit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cpid=".$pageData['page_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['delete']."</a>
				</div>\n";
                echo "</td>\n";
                echo "<td>".getgroupname($pageData['page_access'])."</td>\n";
                echo "<td>".$pageLanguage."</td>\n";
                echo "<td>$pageParent</td>\n";
                echo "<td>".$pageData['page_sub_count']."</td>\n";
                echo "<td>$pageStatus</td>\n";
                echo "<td>".$pageData['page_id']."</td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n";
        } else {
            echo "<tr>\n";
            echo "<td colspan='8' class='text-center'>\n<div class='well'>\n".$locale['458']."</div>\n</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
        echo "</div>\n";
        closetable();
    }

}