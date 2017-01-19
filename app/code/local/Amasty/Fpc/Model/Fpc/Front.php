<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */

class Amasty_Fpc_Model_Fpc_Front extends Varien_Object
{
    protected static $_configCache = null;
    protected static $_additionalConfigs = array(
        'web/url/use_store',
        'web/cookie/cookie_restriction',
        'catalog/frontend/list_mode',
        'catalog/frontend/list_per_page',
        'catalog/frontend/grid_per_page',
        'catalog/frontend/default_sort_by',
        Mage_Core_Helper_Cookie::XML_PATH_COOKIE_RESTRICTION,
        Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACTIVE
    );

    protected static $_cookieUpdated = false;

    protected $_debug = null;
    protected $_cookieDebugTrigger = false;
    protected $_debugInfo = null;
    protected $_dynamicBlocks = null;

    protected static $_currentCacheKey = null;

    protected static $_storeCode = null;
    protected static $_isMobile = null;
    protected static $_isAjax = null;

    protected $_cache = null;
    protected $_sessionName = 'frontend';
    protected $_sessionStarted = false;

    protected $_formKey = null;

    protected $_coreInitTime = 0.015; // Used to measure time on PHP<5.4

    const DEFAULT_LIFETIME = 72;

    const PAGE_LOAD_HIT = 1;
    const PAGE_LOAD_MISS = 2;
    const PAGE_LOAD_NEVER_CACHE = 3;
    const PAGE_LOAD_IGNORE = 4;
    const PAGE_LOAD_HIT_UPDATE = 5;
    const PAGE_LOAD_HIT_SESSION = 6;
    const PAGE_LOAD_IGNORE_PARAM = 7;
    const PAGE_LOAD_CMS_UPDATE = 8;

    const DEBUG_SHOW_PARAM = 'amfpc_debug';
    const DEBUG_ENABLE_PARAM = 'amfpc_debug_enable';
    const DEBUG_DISABLE_PARAM = 'amfpc_debug_disable';

    protected $_ignoreParams = array(
        'amfpc_nocache',
        'amfpc_ajax_blocks',
        'SID'
    );

    protected $_disregardParams = array(
        '___from_store',
        '___store',
        self::DEBUG_SHOW_PARAM,
        self::DEBUG_ENABLE_PARAM,
        self::DEBUG_DISABLE_PARAM,
    );

    protected $_sessionParams = array(
        'limit' => 'limit_page',
        'order' => 'sort_order',
        'dir'   => 'sort_direction',
        'mode'  => 'display_mode'
    );

    protected $_urlInfo = array();

    public function __construct($args = false)
    {
        if ($args === false) { // Created with _getProcessor function
            $registryKey = '_singleton/amfpc/fpc_front';
            Mage::register($registryKey, $this, true);
        }

        if (isset($_SESSION)) {
            $this->_sessionName = session_name();
        }
        else
        {
            $sessionName = (string)Mage::app()
                ->getConfig()
                ->getNode('global/amfpc/session_name');

            if ($sessionName) {
                $this->_sessionName = $sessionName;
            }
        }

        $request = new Zend_Controller_Request_Http();

        $pathInfo = trim(strtok($request->getRequestUri(), '?'), '/');

        if ($this->getDbConfig('web/url/use_store')) {
            $pathParts = explode('/', $pathInfo);
            $storeCode = array_shift($pathParts);

            $this->_urlInfo = array(
                'store_code' => $storeCode,
                'page' => implode('/', $pathParts)
            );
        }
        else {
            $this->_urlInfo = array(
                'store_code' => false,
                'page' => $pathInfo
            );
        }
    }

    public function getCache()
    {
        if (!$this->_cache)
            $this->_cache = new Amasty_Fpc_Model_Fpc();

        return $this->_cache;
    }

    public function getStoreCode()
    {
        if (self::$_storeCode === null)
        {
            $store = isset($_COOKIE['store']) ? $_COOKIE['store'] : false;

            if (isset($_GET['___store']))
            {
                $code = $_GET['___store'];
            }
            else if ($this->_urlInfo['store_code'])
            {
                $code = $this->_urlInfo['store_code'];
            }

            if (isset($code) && $code)
            {
                $resource = Mage::getSingleton('core/resource');
                $adapter = $resource->getConnection('core_read');

                $select = $adapter->select()
                    ->from(array('store' => $resource->getTableName('core/store')), 'IF(group.group_id, 1, 0)')
                    ->joinLeft(
                        array('group' => $resource->getTableName('core/store_group')),
                        'group.group_id = store.group_id AND group.default_store_id = store.store_id',
                        array()
                    )
                    ->where('store.code = ?', $code)
                ;

                $result = $adapter->fetchOne($select);

                if ($result !== false)
                {
                    if ($result) // Default store
                        $store = false;
                    else
                        $store = $code;
                }
            }

            self::$_storeCode = $store;
        }

        return self::$_storeCode;
    }

