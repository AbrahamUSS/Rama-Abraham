// Hash Router for IEP Corazón de Jesús College (Pure JS SPA framework)
(function() {
  window.SchoolRouter = {
    routes: {},
    defaultHash: '',
    
    // Initialize the router with a config map of paths and render functions
    init: function(routesConfig, defaultHash) {
      this.routes = routesConfig;
      this.defaultHash = defaultHash;
      
      const self = this;
      
      // Bind navigation event handlers
      window.addEventListener('hashchange', function() {
        self.handleRoute();
      });
      
      window.addEventListener('DOMContentLoaded', function() {
        self.handleRoute();
      });
    },
    
    // Process route switching
    handleRoute: function() {
      const rawHash = window.location.hash;
      let routeKey = rawHash || this.defaultHash;
      
      // Fallback if hash doesn't have '#' prefix correctly
      if (routeKey && !routeKey.startsWith('#')) {
        routeKey = '#' + routeKey;
      }

      // Sync active state in Sidebar UI
      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.classList.remove('active');
      });
      
      const activeLink = document.querySelector(`.sidebar-menu a[href="${routeKey}"]`);
      if (activeLink) {
        activeLink.closest('.menu-item').classList.add('active');
      }
      
      // Close mobile drawer if active
      const layout = document.getElementById('app-layout');
      if (layout) {
        layout.classList.remove('mobile-active');
      }
      
      // Run rendering function for route
      const renderFn = this.routes[routeKey];
      if (renderFn) {
        const mainContainer = document.getElementById('main-content');
        if (mainContainer) {
          // Clear current content
          mainContainer.innerHTML = '';
          
          // Execute view renderer
          renderFn(mainContainer);
          
          // Scroll to top
          window.scrollTo(0, 0);
        }
      } else {
        console.warn('Ruta no definida: ' + routeKey + '. Redirigiendo a ' + this.defaultHash);
        window.location.hash = this.defaultHash;
      }
    }
  };
})();
