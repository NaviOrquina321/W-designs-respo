import 'package:flutter/material.dart';
import 'database_helper.dart';

class StudentItem {
  final String id;
  final String name;
  final String email;
  final String gradeLevel;
  bool isValidated;

  StudentItem({
    required this.id,
    required this.name,
    required this.email,
    required this.gradeLevel,
    this.isValidated = false,
  });
}

class MatchingSession {
  final String id;
  final String studentName;
  final String tutorName;
  final String subject;
  final String matchScore;
  String status;

  MatchingSession({
    required this.id,
    required this.studentName,
    required this.tutorName,
    required this.subject,
    required this.matchScore,
    this.status = 'Pending',
  });
}

class ScheduleSession {
  final String id;
  final String title;
  final String dateTime;
  final String tutorName;
  final String studentName;

  ScheduleSession({
    required this.id,
    required this.title,
    required this.dateTime,
    required this.tutorName,
    required this.studentName,
  });
}

class PaymentRecord {
  final String id;
  final String payerName;
  final double amount;
  final String date;
  String status;

  PaymentRecord({
    required this.id,
    required this.payerName,
    required this.amount,
    required this.date,
    this.status = 'Pending',
  });
}

class AdminNotification {
  final String id;
  final String title;
  final String message;
  final String recipientGroup;
  final DateTime timestamp;

  AdminNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.recipientGroup,
    DateTime? timestamp,
  }) : timestamp = timestamp ?? DateTime.now();
}

class TutoringReport {
  final String id;
  final String tutorName;
  final String studentName;
  final String subject;
  final int totalHours;
  final String period;

  TutoringReport({
    required this.id,
    required this.tutorName,
    required this.studentName,
    required this.subject,
    required this.totalHours,
    required this.period,
  });
}

class AdminStore extends ChangeNotifier {
  List<StudentItem> _students = [];
  List<MatchingSession> _matchings = [];
  List<ScheduleSession> _schedules = [];
  List<PaymentRecord> _payments = [];
  List<AdminNotification> _notifications = [];
  List<TutoringReport> _reports = [];
  bool _isLoading = true;

  AdminStore() {
    initDatabase();
  }

  bool get isLoading => _isLoading;
  List<StudentItem> get students => List.unmodifiable(_students);
  List<MatchingSession> get matchings => List.unmodifiable(_matchings);
  List<ScheduleSession> get schedules => List.unmodifiable(_schedules);
  List<PaymentRecord> get payments => List.unmodifiable(_payments);
  List<AdminNotification> get notifications => List.unmodifiable(_notifications);
  List<TutoringReport> get reports => List.unmodifiable(_reports);

  Future<void> initDatabase() async {
    _isLoading = true;
    notifyListeners();

    try {
      _students = await DatabaseHelper.instance.getStudents();
      _matchings = await DatabaseHelper.instance.getMatchings();
      _schedules = await DatabaseHelper.instance.getSchedules();
      _payments = await DatabaseHelper.instance.getPayments();
      _notifications = await DatabaseHelper.instance.getNotifications();
      _reports = await DatabaseHelper.instance.getReports();
    } catch (e) {
      debugPrint('Admin database load error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> validateStudent(String studentId) async {
    final index = _students.indexWhere((s) => s.id == studentId);
    if (index != -1) {
      _students[index].isValidated = true;
      notifyListeners();
      await DatabaseHelper.instance.validateStudent(studentId);
    }
  }

  Future<void> approveMatching(String matchingId) async {
    final index = _matchings.indexWhere((m) => m.id == matchingId);
    if (index != -1) {
      _matchings[index].status = 'Approved';
      notifyListeners();
      await DatabaseHelper.instance.updateMatchingStatus(matchingId, 'Approved');
    }
  }

  Future<void> cancelMatching(String matchingId) async {
    final index = _matchings.indexWhere((m) => m.id == matchingId);
    if (index != -1) {
      _matchings[index].status = 'Cancelled';
      notifyListeners();
      await DatabaseHelper.instance.updateMatchingStatus(matchingId, 'Cancelled');
    }
  }

  Future<void> addSchedule(ScheduleSession schedule) async {
    _schedules.add(schedule);
    notifyListeners();
    await DatabaseHelper.instance.insertSchedule(schedule);
  }

  Future<void> addPayment(PaymentRecord payment) async {
    _payments.insert(0, payment);
    notifyListeners();
    await DatabaseHelper.instance.insertPayment(payment);
  }

  Future<void> confirmPayment(String paymentId) async {
    final index = _payments.indexWhere((p) => p.id == paymentId);
    if (index != -1) {
      _payments[index].status = 'Confirmed';
      notifyListeners();
      await DatabaseHelper.instance.confirmPayment(paymentId);
    }
  }

  Future<void> sendNotification(AdminNotification notification) async {
    _notifications.insert(0, notification);
    notifyListeners();
    await DatabaseHelper.instance.insertNotification(notification);
  }
}
