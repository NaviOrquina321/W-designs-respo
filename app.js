/**
 * TutorLink — An Intelligent Tutor-Student Matching System
 * Application Logic & State Management
 */

// Application Initial State & Mock Database
const state = {
  currentRole: 'guest', // 'guest', 'student', 'tutor', 'admin'
  currentUser: {
    name: 'Maria Santos',
    email: 'maria.santos@student.edu.ph',
    role: 'student'
  },

  // Seed Tutors Database
  tutors: [
    {
      id: 'tut-1',
      name: 'Prof. Alex Rivera',
      avatar: '👨‍🏫',
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
      avatar: '👩‍💻',
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
      avatar: '👨‍🔬',
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
      avatar: '👩‍🏫',
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
      status: 'Confirmed', // 'Confirmed', 'Completed', 'Pending'
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
      title: 'Session Confirmed!',
      message: 'Your Calculus II session with Prof. Alex Rivera is confirmed for March 15 at 2:00 PM.',
      time: '10 mins ago',
      read: false
    },
    {
      id: 'notif-2',
      title: 'GCash Payment Received',
      message: 'Payment of ₱385.00 confirmed (Ref: GC-9920182341). Receipt available in dashboard.',
      time: '12 mins ago',
      read: false
    },
    {
      id: 'notif-3',
      title: 'Welcome to TutorLink',
      message: 'Explore AI Tutor Matching or browse available tutors to start your personalized learning.',
      time: '1 day ago',
      read: true
    }
  ],

  // Active Pending Booking Flow
  activeBooking: {
    tutorId: null,
    tutorName: null,
    subject: null,
    date: null,
    timeSlot: null,
    hourlyRate: 0,
    serviceFee: 0,
    total: 0
  },

  activeWorkspaceSession: null
};

// DOM Content Loaded Handler
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initRoleSwitcher();
  initModals();
  initAIMatching();
  initCalendarBooking();
  initGCashPayment();
  initNotifications();
  initWorkspaceSession();
  initAdminView();
  renderAllViews();
});

// Navigation Engine
function initNavigation() {
  const roleSelect = document.getElementById('role-select');
  const navLogo = document.getElementById('nav-logo');
  const loginBtn = document.getElementById('login-link-btn');
  const signupBtn = document.getElementById('signup-btn');

  navLogo.addEventListener('click', () => {
    switchRole('guest');
    roleSelect.value = 'guest';
  });

  loginBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openModal('modal-auth');
  });

  signupBtn.addEventListener('click', () => {
    openModal('modal-auth');
  });

  document.getElementById('hero-find-tutor-btn')?.addEventListener('click', () => {
    switchRole('student');
    roleSelect.value = 'student';
    openModal('modal-ai-matching');
  });

  document.getElementById('hero-become-tutor-btn')?.addEventListener('click', () => {
    switchRole('tutor');
    roleSelect.value = 'tutor';
    showToast('Switched to Tutor View! Set your teaching profile.');
  });

  document.getElementById('cta-find-tutor-btn')?.addEventListener('click', () => {
    switchRole('student');
    roleSelect.value = 'student';
    openModal('modal-ai-matching');
  });

  document.getElementById('cta-become-tutor-btn')?.addEventListener('click', () => {
    switchRole('tutor');
    roleSelect.value = 'tutor';
  });

  document.getElementById('start-ai-match-btn')?.addEventListener('click', () => {
    openModal('modal-ai-matching');
  });

  document.getElementById('tutor-search-input')?.addEventListener('input', (e) => {
    renderTutorDirectory(e.target.value.toLowerCase());
  });
}

// Role Switcher Handler
function initRoleSwitcher() {
  const roleSelect = document.getElementById('role-select');
  roleSelect.addEventListener('change', (e) => {
    switchRole(e.target.value);
  });
}

