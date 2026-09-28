/**
 * TutorLink - An Intelligent Tutor-Student Matching System
 * Application Logic & State Management
 */

// Application Initial State & Mock Database
const state = {
  currentRole: 'guest', // 'guest', 'student', 'tutor', 'admin'
  currentUser: null,

  // Seed Subjects Database
  subjects: [
    { id: 'SUB-101', name: 'Mathematics', category: 'STEM' },
    { id: 'SUB-102', name: 'Calculus', category: 'STEM' },
    { id: 'SUB-103', name: 'Physics', category: 'STEM' },
    { id: 'SUB-104', name: 'Chemistry', category: 'STEM' },
    { id: 'SUB-105', name: 'Programming', category: 'Technology' },
    { id: 'SUB-106', name: 'English', category: 'Humanities' },
    { id: 'SUB-107', name: 'Literature', category: 'Humanities' }
  ],

  // Empty Students Database for fresh operational start
  students: [],

  // Empty Tutors Database for fresh operational start
  tutors: [],

  // Empty Matching Results
  matches: [],

  // Empty Calendar Schedules
  schedules: [],

  // Empty Payments Database
  payments: [],

  // Empty Sessions & Bookings
  sessions: [],

  // System Notifications - Initial System Welcome
  notifications: [
    {
      id: 'notif-init',
      targetRole: 'all',
      target: 'All Users',
      title: 'Welcome to TutorLink',
      message: 'System initialization complete. Log in or register to get started.',
      time: 'Just now',
      read: false
    }
  ],
  // Active Pending Booking Flow
  activeBooking: {
    tutorId: 'tut-1',
    tutorName: 'Prof. Alex Rivera',
    subject: 'Calculus II',
    date: '2026-03-16',
    timeSlot: '02:00 PM',
    hourlyRate: 350,
    serviceFee: 35,
    total: 385,
    paymentMethod: 'GCash'
  },

  activeWorkspaceSession: null,
  activeReportFilter: 'all'
};

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
  initTutorSubTabs();
  initSubjectManagement();

  const savedSessionStr = localStorage.getItem('tutorlink_session');
  if (savedSessionStr) {
    try {
      const savedSession = JSON.parse(savedSessionStr);
      if (savedSession && savedSession.role && savedSession.user) {
        state.currentUser = savedSession.user;
        state.currentRole = savedSession.role;
      }
    } catch (e) {
      localStorage.removeItem('tutorlink_session');
    }
  }

  await syncWithDatabase();

  if (state.currentUser && state.currentRole && state.currentRole !== 'guest') {
    switchRole(state.currentRole, state.currentUser);
  } else {
    switchRole('guest');
  }
});


// XAMPP / Database Synchronization Engine (With Offline Fallback)
async function syncWithDatabase() {
  try {
    const resS = await fetch('api/students.php');
    if (resS.ok) {
      const jsonS = await resS.json();
      if (jsonS.data && jsonS.data.length > 0) {
        state.students = jsonS.data.map(s => ({
          id: s.id,
          name: s.name,
          email: s.email,
          grade: s.grade,
          validated: Boolean(parseInt(s.validated)),
          deactivated: Boolean(parseInt(s.deactivated || 0)),
          bio: s.bio,
          subjectsNeeded: s.subjects_needed ? s.subjects_needed.split(',') : []
        }));
      }
    }
  } catch (e) {
    console.log('XAMPP Backend offline, running on memory state.');
  }

  try {
    const resSub = await fetch('api/subjects.php');
    if (resSub.ok) {
      const jsonSub = await resSub.json();
      if (jsonSub.data && jsonSub.data.length > 0) {
        state.subjects = jsonSub.data;
      }
    }
  } catch (e) {
    console.log('Subjects API fallback active.');
  }

  try {
    const resT = await fetch('api/tutors.php');
    if (resT.ok) {
      const jsonT = await resT.json();
      if (jsonT.data && jsonT.data.length > 0) {
        state.tutors = jsonT.data.map(t => ({
          id: t.id,
          name: t.name,
          initials: t.initials,
          rating: parseFloat(t.rating),
          reviewsCount: parseInt(t.reviewsCount || t.reviews_count || 0),
          hourlyRate: parseInt(t.hourlyRate || t.hourly_rate || 350),
          subjects: Array.isArray(t.subjects) ? t.subjects : (t.subjects ? t.subjects.split(',').map(x => x.trim()) : ['Mathematics']),
          learningStyles: Array.isArray(t.learningStyles || t.learning_styles) ? (t.learningStyles || t.learning_styles) : ['Step-by-Step Explanation'],
          bio: t.bio,
          availableDays: Array.isArray(t.availableDays) ? t.availableDays : (t.available_days ? t.available_days.split(',') : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']),
          availableTimeSlots: t.availableTimeSlots || t.available_time_slots || '09:00 AM - 05:00 PM',
          blockedDates: t.blockedDates || t.blocked_dates || '',
          available: Boolean(t.available),
          deactivated: Boolean(t.deactivated),
          approvalStatus: t.approvalStatus || t.approval_status || 'Approved',
          availabilitySlots: Array.isArray(t.availabilitySlots) ? t.availabilitySlots : ['09:00 AM', '02:00 PM', '04:00 PM']
        }));
      }
    }
  } catch (e) {
    console.log('Tutors API fallback active.');
  }

  try {
    const resSes = await fetch('api/sessions.php');
    if (resSes.ok) {
      const jsonSes = await resSes.json();
      if (jsonSes.data && jsonSes.data.length > 0) state.sessions = jsonSes.data;
    }
  } catch (e) {}

  try {
    const resPay = await fetch('api/payments.php');
    if (resPay.ok) {
      const jsonPay = await resPay.json();
      if (jsonPay.data && jsonPay.data.length > 0) state.payments = jsonPay.data;
    }
  } catch (e) {}

  try {
    const resM = await fetch('api/matches.php');
    if (resM.ok) {
      const jsonM = await resM.json();
      if (jsonM.data && jsonM.data.length > 0) state.matches = jsonM.data;
    }
  } catch (e) {}

  try {
    const resSch = await fetch('api/schedules.php');
    if (resSch.ok) {
      const jsonSch = await resSch.json();
      if (jsonSch.data && jsonSch.data.length > 0) state.schedules = jsonSch.data;
    }
  } catch (e) {}

  try {
    const resN = await fetch('api/notifications.php');
    if (resN.ok) {
      const jsonN = await resN.json();
      if (jsonN.data && jsonN.data.length > 0) state.notifications = jsonN.data;
    }
  } catch (e) {}

  try {
    const resA = await fetch('api/admin.php');
    if (resA.ok) {
      const jsonA = await resA.json();
      if (jsonA.data) {
        const d = jsonA.data;
        document.getElementById('admin-stat-total-students').textContent = d.total_students;
        document.getElementById('admin-stat-total-tutors').textContent = d.total_tutors;
        document.getElementById('admin-stat-total-volume').textContent = `P${d.total_volume.toLocaleString()}`;
        document.getElementById('admin-stat-platform-commission').textContent = `P${d.platform_commission.toLocaleString()}`;
      }
    }
  } catch (e) {
    console.log('Admin SQL stats API offline, computing from state.');
  }
}

// Navigation Engine
function initNavigation() {
  const navLogo = document.getElementById('nav-logo');
  const loginBtn = document.getElementById('login-link-btn');
  const signupBtn = document.getElementById('signup-btn');
  const logoutBtn = document.getElementById('logout-btn');
  const userProfileBtn = document.getElementById('user-profile-btn');

  navLogo.addEventListener('click', () => {
    if (state.currentUser && state.currentUser.role) {
      switchRole(state.currentUser.role);
    } else if (state.currentRole && state.currentRole !== 'guest') {
      switchRole(state.currentRole);
    } else {
      switchRole('guest');
    }
  });

  loginBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openModal('modal-auth');
    switchAuthTab('login');
  });

  signupBtn.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  logoutBtn?.addEventListener('click', () => {
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

  document.getElementById('tutor-profile-page-form')?.addEventListener('submit', (e) => {
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

    const currentTutor = state.tutors.find(t => t.id === (state.currentUser ? state.currentUser.id : '') || t.name === (state.currentUser ? state.currentUser.name : '')) || state.tutors[0];
    if (currentTutor) {
      currentTutor.name = name;
      currentTutor.initials = name.split(' ').map(n=>n[0]).join('');
      currentTutor.subjects = subjects.split(',').map(s => s.trim());
      currentTutor.learningStyles = [style];
      currentTutor.hourlyRate = parseInt(rate) || 350;
      currentTutor.bio = bio;
      currentTutor.available = available;
      currentTutor.availableDays = checkedDays;
      currentTutor.availableTimeSlots = windowVal;
      currentTutor.blockedDates = blockedVal;
    }

    if (state.currentUser) state.currentUser.name = name;
    document.getElementById('tutor-welcome-heading').textContent = `Tutor Portal - ${name}`;

    try {
      fetch('api/tutors.php', {
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
    } catch (e) { console.log('Offline tutor profile edit fallback'); }

    switchRole('tutor');
    showToast('Tutor profile updated successfully!');
  });

  document.getElementById('hero-find-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('hero-become-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('cta-find-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('cta-become-tutor-btn')?.addEventListener('click', () => { openModal('modal-auth'); switchAuthTab('register'); });
  document.getElementById('start-ai-match-btn')?.addEventListener('click', () => openModal('modal-ai-matching'));

  document.getElementById('landing-ai-card')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('login');
    const authRoleSelect = document.getElementById('auth-role');
    if (authRoleSelect) authRoleSelect.value = 'student';
    showToast('Please sign in as a Student to access AI Tutor Matching.');
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
    const tutorSessions = state.sessions.filter(s => s.tutorName.includes('Alex') || (state.currentUser && s.tutorName === state.currentUser.name));
    if (titleEl) titleEl.textContent = `Tutor Schedule Calendar - ${state.currentUser ? state.currentUser.name : 'Prof. Alex Rivera'}`;
    renderSessionCalendar('floating-calendar-container', tutorSessions, 'Tutor');
  } else {
    const studentSessions = state.sessions.filter(s => s.studentName === (state.currentUser ? state.currentUser.name : 'Maria Santos'));
    if (titleEl) titleEl.textContent = `Student Schedule Calendar - ${state.currentUser ? state.currentUser.name : 'Maria Santos'}`;
    renderSessionCalendar('floating-calendar-container', studentSessions, 'Student');
  }
  openModal('modal-floating-calendar');
}

// Role Switcher Logic
function switchRole(role, customUser = null) {
  state.currentRole = role;

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
    localStorage.removeItem('tutorlink_session');
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
    if (state.currentUser) {
      localStorage.setItem('tutorlink_session', JSON.stringify({
        role: role,
        user: state.currentUser
      }));
    }

    if (publicNavLinks) publicNavLinks.style.display = 'none';
    if (loginBtn) loginBtn.classList.add('hidden');
    if (signupBtn) signupBtn.classList.add('hidden');
    if (logoutBtn) logoutBtn.classList.remove('hidden');
    if (notifBell) notifBell.classList.remove('hidden');

    if (role === 'student' || role === 'tutor' || role === 'tutor-profile') {
      if (userProfileBtn) userProfileBtn.classList.remove('hidden');
      if (floatingCalBtn) floatingCalBtn.classList.remove('hidden');
    } else {
      if (userProfileBtn) userProfileBtn.classList.add('hidden');
      if (floatingCalBtn) floatingCalBtn.classList.add('hidden');
    }

    if (role === 'student') {
      state.currentUser = customUser || state.currentUser || { name: 'Maria Santos', role: 'student' };
      const prefix = (state.currentUser && state.currentUser.isNew) ? 'Welcome Student,' : 'Welcome back,';
      const headingEl = document.getElementById('student-welcome-heading');
      if (headingEl) headingEl.textContent = `${prefix} ${state.currentUser ? state.currentUser.name : ''}!`;
      document.getElementById('view-student').classList.add('active');
    } else if (role === 'tutor') {
      state.currentUser = customUser || state.currentUser || { name: 'Prof. Alex Rivera', role: 'tutor' };
      const prefix = (state.currentUser && state.currentUser.isNew) ? 'Welcome Tutor,' : 'Welcome back,';
      const headingEl = document.getElementById('tutor-welcome-heading');
      if (headingEl) headingEl.textContent = `${prefix} ${state.currentUser ? state.currentUser.name : ''}!`;
      document.getElementById('view-tutor').classList.add('active');
    } else if (role === 'tutor-profile') {
      populateTutorProfileEditPage();
      document.getElementById('view-tutor-profile').classList.add('active');
    } else if (role === 'admin') {
      state.currentUser = customUser || state.currentUser || { name: 'System Admin', role: 'admin' };
      document.getElementById('view-admin').classList.add('active');
    }
  }

  renderAllViews();
}

// Modal Auth Tabs
function initAuthModalTabs() {
  const tabLogin = document.getElementById('tab-btn-login');
  const tabRegister = document.getElementById('tab-btn-register');

  tabLogin?.addEventListener('click', () => switchAuthTab('login'));
  tabRegister?.addEventListener('click', () => switchAuthTab('register'));

  const registerForm = document.getElementById('register-form');
  registerForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const role = document.getElementById('reg-role').value;
    const fullname = document.getElementById('reg-fullname').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const grade = document.getElementById('reg-grade')?.value || 'Senior High';
    const subject = document.getElementById('reg-subject')?.value || 'Mathematics';

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
          subject,
          specialty: `${grade} / ${subject}`
        })
      });
      const data = await res.json();
      if (res.ok && data.status === 'success') {
        const user = data.user;
        user.isNew = true;
        switchRole(user.role, user);
        closeModal('modal-auth');
        showToast(`Welcome to TutorLink, ${user.name}!`);
        return;
      } else {
        showToast(data.message || 'Registration failed.', 'error');
        return;
      }
    } catch (err) {
      console.log('Offline API register fallback');
    }

    if (role === 'student') {
      const newId = 'STU-' + Math.floor(100 + Math.random() * 900);
      const newStudent = {
        id: newId,
        name: fullname,
        email: email,
        grade: specialty,
        validated: true,
        deactivated: false,
        bio: `${specialty} Student eager to connect with expert tutors on TutorLink.`,
        subjectsNeeded: [specialty],
        sessionsCompleted: 0
      };
      state.students.unshift(newStudent);

      // Create Admin Notification ONLY
      state.notifications.unshift({
        id: 'notif-' + Date.now(),
        targetRole: 'admin',
        target: 'System Admin',
        title: 'New Student Registered',
        message: `New student registered: ${fullname} (${email})`,
        time: 'Just now',
        read: false
      });

      // API save
      try {
        await fetch('api/students.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ...newStudent, is_new_registration: true, subjects_needed: specialty })
        });
      } catch (err) { console.log('Offline API fallback'); }

      // Clear sessions for fresh isolated user state
      state.sessions = state.sessions.filter(s => s.studentName !== fullname);

      switchRole('student', { name: fullname, role: 'student', id: newId });
    } else {
      const newId = 'tut-' + (state.tutors.length + 1);
      const newTutor = {
        id: newId,
        name: fullname,
        initials: fullname.split(' ').map(n => n[0]).join(''),
        rating: 5.0,
        reviewsCount: 0,
        hourlyRate: 350,
        subjects: [specialty],
        learningStyles: ['Step-by-Step Explanation'],
        bio: `${specialty} Specialist tutor. Dedicated to student growth.`,
        availableDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        availableTimeSlots: '09:00 AM - 05:00 PM',
        blockedDates: '',
        availabilitySlots: ['09:00 AM', '02:00 PM', '04:00 PM'],
        available: true,
        deactivated: false,
        approvalStatus: 'Pending Review'
      };
      state.tutors.unshift(newTutor);

      // Create Admin Notification ONLY
      state.notifications.unshift({
        id: 'notif-' + Date.now(),
        targetRole: 'admin',
        target: 'System Admin',
        title: 'New Tutor Registered',
        message: `New tutor registered: ${fullname} (${specialty})`,
        time: 'Just now',
        read: false
      });

      // API save
      try {
        await fetch('api/tutors.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ...newTutor, is_new_registration: true, subjects: specialty, learning_styles: 'Step-by-Step Explanation' })
        });
      } catch (err) { console.log('Offline API fallback'); }

      switchRole('tutor', { name: fullname, role: 'tutor', id: newId });
    }

    closeModal('modal-auth');
    showToast(`Registration complete! Welcome to TutorLink, ${fullname}.`);
  });
}

