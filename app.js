/**
 * TutorLink - An Intelligent Tutor-Student Matching System
 * Application Logic & State Management
 */

// Application Initial State & Mock Database
const state = {
  currentRole: 'guest', // 'guest', 'student', 'tutor', 'admin'
  currentUser: null,

  // Seed Students Database
  students: [
    { id: 'STU-101', name: 'Maria Santos', email: 'maria@tutorlink.ph', grade: 'Senior High', validated: true, bio: 'Grade 12 STEM Student focusing on Advanced Calculus and College Entrance Exam preparation.', subjectsNeeded: ['Calculus', 'Physics'], sessionsCompleted: 4 },
    { id: 'STU-102', name: 'Juan Dela Cruz', email: 'juan@tutorlink.ph', grade: 'College', validated: true, bio: '2nd Year Computer Science student looking for web development and algorithms mentoring.', subjectsNeeded: ['Programming', 'Mathematics'], sessionsCompleted: 2 },
    { id: 'STU-103', name: 'Angela Torres', email: 'angela@tutorlink.ph', grade: 'High School', validated: false, bio: 'Grade 10 student striving to build strong foundations in High School Algebra and Chemistry.', subjectsNeeded: ['Mathematics', 'Chemistry'], sessionsCompleted: 1 }
  ],

  // Seed Tutors Database
  tutors: [
    {
      id: 'tut-1',
      name: 'Prof. Alex Rivera',
      initials: 'AR',
      rating: 4.9,
      reviewsCount: 38,
      hourlyRate: 350,
      subjects: ['Mathematics', 'Calculus', 'Physics'],
      learningStyles: ['Visual & Diagrams', 'Step-by-Step Explanation'],
      bio: 'Licensed Mathematics Professor with 8+ years experience making complex algebra and calculus easy to grasp.',
      availabilitySlots: ['09:00 AM', '11:00 AM', '02:00 PM', '04:00 PM', '07:00 PM'],
      available: true
    },
    {
      id: 'tut-2',
      name: 'Engr. Bea Soriano',
      initials: 'BS',
      rating: 4.8,
      reviewsCount: 29,
      hourlyRate: 400,
      subjects: ['Programming', 'Mathematics', 'Calculus'],
      learningStyles: ['Hands-on Practice', 'Step-by-Step Explanation'],
      bio: 'Software Engineer & Code Instructor specializing in Python, JavaScript, and data structures.',
      availabilitySlots: ['10:00 AM', '01:00 PM', '03:00 PM', '06:00 PM'],
      available: true
    },
    {
      id: 'tut-3',
      name: 'Dr. Carlos Mendoza',
      initials: 'CM',
      rating: 5.0,
      reviewsCount: 45,
      hourlyRate: 450,
      subjects: ['Physics', 'Chemistry'],
      learningStyles: ['Auditory & Discussion', 'Visual & Diagrams'],
      bio: 'Physics PhD graduate dedicated to interactive, real-world physics experiments and conceptual learning.',
      availabilitySlots: ['09:00 AM', '02:00 PM', '05:00 PM'],
      available: true
    },
    {
      id: 'tut-4',
      name: 'Ms. Diana Reyes',
      initials: 'DR',
      rating: 4.7,
      reviewsCount: 22,
      hourlyRate: 300,
      subjects: ['English', 'Literature'],
      learningStyles: ['Step-by-Step Explanation', 'Auditory & Discussion'],
      bio: 'English Literature Specialist assisting students in essay writing, grammar, and oral communications.',
      availabilitySlots: ['10:00 AM', '11:00 AM', '02:00 PM', '04:00 PM'],
      available: true
    }
  ],

  // Seed Matching Results
  matches: [
    { id: 'MATCH-201', studentName: 'Maria Santos', tutorName: 'Prof. Alex Rivera', subject: 'Calculus II', score: 98, status: 'Approved' },
    { id: 'MATCH-202', studentName: 'Angela Torres', tutorName: 'Dr. Carlos Mendoza', subject: 'Physics', score: 92, status: 'Pending Review' }
  ],

  // Seed Calendar Schedules
  schedules: [
    { id: 'SCH-301', tutorName: 'Prof. Alex Rivera', dateSlot: '2026-03-16 (02:00 PM)', subject: 'Calculus II', status: 'Available' },
    { id: 'SCH-302', tutorName: 'Engr. Bea Soriano', dateSlot: '2026-03-17 (10:00 AM)', subject: 'Programming', status: 'Booked' },
    { id: 'SCH-303', tutorName: 'Dr. Carlos Mendoza', dateSlot: '2026-03-18 (09:00 AM)', subject: 'Physics', status: 'Available' }
  ],

  // Seed Payments Database
  payments: [
    { id: 'PAY-401', studentName: 'Maria Santos', method: 'GCash', refNo: 'GC-9920182341', amount: 385, status: 'Confirmed' },
    { id: 'PAY-402', studentName: 'Maria Santos', method: 'GCash', refNo: 'GC-8812039481', amount: 440, status: 'Confirmed' },
    { id: 'PAY-403', studentName: 'Juan Dela Cruz', method: 'GCash', refNo: 'GC-7712938471', amount: 495, status: 'Confirmed' },
    { id: 'PAY-404', studentName: 'Angela Torres', method: 'PayMaya', refNo: 'PM-5510293841', amount: 330, status: 'Pending Confirmation' }
  ],

  // Seed Sessions & Bookings
  sessions: [
    {
      id: 'SESS-101',
      studentName: 'Maria Santos',
      tutorId: 'tut-1',
      tutorName: 'Prof. Alex Rivera',
      subject: 'Calculus II',
      date: '2026-03-15',
      timeSlot: '02:00 PM',
      hourlyRate: 350,
      commissionFee: 35,
      totalPaid: 385,
      gcashRef: 'GC-9920182341',
      status: 'Confirmed',
      notes: 'Review derivatives and integration techniques for upcoming midterm.'
    },
    {
      id: 'SESS-100',
      studentName: 'Maria Santos',
      tutorId: 'tut-2',
      tutorName: 'Engr. Bea Soriano',
      subject: 'Programming',
      date: '2026-03-10',
      timeSlot: '10:00 AM',
      hourlyRate: 400,
      commissionFee: 40,
      totalPaid: 440,
      gcashRef: 'GC-8812039481',
      status: 'Completed',
      notes: 'Intro to JavaScript Functions and DOM Manipulation.'
    },
    {
      id: 'SESS-099',
      studentName: 'Juan Dela Cruz',
      tutorId: 'tut-3',
      tutorName: 'Dr. Carlos Mendoza',
      subject: 'Physics',
      date: '2026-03-08',
      timeSlot: '02:00 PM',
      hourlyRate: 450,
      commissionFee: 45,
      totalPaid: 495,
      gcashRef: 'GC-7712938471',
      status: 'Completed',
      notes: 'Newtonian Physics & Equilibrium problems.'
    }
  ],

  // System Notifications
  notifications: [
    {
      id: 'notif-1',
      target: 'Maria Santos',
      title: 'Session Confirmed!',
      message: 'Your Calculus II session with Prof. Alex Rivera is confirmed for March 15 at 2:00 PM.',
      time: '10 mins ago',
      read: false
    },
    {
      id: 'notif-2',
      target: 'Maria Santos',
      title: 'GCash Payment Received',
      message: 'Payment of ₱385.00 confirmed (Ref: GC-9920182341). Receipt available in dashboard.',
      time: '12 mins ago',
      read: false
    },
    {
      id: 'notif-3',
      target: 'All Users',
      title: 'Welcome to TutorLink',
      message: 'Explore AI Tutor Matching or browse available tutors to start your personalized learning.',
      time: '1 day ago',
      read: true
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
  await syncWithDatabase();
  renderAllViews();
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
          bio: s.bio,
          subjectsNeeded: s.subjects_needed ? s.subjects_needed.split(',') : []
        }));
      }
    }
  } catch (e) {
    console.log('XAMPP Backend offline, running on memory state.');
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
          reviewsCount: parseInt(t.reviews_count),
          hourlyRate: parseInt(t.hourly_rate),
          subjects: t.subjects.split(',').map(x => x.trim()),
          learningStyles: t.learning_styles.split(',').map(x => x.trim()),
          bio: t.bio,
          available: Boolean(parseInt(t.available)),
          availabilitySlots: ['09:00 AM', '11:00 AM', '02:00 PM', '04:00 PM']
        }));
      }
    }
  } catch (e) {
    console.log('Tutors API fallback active.');
  }

  try {
    const resA = await fetch('api/admin.php');
    if (resA.ok) {
      const jsonA = await resA.json();
      if (jsonA.data) {
        const d = jsonA.data;
        document.getElementById('admin-stat-total-students').textContent = d.total_students;
        document.getElementById('admin-stat-total-tutors').textContent = d.total_tutors;
        document.getElementById('admin-stat-total-volume').textContent = `₱${d.total_volume.toLocaleString()}`;
        document.getElementById('admin-stat-platform-commission').textContent = `₱${d.platform_commission.toLocaleString()}`;
      }
    }
  } catch (e) {
    console.log('Admin SQL stats API offline, computing from state.');
  }
}

