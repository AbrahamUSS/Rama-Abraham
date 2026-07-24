// Enrutador SPA por hash - rutas y renderizado de vistas
(function() {
  window.SchoolRouter = {
    routes: {},
    defaultHash: '',
    
    init: function(routesConfig, defaultHash) {
      this.routes = routesConfig;
      this.defaultHash = defaultHash;
      
      const self = this;
      window.addEventListener('hashchange', function() {
        self.handleRoute();
      });
      self.handleRoute();
    },
    
    handleRoute: function() {
      const rawHash = window.location.hash;
      let routeKey = rawHash || this.defaultHash;
      
      if (routeKey && !routeKey.startsWith('#')) {
        routeKey = '#' + routeKey;
      }

      // Actualizar menú lateral
      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.classList.remove('active');
      });
      const activeLink = document.querySelector(`.sidebar-menu a[href="${routeKey}"]`);
      if (activeLink) {
        activeLink.closest('.menu-item').classList.add('active');
      }
      
      const layout = document.getElementById('app-layout');
      if (layout) {
        layout.classList.remove('mobile-active');
      }
      
      const renderFn = this.routes[routeKey];
      if (renderFn) {
        const mainContainer = document.getElementById('main-content');
        if (mainContainer) {
          mainContainer.innerHTML = '';
          renderFn(mainContainer);
          window.scrollTo(0, 0);
        }
      } else {
        console.warn('Ruta no definida: ' + routeKey + '. Redirigiendo a ' + this.defaultHash);
        window.location.hash = this.defaultHash;
      }
    }
  };
})();
