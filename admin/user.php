<?php
$page_title = 'Users';
require '../includes/admin-header.php';
if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    if ($id !== $_SESSION['user']['id']) {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='user'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            set_flash('User deleted.');
        } catch (mysqli_sql_exception $e) {
            set_flash('This user has orders and cannot be deleted.', 'danger');
        }
    }
    redirect('users.php');
}
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT id,full_name,email,phone,address,created_at FROM users WHERE role='user' AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?) ORDER BY created_at DESC");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result();
} else
    $users = $conn->query("SELECT id,full_name,email,phone,address,created_at FROM users WHERE role='user' ORDER BY created_at DESC");
?>
<div class="admin-actions">
    <div>
        <h1>Users</h1>
        <p class="muted">Customer accounts registered in the store.</p>
    </div>
</div>
<form class="filters" method="get"><input name="search" value="<?= e($search) ?>"
        placeholder="Search name, email, or phone"><button class="btn">Search</button></form>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody><?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= e($u['address']) ?></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this user?')"><input type="hidden"
                                name="delete_id" value="<?= $u['id'] ?>"><button class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/admin-footer.php'; ?>