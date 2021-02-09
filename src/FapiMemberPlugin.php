<?php


class FapiMemberPlugin
{

    private $errorBasket = [];

    public function __construct()
    {
        $this->registerStyles();
        $this->registerScripts();
    }

    public static function isDevelopment()
    {
        $s = $_SERVER['SERVER_NAME'];
        return ($s === 'localhost');
    }

    public function addHooks()
    {
        add_action('admin_menu', [$this, 'addAdminMenu'] );
        add_action('admin_enqueue_scripts', [$this, 'addScripts'] );
        add_action('admin_init', [$this, 'registerSettings']);

        add_action('init', [$this, 'registerLevelsTaxonomy']);
        add_action('rest_api_init', [$this, 'addRestEndpoints']);

        // admin form handling
        add_action('admin_post_fapi_member_api_credentials_submit', [$this, 'handleApiCredentialsSubmit']);
        add_action('admin_post_fapi_member_new_section', [$this, 'handleNewSection']);
        add_action('admin_post_fapi_member_new_level', [$this, 'handleNewLevel']);
        add_action('admin_post_fapi_member_remove_level', [$this, 'handleRemoveLevel']);
        add_action('admin_post_fapi_member_add_pages', [$this, 'handleAddPages']);
        add_action('admin_post_fapi_member_remove_pages', [$this, 'handleRemovePages']);

    }

    public function showError($type, $message)
    {
            add_action( 'admin_notices', function($e) {
                printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
            });
    }

    public function registerStyles()
    {
        $p = plugins_url( 'fapi-member/media/fapi-member.css' );
        wp_register_style( 'fapi-member-admin', $p);
        $p = plugins_url( 'fapi-member/media/font/stylesheet.css' );
        wp_register_style( 'fapi-member-admin-font', $p);
        $p = plugins_url( 'fapi-member/node_modules/sweetalert2/dist/sweetalert2.min.css' );
        wp_register_style( 'fapi-member-swal-css', $p);
    }

    public function registerScripts()
    {
        $p = plugins_url( 'fapi-member/node_modules/sweetalert2/dist/sweetalert2.js' );
        wp_register_script( 'fapi-member-swal', $p);
        $p = plugins_url( 'fapi-member/node_modules/promise-polyfill/dist/polyfill.min.js');
        wp_register_script( 'fapi-member-swal-promise-polyfill', $p);
        if (self::isDevelopment()) {
            $p = plugins_url( 'fapi-member/media/dist/fapi.dev.js' );
        } else {
            $p = plugins_url( 'fapi-member/media/dist/fapi.dist.js' );
        }
        wp_register_script( 'fapi-member-main', $p);
    }

    public function registerLevelsTaxonomy()
    {
        register_taxonomy('fapi_levels', 'page', [
            'public' => true, //TODO: change
            'hierarchical' => true,
            'show_ui' => true, //TODO: change
            'show_in_rest' => false,
        ]);
    }

    public function addRestEndpoints()
    {
        register_rest_route(
            'fapi/v1',
            '/sections',
            [
                'methods' => 'GET',
                'callback' => [$this, 'handleApiSections'],
            ]
        );
        register_rest_route(
            'fapi/v1',
            '/callback',
            [
                'methods' => 'POST',
                'callback' => [$this, 'handleApiCallback'],
            ]
        );
    }

    public function handleApiSections()
    {
        $t = get_terms(
            [
                'taxonomy' => 'fapi_levels',
                'hide_empty' => false,
            ]
        );
        $t = array_map(function($one) {
            return [
                'id' => $one->term_id,
                'parent' => $one->parent,
                'name' => $one->name
            ];
        }, $t);
        $sections = array_reduce($t, function($carry, $one) use ($t) {
            if ($one['parent'] === 0) {
                $children = array_values(
                    array_filter($t, function($i) use ($one) {
                        return ($i['parent'] === $one['id']);
                    })
                );
                $children = array_map(function($j) {
                    unset($j['parent']);
                    return $j;
                }, $children);
                $one['levels'] = $children;
                unset($one['parent']);
                $carry[] = $one;
            }
            return $carry;
        }, []);
        return new WP_REST_Response($sections);
    }

