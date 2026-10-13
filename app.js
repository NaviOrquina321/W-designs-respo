/**
 * TutorLink - An Intelligent Tutor-Student Matching System
 * Application Logic & State Management
 */

// Portal awareness: the admin site (admin.php) and the main site (index.php) use separate
// login sessions. Every API call from the admin site is tagged so the server uses the admin session.
function isAdminPortal() {
  return !!(document.body && document.body.classList.contains('admin-body'));
}
(function installPortalFetch() {
  const nativeFetch = window.fetch.bind(window);
  window.fetch = function (input, init) {
    init = init || {};
    if (isAdminPortal()) {
      const headers = new Headers(init.headers || {});
      headers.set('X-TutorLink-Portal', 'admin');
      init = Object.assign({}, init, { headers });
    }
    return nativeFetch(input, init);
  };
})();

// XSS Sanitization Helper Routine
function esc(str) {
  return String(str ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[c]));
}

// Application State
const state = {
  currentRole: 'guest', // 'guest', 'student', 'tutor', 'admin', 'tutor-profile'
  currentUser: null,

  subjects: [],
  students: [],
  tutors: [],
  matches: [],
  schedules: [],
  payments: [],
  sessions: [],
  notifications: [],

  activeBooking: {
    tutorId: '',
    tutorName: '',
    subject: '',
    date: new Date().toISOString().split('T')[0],
    timeSlot: null,
    hourlyRate: 350,
    serviceFee: 35,
    total: 385,
    paymentMethod: 'GCash'
  },

  directorySubject: 'All',
  activeWorkspaceSession: null,
  activeReportFilter: 'all',
  lastChatId: 0
};

let autoRefreshTimer = null;

// DOM Content Loaded Handler
document.addEventListener('DOMContentLoaded', async () => {
  initNavigation();
  initAuthModalTabs();
  initModals();
  initAIMatching();
  initCalendarBooking();
  initGCashPayment();
  initNotifications();
  initWorkspaceSession();
  initAdminView();
  initTutorScheduleManager();
  initProfileModals();
  initTutorProfileSettings();
  initRescheduleModal();
  initEndSessionModal();

  // Check server session via api/me.php
  try {
    const res = await fetch('api/me.php');
    if (res.ok) {
      const json = await res.json();
      if (json.status === 'success' && json.user) {
        state.currentUser = json.user;
        state.currentRole = json.user.role;
      }
    } else {
      state.currentUser = null;
      state.currentRole = 'guest';
    }
  } catch (e) {
    state.currentUser = null;
    state.currentRole = 'guest';
  }

  await syncWithDatabase();

  if (state.currentUser && state.currentRole && state.currentRole !== 'guest') {
    switchRole(state.currentRole, state.currentUser);
  } else {
    switchRole('guest');
  }

  renderAllViews();

  startAutoRefreshTimer();
});

function startAutoRefreshTimer() {
  if (autoRefreshTimer) clearInterval(autoRefreshTimer);

  autoRefreshTimer = setInterval(async () => {
    if (!state.currentUser || state.currentRole === 'guest') return;

    // Do not re-render if user is actively typing or a modal/drawer is open
    const activeEl = document.activeElement;
    const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
    const hasOpenModal = document.querySelector('.modal-backdrop.active') !== null;
    const hasOpenDrawer = document.querySelector('.drawer.active') !== null || document.querySelector('.drawer-backdrop:not(.hidden)') !== null;

    await syncWithDatabase();

    // Poll workspace chat if workspace is active
    if (state.activeWorkspaceSession) {
      await pollWorkspaceChat();
    }

    if (!isTyping && !hasOpenModal && !hasOpenDrawer) {
      renderAllViews();
    }
  }, 8000);
}

// Database Synchronization Engine
async function syncWithDatabase() {
  try {
    if (state.currentRole === 'guest') {
      const resT = await fetch('api/tutors.php');
      if (resT.ok) {
        const jsonT = await resT.json();
        if (Array.isArray(jsonT.data)) state.tutors = mapTutorsData(jsonT.data);
      }
      const resSub = await fetch('api/subjects.php');
      if (resSub.ok) {
        const jsonSub = await resSub.json();
        if (Array.isArray(jsonSub.data)) state.subjects = jsonSub.data;
      }
      return;
    }

    // Logged in queries
    const resT = await fetch('api/tutors.php');
    if (resT.status === 401 || resT.status === 403) {
      handleAuthError();
      return;
    }
    if (resT.ok) {
      const jsonT = await resT.json();
      if (Array.isArray(jsonT.data)) state.tutors = mapTutorsData(jsonT.data);
    }

    const resSub = await fetch('api/subjects.php');
    if (resSub.ok) {
      const jsonSub = await resSub.json();
      if (Array.isArray(jsonSub.data)) state.subjects = jsonSub.data;
    }

    const resSes = await fetch('api/sessions.php');
    if (resSes.ok) {
      const jsonSes = await resSes.json();
      if (Array.isArray(jsonSes.data)) state.sessions = jsonSes.data;
    }

    const resPay = await fetch('api/payments.php');
    if (resPay.ok) {
      const jsonPay = await resPay.json();
      if (Array.isArray(jsonPay.data)) state.payments = jsonPay.data;
    }

    const resM = await fetch('api/matches.php');
    if (resM.ok) {
      const jsonM = await resM.json();
      if (Array.isArray(jsonM.data)) state.matches = jsonM.data;
    }

    const resSch = await fetch('api/schedules.php');
    if (resSch.ok) {
      const jsonSch = await resSch.json();
      if (Array.isArray(jsonSch.data)) state.schedules = jsonSch.data;
    }

    const resN = await fetch('api/notifications.php');
    if (resN.ok) {
      const jsonN = await resN.json();
      if (Array.isArray(jsonN.data)) state.notifications = mapNotificationsData(jsonN.data);
    }

    if (state.currentRole === 'admin') {
      const resSt = await fetch('api/students.php');
      if (resSt.ok) {
        const jsonSt = await resSt.json();
        if (Array.isArray(jsonSt.data)) state.students = jsonSt.data;
      }
      const resA = await fetch('api/admin.php');
      if (resA.ok) {
        const jsonA = await resA.json();
        if (jsonA.data) {
          const d = jsonA.data;
          const elStu = document.getElementById('admin-stat-total-students');
          const elTut = document.getElementById('admin-stat-total-tutors');
          const elVol = document.getElementById('admin-stat-total-volume');
          const elCom = document.getElementById('admin-stat-platform-commission');
          if (elStu) elStu.textContent = d.total_students;
          if (elTut) elTut.textContent = d.total_tutors;
          if (elVol) elVol.textContent = `₱${d.total_volume}`;
          if (elCom) elCom.textContent = `₱${d.net_commission}`;
        }
      }
    } else if (state.currentRole === 'student') {
      const resSt = await fetch('api/students.php');
      if (resSt.ok) {
        const jsonSt = await resSt.json();
        if (Array.isArray(jsonSt.data)) state.students = jsonSt.data;
      }
    }
  } catch (e) {
    console.error('Database sync error:', e);
  }
}

function mapTutorsData(data) {
  return data.map(t => ({
    id: t.id,
    name: t.name,
    email: t.email || '',
    initials: t.initials,
    rating: parseFloat(t.rating || 0),
    reviewsCount: parseInt(t.reviewsCount || 0),
    hourlyRate: parseInt(t.hourlyRate || 350),
    subjects: Array.isArray(t.subjects) ? t.subjects : [],
    learningStyles: Array.isArray(t.learningStyles) ? t.learningStyles : [],
    bio: t.bio || '',
    availableDays: Array.isArray(t.availableDays) ? t.availableDays : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    availableTimeSlots: t.availableTimeSlots || '09:00 AM - 05:00 PM',
    blockedDates: t.blockedDates || '',
    available: Boolean(t.available),
    deactivated: Boolean(t.deactivated),
    approvalStatus: t.approvalStatus || 'Pending Review',
    diplomaStatus: t.diplomaStatus || 'Pending',
    torStatus: t.torStatus || 'Pending',
    idStatus: t.idStatus || 'Pending',
    availabilitySlots: Array.isArray(t.availabilitySlots) ? t.availabilitySlots : ['09:00 AM', '02:00 PM', '04:00 PM']
  }));
}

function mapNotificationsData(data) {
  const userId = state.currentUser ? state.currentUser.id : 'guest';
  const readStorage = JSON.parse(localStorage.getItem(`tutorlink_read_${userId}`) || '[]');

  return data.map(n => {
    let isRead = Boolean(n.read || n.isRead);
    if (n.targetRole === 'all' || n.target === 'All Users') {
      if (readStorage.includes(n.id)) isRead = true;
    }
    return {
      id: n.id,
      targetRole: n.targetRole,
      target: n.target,
      title: n.title,
      message: n.message,
      time: n.time || 'Just now',
      read: isRead
    };
  });
}

function handleAuthError() {
  state.currentUser = null;
  state.currentRole = 'guest';
  switchRole('guest');
  showToast('Session expired. Please log in again.', 'error');
}

