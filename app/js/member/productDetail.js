$(document).ready(function () {
  // Image Slider
  let currentSlide = 0;
  const $slides = $('.slide');

  // Previous button click
  $('.prev').on('click', function () {
    changeSlide(-1);
  });

  // Next button click
  $('.next').on('click', function () {
    changeSlide(1);
  });

  function changeSlide(n) {
    $slides.eq(currentSlide).removeClass('active');
    currentSlide = (currentSlide + n + $slides.length) % $slides.length;
    $slides.eq(currentSlide).addClass('active');
  }

  // Variation Selection
  const $variationOptions = $('.variation-option');
  const $variationIdInput = $('#variation_id');
  const $addToCartButton = $('#add-to-cart-button');
  const $quantityInput = $('#quantity');

  $variationOptions.each(function () {
    const $option = $(this);

    // Check if the variation is out of stock
    if ($option.hasClass('disabled')) {
      // Disable interaction for out-of-stock items
      $option.css({
        'pointer-events': 'none',
        'opacity': '0.5'
      });
    } else {
      // Add click event listener for in-stock items
      $option.on('click', function () {
        // Remove active class from all options
        $variationOptions.removeClass('selected');
        // Add active class to the selected option
        $option.addClass('selected');
        // Set the hidden variation_id input value
        $variationIdInput.val($option.data('variation-id'));
        // Enable the Add to Cart button
        $addToCartButton.prop('disabled', false);
        // Update max quantity based on stock
        $quantityInput.attr('max', $option.data('stock'));

        // Change the image slider to the corresponding variation image
        const selectedImage = $option.data('image');
        const $matchingSlide = $slides.filter(function () {
          return $(this).find('img').attr('src').includes(selectedImage);
        });

        if ($matchingSlide.length) {
          $slides.eq(currentSlide).removeClass('active');
          currentSlide = $slides.index($matchingSlide);
          $slides.eq(currentSlide).addClass('active');
        }
      });
    }
  });
});