    public function handleApiCallback(WP_REST_Request $request)
    {
        $get = $request->get_params();
        $body = $request->get_body();
        return null;
    }

    public function handleApiCredentialsSubmit()
    {
        $this->verifyNonce('fapi_member_api_credentials_submit_nonce');

        $apiEmail = (isset($_POST['fapiMemberApiEmail']) && !empty($_POST['fapiMemberApiEmail'])) ? $_POST['fapiMemberApiEmail'] : null;
        $apiKey = (isset($_POST['fapiMemberApiKey']) && !empty($_POST['fapiMemberApiKey'])) ? $_POST['fapiMemberApiKey'] : null;

        if ($apiKey === null || $apiEmail === null) {
            $this->redirect('connection', 'apiFormEmpty');
        }

        //TODO: api request - verify

        update_option('fapiMemberApiEmail', $apiEmail);
        update_option('fapiMemberApiKey', $apiKey);

        $this->redirect('connection', 'apiFormSuccess');

    }

    protected function verifyNonce($key)
    {
        if(
            !isset( $_POST[$key] )
            ||
            !wp_verify_nonce($_POST[$key], $key)
        ) {
            wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
        }
    }

    public function handleNewSection()
    {
        $this->verifyNonce('fapi_member_new_section_nonce');

        $name = (isset($_POST['fapiMemberSectionName']) && !empty($_POST['fapiMemberSectionName'])) ? $_POST['fapiMemberSectionName'] : null;

        if ($name === null ) {
            $this->redirect('settingsSectionNew', 'sectionNameEmpty');
        }

        wp_insert_term( $name, 'fapi_levels');

        $this->redirect('settingSectionNew');

    }

    public function handleNewLevel()
    {
        $this->verifyNonce('fapi_member_new_level_nonce');

        $name = (isset($_POST['fapiMemberLevelName']) && !empty($_POST['fapiMemberLevelName'])) ? $_POST['fapiMemberLevelName'] : null;
        $parentId = (isset($_POST['fapiMemberLevelParent']) && !empty($_POST['fapiMemberLevelParent'])) ? $_POST['fapiMemberLevelParent'] : null;

        if ($name === null || $parentId === null) {
            $this->redirect('settingsLevelNew', 'levelNameOrParentEmpty');
        }

        $parent = get_term($parentId, 'fapi_levels');
        if ($parent === null) {
            $this->redirect('settingsLevelNew', 'sectionNotFound');
        }

        // check parent
        wp_insert_term( $name, 'fapi_levels', ['parent' => $parentId]);

        $this->redirect('settingsLevelNew');

    }

    public function handleAddPages()
    {
        $this->verifyNonce('fapi_member_add_pages_nonce');

        $levelId = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;
        $toAdd = (isset($_POST['toAdd']) && !empty($_POST['toAdd'])) ? $_POST['toAdd'] : null;

        if ($levelId === null || $toAdd === null) {
            $this->redirect('settingsContentAdd', 'levelIdOrToAddEmpty');
        }

        $parent = get_term($levelId, 'fapi_levels');
        if ($parent === null) {
            $this->redirect('settingsContentAdd', 'sectionNotFound');
        }

        // check parent
        $old = get_term_meta($parent->term_id, 'fapi_pages', true);

        $old = (empty($old)) ? null : json_decode($old);

        $all = ($old === null) ? $toAdd : array_merge($old, $toAdd);
        $all = array_values(array_unique($all));
        $all = array_map('intval', $all);
        update_term_meta($parent->term_id, 'fapi_pages', json_encode($all));

        $this->redirect('settingsContentRemove', null, ['level' => $levelId]);

    }

