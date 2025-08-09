$(document).ready(function() {
    //-----         TURNOVER MODAL & FUNCTION        -----//
    var turnoverModal = document.getElementById("turnoverModal");
    var turnoverBtn = document.getElementById("turnoverAssetButton");
    var turnoverSpan = turnoverModal ? turnoverModal.querySelector(".close") : null;

    if (turnoverBtn && turnoverModal && turnoverSpan) {
        turnoverBtn.onclick = function() {
            $('#turnoverForm')[0].reset();
            $('input[name="turnoverType"]:checked').trigger('change');
            turnoverModal.style.display = "block";
        };

        turnoverSpan.onclick = function() {
            turnoverModal.style.display = "none";
        };

        $('input[name="turnoverType"]').change(function() {
            var turnoverType = $(this).val();
            var companyDepartmentId = 10;

            if (turnoverType === 'company') {
                $('#ownerTo').val('Safexpress Logistics Inc.').prop('readonly', true);
                $('#departmentID').val(companyDepartmentId).prop('readonly', true);
                $('#ownerPosition').val('Company Holding').prop('readonly', true);
                $('#ownerDateHired').val('').prop('disabled', true);
                $('#ownerPhoneNum').val('').prop('disabled', true);

                $('.custodianFields').hide();

            } else {
                $('#ownerTo').val('').prop('readonly', false).attr('placeholder', 'New Owner Name');
                $('#departmentID').val('').prop('disabled', false);
                $('#ownerPosition').val('').prop('readonly', false).attr('placeholder', 'New Position');
                $('#ownerDateHired').val('').prop('disabled', false);
                $('#ownerPhoneNum').val('').prop('disabled', false);

                $('.custodianFields').show();
            }
        });
        $('input[name="turnoverType"]:checked').trigger('change');

        $('#turnoverForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();
            console.log("Turnover Form Data:", formData);

            $.ajax({
                type: 'POST',
                url: 'process_turnover.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Turnover processed successfully!');
                        turnoverModal.style.display = "none";
                        window.location.reload();
                    } else {
                        alert('Error processing turnover: ' + (response.message || 'Unknown error.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Turnover AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    alert('An error occurred while submitting the turnover form. Check console.');
                }
            });
        });

    } else {
        if (!turnoverBtn) console.warn("Turnover button (#turnoverAssetButton) not found.");
        if (!turnoverModal) console.warn("Turnover modal (#turnoverModal) not found.");
        if (!turnoverSpan) console.warn("Turnover modal close span (.close) not found.");
    }
    //-----                                            -----//
    
//-----         EDIT MODAL & FUNCTION              -----//
    var editModal = document.getElementById("editModal");
    var editBtn = document.getElementById("editAssetButton");
    var editSpan = editModal ? editModal.querySelector(".close") : null;

    if (editBtn && editModal && editSpan) {
        editBtn.onclick = function() {
            var assetID = this.getAttribute('data-asset-id');

            if (!assetID || assetID === '0' || assetID === '') {
                alert('Error: Could not retrieve a valid Asset ID from the edit button.');
                console.error('Asset ID read from button data-asset-id:', assetID);
                return;
            }

            $.ajax({
                url: 'fetch_asset_details.php',
                type: 'GET',
                data: { assetID: assetID },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editModalTitle').text('Editing Details For : ' + response.assetTag);
                        $('#editAssetName').val(response.assetName);
                        $('#editAssetModel').val(response.assetModel);
                        $('#editAssetSerial').val(response.assetSerial);
                        $('#editTypeID').val(response.assetType);
                        $('#editStatusID').val(response.assetStatus);
                        $('#editRegionID').val(response.assetRegion);
                        $('#editSiteID').val(response.assetSiteLocation);
                        $('#editDeprecPeriod').val(response.deprecPeriod);
                        $('#editAssetCost').val(response.assetCost);
                        $('#editDeprecCost').val(response.deprecCost);
                        $('#editDatePurchased').val(response.assetPurchased);
                        $('#editDateRegistered').val(response.assetRegistered);
                        $('#editAssetID').val(response.assetID);

                        editModal.style.display = "block";
                    } else {
                        alert('Error fetching asset data: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Fetch Details AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    alert('An error occurred while fetching asset data. Check console.');
                }
            });
        };

        editSpan.onclick = function() {
            editModal.style.display = "none";
        };

    } else {
        if (!editBtn) console.warn("Edit button (#editAssetButton) not found.");
        if (!editModal) console.warn("Edit modal (#editModal) not found.");
        if (!editSpan) console.warn("Edit modal close span (.close) not found.");
    }

    window.onclick = function(event) {
        if (editModal && event.target == editModal) {
            editModal.style.display = "none";
        }
        if (turnoverModal && event.target == turnoverModal) {
            turnoverModal.style.display = "none";
        }
    };
    $('#editForm').submit(function(event) {
        event.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'process_edit.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || 'Asset updated successfully!');
                    editModal.style.display = "none";
                    window.location.reload();
                } else {
                    alert('Error updating asset: ' + (response.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Edit Form Submit AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                alert('An error occurred while saving changes. Check console.');
            }
        });
    });
    //-----                                            -----//

    // ----- DEPRECIATED VALUE COMPUTATION FUNCTION ------ //
    function calculateDepreciatedCost() {
        const depreciationPeriod = $("#editDeprecPeriod").val();
        const purchaseCost = parseFloat($("#editAssetCost").val());
        if (isNaN(purchaseCost)) {
            $("#editDeprecCost").val('');
            return;
        }
        if (purchaseCost >= 0 && depreciationPeriod > 0) {
            const yearToMonthsDeprec = depreciationPeriod * 12;
            const annualDepreciation = purchaseCost / yearToMonthsDeprec;
            $("#editDeprecCost").val(annualDepreciation.toFixed(2));
        } else {
            $("#editDeprecCost").val('');
            return;
        }
    }
    $("#editDeprecPeriod").change(function() {
        calculateDepreciatedCost();
    });
    $("#editAssetCost").on('input', function() {
        calculateDepreciatedCost();
    });
    // -----                                      ------ //
        
