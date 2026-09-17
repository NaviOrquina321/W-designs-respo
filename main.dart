import 'package:flutter/material.dart';

void main() {
  runApp(const DoctorAppointmentApp());
}

/// Core App Entry Point
class DoctorAppointmentApp extends StatelessWidget {
  const DoctorAppointmentApp({super.key});

  @override
  Widget build(BuildContext context) {
    return AppStateProvider(
      child: MaterialApp(
        title: 'CarePulse - Doctor Appointments',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF0D6EFD),
            primary: const Color(0xFF0D6EFD),
            secondary: const Color(0xFF00C9A7),
            surface: Colors.white,
          ),
          scaffoldBackgroundColor: const Color(0xFFF8F9FA),
          fontFamily: 'Roboto',
          cardTheme: CardThemeData(
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(color: Colors.grey.shade200),
            ),
          ),
          chipTheme: ChipThemeData(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        home: const MainScreen(),
      ),
    );
  }
}

// ============================================================================
// MODELS
// ============================================================================

enum ConsultationType { inPerson, videoCall }

enum AppointmentStatus { upcoming, completed, canceled }

class Specialty {
  final String id;
  final String name;
  final IconData icon;
  final Color color;
  final int doctorCount;

  const Specialty({
    required this.id,
    required this.name,
    required this.icon,
    required this.color,
    required this.doctorCount,
  });
}

class Doctor {
  final String id;
  final String name;
  final String specialty;
  final double rating;
  final int reviewCount;
  final int experienceYears;
  final double fee;
  final String hospital;
  final String about;
  final Color avatarBgColor;
  bool isFavorite;
  final List<String> availableSlots;

  Doctor({
    required this.id,
    required this.name,
    required this.specialty,
    required this.rating,
    required this.reviewCount,
    required this.experienceYears,
    required this.fee,
    required this.hospital,
    required this.about,
    required this.avatarBgColor,
    this.isFavorite = false,
    required this.availableSlots,
  });

  Doctor copyWith({bool? isFavorite}) {
    return Doctor(
      id: id,
      name: name,
      specialty: specialty,
      rating: rating,
      reviewCount: reviewCount,
      experienceYears: experienceYears,
      fee: fee,
      hospital: hospital,
      about: about,
      avatarBgColor: avatarBgColor,
      isFavorite: isFavorite ?? this.isFavorite,
      availableSlots: availableSlots,
    );
  }
}

class Appointment {
  final String id;
  final Doctor doctor;
  DateTime date;
  String timeSlot;
  final ConsultationType type;
  AppointmentStatus status;
  final String notes;

  Appointment({
    required this.id,
    required this.doctor,
    required this.date,
    required this.timeSlot,
    required this.type,
    this.status = AppointmentStatus.upcoming,
    this.notes = '',
  });
}

class PatientProfile {
  String name;
  String email;
  String phone;
  String bloodGroup;
  int age;
  double weightKg;
  List<String> medicalConditions;

  PatientProfile({
    required this.name,
    required this.email,
    required this.phone,
    required this.bloodGroup,
    required this.age,
    required this.weightKg,
    required this.medicalConditions,
  });
}

// ============================================================================
// STATE MANAGEMENT (InheritedWidget + ValueNotifier pattern for DartPad)
// ============================================================================

class AppState extends ChangeNotifier {
  final List<Specialty> specialties = [
    const Specialty(
      id: 'cardiology',
      name: 'Cardiology',
      icon: Icons.favorite,
      color: Color(0xFFFF4D6D),
      doctorCount: 14,
    ),
    const Specialty(
      id: 'neurology',
      name: 'Neurology',
      icon: Icons.psychology,
      color: Color(0xFF7209B7),
      doctorCount: 9,
    ),
    const Specialty(
      id: 'pediatrics',
      name: 'Pediatrics',
      icon: Icons.child_care,
      color: Color(0xFFFF9F1C),
      doctorCount: 18,
    ),
    const Specialty(
      id: 'dental',
      name: 'Dental',
      icon: Icons.clean_hands,
      color: Color(0xFF4CC9F0),
      doctorCount: 22,
    ),
    const Specialty(
      id: 'orthopedics',
      name: 'Orthopedics',
      icon: Icons.accessibility_new,
      color: Color(0xFF2EC4B6),
      doctorCount: 11,
    ),
    const Specialty(
      id: 'dermatology',
      name: 'Dermatology',
      icon: Icons.face,
      color: Color(0xFFF72585),
      doctorCount: 15,
    ),
  ];

  late List<Doctor> doctors;
  late List<Appointment> appointments;
  late PatientProfile profile;

  String searchQuery = '';
  String? selectedSpecialty;

  AppState() {
    _initMockData();
  }