function switchRole(role) {
  state.currentRole = role;

  // Hide all views
  document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active'));

  // Update Nav items visibility
  const publicNavLinks = document.getElementById('public-nav-links');

  if (role === 'guest') {
    document.getElementById('view-landing').classList.add('active');
    if (publicNavLinks) publicNavLinks.style.display = 'flex';
  } else {
    if (publicNavLinks) publicNavLinks.style.display = 'none';
    if (role === 'student') {
      document.getElementById('view-student').classList.add('active');
    } else if (role === 'tutor') {
      document.getElementById('view-tutor').classList.add('active');
    } else if (role === 'admin') {
      document.getElementById('view-admin').classList.add('active');
    }
  }

  renderAllViews();
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
    document.getElementById('role-select').value = role;
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
  renderAdminActivity();
  updateNotificationBadge();
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
        <div class="tutor-card-head">
          <div class="tutor-avatar">${t.avatar}</div>
          <div class="tutor-info">
            <h4>${t.name}</h4>
            <div class="tutor-rating">★ ${t.rating} (${t.reviewsCount} reviews)</div>
          </div>
        </div>
        <p class="sub-text margin-bottom">${t.bio}</p>
        <div class="tutor-details-list">
          <div>📚 <strong>Subjects:</strong> ${t.subjects.join(', ')}</div>
          <div>🧠 <strong>Style:</strong> ${t.learningStyles[0]}</div>
          <div>💵 <strong>Rate:</strong> ₱${t.hourlyRate}/hr</div>
        </div>
      </div>
      <button class="btn btn-primary btn-small full-width margin-top" onclick="openCalendarBooking('${t.id}')">
        📅 Book Session (₱${t.hourlyRate})
      </button>
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
        <h4>${s.subject} — with ${s.tutorName}</h4>
        <p>🗓️ ${s.date} at ${s.timeSlot} | 💳 GCash Ref: <strong>${s.gcashRef}</strong></p>
      </div>
      <div>
        <span class="badge badge-success margin-bottom">Confirmed</span>
        <button class="btn btn-primary btn-small" onclick="launchWorkspace('${s.id}')">
          🚀 Join Live Workspace Session
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
      <td><strong>${s.tutorName}</strong></td>
      <td>${s.subject}</td>
      <td>₱${s.totalPaid}</td>
      <td><code>${s.gcashRef}</code></td>
      <td><span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span></td>
      <td>
        ${s.status === 'Completed' ?
          `<button class="btn btn-secondary btn-small" onclick="openRatingModal('${s.tutorName}')">★ Rate Tutor</button>` :
          `<button class="btn btn-secondary btn-small" onclick="launchWorkspace('${s.id}')">View Room</button>`
        }
      </td>
    </tr>
  `).join('');
}

// Render Tutor Dashboard Sessions
function renderTutorUpcoming() {
  const container = document.getElementById('tutor-upcoming-sessions-list');
  if (!container) return;

  const tutorSessions = state.sessions.filter(s => s.tutorName.includes('Alex'));

  container.innerHTML = tutorSessions.map(s => `
    <div class="session-card">
      <div class="session-card-info">
        <h4>${s.subject} with Student <strong>${s.studentName}</strong></h4>
        <p>🗓️ Date: ${s.date} | Time: ${s.timeSlot} | Earnings: <strong>₱${s.hourlyRate}</strong></p>
      </div>
      <div>
        <span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span>
        <button class="btn btn-primary btn-small margin-top" onclick="launchWorkspace('${s.id}')">
          💻 Launch Session
        </button>
      </div>
    </div>
  `).join('');
}

// Render Admin Central Audit Log Table
function renderAdminActivity() {
  const tbody = document.getElementById('admin-activity-table-body');
  if (!tbody) return;

  const filter = document.getElementById('admin-filter-status')?.value || 'all';

  const list = state.sessions.filter(s => filter === 'all' || s.status === filter);

  tbody.innerHTML = list.map(s => `
    <tr>
      <td><code>${s.id}</code></td>
      <td>${s.studentName}</td>
      <td>${s.tutorName}</td>
      <td>${s.subject}</td>
      <td>${s.date} ${s.timeSlot}</td>
      <td>₱${s.totalPaid}</td>
      <td><code>${s.gcashRef}</code></td>
      <td><span class="badge ${s.status === 'Confirmed' ? 'badge-success' : 'badge-info'}">${s.status}</span></td>
      <td><button class="btn btn-secondary btn-small" onclick="showToast('Audit Session Details for ${s.id}')">Audit</button></td>
    </tr>
  `).join('');
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
      let score = 60; // baseline

      if (tutor.subjects.includes(subject)) score += 25;
      if (tutor.learningStyles.some(s => style.includes(s) || s.includes(style))) score += 10;
      if (tutor.rating >= 4.8) score += 5;

      // cap at 98% max
      const compatibility = Math.min(score, 98);

      return { ...tutor, matchScore: compatibility };
    }).sort((a, b) => b.matchScore - a.matchScore);

    // Render AI Match Cards
    resultsList.innerHTML = matches.map(t => `
      <div class="session-card" style="background: white; border: 1px solid var(--line);">
        <div class="session-card-info">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">${t.avatar}</span>
            <div>
              <h4 style="font-size: 1.1rem; margin: 0;">${t.name}</h4>
              <span class="badge badge-match">${t.matchScore}% Match Score ⚡</span>
            </div>
          </div>
          <p class="sub-text margin-top" style="margin-top: 8px;">
            📚 Subjects: ${t.subjects.join(', ')} | Rate: <strong>₱${t.hourlyRate}/hr</strong>
          </p>
        </div>
        <button class="btn btn-primary btn-small" onclick="selectMatchedTutor('${t.id}', '${subject}')">
          Book This Tutor
        </button>
      </div>
    `).join('');

    resultsBox.classList.remove('hidden');
    showToast('AI Tutor Matching completed!');
  });
}

// Global hook for selecting a matched tutor from AI wizard
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

  // Date picker default today
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
    total: total
  };

  // Render Tutor Preview Card in modal
  const preview = document.getElementById('selected-tutor-preview');
  preview.innerHTML = `
    <div style="display: flex; align-items: center; gap: 12px; background: var(--panel); padding: 12px; border-radius: 8px;">
      <span style="font-size: 2rem;">${tutor.avatar}</span>
      <div>
        <h4 style="margin: 0;">${tutor.name}</h4>
        <span class="sub-text">Subject: <strong>${subject}</strong></span>
      </div>
    </div>
  `;

  // Render Time Slot Chips
  const slotsContainer = document.getElementById('time-slots-container');
  slotsContainer.innerHTML = tutor.availabilitySlots.map(slot => `
    <div class="slot-chip" onclick="selectTimeSlot(this, '${slot}')">${slot}</div>
  `).join('');

  // Update Pricing Breakdown Summary
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


// GCASH ONLINE PAYMENT INTEGRATION MODULE
function initGCashPayment() {
  const step1Next = document.getElementById('gcash-step1-next-btn');
  const step2Pay = document.getElementById('gcash-confirm-pay-btn');
  const step3Done = document.getElementById('gcash-done-btn');
  const closeBtn = document.getElementById('close-gcash-modal');

  closeBtn?.addEventListener('click', () => {
    closeModal('modal-gcash');
  });

  step1Next?.addEventListener('click', () => {
    const phone = document.getElementById('gcash-mobile').value;
    if (phone.length < 10) {
      alert('Please enter a valid 10-digit GCash mobile number.');
      return;
    }
    document.getElementById('gcash-step-1').classList.add('hidden');
    document.getElementById('gcash-step-2').classList.remove('hidden');
  });

  step2Pay?.addEventListener('click', () => {
    // Generate Random GCash Reference Number
    const randomRef = 'GC-' + Math.floor(1000000000 + Math.random() * 9000000000);
    const newSessionId = 'SESS-' + Math.floor(100 + Math.random() * 900);

    const b = state.activeBooking;

    // Create New Session Object
    const newSession = {
      id: newSessionId,
      studentName: state.currentUser.name,
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
      notes: 'Initial session booked via GCash.'
    };

    // Save to State Database
    state.sessions.unshift(newSession);

    // Push System Notification
    state.notifications.unshift({
      id: 'notif-' + Date.now(),
      title: 'Session Booked & Paid!',
      message: `Your ${b.subject} session with ${b.tutorName} is confirmed for ${b.date} at ${b.timeSlot}. GCash Ref: ${randomRef}`,
      time: 'Just now',
      read: false
    });

    // Populate Receipt View
    document.getElementById('gcash-receipt-ref').textContent = randomRef;
    document.getElementById('gcash-receipt-date').textContent = `${b.date}, ${b.timeSlot}`;
    document.getElementById('gcash-receipt-amount').textContent = `₱${b.total}.00`;

    document.getElementById('gcash-step-2').classList.add('hidden');
    document.getElementById('gcash-step-3').classList.remove('hidden');

    renderAllViews();
    showToast('GCash payment confirmed!');
  });

  step3Done?.addEventListener('click', () => {
    closeModal('modal-gcash');
    // Reset steps
    document.getElementById('gcash-step-1').classList.remove('hidden');
    document.getElementById('gcash-step-2').classList.add('hidden');
    document.getElementById('gcash-step-3').classList.add('hidden');
  });
}

function openGCashPayment() {
  document.getElementById('gcash-modal-amount').textContent = `₱${state.activeBooking.total}.00`;
  document.getElementById('gcash-confirm-pay-btn').textContent = `Pay ₱${state.activeBooking.total}.00`;
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


// ADMIN MONITORING & REPORT MODULE
function initAdminView() {
  const reportBtn = document.getElementById('generate-admin-report-btn');
  const filterSelect = document.getElementById('admin-filter-status');
  const closeReport = document.getElementById('close-report-modal');
  const doneReport = document.getElementById('done-report-btn');

  filterSelect?.addEventListener('change', renderAdminActivity);

  reportBtn?.addEventListener('click', () => {
    // Generate Printable Report Summary
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
        <td>${s.studentName}</td>
        <td>${s.tutorName}</td>
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
