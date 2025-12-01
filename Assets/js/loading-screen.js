/**
 * Universal Loading Screen Handler
 * Shows loading screen when navigating to dashboard from any page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if loading overlay exists
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    if (!loadingOverlay) {
        console.warn('Loading overlay not found on this page');
        return;
    }
    
    // Find all links that go to dashboard
    const dashboardLinks = document.querySelectorAll('a[href*="dashboard.php"]');
    
    dashboardLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            loadingOverlay.classList.add('active');
            
            // Get the href (in case it has query params)
            const href = this.getAttribute('href');
            
            // Navigate after a brief moment to ensure overlay is visible
            setTimeout(() => {
                window.location.href = href;
            }, 100);
        });
    });
    
    // Also handle form submissions that redirect to dashboard
    const forms = document.querySelectorAll('form[action*="dashboard.php"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't prevent default - let form submit normally
            // Just show the loading screen
            loadingOverlay.classList.add('active');
        });
    });
});
