<?php
/**
 * Open Source Social Network
 *
 * @package   AccountApproval
 * @author    Custom
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */

$OssnUser  = new OssnUser;
$all_users = $OssnUser->getSiteUsers(array('page_limit' => false));

if (empty($all_users)) {
    echo '<p>' . ossn_print('account:approval:no:users') . '</p>';
    return;
}

// Sort: pending first, then approved
usort($all_users, function($a, $b) {
    // approval_status is loaded as a top-level property via entity subtype merge
    $sa = !empty($a->approval_status) ? $a->approval_status : ossn_account_approval_read_status($a->guid);
    $sb = !empty($b->approval_status) ? $b->approval_status : ossn_account_approval_read_status($b->guid);
    return strcmp($sa, $sb);
});

?>
<div class="account-approval-panel">
    <h3><?php echo ossn_print('account:approval:admin:panel:title'); ?></h3>
    <p style="font-size:13px;color:#555;margin-bottom:12px;">
        <span class="approval-badge approval-pending"><?php echo ossn_print('account:approval:status:pending'); ?></span>
        &nbsp;<?php echo ossn_print('account:approval:legend:pending'); ?>
        &nbsp;&nbsp;&nbsp;
        <span class="approval-badge approval-approved"><?php echo ossn_print('account:approval:status:approved'); ?></span>
        &nbsp;<?php echo ossn_print('account:approval:legend:approved'); ?>
    </p>
    <table class="account-approval-table">
        <thead>
            <tr>
                <th><?php echo ossn_print('account:approval:col:user'); ?></th>
                <th><?php echo ossn_print('account:approval:col:email'); ?></th>
                <th><?php echo ossn_print('account:approval:col:registered'); ?></th>
                <th><?php echo ossn_print('account:approval:col:status'); ?></th>
                <th><?php echo ossn_print('account:approval:col:action'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($all_users as $user):
            if (isset($user->type) && $user->type === 'admin') continue;
            // Get status - entity subtype is merged as top-level property on load
            $status = !empty($user->approval_status)
                ? $user->approval_status
                : ossn_account_approval_read_status($user->guid);
            // Users with no status are pre-existing accounts - show as approved
            if (empty($status)) {
                $status = 'approved';
            }
            $reg_date = date('Y-m-d', (int) $user->time_created);
        ?>
            <tr class="approval-row-<?php echo htmlspecialchars($status); ?>">
                <td>
                    <a href="<?php echo $user->profileURL(); ?>" target="_blank">
                        <?php echo htmlspecialchars($user->fullname); ?>
                        &nbsp;<small>(<?php echo htmlspecialchars($user->username); ?>)</small>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($user->email); ?></td>
                <td><?php echo $reg_date; ?></td>
                <td>
                    <span class="approval-badge approval-<?php echo htmlspecialchars($status); ?>">
                        <?php echo ossn_print('account:approval:status:' . $status); ?>
                    </span>
                </td>
                <td>
                    <?php if ($status === 'pending'): ?>
                        <form method="post" action="<?php echo ossn_site_url('action/admin/approval/set'); ?>">
                            <?php echo ossn_plugin_view('input/security_token'); ?>
                            <input type="hidden" name="guid"   value="<?php echo (int) $user->guid; ?>">
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="approval-btn btn-approve">
                                <?php echo ossn_print('account:approval:btn:approve'); ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?php echo ossn_site_url('action/admin/approval/set'); ?>">
                            <?php echo ossn_plugin_view('input/security_token'); ?>
                            <input type="hidden" name="guid"   value="<?php echo (int) $user->guid; ?>">
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="approval-btn btn-pending">
                                <?php echo ossn_print('account:approval:btn:setpending'); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.account-approval-panel { margin: 20px 0; }
.account-approval-panel h3 { margin-bottom: 8px; }
.account-approval-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.account-approval-table th,
.account-approval-table td { padding: 7px 10px; border: 1px solid #ddd; vertical-align: middle; }
.account-approval-table thead th { background: #f0f0f0; font-weight: bold; }
.approval-row-pending  { background: #fffbe6; }
.approval-row-approved { background: #f6ffed; }
.approval-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold; text-transform:uppercase; }
.approval-pending  { background:#ffe58f; color:#7c5800; }
.approval-approved { background:#b7eb8f; color:#2a5c00; }
.approval-btn { padding:4px 12px; border:none; border-radius:3px; cursor:pointer; font-size:12px; }
.btn-approve { background:#52c41a; color:#fff; }
.btn-approve:hover { background:#389e0d; }
.btn-pending { background:#faad14; color:#fff; }
.btn-pending:hover { background:#d48806; }
</style>