function switchAuthTab(type) {
  const tabLogin = document.getElementById('tab-btn-login');
  const tabRegister = document.getElementById('tab-btn-register');
  const formLogin = document.getElementById('auth-form');
  const formRegister = document.getElementById('register-form');

  if (type === 'login') {
    tabLogin?.classList.add('active');
    tabRegister?.classList.remove('active');
    formLogin?.classList.remove('hidden');
    formRegister?.classList.add('hidden');
  } else {
    tabRegister?.classList.add('active');
    tabLogin?.classList.remove('active');
    formRegister?.classList.remove('hidden');
    formLogin?.classList.add('hidden');
  }
}

// Modal Helpers
function initModals() {
  document.querySelectorAll('.close-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const backdrop = e.target.closest('.modal-backdrop');
      if (backdrop) backdrop.classList.remove('active');
    });
  });

  document.getElementById('auth-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('auth-email').value.trim();
    const password = document.getElementById('auth-password').value;

    try {
      const res = await fetch('api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (res.ok && data.status === 'success') {
        const user = data.user;
        switchRole(user.role, { id: user.id, name: user.name, role: user.role, email: user.email });
        closeModal('modal-auth');
        showToast(`Welcome back, ${user.name}!`);
        return;
      } else {
        showToast(data.message || 'Invalid email or password.', 'error');
        return;
      }
    } catch (err) {
      // Local fallback for offline testing
      const studentMatch = state.students.find(s => s.email && s.email.toLowerCase() === email.toLowerCase());
      if (studentMatch) {
        switchRole('student', { id: studentMatch.id, name: studentMatch.name, role: 'student', email: studentMatch.email });
        closeModal('modal-auth');
        showToast(`Welcome back, ${studentMatch.name}!`);
      } else if (email.includes('admin')) {
        switchRole('admin', { id: 'ADMIN-001', name: 'System Admin', role: 'admin', email: email });
        closeModal('modal-auth');
        showToast('Welcome back, System Admin!');
      } else {
        showToast('Invalid email or password.', 'error');
      }
    }
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

// Tutor Portal Sub-Tabs Logic
function initTutorSubTabs() {
  document.querySelectorAll('.tutor-tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      document.querySelectorAll('.tutor-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tutor-tab-content').forEach(c => c.classList.remove('active'));

      e.target.classList.add('active');
      const targetTab = e.target.getAttribute('data-tab');
      document.getElementById(targetTab)?.classList.add('active');
    });
  });

  document.getElementById('tutor-add-slot-modal-btn')?.addEventListener('click', () => {
    const slot = prompt('Enter custom availability time slot (e.g. 08:00 AM - 10:00 AM):', '08:00 AM - 10:00 AM');
    if (slot) {
      const currentTutor = state.tutors.find(t => t.id === (state.currentUser ? state.currentUser.id : '') || t.name === (state.currentUser ? state.currentUser.name : '')) || state.tutors[0];
      if (currentTutor) {
        currentTutor.availabilitySlots.push(slot);
      }
      showToast(`Custom slot "${slot}" added to availability!`);
    }
  });

  document.getElementById('tutor-reset-availability-btn')?.addEventListener('click', () => {
    document.getElementById('tutor-time-window-input').value = '09:00 AM - 05:00 PM';
    document.getElementById('tutor-blocked-dates-input').value = '';
    document.querySelectorAll('input[name="avail_day"]').forEach(cb => {
      cb.checked = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(cb.value);
    });
    showToast('Reset availability settings to default weekdays!');
  });

  document.getElementById('tutor-availability-settings-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const checkedDays = Array.from(document.querySelectorAll('input[name="avail_day"]:checked')).map(cb => cb.value);
    const windowVal = document.getElementById('tutor-time-window-input').value;
    const blockedVal = document.getElementById('tutor-blocked-dates-input').value;

    const currentTutor = state.tutors.find(t => t.id === (state.currentUser ? state.currentUser.id : '') || t.name === (state.currentUser ? state.currentUser.name : '')) || state.tutors[0];
    if (currentTutor) {
      currentTutor.availableDays = checkedDays;
      currentTutor.availableTimeSlots = windowVal;
      currentTutor.blockedDates = blockedVal;
    }

    renderTutorCalendar();
    showToast('Tutor availability schedule updated!');
  });
  renderTutorCalendar();
}

// Profile Modals & Settings Functionality
function initProfileModals() {
  document.getElementById('close-student-profile-modal')?.addEventListener('click', () => closeModal('modal-student-profile'));
  document.getElementById('close-tutor-profile-modal')?.addEventListener('click', () => closeModal('modal-tutor-profile'));
  document.getElementById('close-edit-student-modal')?.addEventListener('click', () => closeModal('modal-edit-student-profile'));

  const editStudentForm = document.getElementById('edit-student-profile-form');
  editStudentForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const newName = document.getElementById('edit-student-name').value;
    const newGrade = document.getElementById('edit-student-grade').value;
    const newSubjects = document.getElementById('edit-student-subjects').value;
    const newBio = document.getElementById('edit-student-bio').value;

    const currentStudent = state.students.find(s => s.name === (state.currentUser ? state.currentUser.name : 'Maria Santos')) || state.students[0];
    if (currentStudent) {
      currentStudent.name = newName;
      currentStudent.grade = newGrade;
      currentStudent.subjectsNeeded = newSubjects.split(',').map(s => s.trim());
      currentStudent.bio = newBio;
    }

    if (state.currentUser) state.currentUser.name = newName;
    document.getElementById('student-welcome-heading').textContent = `Welcome back, ${newName}!`;

    closeModal('modal-edit-student-profile');
    renderAllViews();
    showToast('Student profile settings updated successfully!');
  });
}

function initTutorProfileSettings() {
  const saveBtn = document.getElementById('save-tutor-profile-btn');
  saveBtn?.addEventListener('click', () => {
    showToast('Tutor profile updated successfully!');
  });
}

// Subject Management Modal & Handlers
function initSubjectManagement() {
  const addBtn = document.getElementById('admin-add-subject-btn');
  const closeBtn = document.getElementById('close-subject-modal');
  const form = document.getElementById('admin-subject-form');

  addBtn?.addEventListener('click', () => {
    document.getElementById('subject-modal-title').textContent = 'Add New Subject';
    document.getElementById('admin-subject-id').value = '';
    document.getElementById('admin-subject-name').value = '';
    openModal('modal-admin-subject');
  });

  closeBtn?.addEventListener('click', () => closeModal('modal-admin-subject'));

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('admin-subject-id').value;
    const name = document.getElementById('admin-subject-name').value;
    const category = document.getElementById('admin-subject-category').value;

    if (id) {
      const sub = state.subjects.find(s => s.id === id);
      if (sub) {
        sub.name = name;
        sub.category = category;
      }
    } else {
      const newSub = { id: 'SUB-' + Math.floor(100 + Math.random() * 900), name, category };
      state.subjects.push(newSub);
    }

    closeModal('modal-admin-subject');
    renderAdminSubjects();
    showToast('Subject catalog updated successfully!');
  });
}