//-----         IMAGE EDIT MODAL & FUNCTION        -----//
var modal = $("#imageUploadModal");
var img = $("#assetImage");
var modalImg = $("#existingImagePreview");
var assetImagePath = img.attr('src');
var span = $("#imageUploadModal .close");

$("#changeImageButton").click(function(event) {
    event.preventDefault();
    assetImagePath = img.attr('src');
    modal.css("display", "block");
    modalImg.attr('src', assetImagePath);
    $('#modalImagePreviewContainer').hide();
    $('#modalImageUpload').val('');
    $('.upload-label').show();
    $('#modalImageFilename').text('');
    $('#modalImageFilesize').text('');
});

span.click(function() {
    modal.css("display", "none");
});

$(window).click(function(event) {
    if (event.target == modal[0]) {
        modal.css("display", "none");
    }
});

$('#modalImageUpload').on('change', function(event) {
    const file = event.target.files[0];
    const imagePreview = $('#modalImagePreview');
    const imagePreviewContainer = $('#modalImagePreviewContainer');
    const uploadLabel = $('.upload-label');
    const imageFilename = $('#modalImageFilename');
    const imageFilesize = $('#modalImageFilesize');

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
            $('#modalImageUpload').val(''); // Clear the file input
            imagePreview.attr('src', '#');
            imagePreviewContainer.hide();
            uploadLabel.show();
            $('#existingImagePreview').show();
            imageFilename.text('');
            imageFilesize.text('');
            return; // Stop further processing
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.attr('src', e.target.result);
            imageFilename.text(file.name);
            imageFilesize.text(formatFileSize(file.size));
            imagePreviewContainer.show();
            uploadLabel.hide();
            $('#existingImagePreview').hide();
        }
        reader.readAsDataURL(file);
    } else {
        imagePreview.attr('src', '#');
        imagePreviewContainer.hide();
        uploadLabel.show();
        $('#existingImagePreview').show();
        imageFilename.text('');
        imageFilesize.text('');
    }
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

