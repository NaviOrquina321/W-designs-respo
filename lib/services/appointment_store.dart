import 'package:flutter/material.dart';
import '../models/doctor.dart';
import '../models/appointment.dart';
import '../models/category.dart';

class AppointmentStore extends ChangeNotifier {
  final List<Category> _categories = [
    Category(
      id: 'cardiology',
      name: 'Cardiology',
      icon: Icons.favorite,
      color: Colors.redAccent,
    ),
    Category(
      id: 'dermatology',
      name: 'Dermatology',
      icon: Icons.face,
      color: Colors.orangeAccent,
    ),
    Category(
      id: 'neurology',
      name: 'Neurology',
      icon: Icons.psychology,
      color: Colors.purpleAccent,
    ),
    Category(
      id: 'pediatrics',
      name: 'Pediatrics',
      icon: Icons.child_care,
      color: Colors.blueAccent,
    ),
    Category(
      id: 'general',
      name: 'General',
      icon: Icons.local_hospital,
      color: Colors.teal,
    ),
    Category(
      id: 'orthopedics',
      name: 'Orthopedics',
      icon: Icons.accessible,
      color: Colors.green,
    ),
  ];

  final List<Doctor> _doctors = [
    Doctor(
      id: 'doc_1',
      name: 'Dr. Sarah Jenkins',
      specialty: 'Cardiologist',
      categoryId: 'cardiology',
      rating: 4.9,
      reviewCount: 124,
      yearsExperience: 12,
      consultationFee: 120.0,
      hospital: 'St. Jude Heart Institute',
      about: 'Dr. Sarah Jenkins is a renowned specialist in interventional cardiology with over 12 years of experience treating cardiovascular conditions.',
      imageUrl: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80',
      availableDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
      availableTimeSlots: ['09:00 AM', '10:30 AM', '02:00 PM', '04:00 PM'],
      isFavorite: true,
    ),
    Doctor(
      id: 'doc_2',
      name: 'Dr. Marcus Vance',
      specialty: 'Dermatologist',
      categoryId: 'dermatology',
      rating: 4.8,
      reviewCount: 98,
      yearsExperience: 8,
      consultationFee: 95.0,
      hospital: 'Skin & Aesthetics Center',
      about: 'Dr. Marcus Vance specializes in medical and cosmetic dermatology, providing tailored skin health solutions.',
      imageUrl: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
      availableDays: ['Mon', 'Wed', 'Fri', 'Sat'],
      availableTimeSlots: ['10:00 AM', '11:30 AM', '01:30 PM', '03:30 PM'],
    ),
    Doctor(
      id: 'doc_3',
      name: 'Dr. Emily Chen',
      specialty: 'Neurologist',
      categoryId: 'neurology',
      rating: 4.9,
      reviewCount: 156,
      yearsExperience: 15,
      consultationFee: 150.0,
      hospital: 'Metropolitan Neurological Clinic',
      about: 'Dr. Emily Chen is a board-certified neurologist dedicated to diagnosing and treating complex neurological disorders.',
      imageUrl: 'https://images.unsplash.com/photo-1594824813566-828380e22709?auto=format&fit=crop&w=400&q=80',
      availableDays: ['Tue', 'Thu', 'Fri'],
      availableTimeSlots: ['09:30 AM', '11:00 AM', '02:30 PM'],
    ),
    Doctor(
      id: 'doc_4',
      name: 'Dr. Robert Rivera',
      specialty: 'Pediatrician',
      categoryId: 'pediatrics',
      rating: 4.7,
      reviewCount: 210,
      yearsExperience: 10,
      consultationFee: 85.0,
      hospital: 'Sunshine Children\'s Hospital',
      about: 'Dr. Robert Rivera delivers compassionate pediatric care focusing on preventive medicine and child growth development.',
      imageUrl: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=400&q=80',
      availableDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      availableTimeSlots: ['08:30 AM', '10:00 AM', '01:00 PM', '03:00 PM', '05:00 PM'],
      isFavorite: true,
    ),
    Doctor(
      id: 'doc_5',
      name: 'Dr. Alisha Patel',
      specialty: 'General Practitioner',
      categoryId: 'general',
      rating: 4.8,
      reviewCount: 88,
      yearsExperience: 7,
      consultationFee: 75.0,
      hospital: 'City Healthcare Clinic',
      about: 'Dr. Alisha Patel offers holistic primary healthcare services for patients of all age groups.',
      imageUrl: 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?auto=format&fit=crop&w=400&q=80',
      availableDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
      availableTimeSlots: ['09:00 AM', '11:00 AM', '02:00 PM', '04:30 PM'],
    ),
  ];