    public function getCustomerInfo()
    {
        if (!$this->hasData('customer_info')) {

            $this->setData('customer_info', false);

            if (isset($_SESSION)) {
                foreach ($_SESSION as $key => $section) {
                    if (preg_match('/customer(_\w+)?/', $key)) {
                        $this->setData('customer_info', $section);
                        break;
                    }
                }
            }
        }

        return $this->getData('customer_info');
    }

    public function getCustomerGroupId()
    {
        if ($this->getDbConfig('amfpc/general/no_groups'))
            return false;

        $customerInfo = $this->getCustomerInfo();

        if ($customerInfo) {
            if (isset($customerInfo['customer_group_id'])) {
                return $customerInfo['customer_group_id'];
            }
            else if (isset($customerInfo['id'])) {
                return $this->_fetchCustomerGroupId($customerInfo['id']);
            }
        }

        return Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
    }

    public function getCustomerId()
    {
        $customerInfo = $this->getCustomerInfo();

        if ($customerInfo && isset($customerInfo['id'])) {
            return $customerInfo['id'];
        }

        return false;
    }

    public function _fetchCustomerGroupId($id)
    {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        $select = $adapter->select()
            ->from(
                array('customer' => $resource->getTableName('customer/entity')),
                'group_id'
            )
            ->where('entity_id = ?', $id)
        ;

        $groupId = +$adapter->fetchOne($select);

        return $groupId;
    }

    public function removeDisregardedParams($getParams)
    {
        if ($paramsString = $this->getDbConfig('amfpc/pages/disregard_params'))
        {
            $params = preg_split('/[,\s]+/', $paramsString, -1, PREG_SPLIT_NO_EMPTY);
            $params = array_merge($params, $this->_disregardParams);

            $disregarded = array_intersect_key(array_flip($params), $getParams);

            foreach ($disregarded as $name => $value)
                unset($getParams[$name]);

            // Page 1 on category view
            if (isset($getParams['p']) && $getParams['p'] == 1) {
                unset($getParams['p']);
            }
        }

        ksort($getParams);

        return $getParams;
    }

    public function applySessionParams($params)
    {
        $getParams = array_intersect_key($this->_sessionParams, $params);

        foreach ($this->_sessionParams as $getParam => $sessionParam) {
            if (isset($getParams[$getParam])) { // Param switched
                if ($params[$getParam] == $this->getDefaultParam($getParam)) { // Switch to default param
                    unset($params[$getParam]);

                    if (isset($_SESSION['catalog'][$sessionParam])) {
                        unset($_SESSION['catalog'][$sessionParam]);
                    }
                }
                else { // Switch to not default param
                    $_SESSION['catalog'][$sessionParam] = $params[$getParam];
                }
            }
            else { // Get param from session
                if (isset($_SESSION['catalog'][$sessionParam])) {
                    $params[$getParam] = $_SESSION['catalog'][$sessionParam];
                }
            }
        }

        ksort($params);

        return $params;
    }

    public function getDefaultParam($param)
    {
        switch ($param) {
            case 'limit':
                $mode = $this->getDbConfig(
                    'catalog/frontend/list_mode',
                    'grid_list'
                );

                $mode = explode('-', $mode);

                if ($mode == 'list') {
                    $limit = $this->getDbConfig(
                        'catalog/frontend/list_per_page',
                        10
                    );
                }
                else {
                    $limit = $this->getDbConfig(
                        'catalog/frontend/grid_per_page',
                        12
                    );
                }

                return $limit;

            case 'order':
                $defaultSort = $this->getDbConfig(
                    'catalog/frontend/default_sort_by',
                    'position'
                );

                return $defaultSort;

            case 'dir':
                return 'asc';

            case 'mode':
                $mode = $this->getDbConfig(
                    'catalog/frontend/list_mode',
                    'grid_list'
                );

                return $mode;

            default:
                return '';
        }
    }

