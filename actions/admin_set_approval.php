<?php
/**
 * Open Source Social Network
 *
 * @package   AccountApproval
 * @author    Custom
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */

if (!ossn_isAdminLoggedin()) {
    ossn_trigger_message(ossn_print('account:approval:admin:required'), 'error');
    redirect(ossn_site_url());
    exit;
}

$guid   = (int) input('guid');
$status = input('status');

if ($guid <= 0) {
    ossn_trigger_message(ossn_print('account:approval:invalid:user'), 'error');
    redirect(REF);
    exit;
}

$allowed = array('approved', 'pending');
if (!in_array($status, $allowed, true)) {
    ossn_trigger_message(ossn_print('account:approval:invalid:status'), 'error');
    redirect(REF);
    exit;
}

$user = ossn_user_by_guid($guid);
if (!$user) {
    ossn_trigger_message(ossn_print('account:approval:user:notfound'), 'error');
    redirect(REF);
    exit;
}

// Write status directly via entity API - same method used at registration
$saved = ossn_account_approval_write_status($guid, $status);

if ($saved) {
    ossn_trigger_message(ossn_print('account:approval:status:updated', array($user->fullname, $status)), 'success');

    // Send approval email to the user
    if ($status === 'approved' && !empty($user->email)) {
        $subject = ossn_print('account:approval:email:user:subject');
        $body    = ossn_print('account:approval:email:user:body', array(
            $user->fullname,
            ossn_site_url(),
        ));
        $mail = new OssnMail;
        $mail->notifyUser($user->email, $subject, $body);
    }
} else {
    ossn_trigger_message(ossn_print('account:approval:status:failed'), 'error');
}

redirect(REF);
exit;