// Navigation & Event Bindings
function initNavigation() {
  const loginBtn = document.getElementById('login-link-btn');
  const signupBtn = document.getElementById('signup-btn');
  const logoutBtn = document.getElementById('logout-btn');
  const navLogo = document.getElementById('nav-logo');
  const userProfileBtn = document.getElementById('user-profile-btn');

  navLogo?.addEventListener('click', () => {
    if (state.currentUser && state.currentUser.role) {
      switchRole(state.currentUser.role);
    } else {
      switchRole('guest');
    }
  });

  loginBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    openModal('modal-auth');
    switchAuthTab('login');
  });

  signupBtn?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  logoutBtn?.addEventListener('click', async () => {
    if (autoRefreshTimer) {
      clearInterval(autoRefreshTimer);
      autoRefreshTimer = null;
    }
    try {
      await fetch('api/logout.php', { method: 'POST' });
    } catch (e) {}
    state.currentUser = null;
    switchRole('guest');
    showToast('Logged out successfully.');
  });

  userProfileBtn?.addEventListener('click', () => {
    if (state.currentRole === 'tutor') {
      populateTutorProfileEditPage();
      switchRole('tutor-profile');
    } else if (state.currentRole === 'student') {
      populateStudentProfileEditModal();
      openModal('modal-edit-student-profile');
    } else {
      showToast('Profile settings are available for logged-in students and tutors.');
    }
  });

  document.getElementById('back-to-tutor-portal-btn')?.addEventListener('click', () => switchRole('tutor'));
  document.getElementById('edit-tutor-schedule-profile-btn')?.addEventListener('click', () => switchRole('tutor-profile'));
  document.getElementById('page-cancel-tutor-profile-btn')?.addEventListener('click', () => switchRole('tutor'));

  // Save Tutor Profile Form Handler
  document.getElementById('tutor-profile-page-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('page-tutor-name-input').value;
    const subjects = document.getElementById('page-tutor-subjects-input').value;
    const style = document.getElementById('page-tutor-style-select').value;
    const rate = document.getElementById('page-tutor-rate-input').value;
    const bio = document.getElementById('page-tutor-bio-input').value;
    const available = document.getElementById('page-tutor-availability-toggle').checked;

    const checkedDays = Array.from(document.querySelectorAll('input[name="avail_day"]:checked')).map(cb => cb.value);
    const windowVal = document.getElementById('tutor-time-window-input')?.value || '09:00 AM - 05:00 PM';
    const blockedVal = document.getElementById('tutor-blocked-dates-input')?.value || '';

    const currentTutor = state.tutors.find(t => t.id === (state.currentUser ? state.currentUser.id : ''));

    try {
      const res = await fetch('api/tutors.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: currentTutor ? currentTutor.id : (state.currentUser ? state.currentUser.id : ''),
          name: name,
          subjects: subjects.split(',').map(s => s.trim()),
          learningStyles: [style],
          hourlyRate: parseInt(rate) || 350,
          bio: bio,
          available: available,
          availableDays: checkedDays,
          availableTimeSlots: windowVal,
          blockedDates: blockedVal
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Failed to update profile.', 'error');
        return;
      }
      await syncWithDatabase();
      renderAllViews();
      switchRole('tutor');
      showToast('Tutor profile updated successfully!');
    } catch (e) {
      showToast('Could not connect to server.', 'error');
    }
  });

  // Hero & CTA buttons
  document.getElementById('hero-find-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('hero-become-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('cta-find-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('cta-become-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('start-ai-match-btn')?.addEventListener('click', () => openModal('modal-ai-matching'));

  // Landing feature card prompts
  document.getElementById('landing-ai-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    showToast('Please sign in to access AI Tutor Matching.');
  });
  document.getElementById('landing-calendar-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    showToast('Please sign in to view calendar scheduling.');
  });
  document.getElementById('landing-notif-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    showToast('Please sign in to access real-time notifications.');
  });
  document.getElementById('landing-payment-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    showToast('Please sign in to manage GCash payments.');
  });
  document.getElementById('landing-session-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    showToast('Please sign in to join virtual session workspaces.');
  });

  document.getElementById('landing-audience-student')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
    const regRoleSelect = document.getElementById('reg-role');
    if (regRoleSelect) {
      regRoleSelect.value = 'student';
      regRoleSelect.dispatchEvent(new Event('change'));
    }
  });
  document.getElementById('landing-audience-tutor')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
    const regRoleSelect = document.getElementById('reg-role');
    if (regRoleSelect) {
      regRoleSelect.value = 'tutor';
      regRoleSelect.dispatchEvent(new Event('change'));
    }
  });
  document.getElementById('landing-audience-admin')?.addEventListener('click', () => {
    // Admins have their own separate site; open it in a new tab so both stay open together
    window.open('admin-login.php', '_blank', 'noopener');
  });

  document.getElementById('tutor-search-input')?.addEventListener('input', (e) => {
    renderTutorDirectory(e.target.value.toLowerCase());
  });

  document.getElementById('reg-role')?.addEventListener('change', (e) => {
    const gradeLabel = document.getElementById('reg-grade-label');
    const subjLabel = document.getElementById('reg-subject-label');
    if (e.target.value === 'tutor') {
      if (gradeLabel) gradeLabel.textContent = 'Grade Level to Teach';
      if (subjLabel) subjLabel.textContent = 'Subject to Teach';
    } else {
      if (gradeLabel) gradeLabel.textContent = 'Grade Level';
      if (subjLabel) subjLabel.textContent = 'Subject Needed';
    }
  });

  document.getElementById('close-receipt-view-modal')?.addEventListener('click', () => closeModal('modal-view-receipt'));
  document.getElementById('done-receipt-view-btn')?.addEventListener('click', () => closeModal('modal-view-receipt'));

  document.getElementById('floating-calendar-btn')?.addEventListener('click', () => {
    openFloatingCalendarModal();
  });
  document.getElementById('close-floating-calendar-modal')?.addEventListener('click', () => closeModal('modal-floating-calendar'));
}

function openFloatingCalendarModal() {
  const titleEl = document.getElementById('floating-calendar-title');
  if (state.currentRole === 'tutor' || state.currentRole === 'tutor-profile') {
    const tutorSessions = state.sessions.filter(s => s.tutorId === (state.currentUser ? state.currentUser.id : '') || s.tutorName === (state.currentUser ? state.currentUser.name : ''));
    if (titleEl) titleEl.textContent = `Tutor Schedule Calendar - ${state.currentUser ? esc(state.currentUser.name) : ''}`;
    renderSessionCalendar('floating-calendar-container', tutorSessions, 'Tutor');
  } else {
    const studentSessions = state.sessions.filter(s => s.studentId === (state.currentUser ? state.currentUser.id : '') || s.studentName === (state.currentUser ? state.currentUser.name : ''));
    if (titleEl) titleEl.textContent = `Student Schedule Calendar - ${state.currentUser ? esc(state.currentUser.name) : ''}`;
    renderSessionCalendar('floating-calendar-container', studentSessions, 'Student');
  }
  openModal('modal-floating-calendar');
}

// Role Switcher Logic
function switchRole(role, customUser = null) {
  state.currentRole = role;

  // Admin site has no guest view: an expired or missing admin session goes back to the admin login
  if (isAdminPortal() && role === 'guest') {
    window.location.href = 'admin-login.php';
    return;
  }

  document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active'));

  const publicNavLinks = document.getElementById('public-nav-links');
  const loginBtn = document.getElementById('login-link-btn');
  const signupBtn = document.getElementById('signup-btn');
  const logoutBtn = document.getElementById('logout-btn');
  const notifBell = document.getElementById('notif-bell-btn');
  const userProfileBtn = document.getElementById('user-profile-btn');
  const floatingCalBtn = document.getElementById('floating-calendar-btn');

  if (role === 'guest') {
    state.currentUser = null;
    document.getElementById('view-landing').classList.add('active');
    if (publicNavLinks) publicNavLinks.style.display = 'flex';
    if (loginBtn) loginBtn.classList.remove('hidden');
    if (signupBtn) signupBtn.classList.remove('hidden');
    if (logoutBtn) logoutBtn.classList.add('hidden');
    if (notifBell) notifBell.classList.add('hidden');
    if (userProfileBtn) userProfileBtn.classList.add('hidden');
    if (floatingCalBtn) floatingCalBtn.classList.add('hidden');
  } else {
    if (customUser) state.currentUser = customUser;
    if (publicNavLinks) publicNavLinks.style.display = 'none';
    if (loginBtn) loginBtn.classList.add('hidden');
    if (signupBtn) signupBtn.classList.add('hidden');
    if (logoutBtn) logoutBtn.classList.remove('hidden');
    if (notifBell) notifBell.classList.remove('hidden');
    if (userProfileBtn) userProfileBtn.classList.remove('hidden');

    if (role === 'student') {
      document.getElementById('view-student').classList.add('active');
      if (floatingCalBtn) floatingCalBtn.classList.remove('hidden');
      const heading = document.getElementById('student-welcome-heading');
      if (heading && state.currentUser) {
        heading.textContent = `Welcome back, ${state.currentUser.name}!`;
      }
    } else if (role === 'tutor') {
      document.getElementById('view-tutor').classList.add('active');
      if (floatingCalBtn) floatingCalBtn.classList.remove('hidden');
      const heading = document.getElementById('tutor-welcome-heading');
      if (heading && state.currentUser) {
        heading.textContent = `Tutor Portal - ${state.currentUser.name}`;
      }
    } else if (role === 'tutor-profile') {
      document.getElementById('view-tutor-profile').classList.add('active');
      if (floatingCalBtn) floatingCalBtn.classList.add('hidden');
    } else if (role === 'admin') {
      // Admins never use the main site: send them to their own portal (already there = stay)
      if (!isAdminPortal()) {
        window.location.href = 'admin-login.php';
        return;
      }
    }
  }

  renderAllViews();
}