window.editSubject = function(subId) {
  const sub = state.subjects.find(s => s.id === subId);
  if (sub) {
    document.getElementById('subject-modal-title').textContent = 'Edit Subject';
    document.getElementById('admin-subject-id').value = sub.id;
    document.getElementById('admin-subject-name').value = sub.name;
    document.getElementById('admin-subject-category').value = sub.category;
    openModal('modal-admin-subject');
  }
};

window.deleteSubject = function(subId) {
  const idx = state.subjects.findIndex(s => s.id === subId);
  if (idx !== -1) {
    state.subjects.splice(idx, 1);
    renderAdminSubjects();
    showToast('Subject removed from system catalog.');
  }
};

window.viewStudentProfile = function(studentId) {
  const student = state.students.find(s => s.id === studentId) || {
    id: studentId,
    name: 'Student User',
    email: 'student@tutorlink.ph',
    grade: 'Senior High',
    validated: true,
    bio: 'Dedicated learner preparing for higher education.',
    subjectsNeeded: ['Mathematics'],
    sessionsCompleted: 2
  };

  const body = document.getElementById('student-profile-body');
  if (body) {
    body.innerHTML = `
      <div style="text-align: center; margin-bottom: 20px;">
        <div class="tutor-avatar" style="margin: 0 auto 12px; width: 64px; height: 64px; font-size: 1.5rem;">${student.name.split(' ').map(n=>n[0]).join('')}</div>
        <h4 style="font-size: 1.3rem;">${student.name}</h4>
        <span class="badge ${student.validated ? 'badge-success' : 'badge-info'}">${student.validated ? 'Validated Student' : 'Pending Validation'}</span>
      </div>
      <div class="tutor-details-list">
        <div><strong>Student ID:</strong> <code>${student.id}</code></div>
        <div><strong>Email:</strong> ${student.email}</div>
        <div><strong>Grade Level:</strong> ${student.grade}</div>
        <div><strong>Sessions Completed:</strong> ${student.sessionsCompleted || 3}</div>
        <div><strong>Subjects Needing Help:</strong> ${student.subjectsNeeded ? student.subjectsNeeded.join(', ') : 'Mathematics, Calculus'}</div>
      </div>
      <p class="sub-text margin-top"><strong>Bio:</strong> ${student.bio || 'Active student member on TutorLink.'}</p>
    `;
  }
  openModal('modal-student-profile');
};

window.viewTutorProfile = function(tutorId) {
  const tutor = state.tutors.find(t => t.id === tutorId) || state.tutors[0];
  const body = document.getElementById('tutor-profile-body');
  if (body) {
    body.innerHTML = `
      <div style="text-align: center; margin-bottom: 20px;">
        <div class="tutor-avatar" style="margin: 0 auto 12px; width: 64px; height: 64px; font-size: 1.5rem;">${tutor.initials}</div>
        <h4 style="font-size: 1.3rem;">${tutor.name}</h4>
        <div class="tutor-rating">★ ${tutor.rating} (${tutor.reviewsCount} reviews)</div>
      </div>
      <div class="tutor-details-list">
        <div><strong>Hourly Rate:</strong> P${tutor.hourlyRate}/hr</div>
        <div><strong>Subjects Taught:</strong> ${tutor.subjects.join(', ')}</div>
        <div><strong>Teaching Styles:</strong> ${tutor.learningStyles.join(', ')}</div>
        <div><strong>Available Days:</strong> ${tutor.availableDays.join(', ')} (${tutor.availableTimeSlots})</div>
        <div><strong>Application Status:</strong> <span class="badge badge-success">${tutor.approvalStatus}</span></div>
      </div>
      <p class="sub-text margin-top"><strong>Bio:</strong> ${tutor.bio}</p>
      <button class="btn btn-primary full-width margin-top" onclick="closeModal('modal-tutor-profile'); openCalendarBooking('${tutor.id}')">
        Book Session with ${tutor.name}
      </button>
    `;
  }
  openModal('modal-tutor-profile');
};

// Toast Notifications
function showToast(msg) {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = msg;
  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 3500);
}

// Dynamic Rendering Routines
function renderAllViews() {
  renderTutorDirectory();
  renderStudentUpcoming();
  renderStudentHistory();
  renderStudentCalendar();
  renderTutorCalendar();
  renderTutorUpcoming();
  renderTutorRequests();
  renderTutorEarnings();
  renderAdminKPIs();
  renderAdminStudents();
  renderAdminTutors();
  renderAdminSubjects();
  renderAdminMatching();
  renderAdminSchedule();
  renderAdminPayments();
  renderAdminNotifications();
  renderAdminReports();
  updateNotificationBadge();
}

function renderAdminKPIs() {
  const totalStudents = state.students.length;
  const totalTutors = state.tutors.length;
  const totalVolume = state.sessions.reduce((acc, s) => acc + (s.totalPaid || 0), 0);
  const platformCommission = state.sessions.reduce((acc, s) => acc + (s.commissionFee || Math.round(s.totalPaid * 0.10)), 0);

  const elStudents = document.getElementById('admin-stat-total-students');
  const elTutors = document.getElementById('admin-stat-total-tutors');
  const elVolume = document.getElementById('admin-stat-total-volume');
  const elCommission = document.getElementById('admin-stat-platform-commission');

  if (elStudents) elStudents.textContent = totalStudents;
  if (elTutors) elTutors.textContent = totalTutors;
  if (elVolume) elVolume.textContent = `P${totalVolume.toLocaleString()}`;
  if (elCommission) elCommission.textContent = `P${platformCommission.toLocaleString()}`;
}

// Render Featured Tutor Directory
function renderTutorDirectory(searchTerm = '') {
  const grid = document.getElementById('tutors-directory-grid');
  if (!grid) return;

  const filtered = state.tutors.filter(t => {
    if (t.deactivated) return false;
    const matchName = t.name.toLowerCase().includes(searchTerm);
    const matchSubj = t.subjects.some(s => s.toLowerCase().includes(searchTerm));
    return matchName || matchSubj;
  });

  grid.innerHTML = filtered.map(t => `
    <div class="tutor-card">
      <div>
        <div class="tutor-card-head" style="cursor: pointer;" onclick="viewTutorProfile('${t.id}')">
          <div class="tutor-avatar">${t.initials}</div>
          <div class="tutor-info">
            <h4 style="text-decoration: underline;">${t.name}</h4>
            <div class="tutor-rating">★ ${t.rating} (${t.reviewsCount} reviews)</div>
          </div>
        </div>
        <p class="sub-text margin-bottom">${t.bio}</p>
        <div class="tutor-details-list">
          <div><strong>Subjects:</strong> ${t.subjects.join(', ')}</div>
          <div><strong>Style:</strong> ${t.learningStyles[0]}</div>
          <div><strong>Rate:</strong> P${t.hourlyRate}/hr</div>
        </div>
      </div>
      <div style="display: flex; gap: 8px;" class="margin-top">
        <button class="btn btn-secondary btn-small full-width" onclick="viewTutorProfile('${t.id}')">View Profile</button>
        <button class="btn btn-primary btn-small full-width" onclick="openCalendarBooking('${t.id}')">Book Session</button>
      </div>
    </div>
  `).join('');
}

// Render Student Upcoming Sessions - Filtered Strictly by Active Student User
function renderStudentUpcoming() {
  const container = document.getElementById('student-upcoming-sessions-list');
  if (!container) return;

  const currentStudentName = state.currentUser ? state.currentUser.name : 'Maria Santos';
  const userSessions = state.sessions.filter(s => s.studentName === currentStudentName);
  const upcoming = userSessions.filter(s => s.status === 'Confirmed');
  const completed = userSessions.filter(s => s.status === 'Completed');
  const totalSpent = userSessions.reduce((sum, s) => sum + (s.totalPaid || 0), 0);

  // Update Student Dashboard Quick Stat Cards dynamically
  const upcomingStatEl = document.getElementById('student-stat-upcoming');
  const completedStatEl = document.getElementById('student-stat-completed');
  const spentStatEl = document.getElementById('student-stat-spent');
  const ratingStatEl = document.getElementById('student-stat-rating');

  if (upcomingStatEl) upcomingStatEl.textContent = upcoming.length;
  if (completedStatEl) completedStatEl.textContent = completed.length;
  if (spentStatEl) spentStatEl.textContent = `P${totalSpent}`;
  if (ratingStatEl) ratingStatEl.textContent = completed.length > 0 ? '5.0 ★' : '0.0 ★';

  if (upcoming.length === 0) {
    container.innerHTML = `<p class="sub-text">No upcoming scheduled sessions. Use AI Matching or Browse Tutors to book one!</p>`;
    return;
  }

  container.innerHTML = upcoming.map(s => `
    <div class="session-card">
      <div class="session-card-info">
        <h4>${s.subject} - with <span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></h4>
        <p>${s.sessionDate || s.date || 'Today'} at ${s.timeSlot} | GCash Ref: <strong>${s.gcashRef}</strong></p>
      </div>
      <div>
        <span class="badge ${s.tutorStarted ? 'badge-success' : 'badge-info'} margin-bottom">${s.tutorStarted ? 'Session Live' : 'Confirmed'}</span>
        ${s.tutorStarted ? `
          <button class="btn btn-primary btn-small" onclick="launchWorkspace('${s.id}')">
            Join Live Workspace Session
          </button>
        ` : `
          <button class="btn btn-secondary btn-small" onclick="launchWorkspace('${s.id}')" title="Waiting for tutor to start session">
            Session Pending - Waiting for Tutor to Start
          </button>
        `}
      </div>
    </div>
  `).join('');
}