$('#saveImageButton').on('click', function() {
    var formData = new FormData();
    var fileInput = $('#modalImageUpload')[0];

    if (fileInput.files.length > 0) {
        var file = fileInput.files[0];

        // ADD VALIDATION HERE AGAIN RIGHT BEFORE AJAX CALL
        const allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        let errorMessage = '';

        if (!allowedImageTypes.includes(file.type)) {
            errorMessage = 'Error: Please select a valid image format (JPEG, JPG, PNG, GIF).';
        } else if (file.size > maxFileSize) {
            errorMessage = 'Error: Image size exceeds the maximum allowed size of 5MB.';
        }

        if (errorMessage) {
            alert(errorMessage);
            return; // Don't proceed with upload if validation fails
        }


        formData.append('modalImageUpload', file);
    } else {
        alert('Please select an image to upload.');
        return;
    }

    var assetId = $("#imageAssetID").val();
    if (!assetId) {
        alert('Error: Asset ID not found.');
        return;
    }
    formData.append('assetId', assetId);

    $.ajax({
        url: 'update_img.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(newImagePathFromServer) {
            console.log("Server returned new image path: " + newImagePathFromServer);
            img.attr('src', newImagePathFromServer + "?" + new Date().getTime());
            assetImagePath = newImagePathFromServer;
            alert('Image updated successfully!');
            modal.hide();
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error: ", status, error);
            console.error("Response Text: ", xhr.responseText);
            alert('Error updating image. Check console for details.');
        }
    });
});

// Clear Image Button
$('#clearImageButton').on('click', function() {
    var assetId = $("#imageAssetID").val();
    console.log("Asset ID being sent to clear_image.php: " + assetId);
    $.ajax({
        url: 'clear_img.php',
        type: 'POST',
        data: {
            'assetId': assetId
        }, // Send the assetId

        success: function(data) {
            // Update the image source with the default image path
            assetImagePath = data; // Should be the Direct path from asset_img

            img.attr('src', assetImagePath + "?" + new Date().getTime());
            modalImg.attr('src', assetImagePath + "?" + new Date().getTime());

            alert('Image cleared successfully!');
            modal.hide();
        },
        error: function(xhr, status, error) {
            // Handle errors (e.g., display an error message)
            alert('Error clearing image: ' + error);
        }
    });
});

//-----     DELETE ASSET FUNCTION       -----//
    $('#deleteAssetButton').on('click', function() {
        var assetID = this.getAttribute('data-asset-id');
        console.log("Asset ID: " + assetID);

        if (!assetID || assetID === '0' || assetID === '') {
            alert('Error: Could not retrieve a valid Asset ID from the edit button.');
            console.error('Asset ID read from button data-asset-id:', assetID);
            return;
        }
        
        if (confirm("Are you sure you want to delete this asset? This action cannot be undone.")) {  //Confirm the action
            $.ajax({
                url: 'delete_asset.php',
                type: 'POST',
                data: { assetID: assetID },
                success: function(response) {
                    alert(response);
                    window.location.href = 'assets.php';
                },
                error: function(xhr, status, error) {
                    alert('Error deleting asset: ' + error);  
                }
            });
        }
    });
    //-----                                            -----//

    //-----         SHOW ASSET TAG PDF FUNCTION        -----//
const previewModal = $("#previewModal");
const pdfIframe = $("#pdfPreviewIframe");
const pdfLoadingSpinner = $("#pdfLoadingSpinner"); // Get the spinner CONTAINER
const pdfLoadingIndicator = $("#pdfLoadingIndicator");
const downloadPdfButton = $('#downloadPdfButton');
const downloadPngButton = $('#downloadPngButton');  // Get the existing button directly