// Authentication Modal Tabs Engine
function initAuthModalTabs() {
  const loginTab = document.getElementById('tab-btn-login');
  const regTab = document.getElementById('tab-btn-register');
  const loginForm = document.getElementById('auth-form');
  const regForm = document.getElementById('register-form');

  loginTab?.addEventListener('click', () => switchAuthTab('login'));
  regTab?.addEventListener('click', () => switchAuthTab('register'));

  loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('auth-email').value;
    const password = document.getElementById('auth-password').value;

    try {
      const res = await fetch('api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Invalid email or password.', 'error');
        return;
      }
      state.currentUser = data.user;
      closeModal('modal-auth');
      await syncWithDatabase();

      if (data.user.role === 'admin') {
        window.location.href = 'admin-login.php';
      } else {
        switchRole(data.user.role, data.user);
        showToast(`Logged in successfully as ${data.user.name}`);
      }
    } catch (e) {
      showToast('Authentication failed. Could not connect to server.', 'error');
    }
  });

  regForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const role = document.getElementById('reg-role').value;
    const fullname = document.getElementById('reg-fullname').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const grade = document.getElementById('reg-grade').value;
    const subject = document.getElementById('reg-subject').value;

    try {
      const res = await fetch('api/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          role,
          name: fullname,
          email,
          password,
          grade,
          subject
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Registration failed.', 'error');
        return;
      }
      state.currentUser = data.user;
      closeModal('modal-auth');
      await syncWithDatabase();

      switchRole(data.user.role, data.user);
      showToast(data.message || 'Registration successful!');
    } catch (e) {
      showToast('Registration failed. Could not connect to server.', 'error');
    }
  });
}

function switchAuthTab(type) {
  const loginTab = document.getElementById('tab-btn-login');
  const regTab = document.getElementById('tab-btn-register');
  const loginForm = document.getElementById('auth-form');
  const regForm = document.getElementById('register-form');

  if (type === 'login') {
    loginTab?.classList.add('active');
    regTab?.classList.remove('active');
    loginForm?.classList.remove('hidden');
    regForm?.classList.add('hidden');
  } else {
    regTab?.classList.add('active');
    loginTab?.classList.remove('active');
    regForm?.classList.remove('hidden');
    loginForm?.classList.add('hidden');
  }
}

// Modal Controllers
function initModals() {
  document.querySelectorAll('.close-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const backdrop = e.target.closest('.modal-backdrop');
      if (backdrop) backdrop.classList.remove('active');
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) backdrop.classList.remove('active');
    });
  });
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('active');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
}

// Profile Modal Handlers
function initProfileModals() {
  document.getElementById('close-edit-student-modal')?.addEventListener('click', () => closeModal('modal-edit-student-profile'));
  document.getElementById('close-student-profile-modal')?.addEventListener('click', () => closeModal('modal-student-profile'));
  document.getElementById('close-tutor-profile-modal')?.addEventListener('click', () => closeModal('modal-tutor-profile'));

  document.getElementById('edit-student-profile-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('edit-student-name').value;
    const grade = document.getElementById('edit-student-grade').value;
    const subjects = document.getElementById('edit-student-subjects').value;
    const bio = document.getElementById('edit-student-bio').value;

    try {
      const res = await fetch('api/students.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: state.currentUser ? state.currentUser.id : '',
          name,
          grade,
          subjects_needed: subjects,
          bio
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Failed to update profile.', 'error');
        return;
      }
      await syncWithDatabase();
      renderAllViews();
      closeModal('modal-edit-student-profile');
      showToast('Student profile updated successfully.');
    } catch (e) {
      showToast('Could not connect to server.', 'error');
    }
  });
}

function populateStudentProfileEditModal() {
  if (!state.currentUser) return;
  const currentStu = state.students.find(s => s.id === state.currentUser.id) || {
    name: state.currentUser.name,
    grade: 'Senior High',
    subjectsNeeded: ['Calculus', 'Physics'],
    bio: 'Student eager to connect with expert tutors on TutorLink.'
  };

  document.getElementById('edit-student-name').value = currentStu.name || '';
  document.getElementById('edit-student-grade').value = currentStu.grade || 'Senior High';
  document.getElementById('edit-student-subjects').value = Array.isArray(currentStu.subjectsNeeded) ? currentStu.subjectsNeeded.join(', ') : (currentStu.subjectsNeeded || '');
  document.getElementById('edit-student-bio').value = currentStu.bio || '';
}

function initTutorProfileSettings() {
  // Configured in initNavigation
}

function populateTutorProfileEditPage() {
  if (!state.currentUser) return;
  const currentTut = state.tutors.find(t => t.id === state.currentUser.id) || state.tutors[0];
  if (!currentTut) return;

  const nameEl = document.getElementById('page-tutor-name-input');
  const subjEl = document.getElementById('page-tutor-subjects-input');
  const styleEl = document.getElementById('page-tutor-style-select');
  const rateEl = document.getElementById('page-tutor-rate-input');
  const bioEl = document.getElementById('page-tutor-bio-input');
  const availToggle = document.getElementById('page-tutor-availability-toggle');

  if (nameEl) nameEl.value = currentTut.name || '';
  if (subjEl) subjEl.value = Array.isArray(currentTut.subjects) ? currentTut.subjects.join(', ') : '';
  if (styleEl && currentTut.learningStyles && currentTut.learningStyles.length > 0) styleEl.value = currentTut.learningStyles[0];
  if (rateEl) rateEl.value = currentTut.hourlyRate || 350;
  if (bioEl) bioEl.value = currentTut.bio || '';
  if (availToggle) availToggle.checked = currentTut.available;

  const availDays = currentTut.availableDays || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
  document.querySelectorAll('input[name="avail_day"]').forEach(cb => {
    cb.checked = availDays.includes(cb.value);
  });

  const winEl = document.getElementById('tutor-time-window-input');
  const blockEl = document.getElementById('tutor-blocked-dates-input');
  if (winEl) winEl.value = currentTut.availableTimeSlots || '09:00 AM - 05:00 PM';
  if (blockEl) blockEl.value = currentTut.blockedDates || '';
}

window.viewStudentProfile = function(studentIdOrName) {
  const student = state.students.find(s => s.id === studentIdOrName || s.name === studentIdOrName) || {
    id: 'STU-101',
    name: studentIdOrName,
    grade: 'Senior High School',
    validated: true,
    bio: 'Student enrolled in STEM curriculum.',
    subjectsNeeded: ['Calculus', 'Physics']
  };

  const titleEl = document.getElementById('student-profile-title');
  const bodyEl = document.getElementById('student-profile-body');

  if (titleEl) titleEl.textContent = `Student Profile - ${esc(student.name)}`;
  if (bodyEl) {
    bodyEl.innerHTML = `
      <div class="detail-card">
        <div class="detail-row"><span class="detail-label">Student ID:</span> <code>${esc(student.id)}</code></div>
        <div class="detail-row"><span class="detail-label">Full Name:</span> <strong>${esc(student.name)}</strong></div>
        <div class="detail-row"><span class="detail-label">Grade Level:</span> <span>${esc(student.grade)}</span></div>
        <div class="detail-row"><span class="detail-label">Account Status:</span> <span class="badge ${student.validated ? 'badge-success' : 'badge-info'}">${student.validated ? 'Validated Student' : 'Pending Validation'}</span></div>
        <div class="detail-row"><span class="detail-label">Subjects Needed:</span> <span>${esc(Array.isArray(student.subjectsNeeded) ? student.subjectsNeeded.join(', ') : student.subjectsNeeded)}</span></div>
        <hr class="divider margin-top margin-bottom">
        <h4>Bio & Academic Goals</h4>
        <p class="sub-text">${esc(student.bio)}</p>
      </div>
    `;
  }
  openModal('modal-student-profile');
};

