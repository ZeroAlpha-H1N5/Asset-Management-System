$(document).ready(function() {
    // -----        SIDEBAR POPUP        ----- //
    $("#toggleSidebarButton").click(function() {
        $(".sidebar").toggleClass("collapsed");
        $(".content").toggleClass("sidebar-collapsed");
        $(this).toggleClass("sidebar-collapsed");

        //Update button text dynamically
        if($(".sidebar").hasClass("collapsed")){
           $(this).text("☰");
        } else {
            $(this).text("☰");
        }
    });

    // -----        LOGOUT MODAL        ----- //
        // Get the modal
        var modal = $("#logoutModal");
    
        // Get the link that opens the modal
        var logoutLink = $("#logoutLink");
    
        // Get the <span> element that closes the modal
        var span = $(".close");
    
        // Get the buttons
        var confirmLogout = $("#confirmLogout");
        var cancelLogout = $("#cancelLogout");
    
        // When the user clicks the link, open the modal
        logoutLink.click(function(event) {
            event.preventDefault();
            modal.css("display", "block");
        });
    
        // When the user clicks on <span> (x), close the modal
        span.click(function() {
            modal.css("display", "none");
        });
    
        // When the user clicks outside the modal, close it.  (Optional - can be annoying)
        $(window).click(function(event) {
             if (event.target == modal[0]) {
                 modal.css("display", "none");
             }
         });
    
        // When the user clicks "Yes, Logout"
        confirmLogout.click(function() {
            window.location.href = "logout.php"; // Redirect to logout script
        });
    
        // When the user clicks "Cancel"
        cancelLogout.click(function() {
            modal.css("display", "none");
        });
    // -----                            ----- //
    
    // -----        LIVE DATE/TIME      ------//
    function updateDateTime() {
        var now = new Date();
    
        // Get day of the week
        const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const dayOfWeek = daysOfWeek[now.getDay()];
    
        // Format the date
        const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
        ];
        const month = monthNames[now.getMonth()];
        const day = now.getDate();
        const year = now.getFullYear();
    
        const formattedDate = `${month} ${day}, ${year}`;
    
        const timeString = now.toLocaleTimeString();
    
        const dateTimeString = `${dayOfWeek}, ${formattedDate} ${timeString}`;
    
        $("#datetime").text(dateTimeString);
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
    // -----                            ----- //

    // ----- SCROLL POSITION RESTORATION ----- //
    // Check if a scroll position was saved from a previous submission
    const savedScrollPosition = sessionStorage.getItem('scrollPosition');
    if (savedScrollPosition !== null) {
        // Convert stored string value back to a number
        const scrollY = parseInt(savedScrollPosition, 10);
        // Scroll to the saved position
        window.scrollTo(0, scrollY);
        // Remove the item so it doesn't affect normal reloads/navigation
        sessionStorage.removeItem('scrollPosition');
    }
    // --------                     --------- //

    // ----- EXPORT FILTER FUNCTION ------ //
    $('#export_filter').change(function() { 
        var filter = this.value;
        $('#export_asset_type').toggle(filter === 'asset_type');
        $('#export_department').toggle(filter === 'department');
        $('#export_site_location').toggle(filter === 'site_location');
        $('#export_asset_brand').toggle(filter === 'asset_brand');
        $('#export_asset_status').toggle(filter === 'asset_status');
        });
    $('#export_filter_turnovered').change(function() {
        var filter = this.value;
        $('#asset_type_turnovered').toggle(filter === 'asset_type');
        $('#department_turnovered').toggle(filter === 'department');
        $('#site_location_turnovered').toggle(filter === 'site_location');
        $('#asset_status_turnovered').toggle(filter === 'asset_status');
        $('#asset_brand_turnovered').toggle(filter === 'asset_brand');
    });
    $('#export_filter_registered').change(function() {
        var filter = this.value;
        $('#asset_type_registered').toggle(filter === 'asset_type');
        $('#department_registered').toggle(filter === 'department');
        $('#site_location_registered').toggle(filter === 'site_location');
        $('#asset_status_registered').toggle(filter === 'asset_status');
        $('#asset_brand_registered').toggle(filter === 'asset_brand');
    });
    // -----                                      ------ //

    // ----- FILTER/SORT CONTROLS FOR MAIN TABLE ----- //
    const $filterForm = $('#filterForm');
    const $searchInput = $('#search_term'); 
    const $sortBySelect = $('#sort_by_select'); 
    const $assetType = $('#asset_type');
    const $assetStatus = $('#asset_status');
    const $siteLocation = $('#site_location');
    const $department = $('#department');
    const $sortToggleButton = $('#sort_toggle_button'); 
    const $sortHiddenInput = $('#sort_order_hidden'); 

    // Function to submit the form
    function submitFilterForm() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
        $filterForm.submit();
    }

    // Handler for search input (Enter key press)
    $searchInput.on('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            submitFilterForm();
        }
    });

    // Handler for Sort By dropdown change
    $assetType.on('change', submitFilterForm); 
    $assetStatus.on('change', submitFilterForm); 
    $siteLocation.on('change', submitFilterForm); 
    $department.on('change', submitFilterForm); 
    $sortBySelect.on('change', submitFilterForm); 


    // Handler for sort toggle button click 
    $sortToggleButton.on('click', function() {
        const currentOrder = $sortHiddenInput.val();
        const newOrder = (currentOrder === 'ASC') ? 'DESC' : 'ASC';
        const $sortIconSpan = $(this).find('.sort-icon');

        $sortHiddenInput.val(newOrder);
        $sortIconSpan.removeClass('sort-asc sort-desc');
        $sortIconSpan.addClass(newOrder === 'ASC' ? 'sort-asc' : 'sort-desc');

        submitFilterForm();
    });
    // -----                          ------ //

    // ----- REGISTERED TABLE FILTER/SORT CONTROLS ----- //
    const $registeredFilterForm = $('#registeredFilterForm'); 
    const $registeredSearchInput = $('#registered_search_term'); 
    const $registeredSortToggleButton = $('#registered_sort_toggle_button');
    const $registeredSortHiddenInput = $('#registered_sort_order_hidden'); 

    // Function to submit the registered filter form
    function submitRegisteredFilterForm() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
        $registeredFilterForm.submit();
    }

    // Handler for registered search input with debouncing
    $registeredSearchInput.on('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            submitRegisteredFilterForm();
        }
    });

    // Handler for registered sort toggle button click
    $registeredSortToggleButton.on('click', function() {
        const currentOrder = $registeredSortHiddenInput.val();
        const newOrder = (currentOrder === 'ASC') ? 'DESC' : 'ASC';
        const $sortIconSpan = $(this).find('.sort-icon');

        $registeredSortHiddenInput.val(newOrder);
        $sortIconSpan.removeClass('sort-asc sort-desc');
        $sortIconSpan.addClass(newOrder === 'ASC' ? 'sort-asc' : 'sort-desc');

        submitRegisteredFilterForm();
    });
    // -----                                      ------ //

    // ----- TURNOVERED TABLE FILTER/SORT CONTROLS ----- //
    const $turnoveredFilterForm = $('#turnoveredFilterForm'); 
    const $turnoveredSearchInput = $('#turnovered_search_term'); 
    const $turnoveredSortToggleButton = $('#turnovered_sort_toggle_button');
    const $turnoveredSortHiddenInput = $('#turnovered_sort_order_hidden'); 

    // Function to submit the turnovered filter form
    function submitTurnoveredFilterForm() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
        $turnoveredFilterForm.submit();
    }

    // Handler for turnovered search input with debouncing
    $turnoveredSearchInput.on('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            submitTurnoveredFilterForm();
        }
    });

    // Handler for turnovered sort toggle button click
    $turnoveredSortToggleButton.on('click', function() {
        const currentOrder = $turnoveredSortHiddenInput.val();
        const newOrder = (currentOrder === 'ASC') ? 'DESC' : 'ASC';
        const $sortIconSpan = $(this).find('.sort-icon');

        $turnoveredSortHiddenInput.val(newOrder);

        $sortIconSpan.removeClass('sort-asc sort-desc');
        $sortIconSpan.addClass(newOrder === 'ASC' ? 'sort-asc' : 'sort-desc');

        submitTurnoveredFilterForm();
    });
    // -----                                      ------ //

    // ----- CARD CATEGORY SEARCH/SORT CONTROLS ----- //
    // Use unique IDs from the HTML for this specific page
    const $assetSelForm = $('#assetTypeSearchSortForm'); 
    const $assetSelSearchInput = $('#asset_search_term'); 
    const $assetSelSortBySelect = $('#asset_sort_by_select'); 
    const $assetSelSortToggleButton = $('#asset_sort_toggle_button'); 
    const $assetSelSortHiddenInput = $('#asset_sort_order_hidden');   

    // Function to submit this specific form
    function submitAssetSelectionForm() {
        sessionStorage.setItem('scrollPosition', window.scrollY); 
        $assetSelForm.submit();
    }

    // Handler for Enter key press in this specific search input
    $assetSelSearchInput.on('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            submitAssetSelectionForm();
        }
    });

    // Handler for Sort By dropdown change
    $assetSelSortBySelect.on('change', submitAssetSelectionForm); 

    // Handler for sort toggle button click
    $assetSelSortToggleButton.on('click', function() {
        const currentOrder = $assetSelSortHiddenInput.val();
        const newOrder = (currentOrder === 'ASC') ? 'DESC' : 'ASC';
        const $sortIconSpan = $(this).find('.sort-icon');

        $assetSelSortHiddenInput.val(newOrder);
        $sortIconSpan.removeClass('sort-asc sort-desc');
        $sortIconSpan.addClass(newOrder === 'ASC' ? 'sort-asc' : 'sort-desc');

        submitAssetSelectionForm(); // Submit when sort order changes
    });
    // -----                                      ------ //

});