// Render Student History Table with View Receipt Action - Isolated by Active Student
function renderStudentHistory() {
  const tbody = document.getElementById('student-history-table-body');
  if (!tbody) return;

  const currentStudentName = state.currentUser ? state.currentUser.name : 'Maria Santos';
  const userSessions = state.sessions.filter(s => s.studentName === currentStudentName);

  if (userSessions.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No session history available yet. Book your first tutor to get started!</td></tr>`;
    const spentEl = document.getElementById('student-stat-spent');
    if (spentEl) spentEl.textContent = `P0`;
    return;
  }

  tbody.innerHTML = userSessions.map(s => `
    <tr>
      <td>${s.date} (${s.timeSlot})</td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</strong></td>
      <td>${s.subject}</td>
      <td>P${s.totalPaid}</td>
      <td><code>${s.gcashRef}</code></td>
      <td><span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span></td>
      <td>
        <button class="btn btn-secondary btn-small" onclick="viewPaymentReceipt('${s.id}')">View Receipt</button>
        ${s.status === 'Completed' ?
          `<button class="btn btn-secondary btn-small" style="margin-left: 4px;" onclick="openRatingModal('${s.tutorName}')">Rate Tutor</button>` :
          `<button class="btn btn-secondary btn-small" style="margin-left: 4px;" onclick="launchWorkspace('${s.id}')">View Room</button>`
        }
      </td>
    </tr>
  `).join('');

  const totalSpent = userSessions.reduce((sum, s) => sum + (s.totalPaid || 0), 0);
  const spentEl = document.getElementById('student-stat-spent');
  if (spentEl) spentEl.textContent = `P${totalSpent}`;
}

window.viewPaymentReceipt = function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (!session) return;

  document.getElementById('view-receipt-ref').textContent = session.gcashRef || 'GC-9920182341';
  document.getElementById('view-receipt-student').textContent = session.studentName || 'Maria Santos';
  document.getElementById('view-receipt-tutor').textContent = session.tutorName;
  document.getElementById('view-receipt-subject').textContent = session.subject;
  document.getElementById('view-receipt-date').textContent = `${session.date} (${session.timeSlot})`;
  document.getElementById('view-receipt-amount').textContent = `P${session.totalPaid}.00`;

  openModal('modal-view-receipt');
};

// Interactive Session Calendar Engine
function renderStudentCalendar() {
  const studentSessions = state.sessions.filter(s => s.studentName === (state.currentUser ? state.currentUser.name : 'Maria Santos'));
  renderSessionCalendar('student-calendar-container', studentSessions, 'Student');
}

function renderTutorCalendar() {
  const tutorSessions = state.sessions.filter(s => s.tutorName.includes('Alex') || (state.currentUser && s.tutorName === state.currentUser.name));
  renderSessionCalendar('tutor-calendar-container', tutorSessions, 'Tutor');
}

function renderSessionCalendar(containerId, sessionsList, userType) {
  const container = document.getElementById(containerId);
  if (!container) return;

  // Render for March 2026
  const year = 2026;
  const month = 2; // March (0-indexed)
  const monthName = 'March 2026';
  const daysInMonth = 31;
  const startDayOfWeek = 0; // March 1, 2026 is Sunday (0)

  const sessionDatesMap = {};
  sessionsList.forEach(s => {
    sessionDatesMap[s.date] = s;
  });

  let calendarHTML = `
    <div class="calendar-header-bar">
      <h4>${monthName} Session Calendar</h4>
      <span class="sub-text">Highlighted dates indicate active sessions</span>
    </div>
    <div class="calendar-grid">
      <div class="calendar-day-header">Sun</div>
      <div class="calendar-day-header">Mon</div>
      <div class="calendar-day-header">Tue</div>
      <div class="calendar-day-header">Wed</div>
      <div class="calendar-day-header">Thu</div>
      <div class="calendar-day-header">Fri</div>
      <div class="calendar-day-header">Sat</div>
  `;

  // Empty leading cells
  for (let i = 0; i < startDayOfWeek; i++) {
    calendarHTML += `<div class="calendar-day-cell empty"></div>`;
  }

  // Days of March 2026
  for (let day = 1; day <= daysInMonth; day++) {
    const dayFormatted = day < 10 ? '0' + day : day;
    const dateStr = `2026-03-${dayFormatted}`;
    const sessionOnDay = sessionDatesMap[dateStr];

    if (sessionOnDay) {
      calendarHTML += `
        <div class="calendar-day-cell has-session" onclick="selectCalendarDate('${containerId}', '${dateStr}')" title="${sessionOnDay.subject} - ${sessionOnDay.timeSlot}">
          <span>${day}</span>
          <div class="session-badge-dot"></div>
        </div>
      `;
    } else {
      calendarHTML += `
        <div class="calendar-day-cell" onclick="selectCalendarDate('${containerId}', '${dateStr}')">
          <span>${day}</span>
        </div>
      `;
    }
  }

  calendarHTML += `</div>`;
  calendarHTML += `<div id="${containerId}-details" class="calendar-selected-details hidden"></div>`;

  container.innerHTML = calendarHTML;
}

window.selectCalendarDate = function(containerId, dateStr) {
  const detailsBox = document.getElementById(`${containerId}-details`);
  if (!detailsBox) return;

  const sessionOnDate = state.sessions.find(s => s.date === dateStr);

  if (sessionOnDate) {
    detailsBox.innerHTML = `
      <div>
        <strong style="font-size: 1rem;">Session on ${dateStr}:</strong>
        <p class="sub-text" style="margin-top: 2px;">
          ${sessionOnDate.subject} with ${sessionOnDate.studentName} & ${sessionOnDate.tutorName} (${sessionOnDate.timeSlot})
        </p>
      </div>
      <div style="display: flex; gap: 8px;">
        <button class="btn btn-primary btn-small" onclick="launchWorkspace('${sessionOnDate.id}')">Join Session Room</button>
        <button class="btn btn-secondary btn-small" onclick="showToast('Notification sent for session on ${dateStr}!')">Notify Participants</button>
      </div>
    `;
    detailsBox.classList.remove('hidden');
    showToast(`Session found on ${dateStr}: ${sessionOnDate.subject}!`);
  } else {
    detailsBox.innerHTML = `
      <div>
        <strong>Date: ${dateStr}</strong>
        <p class="sub-text" style="margin-top: 2px;">No tutoring session scheduled on this date.</p>
      </div>
    `;
    detailsBox.classList.remove('hidden');
  }
};

function renderTutorUpcoming() {
  const container = document.getElementById('tutor-upcoming-sessions-list');
  if (!container) return;

  const currentTutorName = state.currentUser ? state.currentUser.name : 'Prof. Alex Rivera';
  const tutorSessions = state.sessions.filter(s => s.tutorName === currentTutorName);

  if (tutorSessions.length === 0) {
    container.innerHTML = `<div style="background: var(--panel); padding: 24px; border-radius: 6px; text-align: center; border: 1px solid var(--line); color: var(--ink-soft);"><p>No active teaching sessions scheduled at the moment. New student bookings will appear here automatically.</p></div>`;
    return;
  }

  container.innerHTML = tutorSessions.map(s => `
    <div class="session-card">
      <div class="session-card-info">
        <h4>${s.subject} with Student <strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.studentId || s.studentName}')">${s.studentName}</strong></h4>
        <p>Date: ${s.sessionDate || s.date} | Time: ${s.timeSlot} | Fee: <strong>P${s.hourlyRate}</strong></p>
      </div>
      <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <span class="badge ${s.tutorStarted ? 'badge-success' : 'badge-info'}">${s.tutorStarted ? 'Active Live Session' : s.status}</span>
        ${s.tutorStarted ? `
          <button class="btn btn-primary btn-small" onclick="launchWorkspace('${s.id}')">
            Enter Session Workspace
          </button>
        ` : `
          <button class="btn btn-primary btn-small" onclick="startTutorSession('${s.id}')">
            Start Workspace Session
          </button>
        `}
        <button class="btn btn-secondary btn-small" onclick="requestRescheduleSession('${s.id}')">
          Reschedule
        </button>
        ${s.status !== 'Completed' ? `
          <button class="btn btn-secondary btn-small" onclick="markSessionCompleted('${s.id}')">
            Complete
          </button>
        ` : ''}
      </div>
    </div>
  `).join('');
}

window.requestRescheduleSession = function(sessionId) {
  const newTime = prompt('Enter proposed reschedule time slot (e.g. Tomorrow 03:00 PM):', 'Tomorrow 03:00 PM');
  if (newTime) {
    showToast(`Reschedule request sent to student for session ${sessionId}.`);
  }
};

window.downloadSessionInvoice = function(sessionId) {
  showToast(`Downloading Official Receipt & Invoice for Session ${sessionId}...`);
};

function renderTutorRequests() {
  const tbody = document.getElementById('tutor-requests-table-body');
  if (!tbody) return;

  const currentTutorName = state.currentUser ? state.currentUser.name : 'Prof. Alex Rivera';
  const tutorMatches = state.matches.filter(m => m.tutorName === currentTutorName || m.tutorId === (state.currentUser ? state.currentUser.id : ''));

  if (tutorMatches.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No pending student match requests right now. Check back soon!</td></tr>`;
    return;
  }

  tbody.innerHTML = tutorMatches.map(m => `
    <tr>
      <td><code>${m.id}</code></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${m.studentId || m.studentName}')">${m.studentName}</strong></td>
      <td>${m.subject}</td>
      <td><span class="badge badge-match">${m.score}% Match</span></td>
      <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : 'badge-info'}">${m.status}</span></td>
      <td>
        ${m.status === 'Pending Review' ? `
          <button class="btn btn-primary btn-small" onclick="acceptTutorMatch('${m.id}')">Accept Match</button>
          <button class="btn btn-secondary btn-small" onclick="cancelMatch('${m.id}')">Decline</button>
        ` : `<span class="badge badge-success">Accepted</span>`}
      </td>
    </tr>
  `).join('');
}

