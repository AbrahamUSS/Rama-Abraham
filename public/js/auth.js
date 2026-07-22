// Authentication Manager for IEP Corazón de Jesús College
(function() {
  const SESSION_KEY = 'colegio_corazon_jesus_session';

  window.SchoolAuth = {
    // Attempt Login with single username
    login: function(username) {
      username = username.trim().toLowerCase();
      let session = null;

      if (username === 'docen') {
        session = {
          username: 'docen',
          role: 'docente',
          name: 'Prof. Carlos Rivas',
          roleLabel: 'Docente de Primaria'
        };
      } else if (username === 'dire') {
        session = {
          username: 'dire',
          role: 'administrativo',
          name: 'Lic. Jose Perez',
          roleLabel: 'Director Administrativo'
        };
      }

      if (session) {
        localStorage.setItem(SESSION_KEY, JSON.stringify(session));
        return { success: true, session: session };
      }
      return { success: false, error: 'Usuario no reconocido. Utilice "docen" para docente o "dire" para director.' };
    },

    // Retrieve active session
    getSession: function() {
      // 1. Try reading from LocalStorage
      try {
        const data = localStorage.getItem(SESSION_KEY);
        if (data) {
          return JSON.parse(data);
        }
      } catch (e) {
        console.warn('LocalStorage no disponible. Usando fallback de URL.', e);
      }

      // 2. Fallback to URL parameter (critical for file:// origin isolation)
      const urlParams = new URLSearchParams(window.location.search);
      const u = urlParams.get('u');
      if (u === 'docen') {
        const session = {
          username: 'docen',
          role: 'docente',
          name: 'Prof. Carlos Rivas',
          roleLabel: 'Docente de Primaria'
        };
        try {
          localStorage.setItem(SESSION_KEY, JSON.stringify(session));
        } catch (e) {}
        return session;
      } else if (u === 'dire') {
        const session = {
          username: 'dire',
          role: 'administrativo',
          name: 'Lic. Jose Perez',
          roleLabel: 'Director Administrativo'
        };
        try {
          localStorage.setItem(SESSION_KEY, JSON.stringify(session));
        } catch (e) {}
        return session;
      }
      return null;
    },

    // Check if session exists and fits expected role
    checkGuard: function(expectedRole) {
      const session = this.getSession();
      if (!session) {
        window.location.href = 'login.html?u=';
        return false;
      }
      if (session.role !== expectedRole) {
        // Misaligned role - redirect propagating the query param fallback
        if (session.role === 'docente') {
          window.location.href = 'docente.html?u=docen';
        } else {
          window.location.href = 'admin.html?u=dire';
        }
        return false;
      }
      return true;
    },

    // Logout and clear session
    logout: function() {
      try {
        localStorage.removeItem(SESSION_KEY);
      } catch (e) {}
      window.location.href = 'login.html?u=';
    }
  };
})();