async function apiSaveStudent(studentObj) {
  try {
    await fetch('api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: studentObj.id,
        name: studentObj.name,
        email: studentObj.email,
        grade: studentObj.grade,
        validated: studentObj.validated ? 1 : 0,
        bio: studentObj.bio || '',
        subjects_needed: Array.isArray(studentObj.subjectsNeeded) ? studentObj.subjectsNeeded.join(', ') : studentObj.subjectsNeeded
      })
    });
  } catch (e) {
    console.log('Saved to memory state (API offline).');
  }
}

async function apiSaveSession(sessionObj) {
  try {
    await fetch('api/sessions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: sessionObj.id,
        student_name: sessionObj.studentName,
        tutor_id: sessionObj.tutorId,
        tutor_name: sessionObj.tutorName,
        subject: sessionObj.subject,
        session_date: sessionObj.date,
        time_slot: sessionObj.timeSlot,
        hourly_rate: sessionObj.hourlyRate,
        commission_fee: sessionObj.commissionFee,
        total_paid: sessionObj.totalPaid,
        gcash_ref: sessionObj.gcashRef,
        status: sessionObj.status,
        notes: sessionObj.notes
      })
    });
  } catch (e) {
    console.log('Saved session to memory state (API offline).');
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
    switchRole('guest');
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
      const section = document.getElementById('tutor-profile-settings-section');
      if (section) section.scrollIntoView({ behavior: 'smooth' });
    } else if (state.currentRole === 'student') {
      openModal('modal-edit-student-profile');
    } else {
      showToast('Profile settings are available for logged-in students and tutors.');
    }
  });

  document.getElementById('hero-find-tutor-btn')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  document.getElementById('hero-become-tutor-btn')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  document.getElementById('cta-find-tutor-btn')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  document.getElementById('cta-become-tutor-btn')?.addEventListener('click', () => {
    openModal('modal-auth');
    switchAuthTab('register');
  });

  document.getElementById('start-ai-match-btn')?.addEventListener('click', () => {
    openModal('modal-ai-matching');
  });

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
}


