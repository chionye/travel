<?php
$pageTitle = 'Manage Hotels';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'name'           => trim($_POST['name'] ?? ''),
        'location'       => trim($_POST['location'] ?? ''),
        'city'           => trim($_POST['city'] ?? ''),
        'country'        => trim($_POST['country'] ?? ''),
        'description'    => trim($_POST['description'] ?? ''),
        'amenities'      => trim($_POST['amenities'] ?? ''),
        'star_rating'    => max(1, min(5, (int)($_POST['star_rating'] ?? 3))),
        'price_per_night'=> (float)($_POST['price_per_night'] ?? 0),
        'image_url'      => trim($_POST['image_url'] ?? ''),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];
    if (!$data['name'] || !$data['city'] || !$data['country'] || $data['price_per_night'] <= 0) {
        $error = 'Please fill all required fields.';
    } else {
        $postAction = $_POST['form_action'] ?? '';
        if ($postAction === 'add') {
            $stmt = db()->prepare('INSERT INTO hotels (name,location,city,country,description,amenities,star_rating,price_per_night,image_url,is_active) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute(array_values($data));
            flash('success', 'Hotel added successfully.');
            header('Location: hotels.php'); exit;
        } elseif ($postAction === 'edit' && $editId) {
            $stmt = db()->prepare('UPDATE hotels SET name=?,location=?,city=?,country=?,description=?,amenities=?,star_rating=?,price_per_night=?,image_url=?,is_active=? WHERE id=?');
            $stmt->execute(array_merge(array_values($data), [$editId]));
            flash('success', 'Hotel updated successfully.');
            header('Location: hotels.php'); exit;
        }
    }
    $action = ($postAction === 'add') ? 'add' : 'edit';
}

if (isset($_GET['delete']) && (int)$_GET['delete']) {
    db()->prepare('DELETE FROM hotels WHERE id=?')->execute([(int)$_GET['delete']]);
    flash('success', 'Hotel deleted.'); header('Location: hotels.php'); exit;
}
if (isset($_GET['toggle']) && (int)$_GET['toggle']) {
    $tid = (int)$_GET['toggle'];
    $cur = db()->prepare('SELECT is_active FROM hotels WHERE id=?'); $cur->execute([$tid]); $row = $cur->fetch();
    db()->prepare('UPDATE hotels SET is_active=? WHERE id=?')->execute([$row['is_active']?0:1, $tid]);
    header('Location: hotels.php'); exit;
}

$editHotel = null;
if ($action === 'edit' && $editId) {
    $stmt = db()->prepare('SELECT * FROM hotels WHERE id=?'); $stmt->execute([$editId]);
    $editHotel = $stmt->fetch();
    if (!$editHotel) $action = 'list';
}

if ($action === 'add' || $action === 'edit'):
$h = $editHotel ?? [];
$defaults = ['name'=>'','location'=>'','city'=>'','country'=>'','description'=>'','amenities'=>'','star_rating'=>4,'price_per_night'=>'','image_url'=>'','is_active'=>1];
$h = array_merge($defaults, $h);
?>

<div class="flex items-center gap-3 mb-6">
  <a href="hotels.php" class="btn-gray"><i class="fas fa-arrow-left mr-2"></i>Back to Hotels</a>
  <h2 class="font-bold text-gray-900"><?= $action==='add'?'Add New Hotel':'Edit Hotel' ?></h2>
</div>