  void _initMockData() {
    doctors = [
      Doctor(
        id: 'doc_1',
        name: 'Dr. Sarah Jenkins',
        specialty: 'Cardiology',
        rating: 4.9,
        reviewCount: 128,
        experienceYears: 12,
        fee: 120.0,
        hospital: 'St. Jude Heart Institute',
        about:
            'Board certified cardiologist with over 12 years of experience specializing in preventive cardiology and heart failure management.',
        avatarBgColor: const Color(0xFFE3F2FD),
        isFavorite: true,
        availableSlots: ['09:00 AM', '10:30 AM', '02:00 PM', '04:30 PM'],
      ),
      Doctor(
        id: 'doc_2',
        name: 'Dr. Marcus Vance',
        specialty: 'Neurology',
        rating: 4.8,
        reviewCount: 94,
        experienceYears: 9,
        fee: 150.0,
        hospital: 'Metropolitan Neuroscience Center',
        about:
            'Leading specialist in clinical neurology, migraine treatment, and comprehensive brain health diagnostics.',
        avatarBgColor: const Color(0xFFF3E5F5),
        isFavorite: false,
        availableSlots: ['11:00 AM', '01:30 PM', '03:00 PM', '05:00 PM'],
      ),
      Doctor(
        id: 'doc_3',
        name: 'Dr. Elena Rostova',
        specialty: 'Pediatrics',
        rating: 4.9,
        reviewCount: 210,
        experienceYears: 15,
        fee: 95.0,
        hospital: 'Children Care Medical Center',
        about:
            'Compassionate pediatrician dedicated to infant growth monitoring, childhood immunization, and holistic wellness.',
        avatarBgColor: const Color(0xFFFFF3E0),
        isFavorite: true,
        availableSlots: ['08:30 AM', '10:00 AM', '01:00 PM', '03:30 PM'],
      ),
      Doctor(
        id: 'doc_4',
        name: 'Dr. James Wilson',
        specialty: 'Dental',
        rating: 4.7,
        reviewCount: 88,
        experienceYears: 8,
        fee: 80.0,
        hospital: 'Apex Dental Care Clinic',
        about:
            'Expert cosmetic dentist and oral surgeon focused on pain-free treatments and complete dental restoration.',
        avatarBgColor: const Color(0xFFE0F7FA),
        isFavorite: false,
        availableSlots: ['09:30 AM', '11:30 AM', '02:30 PM', '04:00 PM'],
      ),
      Doctor(
        id: 'doc_5',
        name: 'Dr. Priya Patel',
        specialty: 'Dermatology',
        rating: 4.9,
        reviewCount: 165,
        experienceYears: 11,
        fee: 110.0,
        hospital: 'Glow Skin & Laser Institute',
        about:
            'Specialist in medical and cosmetic dermatology, acne therapies, and advanced skincare routines.',
        avatarBgColor: const Color(0xFFFCE4EC),
        isFavorite: false,
        availableSlots: ['10:00 AM', '12:00 PM', '03:00 PM', '04:30 PM'],
      ),
      Doctor(
        id: 'doc_6',
        name: 'Dr. Alexander Hayes',
        specialty: 'Orthopedics',
        rating: 4.8,
        reviewCount: 112,
        experienceYears: 14,
        fee: 140.0,
        hospital: 'City Orthopedic & Spine Center',
        about:
            'Orthopedic surgeon specializing in joint replacement, sports injury recovery, and spinal health.',
        avatarBgColor: const Color(0xFFE8F5E9),
        isFavorite: false,
        availableSlots: ['09:00 AM', '11:00 AM', '02:00 PM', '05:00 PM'],
      ),
    ];

    profile = PatientProfile(
      name: 'Alex Morgan',
      email: 'alex.morgan@example.com',
      phone: '+1 (555) 234-5678',
      bloodGroup: 'O+',
      age: 29,
      weightKg: 68.5,
      medicalConditions: ['Seasonal Allergies', 'Mild Asthma'],
    );

    final tomorrow = DateTime.now().add(const Duration(days: 1));
    final nextWeek = DateTime.now().add(const Duration(days: 4));
    final pastDate = DateTime.now().subtract(const Duration(days: 5));

    appointments = [
      Appointment(
        id: 'app_1',
        doctor: doctors[0],
        date: tomorrow,
        timeSlot: '10:30 AM',
        type: ConsultationType.inPerson,
        status: AppointmentStatus.upcoming,
        notes: 'Routine heart checkup and blood pressure check',
      ),
      Appointment(
        id: 'app_2',
        doctor: doctors[2],
        date: nextWeek,
        timeSlot: '01:00 PM',
        type: ConsultationType.videoCall,
        status: AppointmentStatus.upcoming,
        notes: 'Nutrition and growth consultation',
      ),
      Appointment(
        id: 'app_3',
        doctor: doctors[3],
        date: pastDate,
        timeSlot: '02:30 PM',
        type: ConsultationType.inPerson,
        status: AppointmentStatus.completed,
        notes: 'Annual teeth cleaning and polishing',
      ),
    ];
  }

