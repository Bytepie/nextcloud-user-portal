<div id="user-management">
    <h2>User Storage Management</h2>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Used Space</th>
                <th>Quota</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_['users'] as $user): ?>
                <tr>
                    <td><?php p($user['displayName']) ?></td>
                    <td><?php p(OC_Helper::humanFileSize($user['storage']['used'])) ?></td>
                    <td><?php p($user['storage']['quota'] ?: 'Default') ?></td>
                    <td>
                        <?php if ($_['isAdmin']): ?>
                            <button class="edit-quota" data-user="<?php p($user['id']) ?>">Edit Quota</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>