    public function getFullUrl()
    {
        $protocol = $this->getSecureKey() ? 'https' : 'http';
        $url = "$protocol://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        return $url;
    }

    public function getNormalizedUrl()
    {
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        $params = $this->removeDisregardedParams($_GET);

        if ($this->isCategoryPage()) {
            $params = $this->applySessionParams($params);
        }

        if (!empty($params)) {
            $queryString = '?' . http_build_query($params);

            $url .= $queryString;
        }

        $protocol = $this->getSecureKey() ? 'https' : 'http';
        $url = "$protocol://{$_SERVER['HTTP_HOST']}$url";

        return $url;
    }

    public function getSecureKey()
    {
        return (int)(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    }

    public function getCacheKey()
    {
        if (self::$_currentCacheKey === null)
        {
            $mobile = $this->isMobile() ? 'm' : false;
            $currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : false;
            $store = $this->getStoreCode();
            $customerGroup = $this->getCustomerGroupId();

            $secure = $this->getSecureKey();
            $ajax = $this->isAjax() ? 'ajax_' : '';

            $url = $this->getNormalizedUrl();

            $key = 'amfpc_' . $ajax . $mobile . $currency . $store . $customerGroup
                . ($secure ? 'https://' : 'http://'). $_SERVER['HTTP_HOST']
                . $url
            ;

            self::$_currentCacheKey = sha1($key);
        }

        return self::$_currentCacheKey;
    }

    protected function getSessionSaveMethod()
    {
        return (string)Mage::app()->getConfig()->getNode('global/session_save');
    }

    protected function getSessionSavePath()
    {
        if ($sessionSavePath = Mage::app()->getConfig()->getNode('global/session_save_path'))
        {
            return $sessionSavePath;
        }

        return Mage::getBaseDir('session');
    }

    protected function isAdmin()
    {
        if (preg_match('|/key/\w{32,}/|', $_SERVER['REQUEST_URI']))
            return true;

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/adminhtml_"))
            return true;

        $config = Mage::app()->getConfig();

        $adminKey = (string)$config->getNode('admin/routers/adminhtml/args/frontName');

        if (FALSE !== strpos($_SERVER['REQUEST_URI'], "/$adminKey"))
            return true;
    }

    protected function startSession()
    {
        if (isset($_SESSION))
            return true;

        $moduleName = $this->getSessionSaveMethod();

        if ($moduleName == '') {
            $moduleName = 'files';
        }

        switch ($moduleName) {
            case 'db':
                $moduleName = 'user';
                if ($this->isModuleEnabled('Cm_RedisSession'))
                    $sessionResource = new Amasty_Fpc_Model_Resource_Redis_Session();
                else
                    $sessionResource = new Amasty_Fpc_Model_Resource_Session();

                $sessionResource->setSaveHandler();
                break;
            case 'user':
                call_user_func($this->getSessionSavePath());
                break;
            case 'files':
                if (!is_writable($this->getSessionSavePath())) {
                    break;
                }
            default:
                session_save_path($this->getSessionSavePath());
                break;
        }
        session_module_name($moduleName);

        session_name($this->_sessionName);

        session_start();

        if ($moduleName == 'files')
            $this->_sessionStarted = true;

        return true;
    }

    public function isModuleEnabled($module)
    {
        $fileConfig = new Mage_Core_Model_Config_Base();
        $fileConfig->loadFile(Mage::getBaseDir('etc') . DS . 'modules' . DS . $module . '.xml');

        $isActive = $fileConfig->getNode('modules/'.$module.'/active');

        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }

        return true;
    }

    protected function closeSession()
    {
        if ($this->_sessionStarted)
        {
            $_SESSION = null;
            session_write_close();
        }
    }

    protected function hasMessages()
    {
        if (isset($_SESSION))
        {
            foreach ($_SESSION as $section)
            {
                if (isset($section['messages']) && $section['messages'] instanceof Mage_Core_Model_Message_Collection)
                {
                    if ($section['messages']->count() > 0)
                        return true;
                }
            }
        }
        return false;
    }

    protected function checkSession()
    {
        $result = (isset($_SESSION['core']['visitor_data']['customer_id'])
            ||
            isset($_SESSION['customer_base']['id'])
            ||
            isset($_SESSION['core']['visitor_data']['quote_id'])
            ||
            isset($_SESSION['checkout']['last_added_product_id'])
            ||
            isset($_SESSION['checkout']['cart_was_updated'])
            ||
            isset($_SESSION['checkout']['checkout_state'])
            ||
            $this->hasMessages()
        );

        return $result;
    }

