// ============================================================================
// General Functions
// ============================================================================



// ============================================================================
// Page Load (jQuery)
// ============================================================================

$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();

    // Confirmation message
    // TODO
    $('[data-confirm]').on('click', e => {
        const text = e.target.dataset.confirm || 'Are you sure?';
        if (!confirm(text)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });


    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

    // Auto uppercase
    $('[data-upper]').on('input', e => {
        const a = e.target.selectionStart;
        const b = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(a, b);
    });

    // Photo preview
    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];// e.target.files.item(0);
        const img = $(e.target).siblings('img')[0]; // e.target.nextElementSibling

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        }
        else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });

    // Make the drag and drop work on picture upload
    $('label.upload').on('dragover', e => {
        e.preventDefault();
        e.stopPropagation(); // Stops some browsers from redirecting
        $(e.currentTarget).addClass('dragover');
    });

    $('label.upload').on('dragleave', e => {
        e.preventDefault();
        e.stopPropagation();
        $(e.currentTarget).removeClass('dragover');
    });

    $('label.upload').on('drop', e => {
        e.preventDefault();
        e.stopPropagation();
        $(e.currentTarget).removeClass('dragover');

        const f = e.originalEvent.dataTransfer.files[0];
        const img = $(e.currentTarget).find('img')[0];
        const input = $(e.currentTarget).find('input[type=file]')[0];

        if (!img || !input) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(f);
            input.files = dataTransfer.files;
        } else {
            img.src = img.dataset.src;
        }
    });

    //admin update side
    // When the edit button is clicked
    $('button[id^="edit"]').on('click', function () {
        const inputField = $(this).siblings('input, textarea');  // Target the input/textarea field
        const updateBtn = $(this).siblings('button[id^="update"]');  // Target the update button
        const cancelBtn = $(this).siblings('button[id^="cancel"]');  // Target the cancel button

        // Show input field and the update/cancel buttons
        inputField.show();
        updateBtn.show();
        cancelBtn.show();

        // Hide the edit button
        $(this).hide();
    });

    // // When the cancel button is clicked
    $('button[id^="cancel"]').on('click', function () {
        const inputField = $(this).siblings('input, textarea');  // Target the input/textarea field
        const editBtn = $(this).siblings('button[id^="edit"]');  // Target the edit button
        const updateBtn = $(this).siblings('button[id^="update"]');  // Target the update button

        // Hide input field and the update/cancel buttons
        inputField.hide();
        updateBtn.hide();
        $(this).hide();

        // Show the edit button
        editBtn.show();
    });


});