$(document).ready(function() {
    // ----- REGISTER ASSET/SUCCESS MODAL POPUP ------ //
    var registerAssetModal = $("#registerAssetModal");
    var successModal = $("#successModal");

    // Event delegation for dynamically added elements
    $(document).on('click', '#openRegister', function() {
        registerAssetModal.css("display", "block");
    });

    $(document).on('click', '#registerAssetModal .close', function() {
        registerAssetModal.css("display", "none");
    });

    $(document).on('click', '#successModal .close', function() {
        successModal.css("display", "none");
        $('#pdfPreviewIframe').attr('src', ''); // Clear iframe src to prevent showing old PDF
        location.reload(); // Reload to update the asset list
    });

    // When the user clicks anywhere outside of the modal, close it
    $(window).click(function (event) {
        if (event.target == registerAssetModal[0]) {
            registerAssetModal.css("display", "none");
        }
        if (event.target == successModal[0]) {
            successModal.css("display", "none");
            $('#pdfPreviewIframe').attr('src', ''); // Clear iframe src
        }
    });

    $("#registerAssetForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        // Get form data
        var formData = new FormData(this);

        // Disable the register button to prevent multiple submissions
        $('#registerAssetButton').prop('disabled', true);
        $('#registerAssetButton').text('Registering...');

        // Submit form via AJAX
        $.ajax({
            type: "POST",
            url: "register_assets.php", // Your PHP script to handle form data
            data: formData,
            processData: false, // Important!
            contentType: false, // Important!
            dataType: "json", // Expect JSON response
            success: function (response) {
                // Re-enable the register button
                $('#registerAssetButton').prop('disabled', false);
                $('#registerAssetButton').text('Register');

                if (response.status === "success") {
                    // Set the src attribute of the iframe and display it
                    var pdfSrc = 'data:application/pdf;base64,' + response.pdfData;
                    $('#pdfPreviewIframe').attr('src', pdfSrc).show();

                    //Show modal
                    successModal.css("display", "block");

                    //Hide register modal
                    registerAssetModal.css("display", "none");

                    // Attach download and print functions
                    $('#downloadPdfButton').off('click').on('click', function() {
                        downloadPdf(response.pdfData, response.asset_tag);
                    });

                    $('#printPdfButton').off('click').on('click', function() {
                        printPdf();
                    });

                } else {
                    alert("Error: " + response.message); // Display error message
                }
            },
            error: function (xhr, status, error) {
                // Re-enable the register button
                $('#registerAssetButton').prop('disabled', false);
                $('#registerAssetButton').text('Register');
                console.error("AJAX error: " + xhr.responseText);
                alert("Error generating PDF.");
            }
        });
    });

    // Function to trigger PDF download
    function downloadPdf(base64Pdf, assetTag) {
        const linkSource = `data:application/pdf;base64,${base64Pdf}`;
        const downloadLink = document.createElement("a");
        const fileName = "asset_tag_" + assetTag + ".pdf";

        downloadLink.href = linkSource;
        downloadLink.download = fileName;
        downloadLink.click();
    }
    // -----                                      ------ //

    // ----- DEPRECIATED VALUE COMPUTATION FUNCTION ------ //
    function calculateDepreciatedCost() {
        const depreciationPeriod = $("#deprec_period").val();
        const purchaseCost = parseFloat($("#purchase_cost").val());
        if (isNaN(purchaseCost)) {
            $("#deprec_cost").val('');
            return;
        }
        if (purchaseCost >= 0 && depreciationPeriod > 0) {
            const yearToMonthsDeprec = depreciationPeriod * 12;
            const annualDepreciation = purchaseCost / yearToMonthsDeprec;
            $("#deprec_cost").val(annualDepreciation.toFixed(2));
        } else {
            $("#deprec_cost").val('');
            return;
        }
    }
    $("#deprec_period").change(function() {
        calculateDepreciatedCost();
    });
    $("#purchase_cost").on('input', function() {
        calculateDepreciatedCost();
    });
    // -----                                      ------ //

    // ----- COST/PHONE NUMBER VALIDATION ------ //
    $("#purchase_cost, #deprec_cost, #phone_num").on("input", function() {
        var val = $(this).val();
        // Allow only numbers and a single decimal point
        var sanitized = val.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        $(this).val(sanitized);
    });
    // -----                                      ------ //

// ----- IMAGE UPLOAD FUNCTION ------ //
$('#imageUpload').on('change', function(event) {
    const file = event.target.files[0];
    const imagePreview = $('#imagePreview');
    const imagePreviewContainer = $('#imagePreviewContainer');
    const uploadLabel = $('.upload-label');
    const imageFilename = $('#imageFilename');
    const imageFilesize = $('#imageFilesize');

    // Validation variables
    const allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const maxFileSize = 5 * 1024 * 1024; // 5MB
    let errorMessage = '';

    if (file) {

        // Validate File Type
        if (!allowedImageTypes.includes(file.type)) {
            errorMessage = 'Error: Please select a valid image format (JPEG, JPG, PNG, GIF).';
        }
        // Validate File Size
        else if (file.size > maxFileSize) {
            errorMessage = 'Error: Image size exceeds the maximum allowed size of 5MB.';
        }


        if (errorMessage) {
            alert(errorMessage); // Or display the error message in a designated area
            $('#imageUpload').val(''); // Clear the file input
            imagePreview.attr('src', '#');
            imagePreviewContainer.css('display', 'none');
            uploadLabel.css('display', 'block');
            imageFilename.text('');
            imageFilesize.text('');
            return; // Stop further processing
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            imagePreview.attr('src', e.target.result);
            imageFilename.text(file.name);
            imageFilesize.text(formatFileSize(file.size));
            imagePreviewContainer.css('display', 'flex');
            uploadLabel.css('display', 'none');
        }

        reader.readAsDataURL(file);
    } else {
        imagePreview.attr('src', '#');
        imagePreviewContainer.css('display', 'none');
        uploadLabel.css('display', 'block');
        imageFilename.text('');
        imageFilesize.text('');
    }
});

$('#changePictureButton').on('click', function() {
    $('#imageUpload').val('');
    const imagePreview = $('#imagePreview');
    const imagePreviewContainer = $('#imagePreviewContainer');
    const uploadLabel = $('.upload-label');
    const imageFilename = $('#imageFilename');
    const imageFilesize = $('#imageFilesize');

    imagePreview.attr('src', '#');
    imagePreviewContainer.css('display', 'none');
    uploadLabel.css('display', 'block');
    imageFilename.text('');
    imageFilesize.text('');
});

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
});