// Event delegation for dynamically added elements
$(document).on('click', '#showPdfButton', function() {
    // 3. Show the modal container
    previewModal.css("display", "block");

    // 4. Get Asset ID (ensure this element exists and has the value)
    const assetId = $("#AssetID").val(); // Make sure #AssetID holds the correct value
    if (!assetId) {
        console.error("AssetID not found or is empty.");
        // Show error IN the spinner container
        pdfLoadingSpinner.find('p').text('Error: Could not find Asset ID.');
        pdfLoadingSpinner.find('i').removeClass('fa-spinner fa-spin-pulse').addClass('fa-exclamation-triangle'); // Change icon
        return; 
    }

    // 5. Set the src attribute of the iframe with the 'preview' action
    const pdfSrc = 'generate_pdf.php?asset_id=' + assetId + '&action=preview';

    pdfIframe.off('load').on('load', function() {
        // --- On successful iframe load ---
        console.log("Iframe loaded.");
        pdfLoadingIndicator.css('display', 'none'); // Hide loader
    });

    pdfIframe.off('error').on('error', function() {
        // --- On iframe load error ---
        console.error("Failed to load PDF in iframe.");
        pdfLoadingIndicator.html('<p style="color: red;">Error: Could not load PDF preview.</p>');
        // Keep the loader visible with the error message
    });

    pdfIframe.attr('src', pdfSrc).show(); // Ensure iframe is visible by CSS

    downloadPdfButton.off('click').on('click', function() {
        triggerPdfDownload(assetId);
    });

});

// Close Modal when you click on x
$(document).on('click', '#previewModal .close', function() {
    previewModal.css("display", "none");
    pdfIframe.attr('src', 'about:blank'); // Clear the iframe src
    pdfLoadingIndicator.css('display', 'none'); // Ensure loader is hidden on close
});

// Optional: Close modal if user clicks outside the modal content
previewModal.on('click', function(event) {
    // Check if the click was directly on the modal backdrop (event.target)
    if (event.target === previewModal[0]) { // Compare with the DOM element
        previewModal.css("display", "none");
        pdfIframe.attr('src', 'about:blank'); // Clear the iframe src
        pdfLoadingIndicator.css('display', 'none');
    }
});


// Function to trigger PDF download
function triggerPdfDownload(assetId) {
    // Create a link element
    const downloadLink = document.createElement('a');

    // Set the href to the PDF generation script with the asset ID (no 'preview' action)
    downloadLink.href = 'generate_pdf.php?asset_id=' + assetId;

    // Set the download attribute to suggest a filename
    downloadLink.download = 'SLI-ASSET-' + assetId + '.pdf';

    // Append the link to the body
    document.body.appendChild(downloadLink);

    // Programmatically click the link to trigger the download
    downloadLink.click();

    // Remove the link from the body
    document.body.removeChild(downloadLink);
}


// Event handler for the "Download as PNG" button
downloadPngButton.off('click').on('click', function() {
    downloadPdfAsPng();
});


function downloadPdfAsPng() {
    const iframe = document.getElementById('pdfPreviewIframe');
    const iframeWindow = iframe.contentWindow || iframe;
    const iframeDocument = iframeWindow.document || iframeWindow.contentDocument;

    if (!iframeDocument) {
        console.warn('Iframe content not yet available.');
        alert('The PDF is still loading. Please wait for it to finish before downloading as PNG.');
        return; // Exit the function if the iframe is not yet loaded
    }

    // Wait for the PDF to render (you might need to adjust the delay)
    setTimeout(() => {
        html2canvas(iframeDocument.body).then(canvas => {
            const pngUrl = canvas.toDataURL("image/png");
            const downloadLink = document.createElement("a");
            downloadLink.href = pngUrl;
            downloadLink.download = "ASSET-TAG.png"; // You might want to customize the filename
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }).catch(error => {
            console.error("Error during html2canvas:", error);
            alert('An error occurred while generating the PNG. Please try again.');
        });
    }, 1000); // Adjust the delay if needed.  Try increasing it if the image is incomplete.
}
    //-----                                            -----//
});