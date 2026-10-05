<?php

require '../../_base.php';

// Authentication
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Define fields available for sorting
$fields = [
  'category_id'    => 'Category ID',
  'name'           => 'Category Name',
  'description'    => 'Description'
];

// Get sorting parameters from the URL
$sort = req('sort');
if (!key_exists($sort, $fields)) {
  $sort = 'category_id';  // Default sorting by category_id
}

$dir = req('dir');
if (!in_array($dir, ['asc', 'desc'])) {
  $dir = 'asc';  // Default sorting direction is ascending
}

// Initialize search variables
$search_query = '';

// Handle GET and POST requests
if (is_get() || is_post()) {
  $search_query = req('search_input') ?: '';


  // Build the main query
  $query = "
    SELECT * 
    FROM category
";

  // Apply search filtering by id, name and description
  $params = [];
  if (!empty($search_query)) {
    $query .= " WHERE (LOWER(category_id) LIKE LOWER(:search_query)
                    OR LOWER(name) LIKE LOWER(:search_query)
                    OR LOWER(description) LIKE LOWER(:search_query))";
    $params[':search_query'] = '%' . $search_query . '%';
  }

  // Add sorting to the query
  $query .= " ORDER BY $sort $dir";

  // Execute the query
  $stmt = $_db->prepare($query);
  $stmt->execute($params);
  $categories = $stmt->fetchAll();
}
// Set title and include the header
$_title = 'Category Management';
$_css_file = '/css/admin/product.css';
include '../../_head.php';
?>

<button data-get="/admin/adminDashboard.php" class="back">Back to Dashboard</button>
<?= html_h1('category_title', "Category Management") ?>


<div class="form-container">
  <form action="" method="GET">

    <!-- Search input field -->
    <label for="search_input">Search Category: </label>
    <?= html_search('search_input') ?>

    <!-- Submit button -->
    <input type="submit" name="submit" value="Search">

    <!-- Reset button -->
    <button data-get="categoryManagement.php">Reset</button>

  </form>
</div>

<!-- Flex container for button and result count -->
<div class="action-bar">
  <?= html_button('create', 'Add Category', 'data-get="addCategory.php"'); ?>
  <span class="count-found"><?= count($categories) ?> category(s) found</span>
</div>

<table class="custom-table borderless-table">
  <thead>
    <tr>
      <?php
      // Construct the href parameters from search input
      $search_query_param = isset($_GET['search_input']) ? 'search_input=' . urlencode($_GET['search_input']) : '';

      // Combine the search query and any other filters into a single href string
      $href = http_build_query(array_filter([
        'search_input' => $_GET['search_input'] ?? null,
      ]));

      // table_headers
      table_headers($fields, $sort, $dir, $href);
      ?>
      <th class="center">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($categories) > 0) : ?>
      <?php foreach ($categories as $category) : ?>
        <tr class="category_details_row">
          <td><?= $category->category_id ?></td>
          <td><?= $category->name ?></td>
          <td style="word-wrap: break-word; max-width: 300px;"><?= $category->description ?></td>
          <td class="center">
            <?php if ($category->category_id == 1) { ?>
              <button class="category-disable-button">
                Edit
              </button>
              <button class="category-disable-button">
                Delete
              </button>
            <?php } else { ?>
              <button class="edit_button" data-get="editCategory.php?category_id=<?= $category->category_id ?>" id="editCategory">
                Edit
              </button>
              <button class="delete_button" data-confirm="Are you sure you want to delete this category?" data-post="deleteCategory.php?category_id=<?= $category->category_id ?>" id="deleteCategory">
                Delete
              </button>
            <?php } ?>
          </td>
        </tr>
      <?php endforeach ?>
    <?php else : ?>
      <tr>
        <td colspan="4" style="text-align: center;">No records found.</td>
      </tr>
    <?php endif ?>
  </tbody>
</table>

<?php
include '../../_foot.php';
?>