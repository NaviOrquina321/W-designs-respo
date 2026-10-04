import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:path/path.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

import '../models/doctor.dart';
import '../models/appointment.dart';
import 'admin_store.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('doctor_appointment_app.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    if (!kIsWeb && (defaultTargetPlatform == TargetPlatform.linux ||
        defaultTargetPlatform == TargetPlatform.windows ||
        defaultTargetPlatform == TargetPlatform.macOS)) {
      sqfliteFfiInit();
      databaseFactory = databaseFactoryFfi;
    }

    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future<void> _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE categories (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        iconCode INTEGER NOT NULL,
        colorValue INTEGER NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE doctors (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        specialty TEXT NOT NULL,
        categoryId TEXT NOT NULL,
        rating REAL NOT NULL,
        reviewCount INTEGER NOT NULL,
        yearsExperience INTEGER NOT NULL,
        consultationFee REAL NOT NULL,
        hospital TEXT NOT NULL,
        about TEXT NOT NULL,
        imageUrl TEXT NOT NULL,
        availableDays TEXT NOT NULL,
        availableTimeSlots TEXT NOT NULL,
        isFavorite INTEGER NOT NULL DEFAULT 0
      )
    ''');

    await db.execute('''
      CREATE TABLE appointments (
        id TEXT PRIMARY KEY,
        doctorId TEXT NOT NULL,
        doctorName TEXT NOT NULL,
        doctorSpecialty TEXT NOT NULL,
        doctorHospital TEXT NOT NULL,
        date TEXT NOT NULL,
        timeSlot TEXT NOT NULL,
        patientName TEXT NOT NULL,
        patientPhone TEXT NOT NULL,
        patientReason TEXT NOT NULL,
        consultationFee REAL NOT NULL,
        status TEXT NOT NULL,
        createdAt TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE students (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        gradeLevel TEXT NOT NULL,
        isValidated INTEGER NOT NULL DEFAULT 0
      )
    ''');

    await db.execute('''
      CREATE TABLE matchings (
        id TEXT PRIMARY KEY,
        studentName TEXT NOT NULL,
        tutorName TEXT NOT NULL,
        subject TEXT NOT NULL,
        matchScore TEXT NOT NULL,
        status TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE schedules (
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        dateTime TEXT NOT NULL,
        tutorName TEXT NOT NULL,
        studentName TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE payments (
        id TEXT PRIMARY KEY,
        payerName TEXT NOT NULL,
        amount REAL NOT NULL,
        date TEXT NOT NULL,
        status TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE notifications (
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        recipientGroup TEXT NOT NULL,
        timestamp TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE reports (
        id TEXT PRIMARY KEY,
        tutorName TEXT NOT NULL,
        studentName TEXT NOT NULL,
        subject TEXT NOT NULL,
        totalHours INTEGER NOT NULL,
        period TEXT NOT NULL
      )
    ''');

    await _seedInitialData(db);
  }

  Future<void> _seedInitialData(Database db) async {
    // Seed Categories
    final initialCategories = [
      {'id': 'cardiology', 'name': 'Cardiology', 'iconCode': 0xe25b, 'colorValue': 0xFFFF5252},
      {'id': 'dermatology', 'name': 'Dermatology', 'iconCode': 0xe252, 'colorValue': 0xFFFF9800},
      {'id': 'neurology', 'name': 'Neurology', 'iconCode': 0xe4eb, 'colorValue': 0xFFAB47BC},
      {'id': 'pediatrics', 'name': 'Pediatrics', 'iconCode': 0xe16d, 'colorValue': 0xFF448AFF},
      {'id': 'general', 'name': 'General', 'iconCode': 0xe39d, 'colorValue': 0xFF009688},
      {'id': 'orthopedics', 'name': 'Orthopedics', 'iconCode': 0xe003, 'colorValue': 0xFF4CAF50},
    ];
    for (var cat in initialCategories) {
      await db.insert('categories', cat, conflictAlgorithm: ConflictAlgorithm.replace);
    }

    // Seed Doctors
    final initialDoctors = [
      {
        'id': 'doc_1',
        'name': 'Dr. Sarah Jenkins',
        'specialty': 'Cardiologist',
        'categoryId': 'cardiology',
        'rating': 4.9,
        'reviewCount': 124,
        'yearsExperience': 12,
        'consultationFee': 120.0,
        'hospital': 'St. Jude Heart Institute',
        'about': 'Dr. Sarah Jenkins is a renowned specialist in interventional cardiology with over 12 years of experience treating cardiovascular conditions.',
        'imageUrl': 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80',
        'availableDays': 'Mon,Tue,Wed,Thu,Fri',
        'availableTimeSlots': '09:00 AM,10:30 AM,02:00 PM,04:00 PM',
        'isFavorite': 1,
      },
      {
        'id': 'doc_2',
        'name': 'Dr. Marcus Vance',
        'specialty': 'Dermatologist',
        'categoryId': 'dermatology',
        'rating': 4.8,
        'reviewCount': 98,
        'yearsExperience': 8,
        'consultationFee': 95.0,
        'hospital': 'Skin & Aesthetics Center',
        'about': 'Dr. Marcus Vance specializes in medical and cosmetic dermatology, providing tailored skin health solutions.',
        'imageUrl': 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
        'availableDays': 'Mon,Wed,Fri,Sat',
        'availableTimeSlots': '10:00 AM,11:30 AM,01:30 PM,03:30 PM',
        'isFavorite': 0,
      },
      {
        'id': 'doc_3',
        'name': 'Dr. Emily Chen',
        'specialty': 'Neurologist',
        'categoryId': 'neurology',
        'rating': 4.9,
        'reviewCount': 156,
        'yearsExperience': 15,
        'consultationFee': 150.0,
        'hospital': 'Metropolitan Neurological Clinic',
        'about': 'Dr. Emily Chen is a board-certified neurologist dedicated to diagnosing and treating complex neurological disorders.',
        'imageUrl': 'https://images.unsplash.com/photo-1594824813566-828380e22709?auto=format&fit=crop&w=400&q=80',
        'availableDays': 'Tue,Thu,Fri',
        'availableTimeSlots': '09:30 AM,11:00 AM,02:30 PM',
        'isFavorite': 0,
      },
      {
        'id': 'doc_4',
        'name': 'Dr. Robert Rivera',
        'specialty': 'Pediatrician',
        'categoryId': 'pediatrics',
        'rating': 4.7,
        'reviewCount': 210,
        'yearsExperience': 10,
        'consultationFee': 85.0,
        'hospital': 'Sunshine Children\'s Hospital',
        'about': 'Dr. Robert Rivera delivers compassionate pediatric care focusing on preventive medicine and child growth development.',
        'imageUrl': 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=400&q=80',
        'availableDays': 'Mon,Tue,Wed,Thu,Fri,Sat',
        'availableTimeSlots': '08:30 AM,10:00 AM,01:00 PM,03:00 PM,05:00 PM',
        'isFavorite': 1,
      },
    ];
    for (var doc in initialDoctors) {
      await db.insert('doctors', doc, conflictAlgorithm: ConflictAlgorithm.replace);
    }

    // Seed Initial Appointment
    await db.insert('appointments', {
      'id': 'apt_101',
      'doctorId': 'doc_1',
      'doctorName': 'Dr. Sarah Jenkins',
      'doctorSpecialty': 'Cardiologist',
      'doctorHospital': 'St. Jude Heart Institute',
      'date': 'Tomorrow, 10:30 AM',
      'timeSlot': '10:30 AM',
      'patientName': 'Alex Johnson',
      'patientPhone': '+1 555-0192',
      'patientReason': 'Routine ECG Checkup',
      'consultationFee': 120.0,
      'status': 'upcoming',
      'createdAt': DateTime.now().toIso8601String(),
    }, conflictAlgorithm: ConflictAlgorithm.replace);

    // Seed Admin Students
    await db.insert('students', {'id': 'st_1', 'name': 'Jordan Lee', 'email': 'jordan@example.com', 'gradeLevel': 'Grade 10', 'isValidated': 1});
    await db.insert('students', {'id': 'st_2', 'name': 'Samantha Green', 'email': 'sam@example.com', 'gradeLevel': 'Grade 11', 'isValidated': 0});

    // Seed Admin Matchings
    await db.insert('matchings', {'id': 'm_1', 'studentName': 'Jordan Lee', 'tutorName': 'Dr. Sarah Jenkins', 'subject': 'Biology & Health', 'matchScore': '98% Match', 'status': 'Pending'});

    // Seed Admin Schedules
    await db.insert('schedules', {'id': 'sch_1', 'title': 'Cardiology 101 Lecture', 'dateTime': 'Mon Oct 12, 10:00 AM', 'tutorName': 'Dr. Sarah Jenkins', 'studentName': 'Jordan Lee'});

    // Seed Admin Payments
    await db.insert('payments', {'id': 'pay_1', 'payerName': 'Jordan Lee', 'amount': 120.0, 'date': '2026-09-20', 'status': 'Confirmed'});

    // Seed Admin Notifications
    await db.insert('notifications', {'id': 'notif_1', 'title': 'Schedule Updated', 'message': 'Your session has been rescheduled.', 'recipientGroup': 'All Students', 'timestamp': DateTime.now().toIso8601String()});

    // Seed Admin Reports
    await db.insert('reports', {'id': 'rep_1', 'tutorName': 'Dr. Sarah Jenkins', 'studentName': 'Jordan Lee', 'subject': 'Cardiology Support', 'totalHours': 12, 'period': 'Completed'});
  }

  // --- CRUD METHODS FOR APPOINTMENTS & DOCTORS ---
  Future<List<Doctor>> getDoctors() async {
    final db = await instance.database;
    final maps = await db.query('doctors');
    return maps.map((map) {
      return Doctor(
        id: map['id'] as String,
        name: map['name'] as String,
        specialty: map['specialty'] as String,
        categoryId: map['categoryId'] as String,
        rating: (map['rating'] as num).toDouble(),
        reviewCount: map['reviewCount'] as int,
        yearsExperience: map['yearsExperience'] as int,
        consultationFee: (map['consultationFee'] as num).toDouble(),
        hospital: map['hospital'] as String,
        about: map['about'] as String,
        imageUrl: map['imageUrl'] as String,
        availableDays: (map['availableDays'] as String).split(','),
        availableTimeSlots: (map['availableTimeSlots'] as String).split(','),
        isFavorite: (map['isFavorite'] as int) == 1,
      );
    }).toList();
  }

  Future<void> updateDoctorFavorite(String doctorId, bool isFavorite) async {
    final db = await instance.database;
    await db.update(
      'doctors',
      {'isFavorite': isFavorite ? 1 : 0},
      where: 'id = ?',
      whereArgs: [doctorId],
    );
  }

  Future<List<Appointment>> getAppointments() async {
    final db = await instance.database;
    final maps = await db.query('appointments', orderBy: 'createdAt DESC');
    return maps.map((map) {
      AppointmentStatus status;
      switch (map['status'] as String) {
        case 'completed':
          status = AppointmentStatus.completed;
          break;
        case 'cancelled':
          status = AppointmentStatus.cancelled;
          break;
        default:
          status = AppointmentStatus.upcoming;
      }

      return Appointment(
        id: map['id'] as String,
        doctorId: map['doctorId'] as String,
        doctorName: map['doctorName'] as String,
        doctorSpecialty: map['doctorSpecialty'] as String,
        doctorHospital: map['doctorHospital'] as String,
        date: map['date'] as String,
        timeSlot: map['timeSlot'] as String,
        patientName: map['patientName'] as String,
        patientPhone: map['patientPhone'] as String,
        patientReason: map['patientReason'] as String,
        consultationFee: (map['consultationFee'] as num).toDouble(),
        status: status,
        createdAt: DateTime.parse(map['createdAt'] as String),
      );
    }).toList();
  }

  Future<void> insertAppointment(Appointment appointment) async {
    final db = await instance.database;
    await db.insert('appointments', {
      'id': appointment.id,
      'doctorId': appointment.doctorId,
      'doctorName': appointment.doctorName,
      'doctorSpecialty': appointment.doctorSpecialty,
      'doctorHospital': appointment.doctorHospital,
      'date': appointment.date,
      'timeSlot': appointment.timeSlot,
      'patientName': appointment.patientName,
      'patientPhone': appointment.patientPhone,
      'patientReason': appointment.patientReason,
      'consultationFee': appointment.consultationFee,
      'status': appointment.status.name,
      'createdAt': appointment.createdAt.toIso8601String(),
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<void> updateAppointmentStatus(String id, String status) async {
    final db = await instance.database;
    await db.update('appointments', {'status': status}, where: 'id = ?', whereArgs: [id]);
  }

  // --- CRUD METHODS FOR ADMIN MODULES ---
  Future<List<StudentItem>> getStudents() async {
    final db = await instance.database;
    final maps = await db.query('students');
    return maps.map((map) => StudentItem(
      id: map['id'] as String,
      name: map['name'] as String,
      email: map['email'] as String,
      gradeLevel: map['gradeLevel'] as String,
      isValidated: (map['isValidated'] as int) == 1,
    )).toList();
  }

  Future<void> validateStudent(String id) async {
    final db = await instance.database;
    await db.update('students', {'isValidated': 1}, where: 'id = ?', whereArgs: [id]);
  }

  Future<List<MatchingSession>> getMatchings() async {
    final db = await instance.database;
    final maps = await db.query('matchings');
    return maps.map((map) => MatchingSession(
      id: map['id'] as String,
      studentName: map['studentName'] as String,
      tutorName: map['tutorName'] as String,
      subject: map['subject'] as String,
      matchScore: map['matchScore'] as String,
      status: map['status'] as String,
    )).toList();
  }

  Future<void> updateMatchingStatus(String id, String status) async {
    final db = await instance.database;
    await db.update('matchings', {'status': status}, where: 'id = ?', whereArgs: [id]);
  }

  Future<List<ScheduleSession>> getSchedules() async {
    final db = await instance.database;
    final maps = await db.query('schedules');
    return maps.map((map) => ScheduleSession(
      id: map['id'] as String,
      title: map['title'] as String,
      dateTime: map['dateTime'] as String,
      tutorName: map['tutorName'] as String,
      studentName: map['studentName'] as String,
    )).toList();
  }

  Future<void> insertSchedule(ScheduleSession session) async {
    final db = await instance.database;
    await db.insert('schedules', {
      'id': session.id,
      'title': session.title,
      'dateTime': session.dateTime,
      'tutorName': session.tutorName,
      'studentName': session.studentName,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<List<PaymentRecord>> getPayments() async {
    final db = await instance.database;
    final maps = await db.query('payments');
    return maps.map((map) => PaymentRecord(
      id: map['id'] as String,
      payerName: map['payerName'] as String,
      amount: (map['amount'] as num).toDouble(),
      date: map['date'] as String,
      status: map['status'] as String,
    )).toList();
  }

  Future<void> insertPayment(PaymentRecord record) async {
    final db = await instance.database;
    await db.insert('payments', {
      'id': record.id,
      'payerName': record.payerName,
      'amount': record.amount,
      'date': record.date,
      'status': record.status,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<void> confirmPayment(String id) async {
    final db = await instance.database;
    await db.update('payments', {'status': 'Confirmed'}, where: 'id = ?', whereArgs: [id]);
  }

  Future<List<AdminNotification>> getNotifications() async {
    final db = await instance.database;
    final maps = await db.query('notifications', orderBy: 'timestamp DESC');
    return maps.map((map) => AdminNotification(
      id: map['id'] as String,
      title: map['title'] as String,
      message: map['message'] as String,
      recipientGroup: map['recipientGroup'] as String,
      timestamp: DateTime.parse(map['timestamp'] as String),
    )).toList();
  }

  Future<void> insertNotification(AdminNotification notif) async {
    final db = await instance.database;
    await db.insert('notifications', {
      'id': notif.id,
      'title': notif.title,
      'message': notif.message,
      'recipientGroup': notif.recipientGroup,
      'timestamp': notif.timestamp.toIso8601String(),
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<List<TutoringReport>> getReports() async {
    final db = await instance.database;
    final maps = await db.query('reports');
    return maps.map((map) => TutoringReport(
      id: map['id'] as String,
      tutorName: map['tutorName'] as String,
      studentName: map['studentName'] as String,
      subject: map['subject'] as String,
      totalHours: map['totalHours'] as int,
      period: map['period'] as String,
    )).toList();
  }
}