function renderTutorEarnings() {
  const tbody = document.getElementById('tutor-earnings-table-body');
  if (!tbody) return;

  const currentTutorName = state.currentUser ? state.currentUser.name : 'Prof. Alex Rivera';
  const tutorSessions = state.sessions.filter(s => s.tutorName === currentTutorName);

  // Update Tutor Dashboard Quick Stat Cards dynamically
  const earningsEl = document.getElementById('tutor-stat-earnings');
  const studentsEl = document.getElementById('tutor-stat-students');
  const sessionsEl = document.getElementById('tutor-stat-sessions');
  const tutorRatingEl = document.getElementById('tutor-stat-rating');

  const totalEarnings = tutorSessions.reduce((sum, s) => sum + Math.round((s.totalPaid || 0) * 0.90), 0);
  const grossRev = tutorSessions.reduce((sum, s) => sum + (s.totalPaid || 0), 0);
  const pendingRev = tutorSessions.filter(s => s.payoutStatus !== 'Paid Out').reduce((sum, s) => sum + Math.round((s.totalPaid || 0) * 0.90), 0);
  const uniqueStudents = new Set(tutorSessions.map(s => s.studentName)).size;
  const currentTutorObj = state.tutors.find(t => t.name === currentTutorName);

  if (earningsEl) earningsEl.textContent = `P${totalEarnings}`;
  if (studentsEl) studentsEl.textContent = uniqueStudents;
  if (sessionsEl) sessionsEl.textContent = tutorSessions.length;
  if (tutorRatingEl) {
    if (currentTutorObj && currentTutorObj.reviewsCount > 0) {
      tutorRatingEl.textContent = `${currentTutorObj.rating} ★`;
    } else {
      tutorRatingEl.textContent = '0.0 ★';
    }
  }

  const grossEl = document.getElementById('tutor-gross-earnings');
  const netEl = document.getElementById('tutor-net-earnings');
  const pendingEl = document.getElementById('tutor-payout-pending');

  if (grossEl) grossEl.textContent = `P${grossRev.toLocaleString()}`;
  if (netEl) netEl.textContent = `P${totalEarnings.toLocaleString()}`;
  if (pendingEl) pendingEl.textContent = `P${pendingRev.toLocaleString()}`;

  const reviewsContainer = document.getElementById('tutor-reviews-list');
  if (reviewsContainer) {
    const ratedSessions = tutorSessions.filter(s => s.rating && s.rating > 0);
    if (ratedSessions.length === 0) {
      reviewsContainer.innerHTML = `<p class="sub-text">No student feedback or reviews received yet.</p>`;
    } else {
      reviewsContainer.innerHTML = ratedSessions.map(s => `
        <div class="session-card" style="background: white; border: 1px solid var(--line); padding: 12px; margin-bottom: 8px;">
          <div>
            <strong>${s.studentName}</strong> <span class="sub-text">(${s.subject})</span>
            <div style="font-size: 0.9rem; margin-top: 4px;">★ ${s.rating}.0 - "${s.feedback || 'Great session!'}"</div>
          </div>
        </div>
      `).join('');
    }
  }

  if (tutorSessions.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No earnings or payout history yet. Completed sessions will generate payout records here.</td></tr>`;
    return;
  }

  tbody.innerHTML = tutorSessions.map(s => {
    const netPayout = Math.round(s.totalPaid * 0.90);
    return `
      <tr>
        <td><code>${s.id}</code></td>
        <td>${s.studentName}</td>
        <td>${s.subject}</td>
        <td>${s.date}</td>
        <td>P${s.totalPaid}</td>
        <td><strong>P${netPayout}</strong></td>
        <td>
          <span class="badge ${s.payoutStatus === 'Paid Out' ? 'badge-success' : 'badge-info'}">${s.payoutStatus || 'Unpaid'}</span>
          <button class="btn btn-secondary btn-small" style="margin-left: 6px;" onclick="downloadSessionInvoice('${s.id}')">Receipt</button>
        </td>
      </tr>
    `;
  }).join('');
}

window.markSessionCompleted = function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (session) {
    session.status = 'Completed';
    renderAllViews();
    showToast(`Session ${sessionId} marked as Completed!`);
  }
};

function initTutorScheduleManager() {
  const saveBtn = document.getElementById('tutor-save-slot-btn');
  const cancelBtn = document.getElementById('tutor-cancel-slot-btn');
  const formBox = document.getElementById('tutor-new-schedule-form');

  cancelBtn?.addEventListener('click', () => formBox?.classList.add('hidden'));

  saveBtn?.addEventListener('click', () => {
    const dateVal = document.getElementById('tutor-slot-date').value;
    const timeVal = document.getElementById('tutor-slot-time').value;
    const subjectVal = document.getElementById('tutor-slot-subject').value;

    if (!dateVal || !timeVal || !subjectVal) {
      alert('Please fill out all calendar slot details.');
      return;
    }

    const newSch = {
      id: 'SCH-' + Math.floor(100 + Math.random() * 900),
      tutorName: state.currentUser ? state.currentUser.name : 'Prof. Alex Rivera',
      dateSlot: `${dateVal} (${timeVal})`,
      subject: subjectVal,
      status: 'Available'
    };

    state.schedules.unshift(newSch);
    formBox?.classList.add('hidden');
    renderAdminSchedule();
    showToast('New calendar availability slot saved!');
  });
}

// AI TUTOR MATCHING FEATURE MODULE
function initAIMatching() {
  const form = document.getElementById('ai-matching-form');
  const resultsBox = document.getElementById('ai-match-results');
  const resultsList = document.getElementById('ai-match-cards-list');
  const cancelBtn = document.getElementById('cancel-ai-btn');

  cancelBtn?.addEventListener('click', () => closeModal('modal-ai-matching'));

  form?.addEventListener('submit', (e) => {
    e.preventDefault();

    const subject = document.getElementById('match-subject').value;
    const level = document.getElementById('match-level').value;
    const style = document.getElementById('match-style').value;
    const time = document.getElementById('match-time').value;

    // Available active tutors
    let availableTutors = state.tutors.filter(t => !t.deactivated);

    // If no tutors exist in state yet, provide an AI recommended tutor candidate
    if (availableTutors.length === 0) {
      availableTutors = [{
        id: 'tut-recommended',
        name: 'Prof. Alex Rivera',
        initials: 'AR',
        rating: 5.0,
        reviewsCount: 0,
        hourlyRate: 350,
        subjects: [subject, 'Mathematics'],
        learningStyles: [style],
        bio: `${subject} Specialist Tutor paired via AI Matching.`,
        availableDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        availabilitySlots: ['09:00 AM', '02:00 PM', '04:00 PM'],
        available: true,
        deactivated: false
      }];
    }

    const matches = availableTutors.map(tutor => {
      let score = 80;

      const subjStr = Array.isArray(tutor.subjects) ? tutor.subjects.join(' ') : (tutor.subjects || '');
      if (subjStr.toLowerCase().includes(subject.toLowerCase())) score += 15;

      const styles = Array.isArray(tutor.learningStyles) ? tutor.learningStyles : [tutor.learningStyles || ''];
      if (styles.some(s => s.toLowerCase().includes(style.toLowerCase()) || style.toLowerCase().includes(s.toLowerCase()))) score += 10;

      const compatibility = Math.min(score, 98);

      return { ...tutor, matchScore: compatibility };
    }).sort((a, b) => b.matchScore - a.matchScore);

    const topMatch = matches[0];
    const newMatchRecord = {
      id: 'MATCH-' + Math.floor(100 + Math.random() * 900),
      studentId: state.currentUser ? state.currentUser.id : 'STU-101',
      studentName: state.currentUser ? state.currentUser.name : 'Maria Santos',
      tutorId: topMatch.id,
      tutorName: topMatch.name,
      subject: subject,
      score: topMatch.matchScore,
      status: 'Pending Review',
      matchReason: `Matched based on ${subject} expertise and ${style} learning preference.`
    };

    state.matches.unshift(newMatchRecord);

    try {
      fetch('api/matches.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newMatchRecord)
      });
    } catch (e) { console.log('Offline match API fallback'); }

    resultsList.innerHTML = matches.map(t => {
      const subjDisplay = Array.isArray(t.subjects) ? t.subjects.join(', ') : (t.subjects || subject);
      return `
        <div class="session-card" style="background: white; border: 1px solid var(--line); margin-bottom: 12px; padding: 14px; border-radius: 6px;">
          <div class="session-card-info">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div class="tutor-avatar" style="cursor: pointer;" onclick="viewTutorProfile('${t.id}')">${t.initials || t.name.split(' ').map(n=>n[0]).join('')}</div>
              <div>
                <h4 style="font-size: 1.1rem; margin: 0; cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${t.id}')">${t.name}</h4>
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                  <span class="badge badge-match">${t.matchScore}% Match Score</span>
                  <span style="font-size: 0.85rem; font-weight: 600;">★ ${t.rating ? t.rating : '0.0'}</span>
                </div>
              </div>
            </div>
            <p class="sub-text" style="margin-top: 10px; margin-bottom: 0;">
              <strong>Subjects:</strong> ${subjDisplay} | <strong>Rate:</strong> P${t.hourlyRate || 350}/hr
            </p>
          </div>
          <button class="btn btn-primary btn-small margin-top" onclick="selectMatchedTutor('${t.id}', '${subject}')">
            Choose Tutor & Book Session
          </button>
        </div>
      `;
    }).join('');

    resultsBox.classList.remove('hidden');
    renderAdminMatching();
    showToast('AI Tutor Matching recommendation generated!');
  });
}

window.selectMatchedTutor = function(tutorId, subject) {
  closeModal('modal-ai-matching');
  openCalendarBooking(tutorId, subject);
};

// CALENDAR SCHEDULING FEATURE MODULE
function initCalendarBooking() {
  const cancelBtn = document.getElementById('cancel-booking-btn');
  const proceedBtn = document.getElementById('proceed-to-payment-btn');

  cancelBtn?.addEventListener('click', () => closeModal('modal-calendar'));

  proceedBtn?.addEventListener('click', () => {
    if (!state.activeBooking.timeSlot) {
      alert('Please select an available time slot before proceeding.');
      return;
    }
    closeModal('modal-calendar');
    openGCashPayment();
  });

  const picker = document.getElementById('booking-date-picker');
  if (picker) {
    const today = new Date().toISOString().split('T')[0];
    picker.value = today;
    picker.min = today;
    picker.addEventListener('change', (e) => {
      state.activeBooking.date = e.target.value;
    });
  }
}

window.openCalendarBooking = function(tutorId, preferredSubject = null) {
  const tutor = state.tutors.find(t => t.id === tutorId);
  if (!tutor) return;

  const subject = preferredSubject || tutor.subjects[0];
  const hourlyRate = tutor.hourlyRate;
  const serviceFee = Math.round(hourlyRate * 0.10);
  const total = hourlyRate + serviceFee;

  state.activeBooking = {
    tutorId: tutor.id,
    tutorName: tutor.name,
    subject: subject,
    date: document.getElementById('booking-date-picker')?.value || '2026-03-16',
    timeSlot: null,
    hourlyRate: hourlyRate,
    serviceFee: serviceFee,
    total: total,
    paymentMethod: 'GCash'
  };

  const preview = document.getElementById('selected-tutor-preview');
  preview.innerHTML = `
    <div style="display: flex; align-items: center; gap: 12px; background: var(--panel); padding: 12px; border-radius: 4px;">
      <div class="tutor-avatar" style="cursor: pointer;" onclick="viewTutorProfile('${tutor.id}')">${tutor.initials}</div>
      <div>
        <h4 style="margin: 0; cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${tutor.id}')">${tutor.name}</h4>
        <span class="sub-text">Subject: <strong>${subject}</strong></span>
      </div>
    </div>
  `;

  const slotsContainer = document.getElementById('time-slots-container');
  const slots = (tutor.availabilitySlots && tutor.availabilitySlots.length > 0) ? tutor.availabilitySlots : ['09:00 AM', '02:00 PM', '04:00 PM'];
  slotsContainer.innerHTML = slots.map(slot => `
    <div class="slot-chip" onclick="selectTimeSlot(this, '${slot}')">${slot}</div>
  `).join('');

  document.getElementById('summary-hourly-fee').textContent = `P${hourlyRate}`;
  document.getElementById('summary-service-fee').textContent = `P${serviceFee}`;
  document.getElementById('summary-total-fee').textContent = `P${total}`;

  openModal('modal-calendar');
};

window.selectTimeSlot = function(element, slot) {
  document.querySelectorAll('.slot-chip').forEach(chip => chip.classList.remove('selected'));
  element.classList.add('selected');
  state.activeBooking.timeSlot = slot;
};

// ONLINE PAYMENT INTEGRATION MODULE
function initGCashPayment() {
  const step1Next = document.getElementById('gcash-step1-next-btn');
  const step2Pay = document.getElementById('gcash-confirm-pay-btn');
  const step3Done = document.getElementById('gcash-done-btn');
  const closeBtn = document.getElementById('close-gcash-modal');
  const paymentMethodSelect = document.getElementById('payment-method-select');

  paymentMethodSelect?.addEventListener('change', (e) => {
    const selectedMethod = e.target.value;
    state.activeBooking.paymentMethod = selectedMethod;
    const headerTitle = document.getElementById('payment-method-header-title');
    if (headerTitle) headerTitle.textContent = `${selectedMethod} Payment`;
  });

  closeBtn?.addEventListener('click', () => closeModal('modal-gcash'));

  step1Next?.addEventListener('click', () => {
    const phone = document.getElementById('gcash-mobile').value;
    if (phone.length < 10) {
      alert('Please enter a valid mobile / account number.');
      return;
    }
    document.getElementById('gcash-step-1').classList.add('hidden');
    document.getElementById('gcash-step-2').classList.remove('hidden');
  });

  step2Pay?.addEventListener('click', () => {
    const refPrefix = state.activeBooking.paymentMethod === 'GCash' ? 'GC-' : 'PM-';
    const randomRef = refPrefix + Math.floor(1000000000 + Math.random() * 9000000000);
    const newSessionId = 'SESS-' + Math.floor(100 + Math.random() * 900);

    const b = state.activeBooking;
    const studentName = state.currentUser ? state.currentUser.name : 'Maria Santos';

    const newSession = {
      id: newSessionId,
      studentName: studentName,
      tutorId: b.tutorId,
      tutorName: b.tutorName,
      subject: b.subject,
      date: b.date,
      timeSlot: b.timeSlot,
      hourlyRate: b.hourlyRate,
      commissionFee: b.serviceFee,
      totalPaid: b.total,
      gcashRef: randomRef,
      status: 'Confirmed',
      payoutStatus: 'Unpaid',
      notes: `Booked via ${b.paymentMethod}.`
    };

    state.sessions.unshift(newSession);

    const newPayObj = {
      id: 'PAY-' + Math.floor(100 + Math.random() * 900),
      studentId: state.currentUser ? state.currentUser.id : 'STU-101',
      studentName: studentName,
      tutorId: b.tutorId,
      tutorName: b.tutorName,
      method: b.paymentMethod,
      refNo: randomRef,
      amount: b.total,
      status: 'Confirmed',
      payoutStatus: 'Pending'
    };
    state.payments.unshift(newPayObj);

    try {
      fetch('api/sessions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newSession)
      });
      fetch('api/payments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newPayObj)
      });
    } catch (e) { console.log('Offline booking API fallback'); }

    state.notifications.unshift({
      id: 'notif-' + Date.now(),
      targetRole: 'student',
      target: studentName,
      title: 'Session Booked & Paid!',
      message: `Your ${b.subject} session with ${b.tutorName} is confirmed for ${b.date} at ${b.timeSlot}. Ref: ${randomRef}`,
      time: 'Just now',
      read: false
    });

    state.notifications.unshift({
      id: 'notif-tutor-' + Date.now(),
      targetRole: 'tutor',
      target: b.tutorName,
      title: 'New Student Session Booked',
      message: `${studentName} booked a ${b.subject} tutoring session for ${b.date} at ${b.timeSlot}.`,
      time: 'Just now',
      read: false
    });

    document.getElementById('gcash-receipt-ref').textContent = randomRef;
    document.getElementById('gcash-receipt-method').textContent = b.paymentMethod;
    document.getElementById('gcash-receipt-date').textContent = `${b.date}, ${b.timeSlot}`;
    document.getElementById('gcash-receipt-amount').textContent = `P${b.total}.00`;

    document.getElementById('gcash-step-2').classList.add('hidden');
    document.getElementById('gcash-step-3').classList.remove('hidden');

    renderAllViews();
    showToast(`${b.paymentMethod} payment confirmed! Status: Confirmed.`);
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
  b.paymentMethod = 'GCash';
  const method = 'GCash';
  const selectEl = document.getElementById('payment-method-select');
  if (selectEl) selectEl.value = 'GCash';

  document.getElementById('payment-method-header-title').textContent = `${method} Payment`;
  document.getElementById('gcash-modal-amount').textContent = `P${b.total}.00`;
  document.getElementById('gcash-confirm-pay-btn').textContent = `Confirm & Pay P${b.total}.00`;

  const detailsContainer = document.getElementById('gcash-booking-summary-preview');
  if (detailsContainer) {
    detailsContainer.innerHTML = `
      <div class="receipt-box" style="margin-top: 10px; font-size: 0.88rem; text-align: left; background: rgba(255,255,255,0.9); border: 1px solid var(--line); padding: 10px; border-radius: 4px;">
        <div><strong>Tutor:</strong> ${b.tutorName}</div>
        <div><strong>Subject:</strong> ${b.subject}</div>
        <div><strong>Schedule:</strong> ${b.date} (${b.timeSlot || '02:00 PM'})</div>
      </div>
    `;
  }

  openModal('modal-gcash');
}

// REAL-TIME ROLE-FILTERED NOTIFICATIONS MODULE
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

  markReadBtn?.addEventListener('click', () => {
    const role = state.currentRole;
    state.notifications.forEach(n => {
      if (n.targetRole === role || n.targetRole === 'all') {
        n.read = true;
      }
    });
    renderNotifDrawer();
    updateNotificationBadge();
    showToast('Role notifications marked as read.');
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
      <strong>${n.title}</strong>
      <p>${n.message}</p>
      <div class="notif-time">${n.time}</div>
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

// MATCHING SESSION VIRTUAL WORKSPACE MODULE
function initWorkspaceSession() {
  const closeBtn = document.getElementById('close-workspace-btn');
  const endBtn = document.getElementById('end-session-btn');
  const clearNotesBtn = document.getElementById('clear-notes-btn');
  const saveNotesBtn = document.getElementById('save-notes-btn');
  const chatForm = document.getElementById('chat-form');

  closeBtn?.addEventListener('click', () => closeModal('modal-session-workspace'));

  endBtn?.addEventListener('click', () => {
    if (confirm('Are you sure you want to complete and end this tutoring session?')) {
      if (state.activeWorkspaceSession) {
        state.activeWorkspaceSession.status = 'Completed';
        renderAllViews();
      }
      closeModal('modal-session-workspace');
      openRatingModal(state.activeWorkspaceSession?.tutorName || 'Prof. Alex Rivera');
    }
  });

  clearNotesBtn?.addEventListener('click', () => {
    document.getElementById('session-shared-notes').value = '';
  });

  saveNotesBtn?.addEventListener('click', async () => {
    const notesVal = document.getElementById('session-shared-notes').value;
    if (state.activeWorkspaceSession) {
      state.activeWorkspaceSession.notes = notesVal;
      const s = state.sessions.find(x => x.id === state.activeWorkspaceSession.id);
      if (s) s.notes = notesVal;

      try {
        await fetch('api/sessions.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: state.activeWorkspaceSession.id, notes: notesVal })
        });
      } catch (err) { console.log('Offline notes API fallback'); }

      showToast('Session notes snapshot saved successfully!');
    }
  });

  chatForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msgText = input.value.trim();
    if (!msgText || !state.activeWorkspaceSession) return;

    const senderName = state.currentUser ? state.currentUser.name : (state.currentRole === 'tutor' ? 'Prof. Alex Rivera' : 'Maria Santos');
    const senderRole = state.currentRole || 'student';
    const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const chatContainer = document.getElementById('chat-messages-container');
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${senderRole}`;
    msgDiv.innerHTML = `<strong>${senderName}:</strong> ${msgText} <span style="font-size: 0.75rem; color: var(--muted); opacity: 0.8;">(${timestamp})</span>`;
    chatContainer.appendChild(msgDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    input.value = '';

    try {
      await fetch('api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          sessionId: state.activeWorkspaceSession.id,
          senderName: senderName,
          senderRole: senderRole,
          message: msgText,
          timestamp: timestamp
        })
      });
    } catch (err) { console.log('Offline chat API fallback'); }
  });
}