<?php if ($error): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card-admin p-6">
  <?= csrfField() ?>
  <input type="hidden" name="form_action" value="<?= $action ?>">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
      <label class="admin-label">Hotel Name <span class="text-red-500">*</span></label>
      <input type="text" name="name" value="<?= e($h['name']) ?>" class="admin-input" required placeholder="e.g. The Grand Palace Hotel">
    </div>
    <div>
      <label class="admin-label">City <span class="text-red-500">*</span></label>
      <input type="text" name="city" value="<?= e($h['city']) ?>" class="admin-input" required placeholder="e.g. Paris">
    </div>
    <div>
      <label class="admin-label">Country <span class="text-red-500">*</span></label>
      <input type="text" name="country" value="<?= e($h['country']) ?>" class="admin-input" required placeholder="e.g. France">
    </div>
    <div class="md:col-span-2">
      <label class="admin-label">Full Address / Location</label>
      <input type="text" name="location" value="<?= e($h['location']) ?>" class="admin-input" placeholder="e.g. 42 Champs-Elysees, Paris">
    </div>
    <div>
      <label class="admin-label">Star Rating</label>
      <select name="star_rating" class="admin-input">
        <?php for ($i=1;$i<=5;$i++): ?>
          <option value="<?= $i ?>" <?= ($h['star_rating']==$i)?'selected':'' ?>><?= $i ?> Star<?= $i>1?'s':'' ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div>
      <label class="admin-label">Price per Night (<?= getSetting('currency_symbol','$') ?>) <span class="text-red-500">*</span></label>
      <input type="number" name="price_per_night" value="<?= e($h['price_per_night']) ?>" class="admin-input" required step="0.01" min="0" placeholder="199.99">
    </div>
    <div class="md:col-span-2">
      <label class="admin-label">Image URL <span class="text-gray-400 font-normal">(optional — use direct image link)</span></label>
      <input type="url" name="image_url" value="<?= e($h['image_url']) ?>" class="admin-input" placeholder="https://example.com/hotel-image.jpg">
      <?php if ($h['image_url']): ?>
        <img src="<?= e($h['image_url']) ?>" class="mt-2 h-32 w-56 object-cover rounded-xl" onerror="this.style.display='none'">
      <?php endif; ?>
    </div>
    <div class="md:col-span-2">
      <label class="admin-label">Description</label>
      <textarea name="description" rows="4" class="admin-input" placeholder="Describe the hotel, its atmosphere, location highlights..."><?= e($h['description']) ?></textarea>
    </div>
    <div class="md:col-span-2">
      <label class="admin-label">Amenities <span class="text-gray-400 font-normal">(comma-separated)</span></label>
      <input type="text" name="amenities" value="<?= e($h['amenities']) ?>" class="admin-input" placeholder="WiFi, Pool, Spa, Gym, Restaurant, Bar, Room Service">
      <p class="text-xs text-gray-400 mt-1">Separate each amenity with a comma, e.g.: WiFi, Pool, Spa, Gym</p>
    </div>
    <div class="flex items-center gap-3">
      <input type="checkbox" name="is_active" id="is_active" value="1" <?= $h['is_active']?'checked':'' ?> class="w-4 h-4 text-sky-600">
      <label for="is_active" class="text-sm font-medium text-gray-700">Active (visible to users)</label>
    </div>
  </div>
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button type="submit" class="btn-admin"><i class="fas fa-save mr-2"></i><?= $action==='add'?'Add Hotel':'Update Hotel' ?></button>
    <a href="hotels.php" class="btn-gray">Cancel</a>
  </div>
</form>

<?php else: // LIST
$search = trim($_GET['q'] ?? '');
$where  = ['1=1']; $params = [];
if ($search) { $where[] = '(name LIKE ? OR city LIKE ? OR country LIKE ?)'; $like="%$search%"; $params=array_fill(0,3,$like); }
$stmt = db()->prepare('SELECT * FROM hotels WHERE '.implode(' AND ',$where).' ORDER BY created_at DESC'); $stmt->execute($params); $hotels=$stmt->fetchAll();
?>

<div class="flex flex-wrap items-center gap-3 mb-6">
  <form method="get" class="flex gap-3 flex-1">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search hotel name, city, country..." class="admin-input w-64">
    <button type="submit" class="btn-admin"><i class="fas fa-search"></i></button>
  </form>
  <a href="hotels.php?action=add" class="btn-admin"><i class="fas fa-plus mr-2"></i>Add New Hotel</a>
</div>

<div class="card-admin overflow-hidden">
  <div class="p-4 border-b border-gray-100">
    <p class="text-sm text-gray-500"><strong><?= count($hotels) ?></strong> hotels found</p>
  </div>
  <div class="overflow-x-auto">
    <table class="admin-table">
      <thead><tr><th>Hotel</th><th>Location</th><th>Stars</th><th>Price/Night</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($hotels as $h): ?>
        <tr>
          <td>
            <div class="flex items-center gap-3">
              <?php if ($h['image_url']): ?>
                <img src="<?= e($h['image_url']) ?>" class="w-14 h-10 object-cover rounded-lg" onerror="this.style.display='none'">
              <?php else: ?>
                <div class="w-14 h-10 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-hotel text-sky-400"></i></div>
              <?php endif; ?>
              <div>
                <p class="font-bold text-sm"><?= e($h['name']) ?></p>
                <p class="text-xs text-gray-400"><?= e($h['location']) ?></p>
              </div>
            </div>
          </td>
          <td class="text-sm"><?= e($h['city']) ?>, <?= e($h['country']) ?></td>
          <td><?= str_repeat('★',$h['star_rating']) ?></td>
          <td class="font-bold text-sky-700"><?= formatPrice($h['price_per_night']) ?></td>
          <td>
            <a href="hotels.php?toggle=<?= $h['id'] ?>" class="text-xs px-2 py-1 rounded-full font-semibold <?= $h['is_active']?'bg-green-100 text-green-700':'bg-gray-100 text-gray-500' ?>">
              <?= $h['is_active']?'Active':'Inactive' ?>
            </a>
          </td>
          <td>
            <div class="flex gap-2">
              <a href="hotels.php?action=edit&id=<?= $h['id'] ?>" class="btn-admin text-xs px-3 py-1.5"><i class="fas fa-edit"></i></a>
              <a href="hotels.php?delete=<?= $h['id'] ?>" onclick="return confirm('Delete this hotel?')" class="btn-danger text-xs px-3 py-1.5"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$hotels): ?>
        <tr><td colspan="6" class="text-center text-gray-400 py-10">
          No hotels yet. <a href="hotels.php?action=add" class="text-sky-600 underline">Add your first hotel</a>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

</main></div></div></body></html>