    protected function containsIgnoredParams()
    {
        if ($paramsString = $this->getDbConfig('amfpc/pages/ignored_params'))
        {
            $params = preg_split('/[,\s]+/', $paramsString, -1, PREG_SPLIT_NO_EMPTY);
            $params = array_merge($params, $this->_ignoreParams);

            if (!empty($params))
            {
                foreach ($params as $param)
                {
                    if (isset($_GET[$param]))
                    {
                        Mage::register('amfpc_ignored', self::PAGE_LOAD_IGNORE_PARAM, true);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isAjax()
    {
        if (self::$_isAjax === null) {
            self::$_isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        }

        return self::$_isAjax;
    }

    protected function ignore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET')
            return true;

        if ($this->isAdmin())
            return true;

        if ($this->containsIgnoredParams())
            return true;

        if ($this->inIgnoreList())
        {
            Mage::register('amfpc_ignorelist', true, true);
            return true;
        }

        if (!$this->isDynamicBlocksEnabled())
        {
            if (isset($_COOKIE[Mage_Persistent_Model_Session::COOKIE_NAME]))
                return true;
        }

        if (!isset($_COOKIE[$this->_sessionName]))
            return false;

        $this->startSession();

        $this->_formKey = isset($_SESSION['core']['_form_key']) ? $_SESSION['core']['_form_key'] : false;

        if (!$this->isDynamicBlocksEnabled())
        {
            if ($this->checkSession())
                return true;
        }

        return false;
    }

    public function isDynamicBlocksEnabled()
    {
        if ($this->_dynamicBlocks === null) {
            $this->_dynamicBlocks = (bool)$this->getDbConfig(
                'amfpc/general/dynamic_blocks',
                true
            );
        }

        return $this->_dynamicBlocks;
    }

    public function getBlockSessionKey($name)
    {
        $session = new Amasty_Fpc_Model_Session();

        if ($session->isBlockUpdated($name))
            $result = session_id();
        else
        {
            $blockConfig = (array)Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);

            $persistent = false;
            if (isset($blockConfig['@attributes']))
            {
                if (isset($blockConfig['@attributes']['store_switcher']))
                    return session_id();

                $persistentAttributes = array_intersect_assoc(
                    $blockConfig['@attributes'],
                    array('persistent' => 1, 'customer' => 1, 'cart' => 1)
                );

                if (!empty($persistentAttributes))
                    $persistent = true;
            }

            if ($persistent && isset($_COOKIE['persistent_shopping_cart']))
                $result = $_COOKIE['persistent_shopping_cart'];
            else
                $result = $this->getCustomerGroupId();
        }

        return $result;
    }

    public function getBlockCacheId($name)
    {
        $blockNode = Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);
        $scope = $blockNode ? (string) $blockNode['scope'] : '';

        $isUrlScope = ($scope == 'url');

        $session = $this->getBlockSessionKey($name);

        $url = $isUrlScope ? $_SERVER['REQUEST_URI'] : '';

        $mobile = $this->isMobile() ? 'm' : false;
        $secure = $this->getSecureKey();

        // TODO invalidate blocks cache on currency switch
        $currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : false;
        $store = $this->getStoreCode();

        $ajax = $this->isAjax() ? 'ajax_' : '';

        $key = $ajax.$secure.$_SERVER['HTTP_HOST'].$url.$name.$session.$mobile.$currency.$store;

        return sha1($key);
    }

    public function getBlockCacheTag($name)
    {
        $session = $this->getBlockSessionKey($name);

        return 'amfpc_block_' . sha1($name.$session);
    }

    public function regenerate($key)
    {
        $regen = $this->getDbConfig('amfpc/regen/by_visitor');

        if ($regen || $regen === null) {
            $lifetime = $this->getDbConfig('amfpc/general/page_lifetime');
            if ($lifetime === null) {
                $lifetime = self::DEFAULT_LIFETIME;
            }

            $lifetime *= 3600;

            $meta = $this->getCache()->getFrontend()->getMetadatas($key);

            $timeRemains = $meta['expire'] - time();

            $this->getCache()
                ->getFrontend()
                ->touch($key, $lifetime - $timeRemains);
        }
    }

    public function removeDiscardedBlocks(&$content)
    {
        $content = preg_replace_callback(
            '#<!--AMFPC_DISCARD\[(?P<agents>.+?)\]-->(?P<content>.+?)<!--AMFPC_DISCARD-->#s',
            array($this, 'discardCallback'),
            $content
        );
    }

    public function discardCallback($matches)
    {
        $helper = Mage::helper('core/http');
        $currentAgent = $helper->getHttpUserAgent();

        $agents = explode('|', $matches['agents']);

        foreach ($agents as $agent) {
            if (stripos($currentAgent, $agent) !== false) {
                return '';
            }
        }

        return $matches['content'];
    }

    protected function fetch()
    {
        $key = $this->getCacheKey();

        if ($data = $this->getCache()->loadPage($key))
        {
            $this->regenerate($key);

            $boostRobots = $this->_boostRobots();

            if (!isset($_COOKIE[$this->_sessionName])
                && !$boostRobots
                && (bool)(string)Mage::app()->getConfig()->getNode('global/amfpc/miss_on_first_view')
            )
            {
                return false;
            }

            $sessionRequired = $this->_isSessionRequired() && !$boostRobots;

            $page = $data['content'];

            $this->removeDiscardedBlocks($page);

            if (!$sessionRequired)
            {
                $page = str_replace('AMFPC_FORM_KEY', $this->_formKey, $page);
            }
            else
            {
                Mage::register('amfpc_new_session', true, true);
            }

            if ($this->isDynamicBlocksEnabled())
            {
                $requiredBlocks = $this->getRequiredBlocks($page);
                $ajaxBlocks = $this->getRequiredAjaxBlocks($page);

                $referer = $this->getFullUrl();
                $referer = strtr(base64_encode($referer), '+/=', '-_,');

                $validBlocks = array();
                $invalidBlocks = array();
                foreach ($requiredBlocks as $name)
                {
                    $blockConfig = (array)Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);

                    $cookieBlock = Amasty_Fpc_Model_Config::getCookieNoticeBlockName();
                    if (($name == $cookieBlock && self::$_cookieUpdated) ||
                        ($name == 'google_analytics'
                            && (
                                (
                                    !self::$_cookieUpdated
                                    && $this->getDbConfig(Mage_Core_Helper_Cookie::XML_PATH_COOKIE_RESTRICTION)
                                )
                                || !$this->getDbConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACTIVE)
                            )
                        )
                    ) {
                        // Possible issues:
                        // - Mage_GoogleAnalytics may be disabled in Mage_All.xml
                        // - Account Number may be empty
                        $content = '';
                    }
                    else if ($boostRobots
                        && isset($blockConfig['@attributes']['store_switcher'])) {
                        $content = '';
                    }
                    else
                    {
                        $id = $this->getBlockCacheId($name);
                        $content = $this->getCache()->load($id);
                    }

                    if ($content !== false)
                    {
                        $content = preg_replace('#/referer/[A-Za-z0-9\-_,]+/#', "/referer/$referer/", $content);

                        if (!$sessionRequired)
                            $content = str_replace('AMFPC_FORM_KEY', $this->_formKey, $content);

                        $validBlocks[$name] = $content;
                    }
                    else
                        $invalidBlocks[] = $name;
                }

                foreach ($validBlocks as $name => $content)
                {
                    $blockNode = Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);

                    $debugName = $name;
                    if (isset($blockNode['parent']))
                        $debugName .= "[{$blockNode['parent']}]";

                    $this->addBlockInfo($content, $debugName);
                    $page = str_replace("<amfpc name=\"$name\" />", $content, $page);
                }

                if (!empty($invalidBlocks))
                {
                    $cmsBlocks = (bool)(string)Mage::app()->getConfig()->getNode('global/amfpc/cms_blocks');

                    if ($cmsBlocks)
                    {
                        Mage::register('amfpc_cms_blocks', true, true);
                    }
                    else
                    {
                        $info = array(
                            'page' => $page,
                            'blocks' => array_merge($ajaxBlocks, $invalidBlocks),
                        );

                        Mage::register('amfpc_blocks', $info, true);
                    }
                    return false;
                }
                else
                {
                    if (!empty($ajaxBlocks))
                        $page = $this->addAjaxLoad($page, $ajaxBlocks);
                }
            }

            $page = preg_replace(
                '#<amfpc_ajax name="([^"]+)" />#s',
                '<div id="amfpc-\1"></div>',
                $page
            );

            if ($sessionRequired)
            {
                Mage::register('amfpc_page', $page, true);
                return false;
            }

            if (isset($_GET['___store']))
                setcookie('store', $this->getStoreCode(), 0, '/', '.' . $_SERVER['HTTP_HOST']);

            $this->addBlockInfo($page, 'Early page load');
            $this->addLoadTimeInfo($page);

            $this->_processPageActions($data);

            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');

            return $page;
        }
        else
        {
            Mage::register('amfpc_preserve', true, true);
            return false;
        }
    }

    protected function _processPageActions($data)
    {
        if (isset($data['category_id']) && !isset($data['product_id']) && isset($_SESSION['catalog']))
        {
            $_SESSION['catalog']['last_visited_category_id'] = $data['category_id'];
            $_SESSION['catalog']['last_viewed_category_id'] = $data['category_id'];
        }
        if (isset($data['product_id']) && isset($_SESSION['catalog']) && isset($data['store_id']))
        {
            $_SESSION['catalog']['last_viewed_product_id'] = $data['product_id'];

            $customerId = $this->getCustomerId();
            $info = array(
                'product_id' => $data['product_id'],
                'store_id' => $data['store_id'],
                'customer_id' => $customerId ? $customerId : null,
                'visitor_id' => isset($_SESSION['core']['visitor_data']['visitor_id']) ? $_SESSION['core']['visitor_data']['visitor_id'] : null,
                'added_at' => date('Y-m-d H:i:s')
            );


            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            try {
                $connection->insertOnDuplicate(
                    $resource->getTableName('reports/viewed_product_index'),
                    $info,
                    array_keys($info)
                );

                unset($_SESSION['reports']['product_index_viewed_count']);
            } catch (Exception $e) {
                Mage::log(
                    array('Failed to update reports table', $info),
                    null,
                    'amfpc.log',
                    true
                );
            }
        }
    }

    public function getDbConfig($path, $default = null)
    {
        if (self::$_configCache === null)
        {
            $resource = Mage::getSingleton('core/resource');
            $adapter = $resource->getConnection('core_read');

            $select = $adapter->select()
                ->from($resource->getTableName('core/config_data'), array('path', 'value'))
                ->where('path LIKE \'amfpc/%\' OR path IN (?)', self::$_additionalConfigs)
                ->where('scope_id=?', 0)
            ;

            self::$_configCache = $adapter->fetchAll($select, array(), PDO::FETCH_KEY_PAIR);
        }

        if (isset(self::$_configCache[$path]))
            return self::$_configCache[$path];
        else
            return $default;
    }

    public function allowedDebugInfo()
    {
        if ($this->_debugInfo === null)
        {
            if (!isset($_SERVER['REMOTE_ADDR']) || $this->isAjax())
            {
                $this->_debugInfo = false;
            }
            else
            {
                if ($this->_cookieDebugTrigger && isset($_COOKIE['amfpc_debug'])) {
                    $this->_debugInfo = true;
                    return true;
                }

                $ips = $this->getDbConfig('amfpc/debug/ip');
                $ips = preg_split('/[,\s]+/', $ips, -1, PREG_SPLIT_NO_EMPTY);

                $this->_debugInfo = empty($ips) || in_array($_SERVER['REMOTE_ADDR'], $ips);

                if ($this->getDbConfig('amfpc/debug/get_params')) {
                    if (isset($_GET[self::DEBUG_SHOW_PARAM]) || isset($_SESSION['amfpc']['debug'])) {
                        $this->_debugInfo = true;
                    }
                }
            }
        }

        return $this->_debugInfo;
    }

    public function isDebugEnabled()
    {
        if ($this->_debug === null)
            $this->_debug = $this->getDbConfig('amfpc/debug/hints') && $this->allowedDebugInfo();

        return $this->_debug;
    }

    public function addLoadTimeInfo(&$html, $type = self::PAGE_LOAD_HIT)
    {
        global $amfpc_start_time;

        $displayPopup = $this->allowedDebugInfo() && $this->getDbConfig('amfpc/debug/load_time');
        $displayHidden = $this->getDbConfig('amfpc/debug/hidden_stats');

        if (!$displayHidden && !$displayPopup)
            return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET')
            return;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return;

        if (isset($_SERVER['REQUEST_TIME_FLOAT']))
        {
            $time = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3);
        }
        else if ($amfpc_start_time)
        {
            $time = round(microtime(true) - $amfpc_start_time + $this->_coreInitTime, 3);
        }
        else
            return;

        switch ($type)
        {
            case self::PAGE_LOAD_MISS:
                $typeTitle = "Cache Miss";
                break;
            case self::PAGE_LOAD_NEVER_CACHE:
                $typeTitle = "Never cache";
                break;
            case self::PAGE_LOAD_IGNORE:
                $typeTitle = "In ignore list";
                break;
            case self::PAGE_LOAD_HIT_UPDATE:
                $typeTitle = "Cache Hit<br/>(with block updates)";
                break;
            case self::PAGE_LOAD_HIT_SESSION:
                $typeTitle = "Cache Hit<br/>(with session initialization)";
                break;
            case self::PAGE_LOAD_IGNORE_PARAM:
                $typeTitle = "Contains ignored params";
                break;
            case self::PAGE_LOAD_CMS_UPDATE:
                $typeTitle = "Block updates";
                break;
            default:
                $typeTitle = "Cache Hit";
                break;
        }

        $popup = <<<POPUP
