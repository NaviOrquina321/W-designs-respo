import 'package:flutter/material.dart';
import '../models/doctor.dart';
import '../models/appointment.dart';
import '../models/category.dart';
import 'database_helper.dart';

class AppointmentStore extends ChangeNotifier {
  final List<Category> _categories = [
    Category(id: 'cardiology', name: 'Cardiology', icon: Icons.favorite, color: Colors.redAccent),
    Category(id: 'dermatology', name: 'Dermatology', icon: Icons.face, color: Colors.orangeAccent),
    Category(id: 'neurology', name: 'Neurology', icon: Icons.psychology, color: Colors.purpleAccent),
    Category(id: 'pediatrics', name: 'Pediatrics', icon: Icons.child_care, color: Colors.blueAccent),
    Category(id: 'general', name: 'General', icon: Icons.local_hospital, color: Colors.teal),
    Category(id: 'orthopedics', name: 'Orthopedics', icon: Icons.accessible, color: Colors.green),
  ];

  List<Doctor> _doctors = [];
  List<Appointment> _appointments = [];
  bool _isLoading = true;

  String _searchQuery = '';
  String? _selectedCategoryId;

  AppointmentStore() {
    initDatabase();
  }

  bool get isLoading => _isLoading;
  List<Category> get categories => List.unmodifiable(_categories);
  List<Doctor> get doctors => List.unmodifiable(_doctors);
  List<Appointment> get appointments => List.unmodifiable(_appointments);
  String get searchQuery => _searchQuery;
  String? get selectedCategoryId => _selectedCategoryId;

  Future<void> initDatabase() async {
    _isLoading = true;
    notifyListeners();

    try {
      _doctors = await DatabaseHelper.instance.getDoctors();
      _appointments = await DatabaseHelper.instance.getAppointments();
    } catch (e) {
      debugPrint('Database load error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

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
      _selectedCategoryId = null;
    } else {
      _selectedCategoryId = categoryId;
    }
    notifyListeners();
  }

  Future<void> toggleFavorite(String doctorId) async {
    final index = _doctors.indexWhere((doc) => doc.id == doctorId);
    if (index != -1) {
      _doctors[index].isFavorite = !_doctors[index].isFavorite;
      notifyListeners();
      await DatabaseHelper.instance.updateDoctorFavorite(doctorId, _doctors[index].isFavorite);
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

    DatabaseHelper.instance.insertAppointment(newAppointment);

    return newAppointment;
  }

  Future<void> cancelAppointment(String appointmentId) async {
    final index = _appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      _appointments[index].status = AppointmentStatus.cancelled;
      notifyListeners();
      await DatabaseHelper.instance.updateAppointmentStatus(appointmentId, 'cancelled');
    }
  }
}