window.viewTutorProfile = function(tutorIdOrName) {
  const tutor = state.tutors.find(t => t.id === tutorIdOrName || t.name === tutorIdOrName) || state.tutors[0];
  if (!tutor) return;

  const titleEl = document.getElementById('tutor-profile-title');
  const bodyEl = document.getElementById('tutor-profile-body');

  if (titleEl) titleEl.textContent = `Tutor Profile - ${esc(tutor.name)}`;
  if (bodyEl) {
    bodyEl.innerHTML = `
      <div class="detail-card">
        <div class="detail-row"><span class="detail-label">Tutor ID:</span> <code>${esc(tutor.id)}</code></div>
        <div class="detail-row"><span class="detail-label">Full Name:</span> <strong>${esc(tutor.name)}</strong></div>
        <div class="detail-row"><span class="detail-label">Hourly Rate:</span> <strong class="text-emerald">₱${esc(tutor.hourlyRate)} / hr</strong></div>
        <div class="detail-row"><span class="detail-label">Rating:</span> <span>${esc(tutor.rating.toFixed(1))} / 5.0 ★ (${esc(tutor.reviewsCount)} reviews)</span></div>
        <div class="detail-row"><span class="detail-label">Subjects Taught:</span> <span>${esc(tutor.subjects.join(', '))}</span></div>
        <div class="detail-row"><span class="detail-label">Approval Status:</span> <span class="badge ${tutor.approvalStatus === 'Approved' ? 'badge-success' : 'badge-info'}">${esc(tutor.approvalStatus)}</span></div>
        <hr class="divider margin-top margin-bottom">
        <h4>Professional Biography</h4>
        <p class="sub-text">${esc(tutor.bio)}</p>
        <div class="margin-top">
          <button class="btn btn-primary full-width" onclick="closeModal('modal-tutor-profile'); openCalendarBooking('${esc(tutor.id)}')">Book Session with ${esc(tutor.name)}</button>
        </div>
      </div>
    `;
  }
  openModal('modal-tutor-profile');
};

// System Toast Routine
function showToast(msg, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = msg;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// Master Render All Views Engine
function renderAllViews() {
  if (state.currentRole === 'student') {
    renderStudentUpcoming();
    renderStudentHistory();
    renderTutorDirectory();
  } else if (state.currentRole === 'tutor' || state.currentRole === 'tutor-profile') {
    renderTutorUpcoming();
    renderTutorRequests();
    renderTutorEarnings();
    renderTutorScheduleSummary();
  }
  updateNotificationBadge();
}

function renderTutorDirectory(searchTerm) {
  const grid = document.getElementById('tutors-directory-grid');
  if (!grid) return;

  // Keep the current search text when the page re-renders by itself
  if (typeof searchTerm !== 'string') {
    searchTerm = (document.getElementById('tutor-search-input')?.value || '');
  }
  const term = searchTerm.toLowerCase();

  const approvedTutors = state.tutors.filter(t => t.approvalStatus === 'Approved' && !t.deactivated);

  // Subject navigation: every subject taught by an approved tutor, with a tutor count
  const subjectCounts = new Map();
  approvedTutors.forEach(t => t.subjects.forEach(s => {
    const key = s.trim();
    if (key) subjectCounts.set(key, (subjectCounts.get(key) || 0) + 1);
  }));
  const subjectList = Array.from(subjectCounts.keys()).sort((a, b) => a.localeCompare(b));

  // If the chosen subject no longer exists (tutor deactivated), go back to All
  if (state.directorySubject !== 'All' && !subjectCounts.has(state.directorySubject)) {
    state.directorySubject = 'All';
  }
  const activeSubject = state.directorySubject;

  const bar = document.getElementById('subject-filter-bar');
  if (bar) {
    const chip = (label, count, value) => `
      <button type="button" class="subject-chip${value === activeSubject ? ' active' : ''}" data-subject="${esc(value)}" aria-pressed="${value === activeSubject}">
        ${esc(label)} <span class="subject-chip-count">${esc(count)}</span>
      </button>`;
    bar.innerHTML = chip('All subjects', approvedTutors.length, 'All') +
      subjectList.map(s => chip(s, subjectCounts.get(s), s)).join('');
    bar.querySelectorAll('.subject-chip').forEach(btn => {
      btn.addEventListener('click', () => {
        state.directorySubject = btn.dataset.subject;
        renderTutorDirectory();
      });
    });
  }

  const filtered = approvedTutors.filter(t => {
    const subjectOk = activeSubject === 'All' || t.subjects.some(s => s.trim() === activeSubject);
    const nameMatch = t.name.toLowerCase().includes(term);
    const subMatch = t.subjects.some(s => s.toLowerCase().includes(term));
    return subjectOk && (nameMatch || subMatch);
  });

  const summary = document.getElementById('subject-filter-summary');
  if (summary) {
    summary.textContent = activeSubject === 'All'
      ? `${filtered.length} tutor${filtered.length === 1 ? '' : 's'} available`
      : `${filtered.length} ${activeSubject} tutor${filtered.length === 1 ? '' : 's'} available`;
  }

  if (filtered.length === 0) {
    grid.innerHTML = `<p class="sub-text center-text" style="grid-column: 1/-1; padding: 30px;">No available approved tutors match your search criteria.</p>`;
    return;
  }

  grid.innerHTML = filtered.map(t => {
    // The subject being browsed is shown first and highlighted; the others are outlined
    const subs = t.subjects.map(s => s.trim()).filter(Boolean)
      .sort((a, b) => (b === activeSubject) - (a === activeSubject));
    const tags = subs.map(s => `<span class="subject-tag${s === activeSubject ? ' is-selected' : ''}">${esc(s)}</span>`).join('');
    return `
    <div class="tutor-card">
      <div class="tutor-card-head">
        <div class="tutor-avatar" onclick="viewTutorProfile('${esc(t.id)}')">${esc(t.initials)}</div>
        <div>
          <h4 onclick="viewTutorProfile('${esc(t.id)}')" style="cursor: pointer; text-decoration: underline;">${esc(t.name)}</h4>
          <span class="tutor-credential">&#10003; Verified tutor &middot; ${esc(t.learningStyles[0] || 'All learning styles')}</span>
        </div>
      </div>
      <div class="subject-tags">${tags}</div>
      <p class="tutor-bio-snippet">${esc(t.bio)}</p>
      <div class="tutor-card-foot">
        <div>
          <strong class="price-text">₱${esc(t.hourlyRate)}</strong> <span class="sub-text">/ hr</span>
          <div class="rating-text">${t.reviewsCount > 0 ? '★ ' + esc(t.rating.toFixed(1)) + ' (' + esc(t.reviewsCount) + ')' : 'New tutor'}</div>
        </div>
        <button class="btn btn-primary btn-small" onclick="openCalendarBooking('${esc(t.id)}')">Book Session</button>
      </div>
    </div>`;
  }).join('');
}

function renderStudentUpcoming() {
  const container = document.getElementById('student-upcoming-sessions-list');
  if (!container) return;

  const currentUser = state.currentUser;
  if (!currentUser) {
    container.innerHTML = `<p class="sub-text">Please log in to view upcoming sessions.</p>`;
    return;
  }

  const upcoming = state.sessions.filter(s => (s.studentId === currentUser.id || s.studentName === currentUser.name) && s.status !== 'Completed' && s.status !== 'Cancelled');

  const upcomingStat = document.getElementById('student-stat-upcoming');
  if (upcomingStat) upcomingStat.textContent = upcoming.length;

  if (upcoming.length === 0) {
    container.innerHTML = `<div class="detail-card sub-text center-text" style="padding: 24px;">No active upcoming sessions booked. Use <strong>Start AI Tutor Matching</strong> above to find a tutor!</div>`;
    return;
  }

  container.innerHTML = upcoming.map(s => `
    <div class="session-card">
      <div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <strong>${esc(s.subject)}</strong>
          <span class="badge ${s.status === 'In Progress' ? 'badge-success' : 'badge-info'}">${esc(s.status === 'In Progress' ? 'Session Live' : s.status)}</span>
        </div>
        <div class="sub-text margin-top-xs">Tutor: <strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(s.tutorId)}')">${esc(s.tutorName)}</strong></div>
        <div class="sub-text">Schedule: ${esc(s.date || s.sessionDate)} (${esc(s.timeSlot)})</div>
        <div class="sub-text">Ref: <code>${esc(s.gcashRef)}</code></div>
      </div>
      <div>
        ${s.status === 'In Progress' ? `
          <button class="btn btn-emerald btn-small" onclick="joinLiveWorkspace('${esc(s.id)}')">Join Live Session</button>
        ` : `
          <button class="btn btn-secondary btn-small" onclick="openRescheduleModal('${esc(s.id)}')">Reschedule</button>
        `}
      </div>
    </div>
  `).join('');
}

function renderStudentHistory() {
  const tbody = document.getElementById('student-history-table-body');
  if (!tbody) return;

  const currentUser = state.currentUser;
  if (!currentUser) return;

  const history = state.sessions.filter(s => (s.studentId === currentUser.id || s.studentName === currentUser.name) && (s.status === 'Completed' || s.status === 'Confirmed' || s.status === 'Cancelled'));

  const completed = history.filter(s => s.status === 'Completed');
  const totalSpent = history.filter(s => s.status !== 'Cancelled').reduce((acc, s) => acc + (s.totalPaid || 0), 0);

  const statComp = document.getElementById('student-stat-completed');
  const statSpent = document.getElementById('student-stat-spent');
  if (statComp) statComp.textContent = completed.length;
  if (statSpent) statSpent.textContent = `₱${totalSpent.toLocaleString()}`;

  if (history.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No session history or receipt records found.</td></tr>`;
    return;
  }

  tbody.innerHTML = history.map(s => `
    <tr>
      <td>${esc(s.date || s.sessionDate)} (${esc(s.timeSlot)})</td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(s.tutorId)}')">${esc(s.tutorName)}</strong></td>
      <td>${esc(s.subject)}</td>
      <td>₱${esc(s.totalPaid)}</td>
      <td><code>${esc(s.gcashRef)}</code></td>
      <td><span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${esc(s.status)}</span></td>
      <td>
        <button class="btn btn-secondary btn-small" onclick="viewSessionReceipt('${esc(s.id)}')">View Receipt</button>
        ${(s.status === 'Completed' && (!s.rating || s.rating === 0)) ? `
          <button class="btn btn-primary btn-small" onclick="openRatingModal('${esc(s.id)}', '${esc(s.tutorName)}')">Rate Session</button>
        ` : ''}
      </td>
    </tr>
  `).join('');
}

function renderSessionCalendar(containerId, sessionsList, userType) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const daysInMonth = 31;
  let cellsHTML = '';

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `2026-03-${d < 10 ? '0' + d : d}`;
    const matchedSessions = sessionsList.filter(s => (s.date || s.sessionDate) === dateStr && s.status !== 'Cancelled');

    let badge = '';
    if (matchedSessions.length > 0) {
      badge = `<div style="font-size: 0.72rem; margin-top: 4px; padding: 2px 4px; background: var(--ink); color: var(--paper); border-radius: 2px;">${matchedSessions.length} ${userType === 'Tutor' ? 'Session' : 'Booked'}</div>`;
    }

    cellsHTML += `
      <div class="calendar-day-cell ${matchedSessions.length > 0 ? 'has-session' : ''}" onclick="selectCalendarDateCell('${dateStr}', '${containerId}')">
        <div style="font-weight: 600; font-size: 0.88rem;">Mar ${d}</div>
        ${badge}
      </div>
    `;
  }

  container.innerHTML = `
    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
      ${cellsHTML}
    </div>
    <div id="${containerId}-details" class="margin-top detail-card sub-text center-text">
      Select a date cell above to view scheduled tutoring session details.
    </div>
  `;
}

window.selectCalendarDateCell = function(dateStr, containerId) {
  const detailsEl = document.getElementById(`${containerId}-details`);
  if (!detailsEl) return;

  const isTutor = containerId.includes('tutor') || (state.currentRole === 'tutor' || state.currentRole === 'tutor-profile');
  const matched = state.sessions.filter(s => (s.date || s.sessionDate) === dateStr && s.status !== 'Cancelled');

  if (matched.length === 0) {
    detailsEl.innerHTML = `<strong>No sessions scheduled for ${dateStr}.</strong>`;
    return;
  }

  detailsEl.innerHTML = `
    <h4 style="margin: 0 0 8px 0;">Sessions on ${dateStr}:</h4>
    ${matched.map(s => `
      <div style="padding: 8px; background: var(--paper); border: 1px solid var(--line); margin-bottom: 6px; border-radius: 4px; text-align: left;">
        <div><strong>${esc(s.subject)}</strong> (${esc(s.timeSlot)})</div>
        <div class="sub-text">${isTutor ? 'Student: ' + esc(s.studentName) : 'Tutor: ' + esc(s.tutorName)} - Ref: <code>${esc(s.gcashRef)}</code></div>
        <div><span class="badge ${s.status === 'Completed' ? 'badge-success' : 'badge-info'}">${esc(s.status)}</span></div>
      </div>
    `).join('')}
  `;
};

function renderTutorUpcoming() {
  const container = document.getElementById('tutor-upcoming-sessions-list');
  if (!container) return;

  const currentUser = state.currentUser;
  if (!currentUser) return;

  const upcoming = state.sessions.filter(s => (s.tutorId === currentUser.id || s.tutorName === currentUser.name) && s.status !== 'Cancelled');

  if (upcoming.length === 0) {
    container.innerHTML = `<div class="detail-card sub-text center-text" style="padding: 24px;">No active teaching sessions assigned yet.</div>`;
    return;
  }

  container.innerHTML = upcoming.map(s => `
    <div class="session-card">
      <div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <strong>${esc(s.subject)}</strong>
          <span class="badge ${s.status === 'Completed' ? 'badge-success' : s.status === 'In Progress' ? 'badge-success' : 'badge-info'}">${esc(s.status)}</span>
        </div>
        <div class="sub-text margin-top-xs">Student: <strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(s.studentId)}')">${esc(s.studentName)}</strong></div>
        <div class="sub-text">Schedule: ${esc(s.date || s.sessionDate)} (${esc(s.timeSlot)})</div>
        <div class="sub-text">Ref: <code>${esc(s.gcashRef)}</code></div>
      </div>
      <div>
        ${s.status === 'Confirmed' ? `
          <button class="btn btn-primary btn-small" onclick="startTutorSession('${esc(s.id)}')">Start Live Session</button>
          <button class="btn btn-secondary btn-small" onclick="openRescheduleModal('${esc(s.id)}')">Reschedule</button>
        ` : s.status === 'In Progress' ? `
          <button class="btn btn-emerald btn-small" onclick="joinLiveWorkspace('${esc(s.id)}')">Join Live Session</button>
          <button class="btn btn-primary btn-small" onclick="openEndSessionModal('${esc(s.id)}')">Complete Session</button>
        ` : `<span class="sub-text">Completed</span>`}
      </div>
    </div>
  `).join('');
}

function renderTutorRequests() {
  const tbody = document.getElementById('tutor-requests-table-body');
  if (!tbody) return;

  const currentUser = state.currentUser;
  if (!currentUser) return;

  const requests = state.matches.filter(m => (m.tutorId === currentUser.id || m.tutorName === currentUser.name));

  const statStudents = document.getElementById('tutor-stat-students');
  if (statStudents) statStudents.textContent = requests.filter(m => m.status === 'Approved').length;

  if (requests.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No incoming student match requests.</td></tr>`;
    return;
  }

  tbody.innerHTML = requests.map(m => `
    <tr>
      <td><code>${esc(m.id)}</code></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(m.studentId)}')">${esc(m.studentName)}</strong></td>
      <td>${esc(m.subject)}</td>
      <td><span class="badge badge-match">${esc(m.score)}% Match</span></td>
      <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${esc(m.status)}</span></td>
      <td>
        ${m.status === 'Pending Review' ? `
          <button class="btn btn-primary btn-small" onclick="acceptTutorMatch('${esc(m.id)}')">Accept Match</button>
          <button class="btn btn-secondary btn-small" onclick="declineTutorMatch('${esc(m.id)}')">Decline</button>
        ` : `<span class="sub-text">Processed</span>`}
      </td>
    </tr>
  `).join('');
}

