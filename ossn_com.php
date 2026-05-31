<?php
/**
 * Open Source Social Network
 *
 * @package   AccountApproval
 * @author    Custom
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */

define('__OSSN_ACCOUNT_APPROVAL__', ossn_route()->com . 'AccountApproval/');

/**
 * Initialize AccountApproval component
 *
 * @return void
 * @access private
 */
function ossn_account_approval_init() {
    // Register the admin approve/pending action
    ossn_register_action('admin/approval/set', __OSSN_ACCOUNT_APPROVAL__ . 'actions/admin_set_approval.php');

    // Hook: after a new user is created, write pending status to DB
    ossn_register_callback('user', 'created', 'ossn_account_approval_set_pending');

    // Hook: fires before ANY action executes - intercept restricted ones only
    ossn_register_callback('action', 'load', 'ossn_account_approval_intercept_actions');

    // Add "Approval Manager" link under User Manager menu in admin bar
    ossn_register_menu_item('admin/sidemenu', array(
        'name'   => 'account:approval:menu',
        'text'   => ossn_print('account:approval:menu:title'),
        'href'   => ossn_site_url('admin-approval'),
        'parent' => 'admin:sidemenu:usermanager',
    ));

    // Register a dedicated admin page at: yoursite.com/admin-approval
    // Renders inside the full OSSN admin dashboard layout
    if (ossn_isAdminLoggedin()) {
        ossn_register_page('admin-approval', 'ossn_account_approval_pagehandler');
    }
}

/**
 * Write approval_status directly to ossn_entities table.
 * Uses a raw DB update/insert to avoid any interference with OSSN's
 * user save() flow. This is the same storage used for birthdate/gender.
 *
 * @param int    $guid   User GUID
 * @param string $status 'pending' or 'approved'
 * @return bool
 */
function ossn_account_approval_write_status($guid, $status) {
    $guid = (int) $guid;
    if ($guid <= 0) {
        return false;
    }

    // Check if an approval_status entity already exists for this user
    $check = new OssnEntities;
    $check->owner_guid = $guid;
    $check->type       = 'user';
    $check->subtype    = 'approval_status';
    $check->page_limit = false;
    $existing          = $check->get_entities();

    if ($existing) {
        // Update the existing entity's value in ossn_entities_metadata
        foreach ($existing as $e) {
            $params = array(
                'table'  => 'ossn_entities_metadata',
                'names'  => array('value'),
                'values' => array($status),
                'wheres' => array("guid='{$e->guid}'"),
            );
            $check->update($params);
        }
        return true;
    }

    // No existing entity - insert a new one
    $entity             = new OssnEntities;
    $entity->owner_guid = $guid;
    $entity->type       = 'user';
    $entity->subtype    = 'approval_status';
    $entity->value      = $status;
    return (bool) $entity->add();
}

/**
 * Read approval_status from ossn_entities for a given user GUID.
 * The status is also available as $user->approval_status on any loaded
 * user object (merged automatically by getUser/searchUsers).
 *
 * @param  int $guid User GUID
 * @return string 'pending', 'approved', or '' (no status set)
 */
function ossn_account_approval_read_status($guid) {
    $guid = (int) $guid;
    if ($guid <= 0) {
        return '';
    }
    $entity             = new OssnEntities;
    $entity->owner_guid = $guid;
    $entity->type       = 'user';
    $entity->subtype    = 'approval_status';
    $entity->page_limit = false;
    $existing           = $entity->get_entities();

    if ($existing) {
        foreach ($existing as $e) {
            return isset($e->value) ? (string) $e->value : '';
        }
    }
    return '';
}

/**
 * Set a newly registered user to "pending" status.
 * Fires after the user record is fully committed to the DB.
 *
 * @param string $callback
 * @param string $type
 * @param array  $params  Contains 'guid'
 * @return void
 * @access private
 */
function ossn_account_approval_set_pending($callback, $type, $params) {
    if (empty($params['guid'])) {
        return;
    }
    ossn_account_approval_write_status((int) $params['guid'], 'pending');
}

/**
 * Intercept restricted actions before they execute.
 * Only blocks users whose approval_status is 'pending'.
 * Does NOT affect login - ossn_loggedin_user() is null during login.
 *
 * @param string $callback
 * @param string $type
 * @param array  $vars   Contains $vars['action']
 * @return void
 * @access private
 */
function ossn_account_approval_intercept_actions($callback, $type, $vars) {
    // Exact action names registered by each OSSN component
    $blocked_actions = array(
        // Wall posts (home/news feed, user wall, group wall)
        'wall/post/a',
        'wall/post/u',
        'wall/post/g',
        'wall/post/edit',
        'wall/post/embed',
        // Comments
        'post/comment',
        'post/entity/comment',
        'post/object/comment',
        'comment/edit',
        'comment/embed',
        // Messages
        'message/send',
        // Photos
        'ossn/photos/add',
        'ossn/album/add',
        // Files
        'file/upload',
    );

    if (!isset($vars['action']) || !in_array($vars['action'], $blocked_actions, true)) {
        return; // Not a restricted action - let it through
    }

    $user = ossn_loggedin_user();
    if (!$user) {
        return; // Not logged in - OSSN's own auth handles this
    }

    // Get status from user object (fastest - already loaded from DB)
    // Falls back to direct DB read if property not present
    $status = (isset($user->approval_status) && !empty($user->approval_status))
        ? $user->approval_status
        : ossn_account_approval_read_status($user->guid);

    if ($status !== 'pending') {
        return; // Approved or pre-existing account - let through
    }

    // Block the action
    $message = ossn_print('account:approval:blocked:message');
    ossn_trigger_message($message, 'error');

    if (ossn_is_xhr()) {
        // Most post/comment/upload actions are AJAX
        header('Content-Type: application/json');
        echo json_encode(array('error' => array($message)));
        exit;
    }

    redirect(REF);
    exit;
}

/**
 * Page handler for the Approval Manager admin page.
 * URL: yoursite.com/admin-approval
 * Renders inside the full OSSN admin dashboard layout.
 *
 * @return void
 * @access private
 */
function ossn_account_approval_pagehandler() {
    if (!ossn_isAdminLoggedin()) {
        ossn_error_page();
        return;
    }
    $title                = ossn_print('account:approval:admin:panel:title');
    $contents['contents'] = ossn_plugin_view('account_approval/admin/manage');
    $contents['title']    = $title;
    $content              = ossn_set_page_layout('administrator/administrator', $contents);
    echo ossn_view_page($title, $content, 'administrator');
}

// Initialize the component
ossn_register_callback('ossn', 'init', 'ossn_account_approval_init');