// Role Switcher Logic
function switchRole(role, customUser = null) {
  state.currentRole = role;

  // Hide all views
  document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active'));

  // Update Nav items visibility
  const publicNavLinks = document.getElementById('public-nav-links');
  const loginBtn = document.getElementById('login-link-btn');
  const signupBtn = document.getElementById('signup-btn');
  const logoutBtn = document.getElementById('logout-btn');
  const notifBell = document.getElementById('notif-bell-btn');
  const userProfileBtn = document.getElementById('user-profile-btn');

  if (role === 'guest') {
    state.currentUser = null;
    document.getElementById('view-landing').classList.add('active');
    if (publicNavLinks) publicNavLinks.style.display = 'flex';
    if (loginBtn) loginBtn.classList.remove('hidden');
    if (signupBtn) signupBtn.classList.remove('hidden');
    if (logoutBtn) logoutBtn.classList.add('hidden');
    if (notifBell) notifBell.classList.add('hidden');
    if (userProfileBtn) userProfileBtn.classList.add('hidden');
  } else {
    if (publicNavLinks) publicNavLinks.style.display = 'none';
    if (loginBtn) loginBtn.classList.add('hidden');
    if (signupBtn) signupBtn.classList.add('hidden');
    if (logoutBtn) logoutBtn.classList.remove('hidden');
    if (notifBell) notifBell.classList.remove('hidden');

    if (role === 'student' || role === 'tutor') {
      if (userProfileBtn) userProfileBtn.classList.remove('hidden');
    } else {
      if (userProfileBtn) userProfileBtn.classList.add('hidden');
    }

    if (role === 'student') {
      state.currentUser = customUser || { name: 'Maria Santos', role: 'student' };
      document.getElementById('student-welcome-heading').textContent = `Welcome back, ${state.currentUser.name}!`;
      document.getElementById('view-student').classList.add('active');
    } else if (role === 'tutor') {
      state.currentUser = customUser || { name: 'Prof. Alex Rivera', role: 'tutor' };
      document.getElementById('tutor-welcome-heading').textContent = `Tutor Portal - ${state.currentUser.name}`;
      document.getElementById('view-tutor').classList.add('active');
    } else if (role === 'admin') {
      state.currentUser = customUser || { name: 'System Admin', role: 'admin' };
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
  registerForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const role = document.getElementById('reg-role').value;
    const fullname = document.getElementById('reg-fullname').value;
    const email = document.getElementById('reg-email').value;
    const specialty = document.getElementById('reg-specialty').value;

    if (role === 'student') {
      const newStudent = {
        id: 'STU-' + Math.floor(100 + Math.random() * 900),
        name: fullname,
        email: email,
        grade: specialty,
        validated: true,
        bio: `${specialty} Student eager to connect with expert tutors on TutorLink.`,
        subjectsNeeded: [specialty],
        sessionsCompleted: 0
      };
      state.students.unshift(newStudent);
      switchRole('student', { name: fullname, role: 'student' });
    } else {
      const newTutor = {
        id: 'tut-' + (state.tutors.length + 1),
        name: fullname,
        initials: fullname.split(' ').map(n => n[0]).join(''),
        rating: 5.0,
        reviewsCount: 0,
        hourlyRate: 350,
        subjects: [specialty],
        learningStyles: ['Step-by-Step Explanation'],
        bio: `${specialty} Specialist tutor. Dedicated to student growth.`,
        availabilitySlots: ['09:00 AM', '02:00 PM', '04:00 PM'],
        available: true
      };
      state.tutors.unshift(newTutor);
      switchRole('tutor', { name: fullname, role: 'tutor' });
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

  document.getElementById('auth-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const role = document.getElementById('auth-role').value;
    switchRole(role);
    closeModal('modal-auth');
    showToast(`Logged in successfully as ${role.toUpperCase()}!`);
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
  const settingsToggle = document.getElementById('tutor-availability-settings-toggle');
  const headerToggle = document.getElementById('tutor-availability-toggle');
  const badge = document.getElementById('availability-settings-badge');
  const headerBadge = document.getElementById('availability-status-label');
  const saveBtn = document.getElementById('save-tutor-profile-btn');

  function updateAvailabilityUI(isAvailable) {
    if (settingsToggle) settingsToggle.checked = isAvailable;
    if (headerToggle) headerToggle.checked = isAvailable;

    const labelText = isAvailable ? 'Accepting New Students' : 'Unavailable';
    const badgeClass = isAvailable ? 'badge badge-success' : 'badge badge-danger';

    if (badge) {
      badge.textContent = labelText;
      badge.className = badgeClass;
    }
    if (headerBadge) {
      headerBadge.textContent = labelText;
      headerBadge.className = badgeClass;
    }

    const currentTutor = state.tutors.find(t => t.id === 'tut-1');
    if (currentTutor) currentTutor.available = isAvailable;
  }

  settingsToggle?.addEventListener('change', (e) => {
    updateAvailabilityUI(e.target.checked);
  });

  headerToggle?.addEventListener('change', (e) => {
    updateAvailabilityUI(e.target.checked);
  });

  saveBtn?.addEventListener('click', () => {
    const subjects = document.getElementById('tutor-subjects-input').value;
    const style = document.getElementById('tutor-style-select').value;
    const rate = document.getElementById('tutor-rate-input').value;
    const isAvailable = settingsToggle ? settingsToggle.checked : true;

    const currentTutor = state.tutors.find(t => t.id === 'tut-1');
    if (currentTutor) {
      currentTutor.subjects = subjects.split(',').map(s => s.trim());
      currentTutor.learningStyles = [style];
      currentTutor.hourlyRate = parseInt(rate) || 350;
      currentTutor.available = isAvailable;
    }

    renderAllViews();
    showToast('Tutor profile & availability updated successfully!');
  });
}

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
        <div><strong>Availability Status:</strong> <span class="badge ${tutor.available ? 'badge-success' : 'badge-danger'}">${tutor.available ? 'Accepting New Students' : 'Unavailable'}</span></div>
        <div><strong>Hourly Rate:</strong> ₱${tutor.hourlyRate}/hr</div>
        <div><strong>Subjects Taught:</strong> ${tutor.subjects.join(', ')}</div>
        <div><strong>Teaching Styles:</strong> ${tutor.learningStyles.join(', ')}</div>
        <div><strong>Availability Slots:</strong> ${tutor.availabilitySlots.join(', ')}</div>
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
  renderTutorUpcoming();
  renderAdminKPIs();
  renderAdminStudents();
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
  if (elVolume) elVolume.textContent = `₱${totalVolume.toLocaleString()}`;
  if (elCommission) elCommission.textContent = `₱${platformCommission.toLocaleString()}`;
}

// Render Featured Tutor Directory
function renderTutorDirectory(searchTerm = '') {
  const grid = document.getElementById('tutors-directory-grid');
  if (!grid) return;

  const filtered = state.tutors.filter(t => {
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
          <div><strong>Availability:</strong> <span class="badge ${t.available ? 'badge-success' : 'badge-danger'}">${t.available ? 'Accepting New Students' : 'Unavailable'}</span></div>
          <div><strong>Subjects:</strong> ${t.subjects.join(', ')}</div>
          <div><strong>Style:</strong> ${t.learningStyles[0]}</div>
          <div><strong>Rate:</strong> ₱${t.hourlyRate}/hr</div>
        </div>
      </div>
      <div style="display: flex; gap: 8px;" class="margin-top">
        <button class="btn btn-secondary btn-small full-width" onclick="viewTutorProfile('${t.id}')">View Profile</button>
        <button class="btn btn-primary btn-small full-width" onclick="openCalendarBooking('${t.id}')">Book Session</button>
      </div>
    </div>
  `).join('');
}

// Render Student Upcoming Sessions
function renderStudentUpcoming() {
  const container = document.getElementById('student-upcoming-sessions-list');
  if (!container) return;

  const upcoming = state.sessions.filter(s => s.status === 'Confirmed');

  if (upcoming.length === 0) {
    container.innerHTML = `<p class="sub-text">No upcoming scheduled sessions. Use AI Matching or Browse Tutors to book one!</p>`;
    return;
  }

  container.innerHTML = upcoming.map(s => `
    <div class="session-card">
      <div class="session-card-info">
        <h4>${s.subject} - with <span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></h4>
        <p>${s.date} at ${s.timeSlot} | GCash Ref: <strong>${s.gcashRef}</strong></p>
      </div>
      <div>
        <span class="badge badge-success margin-bottom">Confirmed</span>
        <button class="btn btn-primary btn-small" onclick="launchWorkspace('${s.id}')">
          Join Live Workspace Session
        </button>
      </div>
    </div>
  `).join('');
}

// Render Student History Table
function renderStudentHistory() {
  const tbody = document.getElementById('student-history-table-body');
  if (!tbody) return;

  tbody.innerHTML = state.sessions.map(s => `
    <tr>
      <td>${s.date} (${s.timeSlot})</td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</strong></td>
      <td>${s.subject}</td>
      <td>₱${s.totalPaid}</td>
      <td><code>${s.gcashRef}</code></td>
      <td><span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span></td>
      <td>
        ${s.status === 'Completed' ?
          `<button class="btn btn-secondary btn-small" onclick="openRatingModal('${s.tutorName}')">Rate Tutor</button>` :
          `<button class="btn btn-secondary btn-small" onclick="launchWorkspace('${s.id}')">View Room</button>`
        }
      </td>
    </tr>
  `).join('');

  const totalSpent = state.sessions.reduce((sum, s) => sum + s.totalPaid, 0);
  const spentEl = document.getElementById('student-stat-spent');
  if (spentEl) spentEl.textContent = `₱${totalSpent}`;
}

// Render Tutor Dashboard Sessions & Schedule
function renderTutorUpcoming() {
  const container = document.getElementById('tutor-upcoming-sessions-list');
  if (!container) return;

  const tutorSessions = state.sessions.filter(s => s.tutorName.includes('Alex') || (state.currentUser && s.tutorName === state.currentUser.name));

  container.innerHTML = tutorSessions.map(s => `
    <div class="session-card">
      <div class="session-card-info">
        <h4>${s.subject} with Student <strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('STU-101')">${s.studentName}</strong></h4>
        <p>Date: ${s.date} | Time: ${s.timeSlot} | Earnings: <strong>₱${s.hourlyRate}</strong></p>
      </div>
      <div>
        <span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span>
        <button class="btn btn-primary btn-small margin-top" onclick="launchWorkspace('${s.id}')">
          Launch Session
        </button>
      </div>
    </div>
  `).join('');
}

function initTutorScheduleManager() {
  const addBtn = document.getElementById('tutor-add-schedule-btn');
  const saveBtn = document.getElementById('tutor-save-slot-btn');
  const cancelBtn = document.getElementById('tutor-cancel-slot-btn');
  const formBox = document.getElementById('tutor-new-schedule-form');

  addBtn?.addEventListener('click', () => formBox?.classList.remove('hidden'));
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

  cancelBtn?.addEventListener('click', () => {
    closeModal('modal-ai-matching');
  });

  form?.addEventListener('submit', (e) => {
    e.preventDefault();

    const subject = document.getElementById('match-subject').value;
    const level = document.getElementById('match-level').value;
    const style = document.getElementById('match-style').value;
    const time = document.getElementById('match-time').value;

    // AI Matching Calculation Algorithm
    const matches = state.tutors.map(tutor => {
      let score = 60;

      if (tutor.subjects.includes(subject)) score += 25;
      if (tutor.learningStyles.some(s => style.includes(s) || s.includes(style))) score += 10;
      if (tutor.rating >= 4.8) score += 5;

      const compatibility = Math.min(score, 98);

      return { ...tutor, matchScore: compatibility };
    }).sort((a, b) => b.matchScore - a.matchScore);

    // Save Top Match to Admin Review Queue
    const topMatch = matches[0];
    state.matches.unshift({
      id: 'MATCH-' + Math.floor(100 + Math.random() * 900),
      studentName: state.currentUser ? state.currentUser.name : 'Maria Santos',
      tutorName: topMatch.name,
      subject: subject,
      score: topMatch.matchScore,
      status: 'Pending Review'
    });

    resultsList.innerHTML = matches.map(t => `
      <div class="session-card" style="background: white; border: 1px solid var(--line);">
        <div class="session-card-info">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div class="tutor-avatar" style="cursor: pointer;" onclick="viewTutorProfile('${t.id}')">${t.initials}</div>
            <div>
              <h4 style="font-size: 1.1rem; margin: 0; cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${t.id}')">${t.name}</h4>
              <span class="badge badge-match">${t.matchScore}% Match Score</span>
            </div>
          </div>
          <p class="sub-text margin-top" style="margin-top: 8px;">
            Subjects: ${t.subjects.join(', ')} | Rate: <strong>₱${t.hourlyRate}/hr</strong>
          </p>
        </div>
        <button class="btn btn-primary btn-small" onclick="selectMatchedTutor('${t.id}', '${subject}')">
          Choose Tutor
        </button>
      </div>
    `).join('');

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

  cancelBtn?.addEventListener('click', () => {
    closeModal('modal-calendar');
  });

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
  slotsContainer.innerHTML = tutor.availabilitySlots.map(slot => `
    <div class="slot-chip" onclick="selectTimeSlot(this, '${slot}')">${slot}</div>
  `).join('');

  document.getElementById('summary-hourly-fee').textContent = `₱${hourlyRate}`;
  document.getElementById('summary-service-fee').textContent = `₱${serviceFee}`;
  document.getElementById('summary-total-fee').textContent = `₱${total}`;

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

  closeBtn?.addEventListener('click', () => {
    closeModal('modal-gcash');
  });

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
      notes: `Booked via ${b.paymentMethod}.`
    };

    state.sessions.unshift(newSession);

    state.payments.unshift({
      id: 'PAY-' + Math.floor(100 + Math.random() * 900),
      studentName: studentName,
      method: b.paymentMethod,
      refNo: randomRef,
      amount: b.total,
      status: 'Confirmed'
    });

    state.notifications.unshift({
      id: 'notif-' + Date.now(),
      target: studentName,
      title: 'Session Booked & Paid!',
      message: `Your ${b.subject} session with ${b.tutorName} is confirmed for ${b.date} at ${b.timeSlot}. Ref: ${randomRef}`,
      time: 'Just now',
      read: false
    });

    document.getElementById('gcash-receipt-ref').textContent = randomRef;
    document.getElementById('gcash-receipt-method').textContent = b.paymentMethod;
    document.getElementById('gcash-receipt-date').textContent = `${b.date}, ${b.timeSlot}`;
    document.getElementById('gcash-receipt-amount').textContent = `₱${b.total}.00`;

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
  const method = state.activeBooking.paymentMethod || 'GCash';
  document.getElementById('payment-method-header-title').textContent = `${method} Payment`;
  document.getElementById('gcash-modal-amount').textContent = `₱${state.activeBooking.total}.00`;
  document.getElementById('gcash-confirm-pay-btn').textContent = `Confirm & Pay ₱${state.activeBooking.total}.00`;
  openModal('modal-gcash');
}

// REAL-TIME NOTIFICATIONS MODULE
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
    state.notifications.forEach(n => n.read = true);
    renderNotifDrawer();
    updateNotificationBadge();
    showToast('All notifications marked as read.');
  });
}

function closeNotifDrawer() {
  document.getElementById('notif-drawer')?.classList.remove('active');
  document.getElementById('drawer-overlay')?.classList.remove('active');
}

function renderNotifDrawer() {
  const list = document.getElementById('notif-list');
  if (!list) return;

  list.innerHTML = state.notifications.map(n => `
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
  const unreadCount = state.notifications.filter(n => !n.read).length;
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

  saveNotesBtn?.addEventListener('click', () => {
    showToast('Session notes snapshot saved!');
  });

  chatForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msgText = input.value.trim();
    if (!msgText) return;

    const chatContainer = document.getElementById('chat-messages-container');
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${state.currentRole === 'tutor' ? 'tutor' : 'student'}`;
    msgDiv.innerHTML = `<strong>${state.currentRole === 'tutor' ? 'Prof. Alex' : 'Maria'}:</strong> ${msgText}`;
    chatContainer.appendChild(msgDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    input.value = '';
  });
}

window.launchWorkspace = function(sessionId) {
  const session = state.sessions.find(s => s.id === sessionId);
  if (session) {
    state.activeWorkspaceSession = session;
    document.getElementById('workspace-session-title').textContent = `Live Session: ${session.subject}`;
    document.getElementById('workspace-participants').textContent = `${session.studentName} (Student) & ${session.tutorName} (Tutor)`;
  }
  openModal('modal-session-workspace');
};

// Rating Modal
function openRatingModal(tutorName) {
  document.getElementById('rating-tutor-name').textContent = tutorName;
  openModal('modal-rating');

  document.querySelectorAll('.star-rating .star').forEach(star => {
    star.onclick = function() {
      const val = parseInt(this.getAttribute('data-value'));
      document.querySelectorAll('.star-rating .star').forEach(s => {
        const sVal = parseInt(s.getAttribute('data-value'));
        if (sVal <= val) {
          s.classList.add('active');
        } else {
          s.classList.remove('active');
        }
      });
    };
  });

  document.getElementById('submit-rating-btn').onclick = function() {
    closeModal('modal-rating');
    showToast('Thank you for rating your session!');
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

  document.getElementById('admin-send-notif-btn')?.addEventListener('click', () => {
    const target = document.getElementById('admin-notif-target').value;
    const msg = document.getElementById('admin-notif-message').value;
    if (!msg) {
      alert('Please enter a message to send.');
      return;
    }

    state.notifications.unshift({
      id: 'notif-' + Date.now(),
      target: target,
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

    document.getElementById('report-total-volume').textContent = `₱${totalVolume}`;
    document.getElementById('report-net-revenue').textContent = `₱${totalNet}`;

    const tbody = document.getElementById('report-table-body');
    tbody.innerHTML = state.sessions.map(s => `
      <tr>
        <td><code>${s.id}</code></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('STU-101')">${s.studentName}</span></td>
        <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></td>
        <td>${s.subject}</td>
        <td>${s.status}</td>
        <td>₱${s.totalPaid}</td>
      </tr>
    `).join('');

    openModal('modal-admin-report');
  });

  closeReport?.addEventListener('click', () => closeModal('modal-admin-report'));
  doneReport?.addEventListener('click', () => closeModal('modal-admin-report'));
}

function updateReportFilterUI(activeBtn) {
  document.querySelectorAll('#tab-reports .filter-group button').forEach(b => b.classList.remove('active-filter'));
  activeBtn.classList.add('active-filter');
  renderAdminReports();
}

function renderAdminStudents() {
  const tbody = document.getElementById('admin-students-table-body');
  if (!tbody) return;

  tbody.innerHTML = state.students.map(s => `
    <tr>
      <td><code>${s.id}</code></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('${s.id}')">${s.name}</strong></td>
      <td>${s.email}</td>
      <td>${s.grade}</td>
      <td><span class="badge ${s.validated ? 'badge-success' : 'badge-info'}">${s.validated ? 'Validated' : 'Pending Validation'}</span></td>
      <td>
        <button class="btn btn-secondary btn-small" onclick="viewStudentProfile('${s.id}')">View Profile</button>
        <button class="btn btn-secondary btn-small" onclick="toggleValidateStudent('${s.id}')">
          ${s.validated ? 'Revoke Validation' : 'Validate Student'}
        </button>
      </td>
    </tr>
  `).join('');

  document.getElementById('admin-stat-total-students').textContent = state.students.length;
}

window.toggleValidateStudent = function(studentId) {
  const student = state.students.find(s => s.id === studentId);
  if (student) {
    student.validated = !student.validated;
    renderAdminStudents();
    showToast(`Validation updated for ${student.name}.`);
  }
};

function renderAdminMatching() {
  const tbody = document.getElementById('admin-matching-table-body');
  if (!tbody) return;

  tbody.innerHTML = state.matches.map(m => `
    <tr>
      <td><code>${m.id}</code></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('STU-101')">${m.studentName}</span></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('tut-1')">${m.tutorName}</strong></td>
      <td>${m.subject}</td>
      <td><span class="badge badge-match">${m.score}% Match</span></td>
      <td><span class="badge ${m.status === 'Approved' ? 'badge-success' : m.status === 'Cancelled' ? 'badge-danger' : 'badge-info'}">${m.status}</span></td>
      <td>
        ${m.status === 'Pending Review' ? `
          <button class="btn btn-primary btn-small" onclick="approveMatch('${m.id}')">Approve</button>
          <button class="btn btn-secondary btn-small" onclick="cancelMatch('${m.id}')">Cancel</button>
        ` : `<span class="sub-text">No action needed</span>`}
      </td>
    </tr>
  `).join('');
}

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
  if (!tbody) return;

  tbody.innerHTML = state.schedules.map(sch => `
    <tr>
      <td><code>${sch.id}</code></td>
      <td><strong style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('tut-1')">${sch.tutorName}</strong></td>
      <td>${sch.dateSlot}</td>
      <td>${sch.subject}</td>
      <td><span class="badge ${sch.status === 'Available' ? 'badge-success' : 'badge-info'}">${sch.status}</span></td>
      <td>
        <button class="btn btn-secondary btn-small" onclick="deleteScheduleSlot('${sch.id}')">Modify / Remove</button>
      </td>
    </tr>
  `).join('');
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
  if (!tbody) return;

  tbody.innerHTML = state.payments.map(p => `
    <tr>
      <td><code>${p.id}</code></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('STU-101')">${p.studentName}</span></td>
      <td>${p.method}</td>
      <td><code>${p.refNo}</code></td>
      <td>₱${p.amount}</td>
      <td><span class="badge ${p.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${p.status}</span></td>
      <td>
        ${p.status === 'Pending Confirmation' ? `
          <button class="btn btn-primary btn-small" onclick="confirmPayment('${p.id}')">Confirm Payment</button>
        ` : `<span class="badge badge-success">✓ Confirmed</span>`}
      </td>
    </tr>
  `).join('');

  const totalVol = state.payments.reduce((sum, p) => sum + p.amount, 0);
  const totalComm = Math.round(totalVol * 0.10);

  document.getElementById('admin-stat-total-volume').textContent = `₱${totalVol.toLocaleString()}`;
  document.getElementById('admin-stat-platform-commission').textContent = `₱${totalComm.toLocaleString()}`;
}

window.confirmPayment = function(payId) {
  const p = state.payments.find(x => x.id === payId);
  if (p) {
    p.status = 'Confirmed';
    renderAdminPayments();
    showToast(`Payment ${payId} confirmed.`);
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
  const tbody = document.getElementById('admin-reports-table-body');
  if (!tbody) return;

  const filter = state.activeReportFilter;

  const list = state.sessions.filter(s => {
    if (filter === 'weekly') return s.date >= '2026-03-10';
    if (filter === 'monthly') return s.date >= '2026-03-01';
    return s.status === 'Completed' || s.status === 'Confirmed';
  });

  tbody.innerHTML = list.map(s => `
    <tr>
      <td><code>${s.id}</code></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewStudentProfile('STU-101')">${s.studentName}</span></td>
      <td><span style="cursor: pointer; text-decoration: underline;" onclick="viewTutorProfile('${s.tutorId}')">${s.tutorName}</span></td>
      <td>${s.subject}</td>
      <td>${s.date} ${s.timeSlot}</td>
      <td>₱${s.totalPaid}</td>
      <td><span class="badge ${s.status === 'Completed' ? 'badge-info' : 'badge-success'}">${s.status}</span></td>
    </tr>
  `).join('');
}