window.launchWorkspace = async function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (session) {
    if (state.currentRole === 'student' && !session.tutorStarted) {
      showToast(`Session Pending - Please wait for Tutor ${session.tutorName} to start the session.`, 'warning');
      return;
    }
    state.activeWorkspaceSession = session;
    document.getElementById('workspace-session-title').textContent = `Live Session: ${session.subject}`;
    document.getElementById('workspace-participants').textContent = `${session.studentName} (Student) & ${session.tutorName} (Tutor)`;

    const notesInput = document.getElementById('session-shared-notes');
    if (notesInput) notesInput.value = session.notes || '';

    // Load Chat Messages
    const chatContainer = document.getElementById('chat-messages-container');
    if (chatContainer) {
      chatContainer.innerHTML = `<p class="sub-text center-text">Loading workspace chat...</p>`;
      try {
        const res = await fetch(`api/chat.php?session_id=${sessionId}`);
        if (res.ok) {
          const data = await res.json();
          if (data.data && data.data.length > 0) {
            chatContainer.innerHTML = data.data.map(m => `
              <div class="chat-msg ${m.senderRole}">
                <strong>${m.senderName}:</strong> ${m.message} <span style="font-size:0.75rem; color:var(--muted); opacity:0.8;">(${m.timestamp})</span>
              </div>
            `).join('');
          } else {
            chatContainer.innerHTML = `<p class="sub-text center-text">No workspace chat messages yet. Start conversation below!</p>`;
          }
        }
      } catch (err) {
        chatContainer.innerHTML = `<p class="sub-text center-text">Offline chat mode active.</p>`;
      }
    }
  }
  openModal('modal-session-workspace');
};

// Rating Modal
function openRatingModal(tutorName) {
  document.getElementById('rating-tutor-name').textContent = tutorName;
  openModal('modal-rating');

  let selectedRating = 5;

  document.querySelectorAll('.star-rating .star').forEach(star => {
    star.onclick = function() {
      selectedRating = parseInt(this.getAttribute('data-value'));
      document.querySelectorAll('.star-rating .star').forEach(s => {
        const sVal = parseInt(s.getAttribute('data-value'));
        if (sVal <= selectedRating) {
          s.classList.add('active');
        } else {
          s.classList.remove('active');
        }
      });
    };
  });

  document.getElementById('submit-rating-btn').onclick = async function() {
    const comment = document.getElementById('rating-comment')?.value || '';
    if (state.activeWorkspaceSession) {
      state.activeWorkspaceSession.rating = selectedRating;
      state.activeWorkspaceSession.feedback = comment;
      state.activeWorkspaceSession.status = 'Completed';

      const s = state.sessions.find(x => x.id === state.activeWorkspaceSession.id);
      if (s) {
        s.rating = selectedRating;
        s.feedback = comment;
        s.status = 'Completed';
      }

      // Update tutor average rating and review count
      const tutor = state.tutors.find(t => t.id === state.activeWorkspaceSession.tutorId || t.name === state.activeWorkspaceSession.tutorName);
      if (tutor) {
        const cnt = tutor.reviewsCount || 0;
        const newCount = cnt + 1;
        const newRating = parseFloat((((tutor.rating || 5.0) * cnt + selectedRating) / newCount).toFixed(1));
        tutor.reviewsCount = newCount;
        tutor.rating = newRating;

        try {
          await fetch('api/tutors.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: tutor.id, rating: newRating, reviewsCount: newCount })
          });
        } catch (err) { console.log('Offline API fallback'); }
      }

      try {
        await fetch('api/sessions.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: state.activeWorkspaceSession.id, rating: selectedRating, feedback: comment, status: 'Completed' })
        });
      } catch (err) { console.log('Offline API fallback'); }
    }

    closeModal('modal-rating');
    renderAllViews();
    showToast('Thank you for submitting feedback!');
  };

  document.getElementById('close-rating-modal').onclick = function() {
    closeModal('modal-rating');
  };
}