  void toggleFavorite(String doctorId) {
    final index = doctors.indexWhere((d) => d.id == doctorId);
    if (index != -1) {
      doctors[index].isFavorite = !doctors[index].isFavorite;
      notifyListeners();
    }
  }

  void setSearchQuery(String query) {
    searchQuery = query;
    notifyListeners();
  }

  void setSpecialtyFilter(String? specialty) {
    if (selectedSpecialty == specialty) {
      selectedSpecialty = null;
    } else {
      selectedSpecialty = specialty;
    }
    notifyListeners();
  }

  void addAppointment(Appointment appointment) {
    appointments.insert(0, appointment);
    notifyListeners();
  }

  void cancelAppointment(String appointmentId) {
    final index = appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      appointments[index].status = AppointmentStatus.canceled;
      notifyListeners();
    }
  }

  void rescheduleAppointment(
      String appointmentId, DateTime newDate, String newSlot) {
    final index = appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      appointments[index].date = newDate;
      appointments[index].timeSlot = newSlot;
      appointments[index].status = AppointmentStatus.upcoming;
      notifyListeners();
    }
  }

  void updateProfile(PatientProfile newProfile) {
    profile = newProfile;
    notifyListeners();
  }

  List<Doctor> get filteredDoctors {
    return doctors.where((doc) {
      final matchesQuery = doc.name
              .toLowerCase()
              .contains(searchQuery.toLowerCase()) ||
          doc.specialty.toLowerCase().contains(searchQuery.toLowerCase()) ||
          doc.hospital.toLowerCase().contains(searchQuery.toLowerCase());

      final matchesSpecialty = selectedSpecialty == null ||
          doc.specialty.toLowerCase() == selectedSpecialty!.toLowerCase();

      return matchesQuery && matchesSpecialty;
    }).toList();
  }

  List<Doctor> get favoriteDoctors {
    return doctors.where((d) => d.isFavorite).toList();
  }
}

class AppStateProvider extends InheritedNotifier<AppState> {
  AppStateProvider({
    super.key,
    required Widget child,
  }) : super(notifier: AppState(), child: child);

  static AppState of(BuildContext context) {
    return context
        .dependOnInheritedWidgetOfExactType<AppStateProvider>()!
        .notifier!;
  }
}

// ============================================================================
// MAIN NAVIGATION SCREEN
// ============================================================================

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  void _navigateToTab(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final screens = [
      HomeScreen(
        onSeeAllDoctors: () => _navigateToTab(1),
        onViewAppointments: () => _navigateToTab(2),
      ),
      const DoctorSearchScreen(),
      const AppointmentsScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: _navigateToTab,
          selectedItemColor: Theme.of(context).colorScheme.primary,
          unselectedItemColor: Colors.grey.shade500,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          elevation: 0,
          selectedFontSize: 12,
          unselectedFontSize: 12,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.search_outlined),
              activeIcon: Icon(Icons.search),
              label: 'Doctors',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.calendar_today_outlined),
              activeIcon: Icon(Icons.calendar_today),
              label: 'Appointments',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================================
// HOME SCREEN
// ============================================================================

class HomeScreen extends StatelessWidget {
  final VoidCallback onSeeAllDoctors;
  final VoidCallback onViewAppointments;

