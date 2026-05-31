<?php
/**
 * Open Source Social Network
 *
 * @package   AccountApproval
 * @author    Custom
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
$en = array(
    // Admin menu link
    'account:approval:menu:title'        => 'Approval Manager',

    // Override OSSN core registration success message to inform users about moderation
    'account:created:email'              => 'Your account has been registered! We have sent you an account activation email. If you didn\'t receive the email, please check your spam/junk folder. Once validated, you are welcome to browse the site, but posting, commenting, messaging, and uploads will be available only after an admin approves your account. This is to prevent spammers from posting.',

    // Status labels
    'account:approval:status:pending'  => 'Pending',
    'account:approval:status:approved' => 'Approved',

    // Shown to a pending user when they try to post/comment/message/upload
    'account:approval:blocked:message' => 'Your account is pending admin approval. You can browse the site, but posting, commenting, messaging, and file uploads will be available once your account is approved.',

    // Admin panel
    'account:approval:admin:panel:title' => 'Account Approval Management',
    'account:approval:legend:pending'    => 'user can login and read, but cannot post/comment/message/upload',
    'account:approval:legend:approved'   => 'user has full access',
    'account:approval:no:users'          => 'No users found.',
    'account:approval:col:user'          => 'User',
    'account:approval:col:email'         => 'Email',
    'account:approval:col:registered'    => 'Registered',
    'account:approval:col:status'        => 'Status',
    'account:approval:col:action'        => 'Action',
    'account:approval:btn:approve'       => 'Approve',
    'account:approval:btn:setpending'    => 'Set Pending',

    // Action feedback
    'account:approval:status:updated'   => 'Status for %s has been set to "%s".',
    'account:approval:status:failed'    => 'Could not update approval status. Please try again.',
    'account:approval:invalid:user'     => 'Invalid user specified.',
    'account:approval:invalid:status'   => 'Invalid status value.',
    'account:approval:user:notfound'    => 'User not found.',
    'account:approval:admin:required'   => 'You must be an administrator to perform this action.',

    // Email to user when they are approved (%s = full name, %s = site URL)
    'account:approval:email:user:subject' => 'Your account has been approved!',
    'account:approval:email:user:body'    => "Hi %s,\n\nGreat news - your account has been approved! You now have full access to post, comment, message, and upload files.\n\nVisit the site here: %s\n\nWelcome aboard!",
);
ossn_register_languages('en', $en);