// ADMIN MONITORING MODULE
function initAdminView() {
  const reportBtn = document.getElementById('generate-admin-report-btn');
  const closeReport = document.getElementById('close-report-modal');
  const doneReport = document.getElementById('done-report-btn');

  document.querySelectorAll('.admin-tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      document.querySelectorAll('.admin-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.admin-tab-content').forEach(c => c.classList.remove('active'));

      e.target.classList.add('active');
      const targetTabId = e.target.getAttribute('data-tab');
      document.getElementById(targetTabId)?.classList.add('active');
    });
  });

  document.querySelectorAll('.admin-subtab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const parentTab = e.target.closest('.admin-tab-content');
      if (!parentTab) return;

      parentTab.querySelectorAll('.admin-subtab-btn').forEach(b => b.classList.remove('active'));
      parentTab.querySelectorAll('.admin-subtab-content').forEach(c => c.classList.remove('active'));

      e.target.classList.add('active');
      const targetSubtabId = e.target.getAttribute('data-subtab');
      document.getElementById(targetSubtabId)?.classList.add('active');
    });
  });

  document.getElementById('admin-add-schedule-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const tutorId = document.getElementById('admin-sch-tutor-select').value;
    const tutorObj = state.tutors.find(t => t.id === tutorId) || state.tutors[0];
    const tutorName = tutorObj ? tutorObj.name : 'Tutor';
    const subject = document.getElementById('admin-sch-subject').value;
    const dateSlot = document.getElementById('admin-sch-dateslot').value;

    const newSch = {
      id: 'sch-' + Math.floor(100 + Math.random() * 900),
      tutorId: tutorId,
      tutorName: tutorName,
      dateSlot: dateSlot,
      subject: subject,
      status: 'Available'
    };

    state.schedules.unshift(newSch);

    try {
      await fetch('api/schedules.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newSch)
      });
    } catch (err) { console.log('Offline schedule API fallback'); }

    renderAdminSchedule();
    showToast(`New calendar schedule created for ${tutorName}!`);
    document.getElementById('admin-add-schedule-form').reset();
  });

  document.getElementById('admin-send-notif-btn')?.addEventListener('click', () => {
    const target = document.getElementById('admin-notif-target').value;
    const msg = document.getElementById('admin-notif-message').value;
    if (!msg) {
      alert('Please enter a message to send.');
      return;
    }

    state.notifications.unshift({
      id: 'notif-' + Date.now(),
      targetRole: target,
      target: target === 'all' ? 'All Users' : target,
      title: 'System Broadcast',
      message: msg,
      time: 'Just now',
      read: false
    });

    document.getElementById('admin-notif-message').value = '';
    renderAdminNotifications();
    updateNotificationBadge();
    showToast('Notification broadcast sent to ' + target + '!');
  });

  document.getElementById('report-filter-all')?.addEventListener('click', (e) => {
    state.activeReportFilter = 'all';
    updateReportFilterUI(e.target);
  });
  document.getElementById('report-filter-weekly')?.addEventListener('click', (e) => {
    state.activeReportFilter = 'weekly';
    updateReportFilterUI(e.target);
  });
  document.getElementById('report-filter-monthly')?.addEventListener('click', (e) => {
    state.activeReportFilter = 'monthly';
    updateReportFilterUI(e.target);
  });

  reportBtn?.addEventListener('click', () => {
    document.getElementById('report-generated-date').textContent = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    document.getElementById('report-total-sessions').textContent = state.sessions.length;

    const totalVolume = state.sessions.reduce((acc, s) => acc + s.totalPaid, 0);
    const totalNet = state.sessions.reduce((acc, s) => acc + s.commissionFee, 0);

    document.getElementById('report-total-volume').textContent = `P${totalVolume}`;
    document.getElementById('report-net-revenue').textContent = `P${totalNet}`;

    const tbody = document.getElementById('report-table-body');
    tbody.innerHTML = state.sessions.map(s => `
      <tr>
        <td><code>${s.id}</code></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.studentId || s.studentName}')">${s.studentName}</span></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></td>
        <td>${s.subject}</td>
        <td>${s.status}</td>
        <td>P${s.totalPaid}</td>
      </tr>
    `).join('');

    openModal('modal-admin-report');
  });

  closeReport?.addEventListener('click', () => closeModal('modal-admin-report'));
  doneReport?.addEventListener('click', () => closeModal('modal-admin-report'));

  // Reassign tutor modal listener
  document.getElementById('close-reassign-modal')?.addEventListener('click', () => closeModal('modal-reassign-tutor'));
  document.getElementById('reassign-tutor-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const matchId = document.getElementById('reassign-match-id').value;
    const newTutorName = document.getElementById('reassign-tutor-select').value;

    const match = state.matches.find(m => m.id === matchId);
    if (match) {
      match.tutorName = newTutorName;
      closeModal('modal-reassign-tutor');
      renderAdminMatching();
      showToast(`Tutor for match ${matchId} reassigned to ${newTutorName}.`);
    }
  });
}

function updateReportFilterUI(activeBtn) {
  document.querySelectorAll('#tab-reports .filter-group button').forEach(b => b.classList.remove('active-filter'));
  activeBtn.classList.add('active-filter');
  renderAdminReports();
}

function renderAdminStudents() {
  const tbody = document.getElementById('admin-students-table-body');
  const tbodyValidate = document.getElementById('admin-students-validate-table-body');

  if (tbody) {
    tbody.innerHTML = state.students.map(s => `
      <tr>
        <td><code>${s.id}</code></td>
        <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.id}')">${s.name}</strong></td>
        <td>${s.email}</td>
        <td>${s.grade}</td>
        <td><span class="badge ${s.validated ? 'badge-success' : 'badge-info'}">${s.validated ? 'Validated' : 'Pending Validation'}</span></td>
        <td><span class="badge ${s.deactivated ? 'badge-danger' : 'badge-success'}">${s.deactivated ? 'Deactivated' : 'Active'}</span></td>
        <td>
          <button class="btn btn-secondary btn-small" onclick="viewStudentProfile('${s.id}')">Profile</button>
          <button class="btn btn-secondary btn-small" onclick="toggleValidateStudent('${s.id}')">
            ${s.validated ? 'Revoke' : 'Validate Account'}
          </button>
          <button class="btn btn-secondary btn-small" onclick="toggleDeactivateStudent('${s.id}')">
            ${s.deactivated ? 'Activate' : 'Deactivate'}
          </button>
        </td>
      </tr>
    `).join('');
  }

  if (tbodyValidate) {
    const pending = state.students.filter(s => !s.validated);
    if (pending.length === 0) {
      tbodyValidate.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">All registered students have been validated.</td></tr>`;
    } else {
      tbodyValidate.innerHTML = pending.map(s => `
        <tr>
          <td><code>${s.id}</code></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.id}')">${s.name}</strong></td>
          <td>${s.email}</td>
          <td>${s.grade}</td>
          <td><span class="badge badge-info">Pending Validation</span></td>
          <td>
            <button class="btn btn-primary btn-small" onclick="toggleValidateStudent('${s.id}')">Validate Account</button>
          </td>
        </tr>
      `).join('');
    }
  }

  const el = document.getElementById('admin-stat-total-students');
  if (el) el.textContent = state.students.length;
}

window.toggleValidateStudent = function(studentId) {
  const student = state.students.find(s => s.id === studentId);
  if (student) {
    student.validated = !student.validated;
    renderAdminStudents();
    showToast(`Validation updated for ${student.name}.`);
  }
};

window.toggleDeactivateStudent = function(studentId) {
  const student = state.students.find(s => s.id === studentId);
  if (student) {
    student.deactivated = !student.deactivated;
    renderAdminStudents();
    showToast(`Account status updated for ${student.name}.`);
  }
};

function renderAdminTutors() {
  const tbody = document.getElementById('admin-tutors-table-body');
  if (!tbody) return;

  tbody.innerHTML = state.tutors.map(t => `
    <tr>
      <td><code>${t.id}</code></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${t.id}')">${t.name}</strong></td>
      <td>${t.subjects.join(', ')}</td>
      <td><span class="badge ${t.approvalStatus === 'Approved' ? 'badge-success' : 'badge-info'}">${t.approvalStatus}</span></td>
      <td><span class="badge ${t.deactivated ? 'badge-danger' : 'badge-success'}">${t.deactivated ? 'Deactivated' : 'Active'}</span></td>
      <td>
        ${t.approvalStatus !== 'Approved' ? `<button class="btn btn-primary btn-small" onclick="approveTutorApplication('${t.id}')">Approve</button>` : ''}
        <button class="btn btn-secondary btn-small" onclick="toggleDeactivateTutor('${t.id}')">
          ${t.deactivated ? 'Activate' : 'Deactivate'}
        </button>
      </td>
    </tr>
  `).join('');

  document.getElementById('admin-stat-total-tutors').textContent = state.tutors.length;
}


window.approveTutorApplication = function(tutorId) {
  const tutor = state.tutors.find(t => t.id === tutorId);
  if (tutor) {
    tutor.approvalStatus = 'Approved';
    renderAdminTutors();
    showToast(`Tutor application approved for ${tutor.name}.`);
  }
};

window.toggleDeactivateTutor = function(tutorId) {
  const tutor = state.tutors.find(t => t.id === tutorId);
  if (tutor) {
    tutor.deactivated = !tutor.deactivated;
    renderAdminTutors();
    renderTutorDirectory();
    showToast(`Account status updated for ${tutor.name}.`);
  }
};

function renderAdminSubjects() {
  const tbody = document.getElementById('admin-subjects-table-body');
  if (!tbody) return;

  tbody.innerHTML = state.subjects.map(s => `
    <tr>
      <td><code>${s.id}</code></td>
      <td><strong>${s.name}</strong></td>
      <td><span class="badge badge-info">${s.category}</span></td>
      <td>
        <button class="btn btn-secondary btn-small" onclick="editSubject('${s.id}')">Edit</button>
        <button class="btn btn-secondary btn-small" onclick="deleteSubject('${s.id}')">Remove</button>
      </td>
    </tr>
  `).join('');
}

function renderAdminMatching() {
  const tbody = document.getElementById('admin-matching-table-body');
  const tbodyApprove = document.getElementById('admin-matching-approve-table-body');
  const tbodyCancel = document.getElementById('admin-matching-cancel-table-body');

  if (tbody) {
    tbody.innerHTML = state.matches.map(m => `
      <tr>
        <td><code>${m.id}</code></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${m.studentId || m.studentName}')">${m.studentName}</span></td>
        <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${m.tutorId || m.tutorName}')">${m.tutorName}</strong></td>
        <td>${m.subject}</td>
        <td><span class="badge badge-match">${m.score}% Match</span></td>
        <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${m.status}</span></td>
        <td>
          ${m.status === 'Pending Review' ? `
            <button class="btn btn-primary btn-small" onclick="approveMatch('${m.id}')">Approve</button>
            <button class="btn btn-secondary btn-small" onclick="openReassignModal('${m.id}')">Reassign</button>
            <button class="btn btn-secondary btn-small" onclick="cancelMatch('${m.id}')">Cancel</button>
          ` : `<span class="sub-text">Processed</span>`}
        </td>
      </tr>
    `).join('');
  }

  if (tbodyApprove) {
    const pendingMatches = state.matches.filter(m => m.status === 'Pending Review');
    if (pendingMatches.length === 0) {
      tbodyApprove.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No matching sessions pending approval.</td></tr>`;
    } else {
      tbodyApprove.innerHTML = pendingMatches.map(m => `
        <tr>
          <td><code>${m.id}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${m.studentId || m.studentName}')">${m.studentName}</span></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${m.tutorId || m.tutorName}')">${m.tutorName}</strong></td>
          <td>${m.subject}</td>
          <td><span class="badge badge-match">${m.score}% Match</span></td>
          <td>
            <button class="btn btn-primary btn-small" onclick="approveMatch('${m.id}')">Approve Matching Session</button>
          </td>
        </tr>
      `).join('');
    }
  }

  if (tbodyCancel) {
    const activeMatches = state.matches.filter(m => m.status !== 'Cancelled');
    if (activeMatches.length === 0) {
      tbodyCancel.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No active matching sessions available to cancel.</td></tr>`;
    } else {
      tbodyCancel.innerHTML = activeMatches.map(m => `
        <tr>
          <td><code>${m.id}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${m.studentId || m.studentName}')">${m.studentName}</span></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${m.tutorId || m.tutorName}')">${m.tutorName}</strong></td>
          <td>${m.subject}</td>
          <td><span class="badge badge-info">${m.status}</span></td>
          <td>
            <button class="btn btn-secondary btn-small" onclick="cancelMatch('${m.id}')">Cancel Matching Session</button>
          </td>
        </tr>
      `).join('');
    }
  }
}

window.openReassignModal = function(matchId) {
  document.getElementById('reassign-match-id').value = matchId;
  const select = document.getElementById('reassign-tutor-select');
  select.innerHTML = state.tutors.filter(t => !t.deactivated).map(t => `
    <option value="${t.name}">${t.name} (${t.subjects.join(', ')})</option>
  `).join('');
  openModal('modal-reassign-tutor');
};

window.approveMatch = function(matchId) {
  const m = state.matches.find(x => x.id === matchId);
  if (m) {
    m.status = 'Approved';
    renderAdminMatching();
    showToast(`Matching session ${matchId} approved!`);
  }
};

