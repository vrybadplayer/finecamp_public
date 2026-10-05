
let variationCounter = $('#variations_table tbody tr').length || 0; // Global counter to track variations

// Event listener for adding a variation (works for both add and edit)
$('#add_variation').on('click', function () {
  const $tableBody = $('#variations_table tbody');

  // Remove the "No variations available" message if it exists
  if ($tableBody.children().length === 1 && $tableBody.find('td').attr('colspan') == 6) {
    $tableBody.empty(); // Remove the message row
  }

  variationCounter++; // Increment the counter for each added variation

  const uniqueId = `preview_${Date.now()}`; // Generate a unique ID using timestamp

  // Append new variation row to the table
  $tableBody.append(`
    <tr>
      <td><input type="text" name="new_variations[${variationCounter}][name]" maxlength="20"></td>
      <td><input type="number" min="0.01" step="0.01" name="new_variations[${variationCounter}][unit_price]"></td>
      <td><input type="number" min="0" name="new_variations[${variationCounter}][stock_quantity]"></td>
      <td><input type="number" min="0.01" step="0.01" name="new_variations[${variationCounter}][weight]"></td>
      <td>
          <div class="upload-area" data-id="${uniqueId}">
            <label class="upload" tabindex="0">
              <input type="file" name="new_variation_images[${variationCounter}]" accept="image/*" onchange="previewImage(this, '${uniqueId}')" hidden>
              <img id="${uniqueId}" src="../../images/admin/product/product_image/default.jpg" alt="Default Product Image">
            </label>
          </div>
      </td>
      <td>
          <button type="button" class="delete_variation" data-id="">Delete</button>
      </td>
    </tr>
  `);

  // Attach drag-and-drop functionality to the newly added row
  setupDragAndDrop(uniqueId);
});

// Function to preview the image (shared for both add and edit)
function previewImage(input, imgId) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      console.log("Preview Image Loading", e); // Add this to verify the image is being loaded
      const $previewImg = $(`#${imgId}`);
      $previewImg.attr('src', e.target.result).show(); // Show the image
    };
    reader.readAsDataURL(file); // Read the file as a data URL for preview
  }
}

// Function to setup drag-and-drop functionality using jQuery
function setupDragAndDrop(uniqueId) {
  const $uploadArea = $(`[data-id="${uniqueId}"]`);

  // Prevent default behavior for drag events and allow drop
  $uploadArea.on('dragover dragenter', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).addClass('dragging'); 
  });

  // Remove the visual indicator when dragging leaves the area
  $uploadArea.on('dragleave', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).removeClass('dragging'); // Optional: Remove class when dragging ends
  });

  // Handle drop event to get the file
  $uploadArea.on('drop', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).removeClass('dragging'); // Optional: Remove class after drop

    const files = event.originalEvent.dataTransfer.files; // Get dropped files
    if (files.length > 0) {
      const fileInput = $(this).find('input[type="file"]')[0];
      fileInput.files = files; // Assign dropped file to the input

      // Call the image preview function
      previewImage(fileInput, uniqueId);
    }
  });

  // Handle click to open file dialog
  $uploadArea.on('click', function () {
    $(this).find('input[type="file"]').click(); // Trigger file input click
  });
}


// Event delegation for deleting variations (works for both add and edit)
$('#variations_table').on('click', '.delete_variation', function (e) {
  const $row = $(this).closest('tr');
  const variationId = $(this).data('id');

  // Check if the variation ID exists (for variations already saved in the DB)
  if (variationId) {
    const $deletedVariationsInput = $('#deleted_variations');
    if ($deletedVariationsInput.length) {
      $deletedVariationsInput.val($deletedVariationsInput.val() + variationId + ',');
    }
  }

  // Remove the row from the table
  $row.remove();

  // Check if there are no rows left, and add the "No variations available" message back
  const $tableBody = $('#variations_table tbody');
  if ($tableBody.children().length === 0) {
    $tableBody.append('<tr><td colspan="6">No variations available. Add a new variation below.</td></tr>');
  }
});