window.acceptTutorMatch = async function(matchId) {
  try {
    const res = await fetch('api/matches.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: matchId, status: 'Approved' })
    });
    const data = await res.json();
    if (!res.ok || data.status !== 'success') {
      showToast(data.message || 'Failed to accept match.', 'error');
      return;
    }
    await syncWithDatabase();
    renderAllViews();
    showToast('Match request accepted!');
  } catch (e) {
    showToast('Could not connect to server.', 'error');
  }
};

window.declineTutorMatch = async function(matchId) {
  try {
    const res = await fetch('api/matches.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: matchId, status: 'Cancelled' })
    });
    const data = await res.json();
    if (!res.ok || data.status !== 'success') {
      showToast(data.message || 'Failed to decline match.', 'error');
      return;
    }
    await syncWithDatabase();
    renderAllViews();
    showToast('Match request declined.');
  } catch (e) {
    showToast('Could not connect to server.', 'error');
  }
};

window.startTutorSession = async function(sessionId) {
  try {
    const res = await fetch('api/sessions.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: sessionId, status: 'In Progress' })
    });
    const data = await res.json();
    if (!res.ok || data.status !== 'success') {
      showToast(data.message || 'Failed to start session.', 'error');
      return;
    }
    await syncWithDatabase();
    renderAllViews();
    joinLiveWorkspace(sessionId);
    showToast('Live tutoring session started!');
  } catch (e) {
    showToast('Could not connect to server.', 'error');
  }
};

