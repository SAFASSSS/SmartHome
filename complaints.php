<?php
require_once 'includes/bootstrap.php';
require_once 'includes/layout.php';

$u = require_login('resident', 'manager', 'admin');
$isRes = $u['role'] === 'resident';

if (is_post()) {
    csrf_check();
    $act = $_POST['action'] ?? '';

    if ($act === 'create' && $isRes) {
        $desc = trim($_POST['description'] ?? '');

        if ($desc === '') {
            flash('Please describe the problem.', 'err');
        } else {
            $cat = in_array(
                $_POST['category'] ?? '',
                ['Plumbing', 'Electrical', 'HVAC', 'Security', 'Cleaning', 'Other'],
                true
            ) ? $_POST['category'] : 'Other';

            $pri = in_array(
                $_POST['priority'] ?? '',
                ['low', 'normal', 'urgent'],
                true
            ) ? $_POST['priority'] : 'normal';

            q(
                'INSERT INTO complaints (resident_id,category,description,priority) VALUES (?,?,?,?)',
                [$u['id'], $cat, $desc, $pri]
            );

            notify_role(
                'manager',
                ($pri === 'urgent' ? 'URGENT: ' : '') .
                "New $cat complaint from {$u['name']}"
            );

            flash('Complaint submitted. You will be notified of every update.');
        }

    } elseif ($act === 'assign' && !$isRes) {

        $id = (int)$_POST['id'];
        $tech = (int)$_POST['technician_id'];

        $c = q(
            'SELECT * FROM complaints WHERE id=?',
            [$id]
        )->fetch();

        $technician = q(
            "SELECT id FROM users WHERE id=? AND role='technician' AND status='active'",
            [$tech]
        )->fetch();

        if ($c && $technician) {
            q(
                "UPDATE complaints SET technician_id=?, status='assigned' WHERE id=?",
                [$tech, $id]
            );

            notify(
                (int)$c['resident_id'],
                'Your complaint ' . code($id) . ' has been assigned to a technician.'
            );

            notify(
                $tech,
                'New ticket assigned: ' . code($id) . ' (' . $c['category'] . ')'
            );

            flash('Technician assigned.');
        }
    }

    redirect('complaints.php');
}

$where = $isRes
    ? 'WHERE c.resident_id=' . (int)$u['id']
    : '';

$rows = q(
    "SELECT
        c.*,
        r.name AS rname,
        t.name AS tname
     FROM complaints c
     JOIN users r ON r.id = c.resident_id
     LEFT JOIN users t ON t.id = c.technician_id
     $where
     ORDER BY
        c.status='resolved',
        c.priority='urgent' DESC,
        c.created_at DESC"
)->fetchAll();

$techs = $isRes
    ? []
    : q(
        "SELECT id,name,specialty
         FROM users
         WHERE role='technician' AND status='active'"
    )->fetchAll();

page_start('Complaints', 'complaints.php');
?>

<div class="head">
    <div>
        <h1><?= $isRes ? 'My Complaints' : 'Complaint Management' ?></h1>
        <p>
            <?= $isRes
                ? 'Submit and track maintenance requests'
                : 'Assign technicians and monitor progress' ?>
        </p>
    </div>
</div>

<?php if ($isRes): ?>

<form class="card" method="post" style="margin-bottom:18px">
    <?= csrf_field() ?>


<input type="hidden" name="action" value="create">

<h3>New complaint</h3>

<div class="row2">

    <div class="field">
        <label>Category</label>

        <select name="category">
            <?php foreach (['Plumbing', 'Electrical', 'HVAC', 'Security', 'Cleaning', 'Other'] as $c): ?>
                <option value="<?= e($c) ?>">
                    <?= e($c) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label>Priority</label>

        <select name="priority">
            <option value="low">Low</option>
            <option value="normal" selected>Normal</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>

</div>

<div class="field">
    <label>Description</label>

    <textarea
        name="description"
        placeholder="What's wrong? Where in your flat?"
        required
    ></textarea>
</div>

<button class="btn p">
    <?= icon('plus', 15) ?>
    Submit complaint
</button>


</form>

<?php endif; ?>

<div class="card tbl">

```
<table>

    <tr>
        <th>Ticket</th>

        <?php if (!$isRes): ?>
            <th>Resident</th>
        <?php endif; ?>

        <th>Issue</th>
        <th>Priority</th>
        <th>Status</th>
        <th><?= $isRes ? 'Technician' : 'Assign' ?></th>
    </tr>

    <?php foreach ($rows as $c): ?>

    <tr>

        <td class="mono">
            <?= code($c['id']) ?>
        </td>

        <?php if (!$isRes): ?>
            <td>
                <?= e($c['rname']) ?>
            </td>
        <?php endif; ?>

        <td>
            <b><?= e($c['category']) ?></b>
            <br>
            <small class="muted">
                <?= e(trimw($c['description'], 70, '…')) ?>
            </small>
        </td>

        <td>
            <?= badge($c['priority']) ?>
        </td>

        <td>
            <?= badge($c['status']) ?>
        </td>

        <td>

            <?php if ($isRes): ?>

                <?= e($c['tname'] ?? 'Not yet assigned') ?>

            <?php elseif ($c['status'] === 'resolved'): ?>

                <?= e($c['tname'] ?? '—') ?>

            <?php else: ?>

                <form method="post" style="display:flex;gap:6px">

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="action"
                        value="assign"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$c['id'] ?>"
                    >

                    <select
                        name="technician_id"
                        style="padding:6px"
                    >

                        <?php foreach ($techs as $t): ?>

                            <option
                                value="<?= (int)$t['id'] ?>"
                                <?= $t['id'] == $c['technician_id'] ? 'selected' : '' ?>
                            >
                                <?= e($t['name']) ?>
                                <?= $t['specialty']
                                    ? ' · ' . e($t['specialty'])
                                    : '' ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button class="btn sm p">
                        Assign
                    </button>

                </form>

            <?php endif; ?>

        </td>

    </tr>

    <?php endforeach; ?>

    <?php if (!$rows): ?>

        <tr>
            <td colspan="<?= $isRes ? 5 : 6 ?>" class="empty">
                No complaints.
            </td>
        </tr>

    <?php endif; ?>

</table>
</div>

<?php page_end(); ?>