  final List<Appointment> _appointments = [
    Appointment(
      id: 'apt_101',
      doctorId: 'doc_1',
      doctorName: 'Dr. Sarah Jenkins',
      doctorSpecialty: 'Cardiologist',
      doctorHospital: 'St. Jude Heart Institute',
      date: 'Tomorrow, 10:30 AM',
      timeSlot: '10:30 AM',
      patientName: 'Alex Johnson',
      patientPhone: '+1 555-0192',
      patientReason: 'Routine ECG Checkup',
      consultationFee: 120.0,
      status: AppointmentStatus.upcoming,
    ),
  ];

  String _searchQuery = '';
  String? _selectedCategoryId;

  List<Category> get categories => List.unmodifiable(_categories);
  List<Doctor> get doctors => List.unmodifiable(_doctors);
  List<Appointment> get appointments => List.unmodifiable(_appointments);
  String get searchQuery => _searchQuery;
  String? get selectedCategoryId => _selectedCategoryId;

  List<Doctor> get filteredDoctors {
    return _doctors.where((doc) {
      final matchesSearch = _searchQuery.isEmpty ||
          doc.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          doc.specialty.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          doc.hospital.toLowerCase().contains(_searchQuery.toLowerCase());

      final matchesCategory = _selectedCategoryId == null ||
          doc.categoryId == _selectedCategoryId;

      return matchesSearch && matchesCategory;
    }).toList();
  }

  List<Doctor> get favoriteDoctors {
    return _doctors.where((doc) => doc.isFavorite).toList();
  }

  List<Appointment> get upcomingAppointments {
    return _appointments.where((a) => a.status == AppointmentStatus.upcoming).toList();
  }

  List<Appointment> get completedAppointments {
    return _appointments.where((a) => a.status == AppointmentStatus.completed).toList();
  }

  List<Appointment> get cancelledAppointments {
    return _appointments.where((a) => a.status == AppointmentStatus.cancelled).toList();
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    notifyListeners();
  }

  void setSelectedCategory(String? categoryId) {
    if (_selectedCategoryId == categoryId) {
      _selectedCategoryId = null; // toggle off
    } else {
      _selectedCategoryId = categoryId;
    }
    notifyListeners();
  }

  void toggleFavorite(String doctorId) {
    final index = _doctors.indexWhere((doc) => doc.id == doctorId);
    if (index != -1) {
      _doctors[index].isFavorite = !_doctors[index].isFavorite;
      notifyListeners();
    }
  }

  Appointment bookAppointment({
    required Doctor doctor,
    required String date,
    required String timeSlot,
    required String patientName,
    required String patientPhone,
    required String patientReason,
  }) {
    final newAppointment = Appointment(
      id: 'apt_${DateTime.now().millisecondsSinceEpoch}',
      doctorId: doctor.id,
      doctorName: doctor.name,
      doctorSpecialty: doctor.specialty,
      doctorHospital: doctor.hospital,
      date: date,
      timeSlot: timeSlot,
      patientName: patientName,
      patientPhone: patientPhone,
      patientReason: patientReason,
      consultationFee: doctor.consultationFee,
      status: AppointmentStatus.upcoming,
    );

    _appointments.insert(0, newAppointment);
    notifyListeners();
    return newAppointment;
  }

  void cancelAppointment(String appointmentId) {
    final index = _appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      _appointments[index].status = AppointmentStatus.cancelled;
      notifyListeners();
    }
  }
}
