function applyTheme(mode) {
    if (mode === 'dark') {
        document.body.classList.add('theme-dark');
    } else {
        document.body.classList.remove('theme-dark');
    }

    // Update radio buttons if on the theme page
    const themeRadios = document.querySelectorAll('input[name="themeColor"]');
    if (themeRadios.length > 0) {
        themeRadios.forEach(radio => {
            radio.checked = (radio.value === mode);
        });
    }
}

// Main script execution on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // --- 1. Sidebar Toggle Logic ---
    const sidebarToggle = document.getElementById('sidebarToggle');
    if(sidebarToggle) {
        // Apply saved sidebar state on page load
        const savedSidebarState = localStorage.getItem('sidebarState');
        if (savedSidebarState === 'collapsed') {
            document.body.classList.add('sidebar-collapsed');
        }

        // Add click event to the toggle button
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            // Save the new state to localStorage
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        });
    }

    // --- 2. Theme Switcher Logic ---
    // Apply saved theme on page load
    const savedTheme = localStorage.getItem('dashboardTheme') || 'light';
    applyTheme(savedTheme);

    // Add event listeners to theme radio buttons (if they exist on the page)
    const themeRadios = document.querySelectorAll('input[name="themeColor"]');
    if (themeRadios.length > 0) {
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                localStorage.setItem('dashboardTheme', this.value);
                applyTheme(this.value);
            });
        });
    }
});

document.addEventListener("DOMContentLoaded", function() {
  const deleteButtons = document.querySelectorAll(".delete-btn");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

  deleteButtons.forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      const id = this.getAttribute("data-id");
      confirmDeleteBtn.href = `delete.php?table=hosting_domain&id=${id}`;
      const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
      modal.show();
    });
  });
});

document.addEventListener("DOMContentLoaded", function() {
  const editButtons = document.querySelectorAll(".edit-btn");
  const confirmEditBtn = document.getElementById("confirmEditBtn");

  editButtons.forEach(btn => {
    btn.addEventListener("click", function(e) {
      e.preventDefault();
      const id = this.getAttribute("data-id");
      confirmEditBtn.href = `edit.php?table=hosting_domain&id=${id}`;
      const modal = new bootstrap.Modal(document.getElementById("editModal"));
      modal.show();
    });
  });
});


// Enhanced version with smooth icon transition
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');

    // Function to update theme
    function updateTheme(theme) {
        const themeIcon = themeToggle.querySelector('i');

        // Add rotation animation
        themeIcon.style.transform = 'rotate(360deg)';

        setTimeout(() => {
            if (theme === 'dark') {
                document.body.classList.add('theme-dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeToggle.title = 'Switch to Light Mode';
            } else {
                document.body.classList.remove('theme-dark');
                themeIcon.className = 'bi bi-moon-stars-fill';
                themeToggle.title = 'Switch to Dark Mode';
            }
            themeIcon.style.transform = 'rotate(0deg)';
        }, 150);
    }

    // Check and apply saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    updateTheme(savedTheme);

    // Toggle theme on click
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        updateTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    });
});