<div class="amfpc-info">
    <h1>Full Page Cache</h1>
    <div class="amfpc-content">
        <div>$typeTitle</div>
        <strong>Load Time: </strong>{$time}s
    </div>
</div>
POPUP;

        $hiddenHtml = '<!-- AMFPC|' . strip_tags($typeTitle) . '|' . $time . 's -->';

        $resultHtml = '</body>';

        if ($displayHidden)
            $resultHtml = $hiddenHtml . $resultHtml;

        if ($displayPopup)
            $resultHtml = $popup . $resultHtml;

        $html = str_replace('</body>', $resultHtml, $html);
    }

    public function addBlockInfo(&$html, $message)
    {
        if (!$this->isDebugEnabled())
            return false;

        $html = <<<HTML
<div class="amfpc-block-info updated">
<div class="amfpc-block-handle"
     onmouseover="$(this).parentNode.addClassName('active')"
     onmouseout="$(this).parentNode.removeClassName('active')"
>FPC: {$message}</div>$html</div>
HTML;
    }

    protected function addAjaxLoad($page, $names)
    {
        $names = implode(',', $names);
        $js = <<<AJAX
<script type="text/javascript">
new Ajax.Request('{$_SERVER['REQUEST_URI']}', {
    parameters: {amfpc_ajax_blocks: '$names'},
    onSuccess: function(response) {
        var blocks = response.responseText.evalJSON();
        for (var name in blocks)
        {
            $$('div[id=amfpc-'+name+']').each(function(element){
                element.replace(blocks[name]);
            });
        }
    }
});
</script>
AJAX;

        $page = str_replace("</body>", "\n$js\n</body>", $page);

        return $page;
    }

    protected function getRequiredAjaxBlocks($page)
    {
        $fpcConfig = new Amasty_Fpc_Model_Config();
        $config = $fpcConfig->getConfig();

        $ajaxBlocks = $config['ajax_blocks'];
        if (!$this->hasMessages())
        {
            if (isset($ajaxBlocks['global_messages']))
                unset($ajaxBlocks['global_messages']);

            if (isset($ajaxBlocks['messages']))
                unset($ajaxBlocks['messages']);
        }

        $ajaxBlocks = $this->getPageBlocks($page, array_keys($ajaxBlocks));

        return $ajaxBlocks;
    }

    protected function getRequiredBlocks($page)
    {
        $fpcConfig = new Amasty_Fpc_Model_Config();
        $config = $fpcConfig->getConfig();

        $blocks = $this->getPageBlocks($page, array_keys($config['blocks']));

        return $blocks;
    }

    protected function getPageBlocks($page, $names)
    {
        $blocks = array();

        if (preg_match_all('|<amfpc(_ajax)? name="(?P<name>[^"]+)"\s*/>|', $page, $matches))
        {
            $blocks = array_intersect($names, $matches['name']);
        }

        return $blocks;
    }

    protected function _isSessionRequired()
    {
        $version = Mage::getVersionInfo();

        if ($version['minor'] >= 8 && !isset($_COOKIE[$this->_sessionName]))
            return true;
        else
            return false;
    }

    protected function _boostRobots()
    {
        if ($this->isCrawlerRequest())
            return true;

        if ($this->getDbConfig('amfpc/robots/boost_robots'))
        {
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                return false;

            $agents = $this->getDbConfig('amfpc/robots/agents');
            if (!$agents)
                return false;

            return @preg_match('@' . $agents . '@', $_SERVER['HTTP_USER_AGENT']);
        }
        else
            return false;

    }

    public function isMobile()
    {
        if (self::$_isMobile === null)
        {
            self::$_isMobile = false;

            if (isset($_SERVER['HTTP_USER_AGENT']) && $this->getDbConfig('amfpc/mobile/enabled'))
            {
                $regexp = $this->getDbConfig('amfpc/mobile/agents');

                if (@preg_match('@' . $regexp . '@', $_SERVER['HTTP_USER_AGENT']))
                {
                    self::$_isMobile = true;
                }
            }
        }

        return self::$_isMobile;
    }

    public function inIgnoreList()
    {
        if ($ignore = $this->getDbConfig('amfpc/pages/ignore_list'))
        {
            $ignoreList = preg_split('|[\r\n]+|', $ignore, -1, PREG_SPLIT_NO_EMPTY);
            $path = $_SERVER['REQUEST_URI'];

            foreach ($ignoreList as $pattern)
            {
                if (preg_match("|$pattern|", $path))
                    return true;
            }
        }

        return false;
    }

    protected function _checkCookiesAgreement()
    {
        self::$_cookieUpdated = isset($_COOKIE['user_allowed_save_cookie']);
    }

    public function incrementHits()
    {
        if ($this->isAjax())
            return;

        if ($this->isCrawlerRequest())
            return;

        if (!$this->getDbConfig('amfpc/stats/visits'))
            return;

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = $resource->getConnection('core_write');

        if (!method_exists($adapter, 'insertIgnore')) { // Compatibility with Magento < 1.8.1
            $adapter->insertOnDuplicate(
                $resource->getTableName('amfpc/url'),
                array(
                    'url' => $this->getNormalizedUrl(),
                    'rate' => 1
                ),
                array('rate'=>new Zend_Db_Expr('rate+1'))
            );
        }
        else {
            $select = $adapter->select()
                ->from($resource->getTableName('amfpc/url'), 'rate')
                ->where('url =?', $this->getNormalizedUrl());
            ;

            $rate = $adapter->fetchOne($select);

            if ($rate === false) {
                $adapter->insertIgnore(
                    $resource->getTableName('amfpc/url'),
                    array(
                        'url' => $this->getNormalizedUrl(),
                        'rate' => 1
                    )
                );
            }
            else {
                $adapter->update(
                    $resource->getTableName('amfpc/url'),
                    array('rate' => $rate + 1),
                    array('url = ?' => $this->getNormalizedUrl())
                );
            }
        }
    }

    public function isCrawlerRequest()
    {
        return isset($_COOKIE['amfpc_crawler']);
    }

    public function isCategoryPage()
    {
        $url = trim($this->_urlInfo['page'], '/');

        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');

        if (Mage::getEdition() == Mage::EDITION_COMMUNITY) {
            $select = $adapter->select()
                ->from(array('rewrite' => $resource->getTableName('core/url_rewrite')), 'COUNT(*)')
                ->where('request_path IN (?)', array($url, $url.'/'))
                ->where('id_path LIKE ?', 'category/%')
            ;
        }
        else {
            $select = $adapter->select()
                ->from(array('rewrite' => $resource->getTableName('enterprise_url_rewrite')), 'COUNT(*)')
                ->where('request_path IN (?)', array($url, $url.'/'))
                ->where('target_path LIKE ?', 'catalog/category/%')
            ;
        }

        $result = $adapter->fetchOne($select);

        return (bool)$result;
    }

    public function toggleDebug()
    {
        if (isset($_SESSION)) {
            if (isset($_GET[self::DEBUG_ENABLE_PARAM])) {
                $_SESSION['amfpc']['debug'] = true;
            }
            else if (isset($_GET[self::DEBUG_DISABLE_PARAM])) {
                unset($_SESSION['amfpc']['debug']);
            }
        }
    }

    public function extractContent()
    {
        if (!isset($_SERVER['HTTP_HOST']))
            return false;

        if (!Mage::app()->useCache('amfpc'))
            return false;

        global $amfpc_start_time;
        $amfpc_start_time = microtime(true);

        $this->_checkCookiesAgreement();

        if (!$this->ignore())
        {
            $this->toggleDebug();

            $content = $this->fetch();

            if ($content)
            {
                $this->incrementHits();
                Mage::app()->getResponse()->appendBody($content)->sendResponse();
                exit;
            }
        }

        $this->closeSession();

        return false;
    }
}