    public function handleRemovePages()
    {
        $this->verifyNonce('fapi_member_remove_pages_nonce');

        $levelId = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;
        $toRemove = (isset($_POST['toRemove']) && !empty($_POST['toRemove'])) ? $_POST['toRemove'] : null;

        if ($levelId === null || $toRemove === null) {
            $this->redirect('settingsContentRemove', 'levelIdOrToAddEmpty');
        }

        $parent = get_term($levelId, 'fapi_levels');
        if ($parent === null) {
            $this->redirect('settingsContentRemove', 'sectionNotFound');
        }

        $toRemove = array_map('intval', $toRemove);

        // check parent
        $old = get_term_meta($parent->term_id, 'fapi_pages', true);

        $old = (empty($old)) ? [] : json_decode($old);

        $new = array_values(array_filter($old, function($one) use ($toRemove){
            return !in_array($one, $toRemove);
        }));
        update_term_meta($parent->term_id, 'fapi_pages', json_encode($new));

        $this->redirect('settingsContentRemove', null, ['level' => $levelId]);

    }

    public function handleRemoveLevel()
    {
        $this->verifyNonce('fapi_member_remove_level_nonce');

        $id = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;

        if ($id === null) {
            $this->redirect('settingsSectionNew');
        }

        // check parent
        wp_delete_term($id, 'fapi_levels');

        $this->redirect('settingsLevelNew', 'removeLevelSuccessful');

    }

    public function registerSettings()
    {
        register_setting( 'options', 'fapiMemberApiEmail', [
            'type' => 'string',
            'description' => 'Fapi Member - API e-mail',
            'show_in_rest' => false,
            'default' => null,
        ]);
        register_setting( 'options', 'fapiMemberApiKey', [
            'type' => 'string',
            'description' => 'Fapi Member - API key',
            'show_in_rest' => false,
            'default' => null,
        ]);
    }

    public function addScripts()
    {
        global $pagenow;
        if ($pagenow === 'options-general.php') {
            wp_enqueue_style('fapi-member-admin-font');
            wp_enqueue_style('fapi-member-admin');
            wp_enqueue_style('fapi-member-swal-css');
            wp_enqueue_script('fapi-member-swal');
            wp_enqueue_script('fapi-member-swal-promise-polyfill');
            wp_enqueue_script('fapi-member-main');
        }
    }

    public function addAdminMenu()
    {
        add_options_page( 'Fapi Member', 'Fapi Member', 'manage_options', 'fapi-member-options', [$this, 'constructAdminMenu'] );

    }

    public function constructAdminMenu()
    {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $subpage = $this->findSubpage();

        if (method_exists($this, sprintf('show%s', ucfirst($subpage)))) {
            call_user_func([$this, sprintf('show%s', ucfirst($subpage))]);
        }
    }

    protected function findSubpage()
    {
        return (isset($_GET['subpage'])) ? $_GET['subpage'] : 'index';
    }

    protected function showIndex()
    {
        if (!$this->areApiCredentialsSet()) {
            $this->showTemplate('connection');
        }
        $this->showTemplate('index');
    }

    protected function showSettingsSectionNew()
    {
        $this->showTemplate('settingsSectionNew');
    }

    protected function showSettingsLevelNew()
    {
        $this->showTemplate('settingsLevelNew');
    }

    protected function showSettingsContentSelect()
    {
        $this->showTemplate('settingsContentSelect');
    }

    protected function showSettingsContentRemove()
    {
        $this->showTemplate('settingsContentRemove');
    }

    protected function showSettingsContentAdd()
    {
        $this->showTemplate('settingsContentAdd');
    }

    protected function showConnection()
    {
        $this->showTemplate('connection');
    }

    protected function showTemplate($name)
    {
        $areApiCredentialsSet = $this->areApiCredentialsSet();
        $subpage = $this->findSubpage();

        $path = sprintf('%s/../templates/%s.php', __DIR__, $name);
        if (file_exists($path)) {
            include $path;
        }
    }

    protected function redirect($subpage, $e = null, $other = []) {
        $tail = '';
        foreach ($other as $key => $value) {
            $tail .= sprintf('&%s=%s', $key, urlencode($value));
        }
        if ($e === null) {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail)));
        } else {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s&e=%s%s', $subpage, $e, $tail)));
        }
        exit;
    }

    protected function areApiCredentialsSet()
    {
        $apiEmail = get_option('fapiMemberApiEmail', null);
        $apiKey = get_option('fapiMemberApiKey', null);
        if ($apiKey && $apiEmail && !empty($apiKey) && !empty($apiEmail)) {
            return true;
        } else {
            return false;
        }
    }
}