window.cancelMatch = function(matchId) {
  const m = state.matches.find(x => x.id === matchId);
  if (m) {
    m.status = 'Cancelled';
    renderAdminMatching();
    showToast(`Matching session ${matchId} cancelled.`);
  }
};

function renderAdminSchedule() {
  const tbody = document.getElementById('admin-schedule-table-body');
  const tutorSelect = document.getElementById('admin-sch-tutor-select');

  if (tutorSelect && state.tutors.length > 0) {
    tutorSelect.innerHTML = state.tutors.filter(t => !t.deactivated).map(t => `
      <option value="${t.id}">${t.name} (${t.subjects.join(', ')})</option>
    `).join('');
  }

  if (tbody) {
    if (state.schedules.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">No schedule slots created yet. Use New Calendar Schedule to add one.</td></tr>`;
    } else {
      tbody.innerHTML = state.schedules.map(sch => `
        <tr>
          <td><code>${sch.id}</code></td>
          <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${sch.tutorId || sch.tutorName}')">${sch.tutorName}</strong></td>
          <td>${sch.dateSlot}</td>
          <td>${sch.subject}</td>
          <td><span class="badge ${sch.status === 'Available' ? 'badge-success' : 'badge-info'}">${sch.status}</span></td>
          <td>
            <button class="btn btn-secondary btn-small" onclick="deleteScheduleSlot('${sch.id}')">Remove Slot</button>
          </td>
        </tr>
      `).join('');
    }
  }
}

window.deleteScheduleSlot = function(schId) {
  const idx = state.schedules.findIndex(s => s.id === schId);
  if (idx !== -1) {
    state.schedules.splice(idx, 1);
    renderAdminSchedule();
    showToast('Schedule slot removed.');
  }
};

function renderAdminPayments() {
  const tbody = document.getElementById('admin-payments-table-body');
  const tbodyConfirm = document.getElementById('admin-payments-confirm-table-body');

  if (tbody) {
    tbody.innerHTML = state.payments.map(p => `
      <tr>
        <td><code>${p.id}</code></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${p.studentId || p.studentName}')">${p.studentName}</span></td>
        <td>${p.method}</td>
        <td><code>${p.refNo}</code></td>
        <td>P${p.amount}</td>
        <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${p.status}</span></td>
      </tr>
    `).join('');
  }

  if (tbodyConfirm) {
    const pendingPay = state.payments.filter(p => p.status === 'Pending Confirmation' || p.status === 'Pending');
    if (pendingPay.length === 0) {
      tbodyConfirm.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--ink-soft); padding: 20px;">All payment records confirmed.</td></tr>`;
    } else {
      tbodyConfirm.innerHTML = pendingPay.map(p => `
        <tr>
          <td><code>${p.id}</code></td>
          <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${p.studentId || p.studentName}')">${p.studentName}</span></td>
          <td><code>${p.refNo}</code></td>
          <td>P${p.amount}</td>
          <td><span class="badge badge-info">${p.status}</span></td>
          <td>
            <button class="btn btn-primary btn-small" onclick="confirmPayment('${p.id}')">Confirm Payment Status</button>
          </td>
        </tr>
      `).join('');
    }
  }

  const totalVol = state.payments.reduce((sum, p) => sum + p.amount, 0);
  const totalComm = Math.round(totalVol * 0.10);

  const elV = document.getElementById('admin-stat-total-volume');
  const elC = document.getElementById('admin-stat-platform-commission');
  if (elV) elV.textContent = `P${totalVol.toLocaleString()}`;
  if (elC) elC.textContent = `P${totalComm.toLocaleString()}`;
}

window.confirmPayment = function(payId) {
  const p = state.payments.find(x => x.id === payId);
  if (p) {
    p.status = 'Confirmed';
    renderAdminPayments();
    showToast(`Payment ${payId} confirmed.`);
  }
};

window.processTutorPayout = function(payId) {
  const p = state.payments.find(x => x.id === payId);
  if (p) {
    p.payoutStatus = 'Paid Out';
    renderAdminPayments();
    renderTutorEarnings();
    showToast(`Tutor payout processed for transaction ${payId}.`);
  }
};

function renderAdminNotifications() {
  const logContainer = document.getElementById('admin-notifications-log');
  if (!logContainer) return;

  logContainer.innerHTML = state.notifications.map(n => `
    <div class="session-card" style="background: white; border: 1px solid var(--line);">
      <div class="session-card-info">
        <h4>${n.title} <span class="sub-text">(To: ${n.target || 'All Users'})</span></h4>
        <p>${n.message}</p>
        <span class="sub-text">${n.time}</span>
      </div>
    </div>
  `).join('');
}

function renderAdminReports() {
  const tbodyCompleted = document.getElementById('admin-reports-completed-table-body');
  const tbodyWeekly = document.getElementById('admin-reports-weekly-table-body');
  const tbodyMonthly = document.getElementById('admin-reports-monthly-table-body');

  const today = new Date();
  const weekAgo = new Date(today);
  weekAgo.setDate(today.getDate() - 7);
  const weekAgoStr = weekAgo.toISOString().split('T')[0];

  const monthAgo = new Date(today);
  monthAgo.setMonth(today.getMonth() - 1);
  const monthAgoStr = monthAgo.toISOString().split('T')[0];

  const completedList = state.sessions.filter(s => s.status === 'Completed');
  const weeklyList = state.sessions.filter(s => (s.sessionDate || s.date || '') >= weekAgoStr);
  const monthlyList = state.sessions.filter(s => (s.sessionDate || s.date || '') >= monthAgoStr);

  const rowHtml = list => list.length === 0 ? `<tr><td colspan="7" style="text-align: center; color: var(--ink-soft); padding: 20px;">No report records found.</td></tr>` : list.map(s => `
    <tr>
      <td><code>${s.id}</code></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.studentId || s.studentName}')">${s.studentName}</span></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></td>
      <td>${s.subject}</td>
      <td>${s.sessionDate || s.date} ${s.timeSlot}</td>
      <td>P${s.totalPaid}</td>
      <td><span class="badge ${s.status === 'Completed' ? 'badge-info' : 'badge-success'}">${s.status}</span></td>
    </tr>
  `).join('');

  if (tbodyCompleted) tbodyCompleted.innerHTML = rowHtml(completedList);
  if (tbodyWeekly) tbodyWeekly.innerHTML = rowHtml(weeklyList);
  if (tbodyMonthly) tbodyMonthly.innerHTML = rowHtml(monthlyList);
}

function populateStudentProfileEditModal() {
  if (!state.currentUser) return;
  const s = state.students.find(x => x.id === state.currentUser.id || x.email === state.currentUser.email || x.name === state.currentUser.name) || {
    name: state.currentUser.name || '',
    grade: 'Senior High',
    subjectsNeeded: [],
    bio: ''
  };

  const elName = document.getElementById('edit-student-name');
  const elGrade = document.getElementById('edit-student-grade');
  const elSubj = document.getElementById('edit-student-subjects');
  const elBio = document.getElementById('edit-student-bio');

  if (elName) elName.value = s.name || state.currentUser.name || '';
  if (elGrade) elGrade.value = s.grade || 'Senior High';
  if (elSubj) elSubj.value = s.subjectsNeeded ? s.subjectsNeeded.join(', ') : (s.grade || '');
  if (elBio) elBio.value = s.bio || `${state.currentUser.name} Student Profile`;
}

function populateTutorProfileEditPage() {
  if (!state.currentUser) return;
  const t = state.tutors.find(x => x.id === state.currentUser.id || x.email === state.currentUser.email || x.name === state.currentUser.name) || {
    name: state.currentUser.name || '',
    subjects: ['Mathematics'],
    learningStyles: ['Step-by-Step Explanation'],
    hourlyRate: 350,
    bio: '',
    available: true
  };

  const elName = document.getElementById('page-tutor-name-input');
  const elSubj = document.getElementById('page-tutor-subjects-input');
  const elStyle = document.getElementById('page-tutor-style-select');
  const elRate = document.getElementById('page-tutor-rate-input');
  const elBio = document.getElementById('page-tutor-bio-input');
  const elAvail = document.getElementById('page-tutor-availability-toggle');

  if (elName) elName.value = t.name || state.currentUser.name || '';
  if (elSubj) elSubj.value = t.subjects ? t.subjects.join(', ') : 'Mathematics';
  if (elStyle && t.learningStyles && t.learningStyles.length > 0) elStyle.value = t.learningStyles[0];
  if (elRate) elRate.value = t.hourlyRate || 350;
  if (elBio) elBio.value = t.bio || `${state.currentUser.name} Tutor Profile`;
  if (elAvail) elAvail.checked = Boolean(t.available);

  const days = t.availableDays || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
  document.querySelectorAll('input[name="avail_day"]').forEach(cb => {
    cb.checked = days.includes(cb.value);
  });
  const elWindow = document.getElementById('tutor-time-window-input');
  if (elWindow) elWindow.value = t.availableTimeSlots || '09:00 AM - 05:00 PM';
  const elBlocked = document.getElementById('tutor-blocked-dates-input');
  if (elBlocked) elBlocked.value = t.blockedDates || '';

  renderTutorSummary();
}

window.acceptTutorMatch = async function(matchId) {
  const m = state.matches.find(x => x.id === matchId);
  if (m) {
    m.status = 'Approved';
    renderTutorRequests();

    const studentName = m.studentName;
    const notifObj = {
      targetRole: 'student',
      target: studentName,
      title: 'Match Request Accepted!',
      message: `Your match request with ${m.tutorName} for ${m.subject} has been accepted! Please attend your scheduled session.`
    };
    state.notifications.unshift({ ...notifObj, id: 'notif-' + Date.now(), time: 'Just now', read: false });

    try {
      await fetch('api/matches.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: matchId, status: 'Approved' })
      });
      await fetch('api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(notifObj)
      });
    } catch (err) { console.log('Offline API fallback'); }

    showToast(`Match request accepted! Student ${studentName} notified.`);
  }
};

window.startTutorSession = async function(sessionId) {
  const s = state.sessions.find(x => x.id === sessionId);
  if (s) {
    s.tutorStarted = true;
    renderTutorUpcoming();
    renderStudentUpcoming();

    const notifObj = {
      targetRole: 'student',
      target: s.studentName,
      title: 'Session Started!',
      message: `Tutor ${s.tutorName} has started the live workspace session for ${s.subject}. Please join now!`
    };
    state.notifications.unshift({ ...notifObj, id: 'notif-' + Date.now(), time: 'Just now', read: false });

    try {
      await fetch('api/sessions.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: sessionId, tutorStarted: true })
      });
      await fetch('api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(notifObj)
      });
    } catch (err) { console.log('Offline API fallback'); }

    showToast(`Session started! Student ${s.studentName} notified to join.`);
    launchWorkspace(sessionId);
  }
};

function renderTutorSummary() {
  if (!state.currentUser) return;
  const t = state.tutors.find(x => x.id === state.currentUser.id || x.email === state.currentUser.email || x.name === state.currentUser.name);
  if (!t) return;

  const daysEl = document.getElementById('tutor-summary-days');
  const windowEl = document.getElementById('tutor-summary-window');
  const blockedEl = document.getElementById('tutor-summary-blocked');

  if (daysEl) daysEl.textContent = Array.isArray(t.availableDays) ? t.availableDays.join(', ') : (t.availableDays || 'Mon, Tue, Wed, Thu, Fri');
  if (windowEl) windowEl.textContent = t.availableTimeSlots || '09:00 AM - 05:00 PM';
  if (blockedEl) blockedEl.textContent = t.blockedDates || 'None';
}
