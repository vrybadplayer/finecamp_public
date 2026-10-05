document.addEventListener("DOMContentLoaded", function() {
    // Change product variation option
    $('select[name^="variation_options"]').on('change', function(e) {
        e.target.form.action = '?btn=select';
        e.target.form.submit();
    });

    // Change quantity
    $('input#qty').on('change', function(e) {
        let input = $(this);
        let value = parseInt(input.val());
        let max = parseInt(input.attr('max'));

        if (value > max) {
            input.val(max);
        }

        if (value > 0) {
            e.target.form.action = '?btn=edit';
            e.target.form.submit();
        }
    });

    // Select all checkboxes
    document.getElementById('selectAll').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('input[name="selectId[]"]');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });

        updateCheckedCount();
    });

    // Check selected count
    function updateCheckedCount() {
        let checkedCount = $('input[name="selectId[]"]:checked').length;
        $('#checkedCount').text(checkedCount);
    }

    $('input[name="selectId[]"]').on('change', function() {
        updateCheckedCount();
    });

    updateCheckedCount();

    // Batch deletion
    $('#deleteSelected').on('click', function(e) {
        e.preventDefault();

        if (confirm('Are you sure to remove the cart item(s)?')) {
            document.getElementById('cartAction').action = 'deleteCartItem.php';
            document.getElementById('cartAction').submit();
        }
    });

    // A checkbox is checked/unchecked
    $(document).on('change click', 'input[name="selectId[]"], #selectAll', function() {
        let totalWeight = 0, subtotalPrice = 0, numOfSelectedItems = 0;;

        $('input[name="selectId[]"]:checked').each(function() {
            let parentCard = $(this).closest('.shoppingCartCard');
            let weight = parseFloat(parentCard.find('input.weight').val());
            let unitPrice = parseFloat(parentCard.find('input.unit_price').val());
            let quantity = parseFloat(parentCard.find('input.quantity').val());

            // Total weight and subtotal price
            totalWeight += weight * quantity;
            subtotalPrice += unitPrice * quantity;

            numOfSelectedItems++;
        });

        // Shipping charge
        let shippingCharge = calculateShippingCharge(totalWeight);

        // Tax (10%)
        let tax = subtotalPrice * 0.1;

        // Discount (Every RM200, discount RM20)
        let discount = calculateDiscount(subtotalPrice);

        // Total
        let total = subtotalPrice + shippingCharge + tax - discount;

        $('div[name="subtotal"]').text("RM " + subtotalPrice.toFixed(2));
        $('div[name="shippingCharge"]').text("RM " + shippingCharge.toFixed(2));
        $('div[name="tax"]').text("RM " + tax.toFixed(2));
        $('div[name="discount"]').text("- RM " + discount.toFixed(2));
        $('div[name="total"]').text("RM " + total.toFixed(2));
        $('button[name="checkout"]').text("Checkout (" + numOfSelectedItems.toFixed(0) + ")");
    });

    // Checkout
    $('#checkout').on('click', function(e) {
        e.preventDefault();

        if (confirm('Are you sure to checkout from cart?')) {
            let form = document.getElementById('checkoutForm');
            let cart_item_ids = [];
            let numOfItems = 0;

            // Selected cart items
            $('input[name="selectId[]"]:checked').each(function() {
                let parentCard = $(this).closest('.shoppingCartCard');
                let cart_item_id = parseFloat(parentCard.find('input#cart_item_id').val());
                cart_item_ids[numOfItems++] = cart_item_id;

                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cart_item_id[]';
                input.value = cart_item_id;
                input.form = 'checkoutForm';
                form.appendChild(input);
            });

            if (numOfItems != 0) {
                // Go checkout
                form.action = 'checkout.php';
                form.submit();
            } else {
                alert('Please select at least 1 cart item.');
            }
        }
    });
});

// Delete confirmation
function confirmDelete(element) {
    if (confirm('Are you sure to remove this cart item?')) {
        window.location.href = element.getAttribute('data-url');
    }
}

// Calculate shipping charge
function calculateShippingCharge(totalWeight) {
    if (totalWeight > 0 && totalWeight <= 1) { // RM5 per kg if 0 < x <= 1
        return Math.ceil(totalWeight) * 5; 
    } else if (totalWeight > 1 && totalWeight <= 5) { // RM4.5 per kg if 1 < x <= 5
        return Math.ceil(totalWeight) * 4.5; 
    } else if (totalWeight > 5) { // RM4 per kg if x > 5
        return Math.ceil(totalWeight) * 4;
    } else { // Invalid
        return 0;
    }
}

// Calculate discount
function calculateDiscount(subtotal) {
    return Math.floor(subtotal / 200) * 20;
}