  const HomeScreen({
    super.key,
    required this.onSeeAllDoctors,
    required this.onViewAppointments,
  });

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);
    final upcomingAppointments = state.appointments
        .where((a) => a.status == AppointmentStatus.upcoming)
        .toList();

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 24,
                        backgroundColor:
                            Theme.of(context).colorScheme.primary.withValues(alpha: 0.1),
                        child: Text(
                          state.profile.name.substring(0, 1),
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).colorScheme.primary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Hello, Welcome! 👋',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600,
                            ),
                          ),
                          Text(
                            state.profile.name,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: IconButton(
                      icon: const Icon(Icons.notifications_outlined),
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('No new notifications'),
                            duration: Duration(seconds: 2),
                          ),
                        );
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Search Bar Widget
              TextField(
                onChanged: (val) {
                  state.setSearchQuery(val);
                },
                decoration: InputDecoration(
                  hintText: 'Search doctor, specialty, or clinic...',
                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
                  prefixIcon: const Icon(Icons.search, color: Colors.grey),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(vertical: 14),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: Colors.grey.shade200),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(
                      color: Theme.of(context).colorScheme.primary,
                      width: 1.5,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Upcoming Appointment Card
              if (upcomingAppointments.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Upcoming Appointment',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    TextButton(
                      onPressed: onViewAppointments,
                      child: const Text('View All'),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                UpcomingAppointmentCard(appointment: upcomingAppointments.first),
                const SizedBox(height: 24),
              ],

              // Medical Specialties Section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Categories',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  TextButton(
                    onPressed: onSeeAllDoctors,
                    child: const Text('See All'),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Specialties Grid
              SizedBox(
                height: 100,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: state.specialties.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 16),
                  itemBuilder: (context, index) {
                    final item = state.specialties[index];
                    final isSelected =
                        state.selectedSpecialty?.toLowerCase() ==
                            item.name.toLowerCase();

                    return GestureDetector(
                      onTap: () {
                        state.setSpecialtyFilter(item.name);
                        onSeeAllDoctors();
                      },
                      child: Column(
                        children: [
                          Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? item.color
                                  : item.color.withValues(alpha: 0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(
                              item.icon,
                              color: isSelected ? Colors.white : item.color,
                              size: 28,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            item.name,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: isSelected
                                  ? FontWeight.bold
                                  : FontWeight.w500,
                              color: isSelected
                                  ? Theme.of(context).colorScheme.primary
                                  : Colors.black87,
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 24),

              // Top Rated Doctors Section Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Top Doctors',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  TextButton(
                    onPressed: onSeeAllDoctors,
                    child: const Text('See All'),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Doctor List
              ListView.separated(
                physics: const NeverScrollableScrollPhysics(),
                shrinkWrap: true,
                itemCount: state.filteredDoctors.take(3).length,
                separatorBuilder: (_, __) => const SizedBox(height: 14),
                itemBuilder: (context, index) {
                  final doctor = state.filteredDoctors[index];
                  return DoctorCard(doctor: doctor);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ============================================================================
// DOCTOR SEARCH & LISTING SCREEN
// ============================================================================

class DoctorSearchScreen extends StatelessWidget {
  const DoctorSearchScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);
    final doctors = state.filteredDoctors;

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Find Specialists',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
      ),
      body: Column(
        children: [
          // Filter & Search Header
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: [
                TextField(
                  onChanged: (val) => state.setSearchQuery(val),
                  decoration: InputDecoration(
                    hintText: 'Search doctor, specialty, or clinic...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: state.searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () => state.setSearchQuery(''),
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8F9FA),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Specialty filter chips
                SizedBox(
                  height: 38,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      FilterChip(
                        label: const Text('All Specialties'),
                        selected: state.selectedSpecialty == null,
                        onSelected: (_) => state.setSpecialtyFilter(null),
                        selectedColor: Theme.of(context)
                            .colorScheme
                            .primary
                            .withValues(alpha: 0.15),
                        checkmarkColor: Theme.of(context).colorScheme.primary,
                      ),
                      const SizedBox(width: 8),
                      ...state.specialties.map((spec) {
                        final isSelected = state.selectedSpecialty?.toLowerCase() ==
                            spec.name.toLowerCase();
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: FilterChip(
                            label: Text(spec.name),
                            selected: isSelected,
                            onSelected: (_) => state.setSpecialtyFilter(spec.name),
                            selectedColor: spec.color.withValues(alpha: 0.2),
                            checkmarkColor: spec.color,
                          ),
                        );
                      }),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Doctors List View
          Expanded(
            child: doctors.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.search_off,
                            size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 12),
                        Text(
                          'No doctors found',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Try adjusting your search or specialty filters',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: doctors.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 14),
                    itemBuilder: (context, index) {
                      return DoctorCard(doctor: doctors[index]);
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

// ============================================================================
// DOCTOR DETAIL SCREEN
// ============================================================================

class DoctorDetailScreen extends StatelessWidget {
  final Doctor doctor;

  const DoctorDetailScreen({super.key, required this.doctor});

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Doctor Profile'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        actions: [
          IconButton(
            icon: Icon(
              doctor.isFavorite ? Icons.favorite : Icons.favorite_border,
              color: doctor.isFavorite ? Colors.red : Colors.grey,
            ),
            onPressed: () {
              state.toggleFavorite(doctor.id);
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Doctor Card Hero Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Row(
                children: [
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: doctor.avatarBgColor,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Center(
                      child: Text(
                        doctor.name
                            .split(' ')
                            .where((s) => s != 'Dr.')
                            .map((s) => s[0])
                            .join(''),
                        style: TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.bold,
                          color: Theme.of(context).colorScheme.primary,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          doctor.name,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          doctor.specialty,
                          style: TextStyle(
                            fontSize: 14,
                            color: Theme.of(context).colorScheme.primary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Icon(Icons.location_on_outlined,
                                size: 16, color: Colors.grey.shade600),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                doctor.hospital,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade600,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Quick Stats Row
            Row(
              children: [
                _buildStatItem(
                  context,
                  icon: Icons.people_outline,
                  color: Colors.blue,
                  value: '1,200+',
                  label: 'Patients',
                ),
                const SizedBox(width: 12),
                _buildStatItem(
                  context,
                  icon: Icons.work_outline,
                  color: Colors.orange,
                  value: '${doctor.experienceYears} yrs',
                  label: 'Experience',
                ),
                const SizedBox(width: 12),
                _buildStatItem(
                  context,
                  icon: Icons.star_outline,
                  color: Colors.amber,
                  value: '${doctor.rating}',
                  label: '${doctor.reviewCount} reviews',
                ),
              ],
            ),
            const SizedBox(height: 24),

            // About Doctor
            const Text(
              'About Doctor',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              doctor.about,
              style: TextStyle(
                fontSize: 14,
                height: 1.5,
                color: Colors.grey.shade700,
              ),
            ),
            const SizedBox(height: 24),

            // Consultation Fee Banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.2),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.payments_outlined,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Consultation Fee',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          Text(
                            '\$${doctor.fee.toStringAsFixed(0)}',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Theme.of(context).colorScheme.primary,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Text(
                    'Inc. VAT',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: () {
            _showBookingBottomSheet(context, doctor);
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: Theme.of(context).colorScheme.primary,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
            elevation: 0,
          ),
          child: const Text(
            'Book Appointment',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
        ),
      ),
    );
  }

  Widget _buildStatItem(
    BuildContext context, {
    required IconData icon,
    required Color color,
    required String value,
    required String label,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade500,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================================
// BOOKING BOTTOM SHEET / MODAL
// ============================================================================

void _showBookingBottomSheet(BuildContext context, Doctor doctor) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (context) => BookingBottomSheet(doctor: doctor),
  );
}

class BookingBottomSheet extends StatefulWidget {
  final Doctor doctor;

  const BookingBottomSheet({super.key, required this.doctor});

  @override
  State<BookingBottomSheet> createState() => _BookingBottomSheetState();
}

class _BookingBottomSheetState extends State<BookingBottomSheet> {
  ConsultationType _selectedType = ConsultationType.inPerson;
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));
  late String _selectedTimeSlot;
  final TextEditingController _notesController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _selectedTimeSlot = widget.doctor.availableSlots.first;
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  List<DateTime> get _availableDates {
    final now = DateTime.now();
    return List.generate(10, (index) => now.add(Duration(days: index + 1)));
  }

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Column(
        children: [
          // Drag Handle & Title
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Book Appointment',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Consultation Type Switcher
                  const Text(
                    'Select Consultation Type',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _buildTypeOption(
                          type: ConsultationType.inPerson,
                          label: 'In-Person Visit',
                          subtitle: widget.doctor.hospital,
                          icon: Icons.local_hospital_outlined,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildTypeOption(
                          type: ConsultationType.videoCall,
                          label: 'Video Call',
                          subtitle: 'Online Consultation',
                          icon: Icons.videocam_outlined,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Date Picker
                  const Text(
                    'Select Date',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    height: 75,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: _availableDates.length,
                      itemBuilder: (context, index) {
                        final date = _availableDates[index];
                        final isSelected = date.day == _selectedDate.day &&
                            date.month == _selectedDate.month;

                        final dayName = [
                          'Mon',
                          'Tue',
                          'Wed',
                          'Thu',
                          'Fri',
                          'Sat',
                          'Sun'
                        ][date.weekday - 1];

                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              _selectedDate = date;
                            });
                          },
                          child: Container(
                            width: 60,
                            margin: const EdgeInsets.only(right: 10),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? Theme.of(context).colorScheme.primary
                                  : Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(
                                color: isSelected
                                    ? Theme.of(context).colorScheme.primary
                                    : Colors.grey.shade300,
                              ),
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  dayName,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: isSelected
                                        ? Colors.white70
                                        : Colors.grey.shade600,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '${date.day}',
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: isSelected
                                        ? Colors.white
                                        : Colors.black87,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Time Slot Picker
                  const Text(
                    'Select Time Slot',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 10,
                    runSpacing: 10,
                    children: widget.doctor.availableSlots.map((slot) {
                      final isSelected = slot == _selectedTimeSlot;
                      return ChoiceChip(
                        label: Text(slot),
                        selected: isSelected,
                        selectedColor: Theme.of(context).colorScheme.primary,
                        labelStyle: TextStyle(
                          color: isSelected ? Colors.white : Colors.black87,
                          fontWeight:
                              isSelected ? FontWeight.bold : FontWeight.normal,
                        ),
                        onSelected: (selected) {
                          if (selected) {
                            setState(() {
                              _selectedTimeSlot = slot;
                            });
                          }
                        },
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 24),

                  // Reason / Notes
                  const Text(
                    'Reason for Visit (Optional)',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _notesController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Describe your symptoms or notes for the doctor...',
                      hintStyle:
                          TextStyle(color: Colors.grey.shade400, fontSize: 13),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: Colors.grey.shade300),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Footer Action
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: Row(
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      'Total Price',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    Text(
                      '\$${widget.doctor.fee.toStringAsFixed(0)}',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 20),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      final newAppointment = Appointment(
                        id: 'app_${DateTime.now().millisecondsSinceEpoch}',
                        doctor: widget.doctor,
                        date: _selectedDate,
                        timeSlot: _selectedTimeSlot,
                        type: _selectedType,
                        notes: _notesController.text.trim(),
                      );

                      state.addAppointment(newAppointment);

                      Navigator.pop(context); // Close bottom sheet
                      Navigator.pop(context); // Return from doctor detail screen

                      showDialog(
                        context: context,
                        builder: (context) => AlertDialog(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          title: Column(
                            children: [
                              const CircleAvatar(
                                radius: 28,
                                backgroundColor: Color(0xFFE8F5E9),
                                child: Icon(Icons.check_circle,
                                    color: Colors.green, size: 36),
                              ),
                              const SizedBox(height: 12),
                              const Text('Appointment Booked!'),
                            ],
                          ),
                          content: Text(
                            'Your appointment with ${widget.doctor.name} for ${_selectedDate.day}/${_selectedDate.month}/${_selectedDate.year} at $_selectedTimeSlot has been confirmed.',
                            textAlign: TextAlign.center,
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(context),
                              child: const Text('OK'),
                            ),
                          ],
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).colorScheme.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      elevation: 0,
                    ),
                    child: const Text(
                      'Confirm Booking',
                      style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTypeOption({
    required ConsultationType type,
    required String label,
    required String subtitle,
    required IconData icon,
  }) {
    final isSelected = _selectedType == type;

    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedType = type;
        });
      },
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected
              ? Theme.of(context).colorScheme.primary.withValues(alpha: 0.08)
              : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected
                ? Theme.of(context).colorScheme.primary
                : Colors.grey.shade300,
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              icon,
              color: isSelected
                  ? Theme.of(context).colorScheme.primary
                  : Colors.grey.shade600,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: isSelected
                          ? Theme.of(context).colorScheme.primary
                          : Colors.black87,
                    ),
                  ),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================================
// APPOINTMENTS MANAGEMENT SCREEN
// ============================================================================

class AppointmentsScreen extends StatefulWidget {
  const AppointmentsScreen({super.key});

  @override
  State<AppointmentsScreen> createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends State<AppointmentsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);

    final upcoming = state.appointments
        .where((a) => a.status == AppointmentStatus.upcoming)
        .toList();
    final completed = state.appointments
        .where((a) => a.status == AppointmentStatus.completed)
        .toList();
    final canceled = state.appointments
        .where((a) => a.status == AppointmentStatus.canceled)
        .toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'My Appointments',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          labelColor: Theme.of(context).colorScheme.primary,
          unselectedLabelColor: Colors.grey,
          indicatorColor: Theme.of(context).colorScheme.primary,
          tabs: [
            Tab(text: 'Upcoming (${upcoming.length})'),
            Tab(text: 'Completed (${completed.length})'),
            Tab(text: 'Canceled (${canceled.length})'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildAppointmentList(context, upcoming, state),
          _buildAppointmentList(context, completed, state),
          _buildAppointmentList(context, canceled, state),
        ],
      ),
    );
  }

  Widget _buildAppointmentList(
      BuildContext context, List<Appointment> list, AppState state) {
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy_outlined,
                size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              'No appointments found',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: list.length,
      separatorBuilder: (_, __) => const SizedBox(height: 14),
      itemBuilder: (context, index) {
        final item = list[index];
        return AppointmentCardItem(appointment: item);
      },
    );
  }
}

class AppointmentCardItem extends StatelessWidget {
  final Appointment appointment;

  const AppointmentCardItem({super.key, required this.appointment});

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);

    Color statusColor;
    String statusText;

    switch (appointment.status) {
      case AppointmentStatus.upcoming:
        statusColor = Colors.blue;
        statusText = 'Upcoming';
        break;
      case AppointmentStatus.completed:
        statusColor = Colors.green;
        statusText = 'Completed';
        break;
      case AppointmentStatus.canceled:
        statusColor = Colors.red;
        statusText = 'Canceled';
        break;
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: appointment.doctor.avatarBgColor,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(
                  child: Text(
                    appointment.doctor.name
                        .split(' ')
                        .where((s) => s != 'Dr.')
                        .map((s) => s[0])
                        .join(''),
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      appointment.doctor.name,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      appointment.doctor.specialty,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      appointment.doctor.hospital,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  statusText,
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF8F9FA),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.calendar_today_outlined,
                        size: 16, color: Colors.black54),
                    const SizedBox(width: 6),
                    Text(
                      '${appointment.date.day}/${appointment.date.month}/${appointment.date.year}',
                      style: const TextStyle(
                          fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                Row(
                  children: [
                    const Icon(Icons.access_time_outlined,
                        size: 16, color: Colors.black54),
                    const SizedBox(width: 6),
                    Text(
                      appointment.timeSlot,
                      style: const TextStyle(
                          fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                Row(
                  children: [
                    Icon(
                      appointment.type == ConsultationType.inPerson
                          ? Icons.local_hospital_outlined
                          : Icons.videocam_outlined,
                      size: 16,
                      color: Colors.black54,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      appointment.type == ConsultationType.inPerson
                          ? 'Clinic'
                          : 'Video',
                      style: const TextStyle(
                          fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ],
            ),
          ),
          if (appointment.notes.isNotEmpty) ...[
            const SizedBox(height: 10),
            Text(
              'Notes: ${appointment.notes}',
              style: TextStyle(
                fontSize: 12,
                fontStyle: FontStyle.italic,
                color: Colors.grey.shade600,
              ),
            ),
          ],
          if (appointment.status == AppointmentStatus.upcoming) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () {
                      _showCancelDialog(context, appointment.id, state);
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text('Cancel'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      _showRescheduleDialog(context, appointment, state);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).colorScheme.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text('Reschedule'),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  void _showCancelDialog(
      BuildContext context, String appointmentId, AppState state) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Appointment'),
        content: const Text(
            'Are you sure you want to cancel this appointment? This action cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Keep Appointment'),
          ),
          TextButton(
            onPressed: () {
              state.cancelAppointment(appointmentId);
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Appointment canceled')),
              );
            },
            child: const Text('Yes, Cancel', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  void _showRescheduleDialog(
      BuildContext context, Appointment appointment, AppState state) {
    DateTime selectedDate = appointment.date.add(const Duration(days: 1));
    String selectedSlot = appointment.doctor.availableSlots.first;

    showModalBottomSheet(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          return Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Reschedule Appointment',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                Text('Select New Time Slot for ${appointment.doctor.name}'),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  children: appointment.doctor.availableSlots.map((slot) {
                    final isSelected = slot == selectedSlot;
                    return ChoiceChip(
                      label: Text(slot),
                      selected: isSelected,
                      onSelected: (val) {
                        if (val) setModalState(() => selectedSlot = slot);
                      },
                    );
                  }).toList(),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      state.rescheduleAppointment(
                          appointment.id, selectedDate, selectedSlot);
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text('Appointment rescheduled successfully!')),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).colorScheme.primary,
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Confirm Reschedule'),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

// ============================================================================
// PATIENT PROFILE & MEDICAL RECORDS SCREEN
// ============================================================================

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);
    final profile = state.profile;
    final favDoctors = state.favoriteDoctors;

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Patient Profile',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.edit_outlined),
            onPressed: () {
              _showEditProfileDialog(context, state);
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // User Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 32,
                    backgroundColor:
                        Theme.of(context).colorScheme.primary.withValues(alpha: 0.1),
                    child: Text(
                      profile.name.substring(0, 1),
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          profile.name,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          profile.email,
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          profile.phone,
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Vitals & Health Summary Grid
            Row(
              children: [
                _buildHealthMetricCard(
                  context,
                  title: 'Blood Group',
                  value: profile.bloodGroup,
                  icon: Icons.bloodtype,
                  color: Colors.red,
                ),
                const SizedBox(width: 12),
                _buildHealthMetricCard(
                  context,
                  title: 'Age',
                  value: '${profile.age} yrs',
                  icon: Icons.cake,
                  color: Colors.orange,
                ),
                const SizedBox(width: 12),
                _buildHealthMetricCard(
                  context,
                  title: 'Weight',
                  value: '${profile.weightKg} kg',
                  icon: Icons.monitor_weight,
                  color: Colors.blue,
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Medical Conditions List
            const Text(
              'Known Medical Conditions',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: profile.medicalConditions.map((condition) {
                return Chip(
                  avatar: const Icon(Icons.check_circle_outline, size: 18),
                  label: Text(condition),
                  backgroundColor: Colors.white,
                  side: BorderSide(color: Colors.grey.shade300),
                );
              }).toList(),
            ),
            const SizedBox(height: 24),

            // Favorite Doctors
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Saved Specialists',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  '${favDoctors.length} Doctors',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (favDoctors.isEmpty)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Center(
                  child: Text(
                    'No favorite doctors added yet.',
                    style: TextStyle(color: Colors.grey.shade500),
                  ),
                ),
              )
            else
              ListView.separated(
                physics: const NeverScrollableScrollPhysics(),
                shrinkWrap: true,
                itemCount: favDoctors.length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final doc = favDoctors[index];
                  return Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: doc.avatarBgColor,
                        child: Text(
                          doc.name.split(' ').last[0],
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).colorScheme.primary,
                          ),
                        ),
                      ),
                      title: Text(doc.name,
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Text('${doc.specialty} • ${doc.hospital}'),
                      trailing: IconButton(
                        icon: const Icon(Icons.favorite, color: Colors.red),
                        onPressed: () => state.toggleFavorite(doc.id),
                      ),
                    ),
                  );
                },
              ),
            const SizedBox(height: 24),

            // App Settings / Options List
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  ListTile(
                    leading: const Icon(Icons.receipt_long_outlined),
                    title: const Text('Medical History & Prescriptions'),
                    trailing: const Icon(Icons.chevron_right, size: 20),
                    onTap: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Medical history records up-to-date'),
                        ),
                      );
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.lock_outline),
                    title: const Text('Privacy & Security'),
                    trailing: const Icon(Icons.chevron_right, size: 20),
                    onTap: () {},
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.help_outline),
                    title: const Text('Help & Support'),
                    trailing: const Icon(Icons.chevron_right, size: 20),
                    onTap: () {},
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHealthMetricCard(
    BuildContext context, {
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 10),
            Text(
              value,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditProfileDialog(BuildContext context, AppState state) {
    final nameCtrl = TextEditingController(text: state.profile.name);
    final phoneCtrl = TextEditingController(text: state.profile.phone);
    final ageCtrl = TextEditingController(text: state.profile.age.toString());
    final weightCtrl =
        TextEditingController(text: state.profile.weightKg.toString());

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Edit Patient Info'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameCtrl,
                decoration: const InputDecoration(labelText: 'Full Name'),
              ),
              TextField(
                controller: phoneCtrl,
                decoration: const InputDecoration(labelText: 'Phone Number'),
              ),
              TextField(
                controller: ageCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Age'),
              ),
              TextField(
                controller: weightCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Weight (kg)'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              final newProf = PatientProfile(
                name: nameCtrl.text,
                email: state.profile.email,
                phone: phoneCtrl.text,
                bloodGroup: state.profile.bloodGroup,
                age: int.tryParse(ageCtrl.text) ?? state.profile.age,
                weightKg: double.tryParse(weightCtrl.text) ?? state.profile.weightKg,
                medicalConditions: state.profile.medicalConditions,
              );
              state.updateProfile(newProf);
              Navigator.pop(context);
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }
}

// ============================================================================
// REUSABLE WIDGETS
// ============================================================================

class UpcomingAppointmentCard extends StatelessWidget {
  final Appointment appointment;

  const UpcomingAppointmentCard({super.key, required this.appointment});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Theme.of(context).colorScheme.primary,
            Theme.of(context).colorScheme.primary.withBlue(220),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 22,
                backgroundColor: Colors.white24,
                child: Text(
                  appointment.doctor.name.split(' ').last[0],
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      appointment.doctor.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${appointment.doctor.specialty} • ${appointment.type == ConsultationType.inPerson ? "In-Person" : "Video Call"}',
                      style: const TextStyle(
                        color: Colors.white70,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  appointment.type == ConsultationType.inPerson
                      ? 'Clinic'
                      : 'Online',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.calendar_today,
                        size: 16, color: Colors.white),
                    const SizedBox(width: 8),
                    Text(
                      '${appointment.date.day}/${appointment.date.month}/${appointment.date.year}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    const Icon(Icons.access_time,
                        size: 16, color: Colors.white),
                    const SizedBox(width: 8),
                    Text(
                      appointment.timeSlot,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class DoctorCard extends StatelessWidget {
  final Doctor doctor;

  const DoctorCard({super.key, required this.doctor});

  @override
  Widget build(BuildContext context) {
    final state = AppStateProvider.of(context);

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => DoctorDetailScreen(doctor: doctor),
          ),
        );
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade200),
        ),
        padding: const EdgeInsets.all(14),
        child: Column(
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Avatar
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: doctor.avatarBgColor,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Center(
                    child: Text(
                      doctor.name
                          .split(' ')
                          .where((s) => s != 'Dr.')
                          .map((s) => s[0])
                          .join(''),
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 14),

                // Info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              doctor.name,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          InkWell(
                            onTap: () => state.toggleFavorite(doctor.id),
                            child: Icon(
                              doctor.isFavorite
                                  ? Icons.favorite
                                  : Icons.favorite_border,
                              color: doctor.isFavorite
                                  ? Colors.red
                                  : Colors.grey.shade400,
                              size: 22,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${doctor.specialty} • ${doctor.experienceYears} yrs exp',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey.shade600,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          const Icon(Icons.star, size: 16, color: Colors.amber),
                          const SizedBox(width: 4),
                          Text(
                            doctor.rating.toString(),
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            ' (${doctor.reviewCount} reviews)',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade500,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 10),

            // Fee & Book button
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Consultation Fee',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    Text(
                      '\$${doctor.fee.toStringAsFixed(0)}',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ],
                ),
                ElevatedButton(
                  onPressed: () {
                    _showBookingBottomSheet(context, doctor);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Theme.of(context).colorScheme.primary,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                  ),
                  child: const Text('Book Appointment'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