function renderTutorEarnings() {
  const tbody = document.getElementById('tutor-earnings-table-body');
  const currentUser = state.currentUser;
  if (!currentUser) return;

  const tutorSessions = state.sessions.filter(s => (s.tutorId === currentUser.id || s.tutorName === currentUser.name) && s.status !== 'Cancelled');
  const completedSessions = tutorSessions.filter(s => s.status === 'Completed');

  // Hourly rate fee calculations: Tutor gets 100% of hourly rate
  const totalGross = tutorSessions.reduce((acc, s) => acc + (s.totalPaid || 0), 0);
  const totalNet = tutorSessions.reduce((acc, s) => acc + (s.hourlyRate || 0), 0);
  const pendingPayout = tutorSessions.filter(s => s.payoutStatus !== 'Paid Out').reduce((acc, s) => acc + (s.hourlyRate || 0), 0);

  const elEarnings = document.getElementById('tutor-stat-earnings');
  const elGross = document.getElementById('tutor-gross-earnings');
  const elNet = document.getElementById('tutor-net-earnings');
  const elPending = document.getElementById('tutor-payout-pending');
  const elStatSes = document.getElementById('tutor-stat-sessions');

  if (elEarnings) elEarnings.textContent = `₱${totalNet.toLocaleString()}`;
  if (elGross) elGross.textContent = `₱${totalGross.toLocaleString()}`;
  if (elNet) elNet.textContent = `₱${totalNet.toLocaleString()}`;
  if (elPending) elPending.textContent = `₱${pendingPayout.toLocaleString()}`;
  if (elStatSes) elStatSes.textContent = completedSessions.length;

  // Tutor Rating and Feedback Summary
  const ratedSessions = completedSessions.filter(s => s.rating && s.rating > 0);
  const avgRating = ratedSessions.length > 0 ? (ratedSessions.reduce((acc, s) => acc + s.rating, 0) / ratedSessions.length).toFixed(1) : '0.0';

  const elOvrRating = document.getElementById('tutor-overall-rating');
  const elTotReviews = document.getElementById('tutor-total-reviews');
  const elStatRating = document.getElementById('tutor-stat-rating');
  const elReviewsList = document.getElementById('tutor-reviews-list');

  if (elOvrRating) elOvrRating.textContent = `${avgRating} / 5.0 ★`;
  if (elTotReviews) elTotReviews.textContent = `${ratedSessions.length} Verified Reviews`;
  if (elStatRating) elStatRating.textContent = ratedSessions.length > 0 ? `${avgRating} ★` : 'New';

  if (elReviewsList) {
    if (ratedSessions.length === 0) {
      elReviewsList.innerHTML = `<p class="sub-text center-text" style="padding: 16px;">No student feedback reviews submitted yet.</p>`;
    } else {
      elReviewsList.innerHTML = ratedSessions.map(s => `
        <div class="session-card">
          <div>
            <strong>${esc(s.studentName)}</strong> <span class="sub-text">(${esc(s.subject)})</span>
            <div style="color: var(--ink); font-weight: 700; margin-top: 2px;">★ ${esc(s.rating.toFixed(1))}</div>
            <p class="sub-text" style="margin-top: 4px;">${esc(s.feedback || 'No written comment left.')}</p>
          </div>
        </div>
      `).join('');
    }
  }

  if (tbody) {
    if (tutorSessions.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No earnings or payout records available.</td></tr>`;
      return;
    }

    tbody.innerHTML = tutorSessions.map(s => `
      <tr>
        <td><code>${esc(s.id)}</code></td>
        <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${esc(s.studentId)}')">${esc(s.studentName)}</strong></td>
        <td>${esc(s.subject)}</td>
        <td>${esc(s.date || s.sessionDate)}</td>
        <td>₱${esc(s.totalPaid)}</td>
        <td><strong class="text-emerald">₱${esc(s.hourlyRate)}</strong></td>
        <td><span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-pending'}">${esc(s.payoutStatus || 'Unpaid')}</span></td>
      </tr>
    `).join('');
  }
}

function renderTutorScheduleSummary() {
  const currentUser = state.currentUser;
  if (!currentUser) return;
  const currentTut = state.tutors.find(t => t.id === currentUser.id);
  if (!currentTut) return;

  const daysEl = document.getElementById('tutor-summary-days');
  const winEl = document.getElementById('tutor-summary-window');
  const blockEl = document.getElementById('tutor-summary-blocked');

  if (daysEl) daysEl.textContent = Array.isArray(currentTut.availableDays) ? currentTut.availableDays.join(', ') : 'Mon, Tue, Wed, Thu, Fri';
  if (winEl) winEl.textContent = currentTut.availableTimeSlots || '09:00 AM - 05:00 PM';
  if (blockEl) blockEl.textContent = currentTut.blockedDates || 'None';
}

function initTutorScheduleManager() {
  document.getElementById('tutor-new-schedule-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const dateVal = document.getElementById('tutor-slot-date').value;
    const timeVal = document.getElementById('tutor-slot-time').value;
    const subjVal = document.getElementById('tutor-slot-subject').value;

    if (!dateVal || !timeVal || !subjVal) {
      showToast('Please fill in date, time slot, and subject.', 'error');
      return;
    }

    try {
      const res = await fetch('api/schedules.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tutor_id: state.currentUser ? state.currentUser.id : '',
          date_slot: `${dateVal} ${timeVal}`,
          subject: subjVal
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Failed to create schedule slot.', 'error');
        return;
      }
      await syncWithDatabase();
      renderAllViews();
      showToast('New bookable schedule slot created!');
      document.getElementById('tutor-new-schedule-form').reset();
    } catch (e) {
      showToast('Could not connect to server.', 'error');
    }
  });

  document.getElementById('tutor-cancel-slot-btn')?.addEventListener('click', () => {
    document.getElementById('tutor-new-schedule-form')?.reset();
  });
}

// AI Matching Wizard Engine
function initAIMatching() {
  const cancelBtn = document.getElementById('cancel-ai-btn');
  const closeBtn = document.getElementById('close-ai-modal');
  const form = document.getElementById('ai-matching-form');

  cancelBtn?.addEventListener('click', () => closeModal('modal-ai-matching'));
  closeBtn?.addEventListener('click', () => closeModal('modal-ai-matching'));

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const subject = document.getElementById('match-subject').value;
    const style = document.getElementById('match-style').value;
    const time = document.getElementById('match-time').value;

    const resultsBox = document.getElementById('ai-match-results');
    const cardsList = document.getElementById('ai-match-cards-list');
    const runBtn = document.getElementById('run-match-btn');
    const runBtnLabel = runBtn ? runBtn.textContent : '';

    // Loading state
    if (runBtn) { runBtn.disabled = true; runBtn.textContent = 'Finding your best tutors...'; }
    cardsList.innerHTML = `<p class="sub-text center-text" style="padding: 20px;">Matching you with tutors...</p>`;
    resultsBox.classList.remove('hidden');

    try {
      // The server ranks the tutors (AI model, or rule-based fallback)
      const res = await fetch('api/match.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subject, style, time })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        cardsList.innerHTML = '';
        resultsBox.classList.add('hidden');
        showToast(data.message || 'Could not find matches. Please try again.', 'error');
        return;
      }

      const ranked = (data.data || [])
        .map(r => ({ r, tutor: state.tutors.find(t => t.id === r.tutorId) }))
        .filter(x => x.tutor);

      if (ranked.length === 0) {
        cardsList.innerHTML = `<p class="sub-text center-text" style="padding: 20px;">No matching tutors yet for ${esc(subject)}.</p>`;
        return;
      }

      const label = data.mode === 'ai' ? 'AI Match' : 'Rule-based match';
      const note = data.mode === 'ai'
        ? 'Ranked by our AI based on your needs.'
        : 'AI is not available right now, so these tutors were ranked by our rule-based matcher.';

      cardsList.innerHTML = `<p class="sub-text" style="margin-bottom: 10px;">${esc(note)}</p>` + ranked.map(({ r, tutor }) => `
        <div class="session-card" style="align-items: flex-start;">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <strong style="font-size: 1.1rem; cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(tutor.id)}')">${esc(tutor.name)}</strong>
              <span class="badge badge-match">${esc(r.score)}% ${esc(label)}</span>
            </div>
            <div class="sub-text margin-top-xs">Subject: <strong>${esc(subject)}</strong></div>
            <div class="sub-text">Teaching Style: ${esc(tutor.learningStyles.join(', '))}</div>
            <div class="sub-text">Rating: ${tutor.reviewsCount > 0 ? '★ ' + esc(tutor.rating.toFixed(1)) + ' (' + esc(tutor.reviewsCount) + ' reviews)' : 'New tutor (no reviews yet)'}</div>
            <p class="sub-text" style="margin-top: 6px;"><em>Why this tutor: ${esc(r.reason)}</em></p>
            <p class="sub-text" style="margin-top: 6px;">${esc(tutor.bio)}</p>
          </div>
          <div>
            <button class="btn btn-primary btn-small match-choose-btn" data-tutor-id="${esc(tutor.id)}" data-score="${esc(r.score)}" data-mode="${esc(data.mode)}" data-reason="${esc(r.reason)}">Choose Tutor & Book</button>
          </div>
        </div>
      `).join('');

      cardsList.querySelectorAll('.match-choose-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          selectMatchedTutor(btn.dataset.tutorId, subject, Number(btn.dataset.score), btn.dataset.mode, btn.dataset.reason);
        });
      });

      resultsBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } catch (err) {
      cardsList.innerHTML = '';
      resultsBox.classList.add('hidden');
      showToast('Could not connect to server.', 'error');
    } finally {
      if (runBtn) { runBtn.disabled = false; runBtn.textContent = runBtnLabel; }
    }
  });
}

window.selectMatchedTutor = async function(tutorId, subject, score, mode, reason) {
  closeModal('modal-ai-matching');

  // Submit match request row to backend
  try {
    await fetch('api/matches.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tutor_id: tutorId,
        subject,
        score,
        match_reason: (mode === 'ai' ? 'AI match: ' : 'Rule-based match: ') + (reason || 'Compatibility match')
      })
    });
  } catch (e) {}

  openCalendarBooking(tutorId, subject);
};

// Calendar Booking Engine
function initCalendarBooking() {
  const cancelBtn = document.getElementById('cancel-booking-btn');
  const proceedBtn = document.getElementById('proceed-to-payment-btn');

  cancelBtn?.addEventListener('click', () => closeModal('modal-calendar'));

  proceedBtn?.addEventListener('click', () => {
    if (!state.activeBooking.timeSlot) {
      showToast('Please select an available time slot before proceeding.', 'error');
      return;
    }
    closeModal('modal-calendar');
    openGCashPayment();
  });

  const picker = document.getElementById('booking-date-picker');
  if (picker) {
    const today = new Date().toISOString().split('T')[0];
    picker.min = today;
    if (!picker.value) picker.value = today;
    state.activeBooking.date = picker.value;
    picker.addEventListener('change', (e) => {
      state.activeBooking.date = e.target.value;
    });
  }
}

window.openCalendarBooking = function(tutorId, preferredSubject = null) {
  const tutor = state.tutors.find(t => t.id === tutorId);
  if (!tutor) return;

  const subject = preferredSubject || tutor.subjects[0] || 'Mathematics';
  const hourlyRate = tutor.hourlyRate;
  const serviceFee = Math.round(hourlyRate * 0.10);
  const total = hourlyRate + serviceFee;

  const todayStr = new Date().toISOString().split('T')[0];
  const activePickerVal = document.getElementById('booking-date-picker')?.value || todayStr;

  state.activeBooking = {
    tutorId: tutor.id,
    tutorName: tutor.name,
    subject: subject,
    date: activePickerVal,
    timeSlot: null,
    hourlyRate: hourlyRate,
    serviceFee: serviceFee,
    total: total,
    paymentMethod: 'GCash'
  };

  const preview = document.getElementById('selected-tutor-preview');
  if (preview) {
    preview.innerHTML = `
      <div style="display: flex; align-items: center; gap: 12px; background: var(--panel); padding: 12px; border-radius: 4px;">
        <div class="tutor-avatar" style="cursor: pointer;" onclick="viewTutorProfile('${esc(tutor.id)}')">${esc(tutor.initials)}</div>
        <div>
          <h4 style="margin: 0; cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${esc(tutor.id)}')">${esc(tutor.name)}</h4>
          <span class="sub-text">Subject: <strong>${esc(subject)}</strong></span>
        </div>
      </div>
    `;
  }

  const slotsContainer = document.getElementById('time-slots-container');
  const slots = (tutor.availabilitySlots && tutor.availabilitySlots.length > 0) ? tutor.availabilitySlots : ['09:00 AM', '02:00 PM', '04:00 PM'];
  if (slotsContainer) {
    slotsContainer.innerHTML = slots.map(slot => `
      <div class="slot-chip" onclick="selectTimeSlot(this, '${esc(slot)}')">${esc(slot)}</div>
    `).join('');
  }

  const hFee = document.getElementById('summary-hourly-fee');
  const sFee = document.getElementById('summary-service-fee');
  const tFee = document.getElementById('summary-total-fee');

  if (hFee) hFee.textContent = `₱${hourlyRate}`;
  if (sFee) sFee.textContent = `₱${serviceFee}`;
  if (tFee) tFee.textContent = `₱${total}`;

  openModal('modal-calendar');
};

window.selectTimeSlot = function(element, slot) {
  document.querySelectorAll('.slot-chip').forEach(chip => chip.classList.remove('selected'));
  element.classList.add('selected');
  state.activeBooking.timeSlot = slot;
};

// Simulated GCash Payment Integration
function initGCashPayment() {
  const step1Next = document.getElementById('gcash-step1-next-btn');
  const step2Pay = document.getElementById('gcash-confirm-pay-btn');
  const step3Done = document.getElementById('gcash-done-btn');
  const closeBtn = document.getElementById('close-gcash-modal');

  closeBtn?.addEventListener('click', () => closeModal('modal-gcash'));

  step1Next?.addEventListener('click', () => {
    const phone = document.getElementById('gcash-mobile').value;
    if (phone.length < 10) {
      showToast('Please enter a valid mobile number.', 'error');
      return;
    }
    document.getElementById('gcash-step-1').classList.add('hidden');
    document.getElementById('gcash-step-2').classList.remove('hidden');
  });

  step2Pay?.addEventListener('click', async () => {
    const b = state.activeBooking;

    try {
      const res = await fetch('api/sessions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tutor_id: b.tutorId,
          subject: b.subject,
          session_date: b.date,
          time_slot: b.timeSlot
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Booking failed. Please try again.', 'error');
        return;
      }

      await syncWithDatabase();

      const createdSession = data.session || {};
      const randomRef = createdSession.gcashRef || createdSession.gcash_ref || 'GC-Confirmed';

      document.getElementById('gcash-receipt-ref').textContent = randomRef;
      document.getElementById('gcash-receipt-method').textContent = 'GCash';
      document.getElementById('gcash-receipt-date').textContent = `${b.date}, ${b.timeSlot}`;
      document.getElementById('gcash-receipt-amount').textContent = `₱${createdSession.totalPaid || b.total}.00`;

      document.getElementById('gcash-step-2').classList.add('hidden');
      document.getElementById('gcash-step-3').classList.remove('hidden');

      renderAllViews();
      showToast('Payment submitted - awaiting admin confirmation.');
    } catch (e) {
      showToast('Booking failed. Could not connect to server.', 'error');
    }
  });

  step3Done?.addEventListener('click', () => {
    closeModal('modal-gcash');
    document.getElementById('gcash-step-1').classList.remove('hidden');
    document.getElementById('gcash-step-2').classList.add('hidden');
    document.getElementById('gcash-step-3').classList.add('hidden');
  });
}

function openGCashPayment() {
  const b = state.activeBooking;

  const headerTitle = document.getElementById('payment-method-header-title');
  const modalAmt = document.getElementById('gcash-modal-amount');
  const confirmBtn = document.getElementById('gcash-confirm-pay-btn');

  if (headerTitle) headerTitle.textContent = `Simulated GCash Checkout`;
  if (modalAmt) modalAmt.textContent = `₱${b.total}.00`;
  if (confirmBtn) confirmBtn.textContent = `Confirm & Pay ₱${b.total}.00`;

  const detailsContainer = document.getElementById('gcash-booking-summary-preview');
  if (detailsContainer) {
    detailsContainer.innerHTML = `
      <div class="receipt-box" style="margin-top: 10px; font-size: 0.88rem; text-align: left; background: rgba(255,255,255,0.9); border: 1px solid var(--line); padding: 10px; border-radius: 4px;">
        <div><strong>Tutor:</strong> ${esc(b.tutorName)}</div>
        <div><strong>Subject:</strong> ${esc(b.subject)}</div>
        <div><strong>Schedule:</strong> ${esc(b.date)} (${esc(b.timeSlot || '02:00 PM')})</div>
      </div>
    `;
  }

  openModal('modal-gcash');
}

// Notifications Engine
function initNotifications() {
  const bellBtn = document.getElementById('notif-bell-btn');
  const closeDrawer = document.getElementById('close-notif-drawer');
  const overlay = document.getElementById('drawer-overlay');
  const markReadBtn = document.getElementById('mark-all-read-btn');

  bellBtn?.addEventListener('click', () => {
    renderNotifDrawer();
    document.getElementById('notif-drawer').classList.add('active');
    overlay.classList.add('active');
  });

  closeDrawer?.addEventListener('click', closeNotifDrawer);
  overlay?.addEventListener('click', closeNotifDrawer);

  markReadBtn?.addEventListener('click', async () => {
    const role = state.currentRole;
    state.notifications.forEach(n => {
      if (n.targetRole === role || n.targetRole === 'all') {
        n.read = true;
      }
    });

    // Save broadcast notification read status locally
    const userId = state.currentUser ? state.currentUser.id : 'guest';
    const readStorage = state.notifications.filter(n => n.read).map(n => n.id);
    localStorage.setItem(`tutorlink_read_${userId}`, JSON.stringify(readStorage));

    try {
      await fetch('api/notifications.php', { method: 'PUT' });
    } catch (e) {}

    renderNotifDrawer();
    updateNotificationBadge();
    showToast('Notifications marked as read.');
  });
}

function closeNotifDrawer() {
  document.getElementById('notif-drawer')?.classList.remove('active');
  document.getElementById('drawer-overlay')?.classList.remove('active');
}

function renderNotifDrawer() {
  const list = document.getElementById('notif-list');
  if (!list) return;

  const role = state.currentRole;
  const filtered = state.notifications.filter(n => n.targetRole === role || n.targetRole === 'all');

  if (filtered.length === 0) {
    list.innerHTML = `<p class="sub-text center-text" style="padding: 20px;">No notifications for your account.</p>`;
    return;
  }

  list.innerHTML = filtered.map(n => `
    <div class="notif-item ${n.read ? '' : 'unread'}">
      <strong>${esc(n.title)}</strong>
      <p>${esc(n.message)}</p>
      <div class="notif-time">${esc(n.time)}</div>
    </div>
  `).join('');
}

function updateNotificationBadge() {
  const badge = document.getElementById('notif-badge-count');
  if (!badge) return;

  const role = state.currentRole;
  const unreadCount = state.notifications.filter(n => (!n.read) && (n.targetRole === role || n.targetRole === 'all')).length;

  badge.textContent = unreadCount;
  badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
}

// Reschedule Modal Handler
function initRescheduleModal() {
  const closeBtn = document.getElementById('close-reschedule-modal');
  const cancelBtn = document.getElementById('cancel-reschedule-btn');
  const form = document.getElementById('reschedule-session-form');

  closeBtn?.addEventListener('click', () => closeModal('modal-reschedule'));
  cancelBtn?.addEventListener('click', () => closeModal('modal-reschedule'));

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const sessionId = document.getElementById('reschedule-session-id').value;
    const newDate = document.getElementById('reschedule-date-input').value;
    const newSlot = document.getElementById('reschedule-timeslot-select').value;

    try {
      const res = await fetch('api/sessions.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: sessionId,
          session_date: newDate,
          time_slot: newSlot
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Reschedule failed.', 'error');
        return;
      }
      closeModal('modal-reschedule');
      await syncWithDatabase();
      renderAllViews();
      showToast('Session rescheduled successfully.');
    } catch (e) {
      showToast('Could not connect to server.', 'error');
    }
  });
}

window.openRescheduleModal = function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (!session) return;

  document.getElementById('reschedule-session-id').value = sessionId;
  const dateInput = document.getElementById('reschedule-date-input');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    dateInput.value = session.date || session.sessionDate || today;
  }

  // Populate time slot select from tutor's slots
  const tutor = state.tutors.find(t => t.id === session.tutorId || t.name === session.tutorName);
  const slots = tutor && tutor.availabilitySlots && tutor.availabilitySlots.length > 0 ? tutor.availabilitySlots : ['09:00 AM', '02:00 PM', '04:00 PM'];

  const slotSelect = document.getElementById('reschedule-timeslot-select');
  if (slotSelect) {
    slotSelect.innerHTML = slots.map(s => `
      <option value="${esc(s)}" ${s === session.timeSlot ? 'selected' : ''}>${esc(s)}</option>
    `).join('');
  }

  openModal('modal-reschedule');
};

// End Session Modal Handler
function initEndSessionModal() {
  const closeBtn = document.getElementById('close-end-session-modal');
  const cancelBtn = document.getElementById('cancel-end-session-btn');
  const form = document.getElementById('end-session-form');

  closeBtn?.addEventListener('click', () => closeModal('modal-end-session'));
  cancelBtn?.addEventListener('click', () => closeModal('modal-end-session'));

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const sessionId = document.getElementById('end-session-id').value;
    const notes = document.getElementById('end-session-notes-input').value;

    try {
      const res = await fetch('api/sessions.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: sessionId,
          status: 'Completed',
          notes: notes
        })
      });
      const data = await res.json();
      if (!res.ok || data.status !== 'success') {
        showToast(data.message || 'Failed to complete session.', 'error');
        return;
      }
      closeModal('modal-end-session');
      await syncWithDatabase();
      renderAllViews();
      showToast('Session completed successfully!');
    } catch (e) {
      showToast('Could not connect to server.', 'error');
    }
  });
}

window.openEndSessionModal = function(sessionId) {
  document.getElementById('end-session-id').value = sessionId;
  document.getElementById('end-session-notes-input').value = '';
  openModal('modal-end-session');
};

// Workspace Module
function initWorkspaceSession() {
  const closeBtn = document.getElementById('close-workspace-btn');
  const endBtn = document.getElementById('end-session-btn');
  const chatForm = document.getElementById('chat-form');

  closeBtn?.addEventListener('click', () => closeModal('modal-session-workspace'));

  endBtn?.addEventListener('click', () => {
    closeModal('modal-session-workspace');
    if (state.currentRole === 'tutor' && state.activeWorkspaceSession) {
      openEndSessionModal(state.activeWorkspaceSession.id);
    }
  });

  chatForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msgText = input ? input.value.trim() : '';
    if (!msgText || !state.activeWorkspaceSession) return;

    try {
      const res = await fetch('api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          session_id: state.activeWorkspaceSession.id,
          message: msgText
        })
      });
      const data = await res.json();
      if (res.ok && data.status === 'success') {
        if (input) input.value = '';
        await pollWorkspaceChat();
      } else {
        showToast(data.message || 'Failed to send message.', 'error');
      }
    } catch (e) {
      showToast('Failed to send message.', 'error');
    }
  });
}

window.joinLiveWorkspace = async function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (!session) return;

  state.activeWorkspaceSession = session;
  state.lastChatId = 0;

  const titleEl = document.getElementById('workspace-session-title');
  const partEl = document.getElementById('workspace-participants');
  const notesEl = document.getElementById('session-shared-notes');

  if (titleEl) titleEl.textContent = `Live Session Workspace: ${esc(session.subject)}`;
  if (partEl) partEl.textContent = `${esc(session.studentName)} (Student) & ${esc(session.tutorName)} (Tutor)`;
  if (notesEl) notesEl.value = session.notes || '';

  const chatContainer = document.getElementById('chat-messages-container');
  if (chatContainer) {
    chatContainer.innerHTML = `<div class="chat-msg system">Live Virtual Workspace connected.</div>`;
  }

  openModal('modal-session-workspace');
  await pollWorkspaceChat();
};

async function pollWorkspaceChat() {
  if (!state.activeWorkspaceSession) return;
  const sessionId = state.activeWorkspaceSession.id;

  try {
    const res = await fetch(`api/chat.php?session_id=${encodeURIComponent(sessionId)}&after_id=${state.lastChatId}`);
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data.data) && data.data.length > 0) {
        const chatContainer = document.getElementById('chat-messages-container');
        data.data.forEach(msg => {
          if (msg.id > state.lastChatId) state.lastChatId = msg.id;
          if (chatContainer) {
            const div = document.createElement('div');
            const isSelf = state.currentUser && msg.senderName === state.currentUser.name;
            div.className = `chat-msg ${isSelf ? 'student' : 'tutor'}`;
            div.innerHTML = `<strong>${esc(msg.senderName)}:</strong> ${esc(msg.message)}`;
            chatContainer.appendChild(div);
          }
        });
        if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
      }
    }
  } catch (e) {}
}

// Rating Modal Handler
function openRatingModal(sessionId, tutorName) {
  const nameEl = document.getElementById('rating-tutor-name');
  if (nameEl) nameEl.textContent = tutorName;

  let selectedRating = 5;
  const stars = document.querySelectorAll('#star-rating-group .star');
  stars.forEach(s => {
    s.onclick = function() {
      selectedRating = parseInt(s.getAttribute('data-value'));
      stars.forEach(st => {
        const val = parseInt(st.getAttribute('data-value'));
        st.classList.toggle('active', val <= selectedRating);
      });
    };
  });

  const submitBtn = document.getElementById('submit-rating-btn');
  if (submitBtn) {
    submitBtn.onclick = async function() {
      const comment = document.getElementById('rating-comment')?.value || '';

      try {
        const res = await fetch('api/sessions.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id: sessionId,
            rating: selectedRating,
            feedback: comment
          })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          showToast(data.message || 'Rating submission failed.', 'error');
          return;
        }
        closeModal('modal-rating');
        await syncWithDatabase();
        renderAllViews();
        showToast('Thank you for submitting feedback!');
      } catch (e) {
        showToast('Could not connect to server.', 'error');
      }
    };
  }

  document.getElementById('close-rating-modal').onclick = function() {
    closeModal('modal-rating');
  };

  openModal('modal-rating');
}

window.viewSessionReceipt = function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (!session) return;

  const refEl = document.getElementById('view-receipt-ref');
  const studentEl = document.getElementById('view-receipt-student');
  const tutorEl = document.getElementById('view-receipt-tutor');
  const subjectEl = document.getElementById('view-receipt-subject');
  const dateEl = document.getElementById('view-receipt-date');
  const amountEl = document.getElementById('view-receipt-amount');
  const feedbackEl = document.getElementById('view-receipt-tutor-feedback');

  if (refEl) refEl.textContent = session.gcashRef;
  if (studentEl) studentEl.textContent = session.studentName;
  if (tutorEl) tutorEl.textContent = session.tutorName;
  if (subjectEl) subjectEl.textContent = session.subject;
  if (dateEl) dateEl.textContent = `${session.date || session.sessionDate} (${session.timeSlot})`;
  if (amountEl) amountEl.textContent = `₱${session.totalPaid}.00`;

  if (feedbackEl) {
    if (session.notes) {
      feedbackEl.style.display = 'block';
      feedbackEl.innerHTML = `<strong>Tutor Session Remarks:</strong> ${esc(session.notes)}`;
    } else {
      feedbackEl.style.display = 'none';
    }
  }

  openModal('modal-view-receipt');
};

function initAdminView() {
  // Configured in admin.php and page handlers
}
