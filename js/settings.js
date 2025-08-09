$(document).ready(function() {
//------               MODAL FORM/BUTTON DECLARATION                   ------//
  var modal = $("#actionModal");
  var actionButtons = $("#actionButtons");
  var formContainers = $(".modal-content > div[id$='Container']");
  var span = $(".close");
  var currentCardType = "";

  $(".card").click(function() {
      currentCardType = $(this).data('card-type');
      modal.show();
      formContainers.hide();
      actionButtons.show();
  });

  span.click(function() {modal.hide();});
  $(window).click(function(event) {if (event.target == modal[0]) {modal.hide();}});
//------                                                ------//

//------          FORM CONTENT SWITCH FUNCTION          ------//
  $(".action-button").click(function() {
      var action = $(this).data('action');
      actionButtons.hide();
      if (action === "add" && currentCardType === "asset_status") {
          $("#assetStatusFormContainer").show();
      } else if (action === "edit" && currentCardType === "asset_status") {
          $("#editAssetStatusFormContainer").show();
      } else if (action === "delete" && currentCardType === "asset_status") {
          $("#deleteAssetStatusFormContainer").show();
      } else if (action === "add" && currentCardType === "departments") {
          $("#departmentFormContainer").show();
      } else if (action === "edit" && currentCardType === "departments") {
          $("#editDepartmentFormContainer").show();
      } else if (action === "delete" && currentCardType === "departments") {
          $("#deleteDepartmentFormContainer").show();
      } else if (action === "add" && currentCardType === "asset_type") {
          $("#assetTypeFormContainer").show();
      } else if (action === "edit" && currentCardType === "asset_type") {
          $("#editAssetTypeFormContainer").show();
      } else if (action === "delete" && currentCardType === "asset_type") {
          $("#deleteAssetTypeFormContainer").show();
      } else if (action === "add" && currentCardType === "site_locations") {
          $("#siteLocationFormContainer").show();
      } else if (action === "edit" && currentCardType === "site_locations") {
          $("#editSiteLocationFormContainer").show();
      } else if (action === "delete" && currentCardType === "site_locations") {
          $("#deleteSiteLocationFormContainer").show();
      } else {
          alert("Javascript Error");
      }
  });
//------                                                    ------//

//------      ASSET STATUS ADD/EDIT/DELETE FUNCTION         ------//

  $("#addAssetStatusForm").submit(function(event) {
      event.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_status_data.php",
          data: formData,
          success: function(response) {
            $("#assetStatusFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#assetStatusFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#editAssetStatusForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_status_id"]:checked').length === 0) {
          alert("Please select an entry to edit."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_status_data.php",
          data: formData,
          success: function(response) {
            $("#editAssetStatusFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#editAssetStatusFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#deleteAssetStatusForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_status_id"]:checked').length === 0) {
          alert("Please select an entry to delete."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_status_data.php",
          data: formData,
          success: function(response) {
            $("#deleteAssetStatusFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#deleteAssetStatusFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
//------                                                       ------//

//------          DEPARTMENTS ADD/EDIT/DELETE FUNCTION         ------//
  $("#addDepartmentForm").submit(function(event) {
      event.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_departments_data.php",
          data: formData,
          success: function(response) {
            $("#departmentFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#departmentFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#editDepartmentForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_department_id"]:checked').length === 0) {
          alert("Please select an entry to edit."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_departments_data.php",
          data: formData,
          success: function(response) {
            $("#editDepartmentFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#editDepartmentFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");
          }
      });
  });
  $("#deleteDepartmentForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_department_id"]:checked').length === 0) {
          alert("Please select an entry to delete."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_departments_data.php",
          data: formData,
          success: function(response) {
            $("#deleteDepartmentFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#deleteDepartmentFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
//------                                                                   ------//

//------                ASSET TYPE ADD/EDIT/DELETE FUNCTION                ------//
  $("#addAssetTypeForm").submit(function(event) {
      event.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_type_data.php",
          data: formData,
          success: function(response) {
            $("#assetTypeFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#assetTypeFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#editAssetTypeForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_type_id"]:checked').length === 0) {
          alert("Please select an entry to edit."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_type_data.php",
          data: formData,
          success: function(response) {
            $("#editAssetTypeFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#editAssetTypeFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#deleteAssetTypeForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_type_id"]:checked').length === 0) {
          alert("Please select an entry to delete."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_asset_type_data.php",
          data: formData,
          success: function(response) {
            $("#deleteAssetTypeFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#deleteAssetTypeFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
//------                                                                   ------//

//------               SITE LOCATION ADD/EDIT/DELETE FUNCTION              ------//
  $("#addSiteLocationForm").submit(function(event) {
      event.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_site_locations_data.php",
          data: formData,
          success: function(response) {
            $("#siteLocationFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);},
          error: function(xhr, status, error) {$("#siteLocationFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#editSiteLocationForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_site_id"]:checked').length === 0) {
          alert("Please select an entry to edit."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_site_locations_data.php",
          data: formData,
          success: function(response) {
            $("#editSiteLocationFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);},
          error: function(xhr, status, error) {$("#editSiteLocationFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
  $("#deleteSiteLocationForm").submit(function(event) {
      event.preventDefault();
      if ($('input[name="selected_site_id"]:checked').length === 0) {
          alert("Please select an entry to delete."); return;
      }
      var formData = $(this).serialize();
      $.ajax({
          type: "POST",
          url: "process_site_locations_data.php",
          data: formData,
          success: function(response) {
            $("#deleteSiteLocationFormContainer").html(response);
            setTimeout(function() {location.reload();}, 1000);
          },
          error: function(xhr, status, error) {$("#deleteSiteLocationFormContainer").html("<p style='color: red;'>Error: " + error + "</p>");}
      });
  });
});