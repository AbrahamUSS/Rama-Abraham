// Docente Module Views & Event Listeners
(function() {

  // Global update page title helper
  function setPageTitle(title) {
    const el = document.getElementById('navbar-page-title');
    if (el) el.textContent = title;
  }

  /* ==========================================================================
     1. INFORMACIÓN PERSONAL & MENSAJERÍA
     ========================================================================== */
  function renderInfoPersonal(container) {
    setPageTitle('Información Personal y Mensajería');
    
    const db = window.SchoolDB.getData();
    const session = window.SchoolAuth.getSession() || { name: 'Prof. Carlos Rivas', email: 'carlos.rivas@colegio.edu.pe' };
    
    let chatHtml = '';
    const myMessages = db.messages.docentes.filter(m => m.from === 'Prof. Carlos Rivas' || m.to === 'Prof. Carlos Rivas');
    
    myMessages.forEach(msg => {
      const isMe = msg.from === 'Prof. Carlos Rivas';
      chatHtml += `
        <div class="msg-bubble ${isMe ? 'msg-sent' : 'msg-received'}">
          <strong>${msg.from}</strong>
          <div>${msg.content}</div>
          <div class="msg-time">${msg.timestamp}</div>
        </div>
      `;
    });

    container.innerHTML = `
      <div class="dashboard-grid" style="grid-template-columns: 1fr 1.2fr;">
        <!-- Left: Form to update data -->
        <div class="card card-accent">
          <div class="card-header">
            <h3 class="card-title">
              <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Actualizar Datos de Docente
            </h3>
          </div>
          <form id="docent-info-form" class="form-layout" style="display: flex; flex-direction: column; gap: 14px;">
            <div class="form-group">
              <label class="form-label-desc">Nombre Completo</label>
              <input type="text" class="control-input" value="${session.name}" disabled>
            </div>
            <div class="form-group">
              <label class="form-label-desc">Correo Electrónico</label>
              <input type="email" id="docent-email" class="control-input" value="${session.email}">
            </div>
            <div class="form-group">
              <label class="form-label-desc">Teléfono de Contacto</label>
              <input type="text" id="docent-phone" class="control-input" value="987 654 321">
            </div>
            <div class="form-group">
              <label class="form-label-desc">Dirección de Domicilio</label>
              <input type="text" id="docent-address" class="control-input" value="Av. San Martín 456, Pueblo Libre">
            </div>
            <div class="form-group">
              <label class="form-label-desc">Especialidades</label>
              <input type="text" class="control-input" value="Matemática y Ciencias Naturales" disabled>
            </div>
            <div id="form-alert-container"></div>
            <button type="submit" class="btn btn-primary" style="margin-top: 10px; width: 100%;">
              Guardar Cambios
            </button>
          </form>
        </div>

        <!-- Right: Message with Director -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
              Mensajería Interna con Dirección
            </h3>
            <span class="badge badge-info">Director Conectado</span>
          </div>
          
          <div class="chat-messages-panel" style="border: 1px solid var(--neutral-light); border-radius: var(--radius-md); overflow: hidden;">
            <div class="messages-scroller" id="docent-chat-scroller" style="height: 300px;">
              ${chatHtml}
            </div>
            <div class="chat-input-panel">
              <input type="text" id="docent-chat-input" class="chat-text-input" placeholder="Escriba un mensaje al Director...">
              <button class="btn btn-primary" id="docent-chat-send-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Handle profile update submit
    const form = document.getElementById('docent-info-form');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const alertContainer = document.getElementById('form-alert-container');
      alertContainer.innerHTML = `
        <div class="badge badge-success" style="padding: 10px; width: 100%; border-radius: 6px; text-align: center;">
          ✓ Datos guardados exitosamente.
        </div>
      `;
      setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
    });

    // Handle Send Message
    const chatInput = document.getElementById('docent-chat-input');
    const sendBtn = document.getElementById('docent-chat-send-btn');
    const chatScroller = document.getElementById('docent-chat-scroller');
    
    // Auto-scroll chat to bottom
    chatScroller.scrollTop = chatScroller.scrollHeight;

    function sendMessage() {
      const text = chatInput.value.trim();
      if (!text) return;

      const newMsg = window.SchoolDB.sendDocentMessage('Prof. Carlos Rivas', text);
      
      // Append bubble in UI
      const bubble = document.createElement('div');
      bubble.className = 'msg-bubble msg-sent';
      bubble.innerHTML = `
        <strong>${newMsg.from}</strong>
        <div>${newMsg.content}</div>
        <div class="msg-time">${newMsg.timestamp}</div>
      `;
      chatScroller.appendChild(bubble);
      chatInput.value = '';
      chatScroller.scrollTop = chatScroller.scrollHeight;

      // Simulated Director Reply
      setTimeout(() => {
        const reply = window.SchoolDB.sendDocentMessage('Director', 'Entendido profesor Carlos. Estaré atendiendo su solicitud a la brevedad.');
        const replyBubble = document.createElement('div');
        replyBubble.className = 'msg-bubble msg-received';
        replyBubble.innerHTML = `
          <strong>${reply.from}</strong>
          <div>${reply.content}</div>
          <div class="msg-time">${reply.timestamp}</div>
        `;
        chatScroller.appendChild(replyBubble);
        chatScroller.scrollTop = chatScroller.scrollHeight;
      }, 2000);
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') sendMessage();
    });
  }

  /* ==========================================================================
     2. CURSOS: ELEGIR CURSO, MATERIAL DE CLASE & REGISTRAR NOTAS
     ========================================================================== */
  function renderCursos(container) {
    setPageTitle('Gestión de Cursos');

    const db = window.SchoolDB.getData();
    let courseOptions = '';
    db.courses.forEach(c => {
      courseOptions += `<option value="${c.id}">${c.name}</option>`;
    });

    container.innerHTML = `
      <!-- selector top panel -->
      <div class="selector-panel">
        <label class="selector-label" for="course-picker">Seleccione Curso:</label>
        <select id="course-picker" class="control-select" style="max-width: 320px;">
          <option value="" disabled selected>-- Elija un curso --</option>
          ${courseOptions}
        </select>
      </div>

      <div id="course-content-area" style="display: none;">
        <div class="tabs-container">
          <button class="tab-btn active" id="tab-materials">Material de Clase</button>
          <button class="tab-btn" id="tab-grades">Registrar Notas</button>
        </div>

        <div id="tab-view-container">
          <!-- view content injected dynamically -->
        </div>
      </div>
    `;

    const coursePicker = document.getElementById('course-picker');
    const contentArea = document.getElementById('course-content-area');
    const tabMaterials = document.getElementById('tab-materials');
    const tabGrades = document.getElementById('tab-grades');
    let activeCourseId = '';
    let currentTab = 'materials'; // materials | grades

    coursePicker.addEventListener('change', function() {
      activeCourseId = this.value;
      contentArea.style.display = 'block';
      renderActiveTab();
    });

    tabMaterials.addEventListener('click', function() {
      tabMaterials.classList.add('active');
      tabGrades.classList.remove('active');
      currentTab = 'materials';
      renderActiveTab();
    });

    tabGrades.addEventListener('click', function() {
      tabGrades.classList.add('active');
      tabMaterials.classList.remove('active');
      currentTab = 'grades';
      renderActiveTab();
    });

    function renderActiveTab() {
      const tabContainer = document.getElementById('tab-view-container');
      const course = db.courses.find(c => c.id === activeCourseId);
      
      if (currentTab === 'materials') {
        tabContainer.innerHTML = `
          <div class="card card-accent" style="max-width: 600px;">
            <div class="card-header">
              <h3 class="card-title">Descarga de Material Docente - ${course.name}</h3>
            </div>
            <p style="font-size: 13.5px; color: var(--neutral-medium); margin-bottom: 20px;">
              Descargue los documentos base para el desarrollo del plan curricular académico vigente homologados por el Minedu.
            </p>

            <div class="download-item">
              <div class="download-info">
                <div class="download-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <div class="download-text">
                  <h4>Currículo Nacional 2026</h4>
                  <span>Documento oficial curricular del Ministerio de Educación (PDF, 240 KB)</span>
                </div>
              </div>
              <a href="docs/curriculo.pdf" download="Curriculo_Nacional_2026.pdf" class="btn btn-primary btn-sm">Descargar</a>
            </div>

            <div class="download-item">
              <div class="download-info">
                <div class="download-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <div class="download-text">
                  <h4>Sílabo Escolar de Asignatura</h4>
                  <span>Dosificación y plan de actividades por bimestre (PDF, 150 KB)</span>
                </div>
              </div>
              <a href="docs/syllabus.pdf" download="Silabo_Curso.pdf" class="btn btn-primary btn-sm">Descargar</a>
            </div>
          </div>
        `;
      } else {
        // Grades Entry View
        // Map courseId to db subject key (MAT5P -> matematica, COM5P -> comunicacion, etc)
        let subjectKey = 'matematica';
        if (activeCourseId === 'MAT5P' || activeCourseId === 'INI5A') subjectKey = 'matematica';
        if (activeCourseId === 'COM5P') subjectKey = 'comunicacion';
        if (activeCourseId === 'CIEN5P') subjectKey = 'ciencia';

        // Filter students according to course level (Primaria or Inicial)
        const relevantStudents = db.students.filter(s => s.nivel === course.nivel);
        
        let tableRows = '';
        relevantStudents.forEach(st => {
          const currentGrade = st.grades[subjectKey] !== undefined ? st.grades[subjectKey] : '';
          const classColor = currentGrade !== '' && currentGrade < 11 ? 'fail' : 'pass';

          tableRows += `
            <tr data-student-id="${st.id}">
              <td style="font-weight: 600;">${st.id}</td>
              <td>${st.name}</td>
              <td>${st.grado}</td>
              <td style="text-align: center;">
                <input type="number" min="0" max="20" class="grade-input ${classColor}" value="${currentGrade}" data-student="${st.id}" data-subject="${subjectKey}">
              </td>
              <td style="text-align: center;" class="row-status">
                <span style="font-size: 11px; color: var(--neutral-medium);">Cargado</span>
              </td>
            </tr>
          `;
        });

        tabContainer.innerHTML = `
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Registro de Calificaciones: ${course.name}</h3>
              <div id="grades-save-indicator" class="badge badge-success" style="display:none;">¡Notas guardadas automáticamente!</div>
            </div>
            
            <div class="table-responsive">
              <table class="school-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Apellidos y Nombres</th>
                    <th>Nivel / Grado</th>
                    <th style="width: 120px; text-align: center;">Nota (0 - 20)</th>
                    <th style="width: 150px; text-align: center;">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  ${tableRows}
                </tbody>
              </table>
            </div>

            <div class="btn-container" style="justify-content: flex-end; margin-top: 20px;">
              <button class="btn btn-secondary" id="btn-revert-grades">Reestablecer</button>
              <button class="btn btn-primary" id="btn-save-grades-manually">Guardar Registro</button>
            </div>
          </div>
        `;

        // Handle auto color adjustments and auto-save indicators
        const gradeInputs = tabContainer.querySelectorAll('.grade-input');
        gradeInputs.forEach(input => {
          input.addEventListener('input', function() {
            const val = parseInt(this.value);
            const statusCell = this.closest('tr').querySelector('.row-status');
            
            // Adjust colors based on grade value
            if (!isNaN(val)) {
              if (val < 11) {
                this.className = 'grade-input fail';
              } else {
                this.className = 'grade-input pass';
              }
              statusCell.innerHTML = `<span style="font-size: 11px; color: var(--accent-orange); font-weight: 600;">Modificado</span>`;
            } else {
              this.className = 'grade-input';
              statusCell.innerHTML = ``;
            }
          });
        });

        // Save actions
        const saveBtn = document.getElementById('btn-save-grades-manually');
        const revertBtn = document.getElementById('btn-revert-grades');
        const indicator = document.getElementById('grades-save-indicator');

        saveBtn.addEventListener('click', function() {
          gradeInputs.forEach(input => {
            const stId = input.getAttribute('data-student');
            const sub = input.getAttribute('data-subject');
            const val = input.value;
            window.SchoolDB.saveGrade(stId, sub, val);
            
            const statusCell = input.closest('tr').querySelector('.row-status');
            statusCell.innerHTML = `<span style="font-size: 11px; color: var(--success); font-weight: 600;">Guardado ✓</span>`;
          });
          
          indicator.style.display = 'block';
          setTimeout(() => { indicator.style.display = 'none'; }, 3000);
        });

        revertBtn.addEventListener('click', function() {
          renderActiveTab(); // simply re-render view from database values
        });
      }
    }
  }

  /* ==========================================================================
     3. ACTIVIDADES: CALENDARIO ESCOLAR
     ========================================================================== */
  function renderActividades(container) {
    setPageTitle('Calendario de Actividades');
    
    // We render a standard static June 2026 calendar view
    const db = window.SchoolDB.getData();
    
    // June 2026 starts on a Monday, has 30 days
    const totalDays = 30;
    const offset = 0; // Starts directly on Monday (June 1st)
    
    let calendarCells = '';
    
    // Pre-populate events by day
    const eventsByDay = {};
    db.calendarEvents.forEach(ev => {
      if (ev.date.startsWith('2026-06')) {
        const day = parseInt(ev.date.split('-')[2]);
        if (!eventsByDay[day]) eventsByDay[day] = [];
        eventsByDay[day].push(ev);
      }
    });

    // Weekdays labels
    const daysLabel = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    let labelsHtml = '';
    daysLabel.forEach(dl => {
      labelsHtml += `<div class="calendar-day-name">${dl}</div>`;
    });

    for (let day = 1; day <= 30; day++) {
      const dateStr = `2026-06-${day < 10 ? '0' + day : day}`;
      const isToday = dateStr === '2026-06-25'; // Simulated "today" based on project timestamp
      
      let eventsHtml = '';
      if (eventsByDay[day]) {
        eventsByDay[day].forEach(ev => {
          let eventClass = 'event-docente';
          if (ev.type === 'admin') eventClass = 'event-admin';
          if (ev.type === 'reunion') eventClass = 'event-reunion';
          
          eventsHtml += `
            <div class="calendar-event-item ${eventClass}" title="${ev.title}">
              ${ev.title}
            </div>
          `;
        });
      }

      calendarCells += `
        <div class="calendar-day-cell ${isToday ? 'today' : ''}">
          <span class="calendar-day-number">${day}</span>
          <div class="calendar-event-list">
            ${eventsHtml}
          </div>
        </div>
      `;
    }

    // Next month cells to fill grid (June ends on Tuesday, July starts on Wednesday)
    // Needs 35 cells total (5 rows of 7 days)
    for (let fill = 1; fill <= 5; fill++) {
      calendarCells += `
        <div class="calendar-day-cell other-month">
          <span class="calendar-day-number">${fill}</span>
          <div class="calendar-event-list"></div>
        </div>
      `;
    }

    container.innerHTML = `
      <div class="calendar-wrapper">
        <div class="calendar-header-actions">
          <h3 class="calendar-month-title">Junio 2026</h3>
          <div style="display: flex; gap: 8px;">
            <button class="calendar-nav-btn" disabled>&lt;</button>
            <button class="calendar-nav-btn" disabled>Hoy</button>
            <button class="calendar-nav-btn" disabled>&gt;</button>
          </div>
        </div>
        
        <div class="calendar-grid">
          ${labelsHtml}
          ${calendarCells}
        </div>
        
        <div style="margin-top: 20px; display: flex; gap: 16px; flex-wrap: wrap; font-size: 12px; font-weight: 600;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; background-color: var(--primary-dark); display: inline-block; border-radius: 2px;"></span>
            Actividad Docente
          </div>
          <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; background-color: var(--accent-orange); display: inline-block; border-radius: 2px;"></span>
            Eventos Administrativos
          </div>
          <div style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 12px; height: 12px; background-color: var(--success); display: inline-block; border-radius: 2px;"></span>
            Reuniones / Feriados
          </div>
        </div>
      </div>
    `;
  }

  /* ==========================================================================
     4. REGISTRAR ASISTENCIA: SECTOR DE CURSO, ASISTENCIA GENERAL & FILTROS
     ========================================================================== */
  function renderAsistencia(container) {
    setPageTitle('Registrar Asistencia');
    const db = window.SchoolDB.getData();

    let courseOptions = '';
    db.courses.forEach(c => {
      courseOptions += `<option value="${c.id}">${c.name}</option>`;
    });

    container.innerHTML = `
      <div class="selector-panel">
        <label class="selector-label" for="asist-course-picker">Seleccione Curso:</label>
        <select id="asist-course-picker" class="control-select" style="max-width: 320px;">
          <option value="" disabled selected>-- Elija un curso --</option>
          ${courseOptions}
        </select>
      </div>

      <div id="asist-content-area" style="display: none;">
        <div class="tabs-container">
          <button class="tab-btn active" id="asist-tab-primaria">Primaria</button>
          <button class="tab-btn" id="asist-tab-inicial">Inicial</button>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Control de Asistencia del Día</h3>
            <div id="asist-save-indicator" class="badge badge-success" style="display:none;">Asistencia registrada correctamente</div>
          </div>

          <div class="table-responsive">
            <table class="school-table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Alumno</th>
                  <th>Grado</th>
                  <th style="text-align: center; width: 280px;">Registro de Estado</th>
                </tr>
              </thead>
              <tbody id="asistencia-tbody">
                <!-- Rows load here based on level -->
              </tbody>
            </table>
          </div>

          <div class="btn-container" style="justify-content: flex-end; margin-top: 20px;">
            <button class="btn btn-primary" id="btn-save-asist">Confirmar Asistencia</button>
          </div>
        </div>
      </div>
    `;

    const picker = document.getElementById('asist-course-picker');
    const area = document.getElementById('asist-content-area');
    const tabPrimaria = document.getElementById('asist-tab-primaria');
    const tabInicial = document.getElementById('asist-tab-inicial');
    
    let activeLevel = 'Primaria'; // Primaria | Inicial
    let selectedDate = '2026-06-25'; // Simulated Attendance Date

    picker.addEventListener('change', function() {
      area.style.display = 'block';
      loadAttendanceTable();
    });

    tabPrimaria.addEventListener('click', function() {
      tabPrimaria.classList.add('active');
      tabInicial.classList.remove('active');
      activeLevel = 'Primaria';
      loadAttendanceTable();
    });

    tabInicial.addEventListener('click', function() {
      tabInicial.classList.add('active');
      tabPrimaria.classList.remove('active');
      activeLevel = 'Inicial';
      loadAttendanceTable();
    });

    function loadAttendanceTable() {
      const tbody = document.getElementById('asistencia-tbody');
      const levelStudents = db.students.filter(s => s.nivel === activeLevel);
      
      let rows = '';
      levelStudents.forEach(st => {
        const currentStatus = st.attendance[selectedDate] || 'P'; // default present
        
        rows += `
          <tr data-st-id="${st.id}">
            <td style="font-weight: 600;">${st.id}</td>
            <td>${st.name}</td>
            <td>${st.grado}</td>
            <td style="text-align: center;">
              <div style="display: flex; gap: 10px; justify-content: center;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; color: var(--success);">
                  <input type="radio" name="att-${st.id}" value="P" ${currentStatus === 'P' ? 'checked' : ''}> P
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; color: var(--warning);">
                  <input type="radio" name="att-${st.id}" value="T" ${currentStatus === 'T' ? 'checked' : ''}> T
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; color: var(--danger);">
                  <input type="radio" name="att-${st.id}" value="F" ${currentStatus === 'F' ? 'checked' : ''}> F
                </label>
              </div>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = rows;
    }

    // Save action
    container.addEventListener('click', function(e) {
      if (e.target && e.target.id === 'btn-save-asist') {
        const rows = document.querySelectorAll('#asistencia-tbody tr');
        rows.forEach(tr => {
          const stId = tr.getAttribute('data-st-id');
          const radio = tr.querySelector(`input[name="att-${stId}"]:checked`);
          if (radio) {
            window.SchoolDB.saveAttendance(stId, selectedDate, radio.value);
          }
        });

        const indicator = document.getElementById('asist-save-indicator');
        if (indicator) {
          indicator.style.display = 'block';
          setTimeout(() => { indicator.style.display = 'none'; }, 3000);
        }
      }
    });
  }

  /* ==========================================================================
     5. INCIDENCIAS: REDACCIÓN & LISTA DE INCIDENCIAS
     ========================================================================== */
  function renderIncidencias(container) {
    setPageTitle('Registro de Incidencias');

    function buildTableRows(incidents) {
      let rows = '';
      incidents.forEach(inc => {
        let badgeClass = 'badge-warning';
        if (inc.status === 'Resuelto') badgeClass = 'badge-success';
        
        rows += `
          <tr>
            <td style="font-weight: 600;">${inc.id}</td>
            <td>${inc.date}</td>
            <td>${inc.studentName}</td>
            <td>${inc.detail}</td>
            <td><span class="badge ${badgeClass}">${inc.status}</span></td>
          </tr>
        `;
      });
      return rows;
    }

    const db = window.SchoolDB.getData();
    let studentOptions = '';
    db.students.forEach(st => {
      studentOptions += `<option value="${st.name}">${st.name} (${st.grado})</option>`;
    });

    container.innerHTML = `
      <div class="dashboard-grid" style="grid-template-columns: 1fr 1.5fr;">
        <!-- Incident form -->
        <div class="card card-accent">
          <div class="card-header">
            <h3 class="card-title">Redactar Nueva Incidencia</h3>
          </div>
          <form id="incident-form" class="form-layout" style="display: flex; flex-direction: column; gap: 16px;">
            <div class="form-group">
              <label class="form-label-desc">Alumno Relacionado</label>
              <select id="inc-student" class="control-select" required>
                <option value="" disabled selected>-- Seleccione Alumno --</option>
                ${studentOptions}
              </select>
            </div>
            <div class="form-group">
              <label class="form-label-desc">Detalle del Suceso</label>
              <textarea id="inc-detail" class="control-textarea" placeholder="Escriba los pormenores del suceso con el alumno..." required></textarea>
            </div>
            <div id="inc-alert" class="badge badge-success" style="display:none; text-align: center; padding: 10px; border-radius: 6px;">
              ✓ Enviado correctamente a Dirección.
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
              Reportar a Dirección
            </button>
          </form>
        </div>

        <!-- Incidents List -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Mis Incidencias Reportadas</h3>
          </div>
          <div class="table-responsive">
            <table class="school-table">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th style="width: 100px;">Fecha</th>
                  <th style="width: 180px;">Alumno</th>
                  <th>Detalle / Incidencia</th>
                  <th style="width: 120px;">Estado</th>
                </tr>
              </thead>
              <tbody id="incidents-list-tbody">
                ${buildTableRows(db.incidents)}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;

    // Form Submission
    const form = document.getElementById('incident-form');
    const tableBody = document.getElementById('incidents-list-tbody');
    const alertBox = document.getElementById('inc-alert');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const studentVal = document.getElementById('inc-student').value;
      const detailVal = document.getElementById('inc-detail').value;

      // Add to simulated DB
      const newInc = window.SchoolDB.addIncident(studentVal, 'Carlos Rivas', detailVal);
      
      // Update List view immediately
      const updatedDb = window.SchoolDB.getData();
      tableBody.innerHTML = buildTableRows(updatedDb.incidents);

      // Reset form and show success message
      form.reset();
      alertBox.style.display = 'block';
      setTimeout(() => { alertBox.style.display = 'none'; }, 3000);
    });
  }

  /* ==========================================================================
     6. REPORTES: NOTAS, ASISTENCIAS & ALUMNOS CON FILTROS
     ========================================================================== */
  function renderReportes(container) {
    setPageTitle('Reportes Generales');

    container.innerHTML = `
      <div class="tabs-container">
        <button class="tab-btn active" id="rep-tab-grades">Reporte de Notas</button>
        <button class="tab-btn" id="rep-tab-attendance">Reporte de Asistencias</button>
        <button class="tab-btn" id="rep-tab-filters">Alumnos con Filtros</button>
      </div>

      <div id="report-view-content">
        <!-- Rendered view -->
      </div>
    `;

    const btnGrades = document.getElementById('rep-tab-grades');
    const btnAttendance = document.getElementById('rep-tab-attendance');
    const btnFilters = document.getElementById('rep-tab-filters');

    btnGrades.addEventListener('click', function() {
      switchTab('grades');
    });
    btnAttendance.addEventListener('click', function() {
      switchTab('attendance');
    });
    btnFilters.addEventListener('click', function() {
      switchTab('filters');
    });

    // Default view
    switchTab('grades');

    function switchTab(tabName) {
      const activeTab = document.querySelector('.tabs-container .active');
      if (activeTab) activeTab.classList.remove('active');
      
      const content = document.getElementById('report-view-content');
      const db = window.SchoolDB.getData();

      if (tabName === 'grades') {
        btnGrades.classList.add('active');
        
        // Calculate grades stats (e.g. general average per subject)
        let totalMat = 0, totalCom = 0, totalCie = 0, count = 0;
        let tableRows = '';

        db.students.forEach(s => {
          const mat = s.grades.matematica || 0;
          const com = s.grades.comunicacion || 0;
          const cie = s.grades.ciencia || 0;
          const average = parseFloat(((mat + com + cie) / 3).toFixed(1));
          const avgClass = average < 11 ? 'color: var(--danger); font-weight:700;' : 'color: var(--success); font-weight:700;';

          totalMat += mat;
          totalCom += com;
          totalCie += cie;
          count++;

          tableRows += `
            <tr>
              <td style="font-weight:600;">${s.id}</td>
              <td>${s.name}</td>
              <td>${s.grado}</td>
              <td style="text-align: center;">${mat}</td>
              <td style="text-align: center;">${com}</td>
              <td style="text-align: center;">${cie}</td>
              <td style="text-align: center; ${avgClass}">${average}</td>
            </tr>
          `;
        });

        const avgMat = parseFloat((totalMat / count).toFixed(1));
        const avgCom = parseFloat((totalCom / count).toFixed(1));
        const avgCie = parseFloat((totalCie / count).toFixed(1));

        // SVG Visual Bar representation (pure HTML/CSS bar heights)
        content.innerHTML = `
          <div class="charts-container" style="margin-bottom: 24px;">
            <div class="chart-card" style="grid-column: 1 / -1;">
              <div class="card-header">
                <h3 class="card-title">Promedio General por Asignatura</h3>
              </div>
              <div class="chart-body">
                <div class="chart-axis-lines">
                  <div class="chart-grid-line"><span>20</span></div>
                  <div class="chart-grid-line"><span>15</span></div>
                  <div class="chart-grid-line"><span>10</span></div>
                  <div class="chart-grid-line"><span>05</span></div>
                </div>
                
                <div class="chart-bar-col">
                  <div class="chart-bar-fill" style="height: ${(avgMat/20)*100}%; background-color: var(--primary-dark);">
                    <div class="chart-bar-tooltip">${avgMat}</div>
                  </div>
                  <span class="chart-bar-label">Matemática (${avgMat})</span>
                </div>

                <div class="chart-bar-col">
                  <div class="chart-bar-fill" style="height: ${(avgCom/20)*100}%; background-color: var(--primary-orange);">
                    <div class="chart-bar-tooltip">${avgCom}</div>
                  </div>
                  <span class="chart-bar-label">Comunicación (${avgCom})</span>
                </div>

                <div class="chart-bar-col">
                  <div class="chart-bar-fill" style="height: ${(avgCie/20)*100}%; background-color: var(--accent-orange);">
                    <div class="chart-bar-tooltip">${avgCie}</div>
                  </div>
                  <span class="chart-bar-label">Ciencia y Tec. (${avgCie})</span>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Consolidado General de Calificaciones</h3>
            </div>
            <div class="table-responsive">
              <table class="school-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Apellidos y Nombres</th>
                    <th>Grado</th>
                    <th style="text-align: center;">Matemática</th>
                    <th style="text-align: center;">Comunicación</th>
                    <th style="text-align: center;">Ciencia y Tec.</th>
                    <th style="text-align: center;">Promedio</th>
                  </tr>
                </thead>
                <tbody>
                  ${tableRows}
                </tbody>
              </table>
            </div>
          </div>
        `;
      } 
      else if (tabName === 'attendance') {
        btnAttendance.classList.add('active');

        // General attendance rates
        let presentCount = 0, lateCount = 0, absentCount = 0, totalAtt = 0;
        let rows = '';

        db.students.forEach(s => {
          let p = 0, t = 0, f = 0;
          Object.values(s.attendance).forEach(status => {
            if (status === 'P') p++;
            if (status === 'T') t++;
            if (status === 'F') f++;
            totalAtt++;
          });

          presentCount += p;
          lateCount += t;
          absentCount += f;

          const totalDays = p + t + f;
          const attendancePercent = totalDays > 0 ? Math.round((p / totalDays) * 100) : 100;
          const badCell = attendancePercent < 80 ? 'background-color: var(--danger-bg); color: var(--danger); font-weight:700;' : '';

          rows += `
            <tr>
              <td style="font-weight:600;">${s.id}</td>
              <td>${s.name}</td>
              <td>${s.grado}</td>
              <td style="text-align: center; color: var(--success); font-weight: 600;">${p}</td>
              <td style="text-align: center; color: var(--warning); font-weight: 600;">${t}</td>
              <td style="text-align: center; color: var(--danger); font-weight: 600;">${f}</td>
              <td style="text-align: center; ${badCell}">${attendancePercent}%</td>
            </tr>
          `;
        });

        const ratePresent = totalAtt > 0 ? Math.round((presentCount / totalAtt) * 100) : 100;
        const rateLate = totalAtt > 0 ? Math.round((lateCount / totalAtt) * 100) : 0;
        const rateAbsent = totalAtt > 0 ? Math.round((absentCount / totalAtt) * 100) : 0;

        content.innerHTML = `
          <div class="financial-metrics" style="margin-bottom: 24px;">
            <div class="metric-card">
              <div class="metric-icon-box" style="background-color: var(--success-bg); color: var(--success);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
              </div>
              <div class="metric-details">
                <span class="metric-lbl">Tasa de Asistencia</span>
                <span class="metric-val">${ratePresent}%</span>
              </div>
            </div>
            <div class="metric-card">
              <div class="metric-icon-box" style="background-color: var(--warning-bg); color: var(--warning);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              </div>
              <div class="metric-details">
                <span class="metric-lbl">Tardanzas Totales</span>
                <span class="metric-val">${rateLate}%</span>
              </div>
            </div>
            <div class="metric-card">
              <div class="metric-icon-box" style="background-color: var(--danger-bg); color: var(--danger);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
              </div>
              <div class="metric-details">
                <span class="metric-lbl">Inasistencias Totales</span>
                <span class="metric-val">${rateAbsent}%</span>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Resumen Escolar de Asistencias (Mes en Curso)</h3>
            </div>
            <div class="table-responsive">
              <table class="school-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Apellidos y Nombres</th>
                    <th>Grado</th>
                    <th style="text-align: center;">Asistencias (P)</th>
                    <th style="text-align: center;">Tardanzas (T)</th>
                    <th style="text-align: center;">Faltas (F)</th>
                    <th style="text-align: center;">Porcentaje Asist.</th>
                  </tr>
                </thead>
                <tbody>
                  ${rows}
                </tbody>
              </table>
            </div>
          </div>
        `;
      } 
      else if (tabName === 'filters') {
        btnFilters.classList.add('active');

        content.innerHTML = `
          <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
              <h3 class="card-title">Filtros Dinámicos de Búsqueda</h3>
            </div>
            <div class="form-layout" style="grid-template-columns: 1fr 1fr 1fr; margin-bottom: 0;">
              <div class="form-group">
                <label class="form-label-desc">Buscar Alumno (Nombre)</label>
                <input type="text" id="filter-search" class="control-input" placeholder="Escriba apellido o nombre...">
              </div>
              <div class="form-group">
                <label class="form-label-desc">Nivel de Educación</label>
                <select id="filter-level" class="control-select">
                  <option value="todos">Todos los Niveles</option>
                  <option value="Primaria">Primaria</option>
                  <option value="Inicial">Inicial</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label-desc">Rendimiento Académico</label>
                <select id="filter-performance" class="control-select">
                  <option value="todos">Todos los Rendimientos</option>
                  <option value="aprobado">Alumnos Aprobados (Promedio &gt;= 11)</option>
                  <option value="desaprobado">Alumnos en Alerta (Promedio &lt; 11)</option>
                </select>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Lista de Alumnos Coincidentes</h3>
              <span class="badge badge-primary" id="filter-result-count">9 Alumnos</span>
            </div>
            <div class="table-responsive">
              <table class="school-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Apellidos y Nombres</th>
                    <th>Grado</th>
                    <th>Nivel</th>
                    <th style="text-align: center;">Promedio General</th>
                    <th>Estado Académico</th>
                  </tr>
                </thead>
                <tbody id="filter-table-body">
                  <!-- Dynamically filtered rows -->
                </tbody>
              </table>
            </div>
          </div>
        `;

        const filterSearch = document.getElementById('filter-search');
        const filterLevel = document.getElementById('filter-level');
        const filterPerformance = document.getElementById('filter-performance');
        const filterTableBody = document.getElementById('filter-table-body');
        const countBadge = document.getElementById('filter-result-count');

        function applyFilters() {
          const query = filterSearch.value.trim().toLowerCase();
          const levelVal = filterLevel.value;
          const perfVal = filterPerformance.value;
          const students = db.students;

          let filtered = students.filter(s => {
            // 1. Text filter
            const matchesText = s.name.toLowerCase().includes(query) || s.id.toLowerCase().includes(query);
            
            // 2. Level filter
            const matchesLevel = levelVal === 'todos' || s.nivel === levelVal;
            
            // 3. Performance filter
            const mat = s.grades.matematica || 0;
            const com = s.grades.comunicacion || 0;
            const cie = s.grades.ciencia || 0;
            const avg = (mat + com + cie) / 3;
            
            let matchesPerf = true;
            if (perfVal === 'aprobado') matchesPerf = avg >= 11;
            if (perfVal === 'desaprobado') matchesPerf = avg < 11;

            return matchesText && matchesLevel && matchesPerf;
          });

          // Draw Rows
          let rowsHtml = '';
          filtered.forEach(s => {
            const mat = s.grades.matematica || 0;
            const com = s.grades.comunicacion || 0;
            const cie = s.grades.ciencia || 0;
            const average = parseFloat(((mat + com + cie) / 3).toFixed(1));
            const isPassing = average >= 11;

            rowsHtml += `
              <tr>
                <td style="font-weight:600;">${s.id}</td>
                <td>${s.name}</td>
                <td>${s.grado}</td>
                <td>${s.nivel}</td>
                <td style="text-align: center; font-weight:700;">${average}</td>
                <td>
                  <span class="badge ${isPassing ? 'badge-success' : 'badge-danger'}">
                    ${isPassing ? 'Aprobado' : 'En Alerta'}
                  </span>
                </td>
              </tr>
            `;
          });

          if (filtered.length === 0) {
            rowsHtml = `<tr><td colspan="6" style="text-align:center; color: var(--neutral-medium); padding: 24px;">No se encontraron alumnos con los filtros seleccionados.</td></tr>`;
          }

          filterTableBody.innerHTML = rowsHtml;
          countBadge.textContent = `${filtered.length} Alumno(s)`;
        }

        // Attach event listeners
        filterSearch.addEventListener('input', applyFilters);
        filterLevel.addEventListener('change', applyFilters);
        filterPerformance.addEventListener('change', applyFilters);

        // Run initially
        applyFilters();
      }
    }
  }

  // Expose methods globally for Router config in docente.html
  window.DocenteModule = {
    renderInfoPersonal: renderInfoPersonal,
    renderCursos: renderCursos,
    renderActividades: renderActividades,
    renderAsistencia: renderAsistencia,
    renderIncidencias: renderIncidencias,
    renderReportes: renderReportes
  };

})();
