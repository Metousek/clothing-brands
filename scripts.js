// Toggling dropdowns with smooth animation
function toggleDropdown(id) {
    let dropdown = document.getElementById(id);
    let isActive = dropdown.classList.contains('active');
    
    // Hides all dropdowns
    document.querySelectorAll('.dropdown').forEach(d => {
        d.classList.remove('active');
    });
    
    // Reset all spacers to 0 height
    adjustBrandList(0);
    
    // If the clicked dropdown was closed, open it
    if (!isActive) {
        dropdown.classList.add('active');
        
        // Measure the dropdown's height and adjust the spacer
        setTimeout(() => {
            let dropdownHeight = dropdown.offsetHeight;
            adjustBrandList(dropdownHeight);
        }, 50);
    }
}

// Function to adjust the brand list position by creating/adjusting a spacer
function adjustBrandList(height) {
    // Find or create a spacer element
    let spacer = document.querySelector('.brand-list-spacer');
    if (!spacer) {
        spacer = document.createElement('div');
        spacer.className = 'brand-list-spacer';
        const filterContainer = document.querySelector('.filter-container');
        filterContainer.after(spacer);
    }
    
    // Set the height of the spacer
    spacer.style.height = height + 'px';
}

// Adding filter
function addFilter(type, id, label) {
    let activeFilters = document.getElementById('active-filters');
    let existingTag = document.querySelector(`.filter-tag[data-type="${type}"][data-id="${id}"]`);
    
    // Checks if the checkbox is checked
    let checkbox = event.target;
    
    if (checkbox.checked) {
        // If the filter doesn't exist yet, add it
        if (!existingTag) {
            // Creates filter tag
            let filterTag = document.createElement('div');
            filterTag.className = 'filter-tag';
            filterTag.setAttribute('data-type', type);
            filterTag.setAttribute('data-id', id);
            filterTag.innerHTML = `${label} <button type="button" onclick="removeFilter('${type}', '${id}')">×</button>`;
            activeFilters.appendChild(filterTag);
            
            // Create hidden input for form submission
            let form = document.getElementById('searchForm');
            let hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `${type}[]`;
            hiddenInput.value = id;
            form.appendChild(hiddenInput);
            
            // Submit form
            form.submit();
        }
    } else {
        // Remove filter when unchecked
        removeFilter(type, id);
    }
}

// Function to remove a filter
function removeFilter(type, id) {
    const activeFilters = document.getElementById('active-filters');
    const form = document.getElementById('searchForm');
    
    // Remove filter tag
    const filterTag = activeFilters.querySelector(`.filter-tag[data-type="${type}"][data-id="${id}"]`);
    if (filterTag) {
        filterTag.remove();
    }
    
    // Remove hidden input
    const hiddenInput = form.querySelector(`input[name="${type}[]"][value="${id}"]`);
    if (hiddenInput) {
        hiddenInput.remove();
    }
    
    // Uncheck the checkbox if it exists
    const checkbox = document.querySelector(`input[type="checkbox"][name="${type}[]"][value="${id}"]`);
    if (checkbox) {
        checkbox.checked = false;
    }
    
    // Submit form
    form.submit();
}

// Function to toggle brand details
function toggleBrandDetails(header) {
    const details = header.nextElementSibling;
    details.classList.toggle('active');
}

// Add link field in edit/add forms
function addLinkField() {
    const linksContainer = document.getElementById('links-container');
    const linkGroup = document.createElement('div');
    linkGroup.className = 'link-group';
    
    const linkIndex = document.querySelectorAll('.link-group').length;
    
    linkGroup.innerHTML = `
        <button type="button" class="link-remove" onclick="removeLink(this)">×</button>
        <div class="form-group">
            <label for="link_titles[${linkIndex}]">Link Title</label>
            <input type="text" id="link_titles[${linkIndex}]" name="link_titles[]" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="link_urls[${linkIndex}]">Link URL</label>
            <input type="url" id="link_urls[${linkIndex}]" name="link_urls[]" class="form-control" required>
        </div>
    `;
    
    linksContainer.appendChild(linkGroup);
}

// Remove link field
function removeLink(button) {
    const linkGroup = button.parentElement;
    linkGroup.remove();
}

// Document ready function to ensure smooth animations work on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initial setup for any page-specific features
    
    // Create the spacer element if needed
    if (!document.querySelector('.brand-list-spacer')) {
        const spacer = document.createElement('div');
        spacer.className = 'brand-list-spacer';
        const filterContainer = document.querySelector('.filter-container');
        filterContainer.after(spacer);
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.filter')) {
            document.querySelectorAll('.dropdown').forEach(d => {
                d.classList.remove('active');
            });
            adjustBrandList(0);
        }
    });
});